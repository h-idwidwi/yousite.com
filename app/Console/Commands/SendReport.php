<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\LogRequests;
use App\Models\ChangeLogs;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class SendReport extends Command
{
    protected $signature = 'app:send-report';
    protected $description = 'Send a report to admins';

    public function handle()
    {
        $lastRun = Cache::get('last_report_run');
        $now = Carbon::now();

        if (!$lastRun || $now->diffInHours($lastRun) >= env('SCHEDULE_REPORT', '1')) {
            Cache::put('last_report_run', $now);
            Log::info('Команда начала выполнение');
            try {
                $this->report();
                Log::info('Команда успешно завершила выполнение');
            } catch (\Exception $e) {
                Log::error('Произошла ошибка: ' . $e->getMessage());
            }
        } else {
            Log::info('Команда пропущена, так как прошло меньше 1 часа');
        }
    }

    public function report()
    {
        Log::info('Начало формирования отчета');
        $reportCreate = Carbon::now()->subHours(env('REPORT_INTERVAL_HOURS', 24));

        $method = LogRequests::select('controller_method', DB::raw('count(*) as total'))
            ->where('logs_requests.created_at', '>=', $reportCreate)
            ->groupBy('controller_method')
            ->orderByDesc('total')
            ->get();

        $entity = ChangeLogs::select('entity_type', DB::raw('count(*) as total'))
            ->where('change_logs.created_at', '>=', $reportCreate)
            ->groupBy('entity_type')
            ->orderByDesc('total')
            ->get();

        $userRequest = User::select('users.id', 'users.username', DB::raw('count(logs_requests.id) as total'))
            ->join('logs_requests', 'users.id', '=', 'logs_requests.user_id')
            ->where('logs_requests.created_at', '>=', $reportCreate)
            ->groupBy('users.id', 'users.username')
            ->orderByDesc('total')
            ->get();

        $logRequests = LogRequests::select('request_body')
            ->where('controller_method', 'login')
            ->where('logs_requests.created_at', '>=', $reportCreate)
            ->get();

        $userLoginData = [];

        foreach ($logRequests as $logRequest) {
            $requestBody = json_decode($logRequest->request_body, true);
            if (isset($requestBody['username'])) {
                $username = $requestBody['username'];
                if (!isset($userLoginData[$username])) {
                    $userLoginData[$username] = 0;
                }
                $userLoginData[$username]++;
            }
        }

        $userLogin = collect($userLoginData)->map(function ($total, $username) {
            return (object) ['username' => $username, 'total' => $total];
        })->sortByDesc('total')->values()->all();

        Log::info('User Login Data: ', $userLogin);

        $userPermissions = User::select('users.id', 'users.username', DB::raw('count(*) as total_permissions'))
            ->join('users_and_roles', 'users.id', '=', 'users_and_roles.user_id')
            ->join('roles', 'users_and_roles.role_id', '=', 'roles.id')
            ->join('roles_and_permissions', 'roles.id', '=', 'roles_and_permissions.role_id')
            ->groupBy('users.id', 'users.username')
            ->orderByDesc('total_permissions')
            ->get();

        $userChanges = ChangeLogs::select('users.id', 'users.username', DB::raw('count(change_logs.id) as total'))
            ->join('users', 'change_logs.changed_by', '=', 'users.id')
            ->where('change_logs.created_at', '>=', $reportCreate)
            ->groupBy('users.id', 'users.username')
            ->orderByDesc('total')
            ->get();

        $reportData = [
            'method' => $method,
            'entity' => $entity,
            'userRequest' => $userRequest,
            'userLogin' => $userLogin,
            'userPermissions' => $userPermissions,
            'userChanges' => $userChanges,
            'reportCreate' => $reportCreate,
            'reportGeneratedAt' => Carbon::now(),
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('report', $reportData);
        $pdfPath = 'reports/report_' . Carbon::now()->format('Y_m_d_H_i_s') . '.pdf';
        Storage::put($pdfPath, $pdf->output());

        $adminEmails = User::select('users.email')
            ->join('users_and_roles', 'users.id', '=', 'users_and_roles.user_id')
            ->where('users_and_roles.role_id', 1)
            ->pluck('email');

        $this->sendReport($adminEmails, Storage::path($pdfPath));
        Storage::delete($pdfPath);
        Log::info('Отчет сформирован и отправлен администраторам на почту!');
    }

    private function sendReport($adminEmails, $pdfPath)
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.mail.ru';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'uliana.uformat@mail.ru';
            $mail->Password   = 'mrPyCfam3gAsJ62qk9sU';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('uliana.uformat@mail.ru', 'Server');
            foreach ($adminEmails as $email) {
                $mail->addAddress($email);
            }

            $mail->isHTML(false);
            $mail->Subject = 'Report';
            $mail->Body    = 'Please? Find the attachment';
            $mail->addAttachment($pdfPath);
            $mail->send();
        } catch (Exception $e) {
            Log::error('Произошла ошибка при отправке сообщения: ' . $mail->ErrorInfo);
        }
    }
}

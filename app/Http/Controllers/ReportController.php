<?php

namespace App\Http\Controllers;

use App\Models\ChangeLogs;
use App\Models\LogRequests;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    public function report()
    {
        Log::info('Начало формирования отчета');
        $reportCreate = Carbon::now()->subHours(env('REPORT_INTERVAL_HOURS', 24));

        // Рейтинг вызываемых методов
        $method = LogRequests::select('controller_method', DB::raw('count(*) as total'))->where('created_at', '>=', $reportCreate)->groupBy('controller_method')->orderByDesc('total')->get();

        // Рейтинг редактируемых сущностей
        $entity = ChangeLogs::select('entity_type', DB::raw('count(*) as total'))->where('created_at', '>=', $reportCreate)->groupBy('entity_type')->orderByDesc('total')->get();

        // Рейтинг пользователей по запросам
        $userRequest = User::select('users.id', 'users.username', DB::raw('count(*) as total'))->join('logs_requests', 'users.id', '=', 'logs_requests.user_id')->groupBy('users.id', 'users.username')->orderByDesc('total')->get();

        // Рейтинг пользователей по авторизациям
        $userLogin = User::select('users.id', 'users.username', DB::raw('count(*) as total'))->join('logs_requests', 'users.id', '=', 'logs_requests.user_id')->groupBy('users.id', 'users.username')->where('controller_method', '=', 'login')->orderByDesc('total')->get();

        // Рейтинг пользователей по разрешениям
        $userPermissions = User::select('users.id', 'users.username', DB::raw('count(*) as total_permissions'))->join('users_and_roles', 'users.id', '=', 'users_and_roles.user_id')->join('roles', 'users_and_roles.role_id', '=', 'roles.id')->join('roles_and_permissions', 'roles.id', '=', 'roles_and_permissions.role_id')->groupBy('users.id', 'users.username')->orderByDesc('total_permissions')->get();

        // Рейтинг пользователей по изменениям
        $userChanges = User::select('users.id', 'users.username', DB::raw('count(*) as total'))->join('change_logs', 'users.id', '=', 'change_logs.changed_by')->groupBy('users.id', 'users.username')->orderByDesc('total')->get();

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

        $adminEmails = User::select('users.email')->join('users_and_roles', 'users.id', '=', 'users_and_roles.user_id')->where('users_and_roles.role_id', 1)->pluck('email');

        $this->sendReport($adminEmails, Storage::path($pdfPath));
        Storage::delete($pdfPath);
        Log::info('Отчет сформирован и отправлен администраторам на почту!');

        return response()->json(['message' => 'Отчет сформирован и отправлен администраторам на почту!']);
    }

    private function sendReport($adminEmails, $pdfPath)
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.mail.ru';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'hidwidwi@mail.ru';
            $mail->Password   = 'e7mPf9g0jVajcdk2eLSm';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('hidwidwi@mail.ru', 'Server');
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

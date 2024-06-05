<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GitController extends Controller
{
    public function startUpdate(Request $request)
    {
        $secretKey = $request->input('secret_key');
        $envSecretKey = env('SECRET_KEY');
        if ($secretKey !== $envSecretKey)
        {
            return response()->json(['error' => 'Неверный ключ!'], 403);
        }
        $lock = Cache::lock('update-git', 120);
        if (!$lock->get())
        {
            return response()->json(['message' => 'Обновление уже выполняется'], 429);
        }
        try
        {
            $this->logRequest($request);
            $this->gitUpdate();
            $lock->release();
            return response()->json(['message' => 'Обновление прошло успешно']);
        }
        catch (\Exception $e)
        {
            $lock->release();
            return response()->json(['error' => 'Ошибка при обновлении'], 500);
        }
    }

    private function logRequest(Request $request)
    {
        $logData = [
            'date' => Carbon::now(),
            'ip' => $request->ip(),
        ];
        Log::info('Процесс обновления кода', $logData);
    }

    private function gitUpdate()
    {
        $commands = [
            'git checkout main',
            'git fetch --all',
            'git reset --hard origin/main',
            'git pull origin main',
        ];
        foreach ($commands as $command) {
            Log::info("Выполнение команды: $command");
            $output = shell_exec($command);
            Log::info($output);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\DTO\LogRequestsDTO;
use App\Models\LogRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LogRequestController extends Controller
{
    public function getLogs(Request $request)
    {
        LogRequests::where('created_at', '<', Carbon::now()->subHours(73))->delete();
        $logs = LogRequests::query();
        $count = $request->input('count', 10);
        $logs = $logs->paginate($count);

        $logs->getCollection()->transform(function ($log) {
            return [
                'url' => $log->url,
                'method' => $log->method,
                'controller' => $log->controller,
                'controller_method' => $log->controller_method,
                'request_body' => $log->request_body,
                'request_headers' => $log->request_headers,
                'user_id' => $log->user_id,
                'user_ip' => $log->user_ip,
                'user_agent' => $log->user_agent,
                'response_status' => $log->response_status,
                'response_body' => $log->response_body,
                'response_headers' => $log->response_hesders,
                'called_at' => $log->called_at,
            ];
        });
        return response()->json($logs);
    }

    public function getLog($id)
    {
        LogRequests::where('created_at', '<', Carbon::now()->subHours(73))->delete();
        $log = LogRequests::findOrFail($id);
        $logDTO = new LogRequestsDTO($log->toArray());
        return response()->json($logDTO);
    }

    public function deleteLog($id)
    {
        LogRequests::where('created_at', '<', Carbon::now()->subHours(73))->delete();
        $log = LogRequests::findOrFail($id);
        $log->forceDelete();
        return response()->json(['message' => 'Лог удален']);
    }
}

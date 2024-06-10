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

        if ($request->has('filter')) {
            $filters = $request->filter;
            foreach ($filters as $filter) {
                $logs->where($filter['key'], $filter['value']);
            }
        }

        if ($request->has('sortBy')) {
            $sortsBy = $request->sortBy;
            foreach ($sortsBy as $sortBy) {
                $logs->orderBy($sortBy['key'], $sortBy['value']);
            }
        }

        $count = $request->input('count', 10);
        $logs = $logs->paginate($count);

        $logs->getCollection()->transform(function ($log) {
            return [
                'url' => $log->url,
                'controller' => $log->controller,
                'controller_method' => $log->controller_method,
                'response_status' => $log->response_status,
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

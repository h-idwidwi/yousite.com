<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChangeLogs;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ChangeLogsController extends Controller
{
    public function createLogs($entity_type, $entity_id, $before, $after, $user_id) {
        ChangeLogs::create([
            'entity_type' => $entity_type,
            'entity_id' => $entity_id,
            'before' => json_encode($before),
            'after' => json_encode($after),
            'created_by' => $user_id,
        ]);
    }

    public function getUserLogs($id) {
        $logs = ChangeLogs::where('entity_id', $id)->get();
        return response()->json($logs);
    }

    public function getRoleLogs($id) {
        $logsRole = ChangeLogs::where('entity_type', 'roles')->where('entity_id', $id)->get();

        $logsUsersAndRoles = ChangeLogs::where('entity_type', 'users_and_roles')->where('entity_id', $id)->get();

        $logsRolesAndPermissions = ChangeLogs::where('entity_type', 'roles_and_permissions')->where('entity_id', $id)->get();

        $logs = $logsRole->concat($logsUsersAndRoles)->concat($logsRolesAndPermissions);
        return response()->json($logs);
    }

    public function getPermissionLogs($id) {
        $logsPermission = ChangeLogs::where('entity_type', 'permissions')->where('entity_id', $id)->get();

        $logsRolesAndPermissions = ChangeLogs::where('entity_type', 'roles_and_permissions')->where('entity_id', $id)->get();

        $logs = $logsPermission->concat($logsRolesAndPermissions);
        return response()->json($logs);
    }

    public function restoreEntity(Request $request, $id)
    {
        $log_id = $id;
        $user = $request->user();

        DB::beginTransaction();

        try {
            $log = ChangeLogs::findOrFail($log_id);
            $table = $log->entity_type;
            $entity_id = $log->entity_id;
            $current_value = json_decode($log->after, true);
            $prev_value = json_decode($log->before, true);
            if (is_null($prev_value)) {
                DB::table($table)->where('id', $entity_id)->delete();
                $this->createLogs($table, $entity_id, $current_value, null, $user->id);
            } else if (is_null($current_value)) {
                DB::table($table)->insert(array_merge($prev_value, ['id' => $entity_id]));
                $this->createLogs($table, $entity_id, null, $prev_value, $user->id);
            } else {
                if (isset($prev_value['created_at']) && isset($prev_value['updated_at'])) {
                    $prev_value['created_at'] = Carbon::createFromFormat('Y-m-d\TH:i:s.u\Z', $prev_value['created_at'])->format('Y-m-d H:i:s');
                    $prev_value['updated_at'] = Carbon::createFromFormat('Y-m-d\TH:i:s.u\Z', $prev_value['updated_at'])->format('Y-m-d H:i:s');
                }
                DB::table($table)->where('id', $entity_id)->update($prev_value);
                $this->createLogs($table, $entity_id, $current_value, $prev_value, $user->id);
            }
            DB::commit();
            return response()->json(['status' => 200]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 500, 'error' => $e->getMessage()], 500);
        }
    }

}

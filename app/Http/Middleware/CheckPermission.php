<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $actions = [
            'getUsers' => ['Guest', 'Admin', 'User'],
            'getUserRoles' => ['Admin', 'User'],
            'updateUser' => ['Admin'],
            'giveUserRoles' => ['Admin'],
            'hardDeleteRole' => ['Admin'],
            'softDeleteRole' => ['Admin'],
            'restoreDeletedRole' => ['Admin'],
            'userHardDeleteRole' => ['Admin'],
            'userSoftDeleteRole' => ['Admin'],
            'userRestoreDeletedRole' => ['Admin'],
            'me' => ['User', 'Admin'],
            'getRoles' => ['Admin'],
            'getTargetRole' => ['Admin'],
            'createRole' => ['Admin'],
            'updateRole' => ['Admin'],
            'getPermissions' => ['Admin'],
            'getTargetPermission' => ['Admin'],
            'createPermission' => ['Admin'],
            'updatePermission' => ['Admin'],
            'hardDeletePermission' => ['Admin'],
            'softDeletePermission' => ['Admin'],
            'restoreDeletedPermission' => ['Admin'],
            'getRolePermission' => ['Admin'],
            'addRolePermission' => ['Admin'],
            'hardDeleteRolePermission' => ['Admin'],
            'softDeleteRolePermission' => ['Admin'],
            'restoreDeletedRolePermission' => ['Admin'],
            'restoreEntity' => ['Admin'],
            'getUserLogs' => ['Admin'],
            'getRoleLogs' => ['Admin'],
            'getPermissionLogs' => ['Admin'],
            'getLogs' => ['Admin'],
            'getLog' => ['Admin'],
            'deleteLog' => ['Admin'],
        ];

        $route = $request->route();
        $action = explode('@', $route->getActionName())[1];

        $user = $request->user();

        if (!$user) {
            if ($action === 'getUsers') {
                return $next($request);
            } else {
                return redirect()->route('getUsers')->with('message', 'Вы не авторизованы');
            }
        } else {
            $userRoles = $user->roles()->pluck('name')->toArray();

            foreach ($actions[$action] as $actionRole) {
                if (in_array($actionRole, $userRoles)) {
                    return $next($request);
                }
            }
            return response()->json(['message' => 'Маршрут перестроен, недостаточно прав!'], 403);
        }
    }
}

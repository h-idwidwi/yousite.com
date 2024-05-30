<?php

namespace App\Http\Controllers;

use App\DTO\PermissionCreateDTO;
use App\DTO\PermissionDTO;
use App\Http\Requests\CreateRoleAndPermissionRequest;
use App\Http\Requests\UpdatePermissionRequest;
use App\Models\RolesAndPermissions;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\DTO\PermissionCollectionDTO;
use App\Models\Permission;
use App\Http\Requests\CreatePermissionRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class PermissionController extends Controller
{
    public function getPermissions(): JsonResponse
    {
        $permissions = new PermissionCollectionDTO(Permission::all());
        return response()->json($permissions->permissions);
    }

    public function getTargetPermission($id): JsonResponse
    {
        $permission = Permission::findOrFail($id);
        return response()->json($permission);
    }

    public function createPermission(CreatePermissionRequest $permissionRequest, CreateRoleAndPermissionRequest $rolePermissionRequest): JsonResponse
    {
        $user = $permissionRequest->user();

        $newPermission = Permission::create([
            'name' => $permissionRequest->input('name'),
            'description' => $permissionRequest->input('description'),
            'code' => $permissionRequest->input('code'),
            'created_by' => $user->id,
        ]);

        $role_id = $rolePermissionRequest->input('role_id');
        $permission_id = $newPermission->id;

        $count = RolesAndPermissions::where('role_id', $role_id)->where('permission_id', $permission_id)->count();
        if ($count) {
            return response()->json(['status' => 501]);
        }

        RolesAndPermissions::create([
            'role_id' => $role_id,
            'permission_id' => $permission_id,
            'created_by' => $user->id
        ]);

        return response()->json(new PermissionCreateDTO($newPermission->id, $newPermission->name, $newPermission->description, $newPermission->code, $newPermission->created_by), 201);
    }

    public function updatePermission(UpdatePermissionRequest $request, $id): JsonResponse
    {
        $permission = Permission::findOrFail($id);

        $permissionDTO = $request->createDTO($permission->id);

        $updateData = array_filter([
            'name' => $permissionDTO->name,
            'code' => $permissionDTO->code,
            'description' => $permissionDTO->description,
            'created_by' => $permissionDTO->created_by,
            'deleted_by' => $permissionDTO->deleted_by,
        ], function($value) {
            return !is_null($value);
        });

        $permission->update($updateData);

        return response()->json(new PermissionDTO($permission->id, $permission->name, $permission->description, $permission->code, $permission->created_by, $permission->deleted_by));
    }

    public function hardDeletePermission($id): JsonResponse
    {
        $permission = Permission::withTrashed()->findOrFail($id);
        $permission->forceDelete();
        return response()->json(['message' => 'Разрешение жестко удалено']);
    }

    public function softDeletePermission($id, Request $request): JsonResponse
    {
        $permission = Permission::findOrFail($id);
        $permission->deleted_by = $request->user()->id;
        $permission->save();
        $permission->deleted_at = Carbon::now();
        $permission->delete();

        return response()->json(['message' => 'Разрешение мягко удалено'], 200);
    }

    public function restoreDeletedPermission($id): JsonResponse
    {
        $permission = Permission::withTrashed()->findOrFail($id);
        $permission->restore();
        return response()->json(new PermissionDTO($permission->id, $permission->name, $permission->code, $permission->description, $permission->created_by, $permission->deleted_by));
    }
}

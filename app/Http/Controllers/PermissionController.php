<?php

namespace App\Http\Controllers;

use App\DTO\PermissionCreateDTO;
use App\DTO\PermissionDTO;
use App\Http\Requests\CreateRoleAndPermissionRequest;
use App\Http\Requests\UpdatePermissionRequest;
use App\Models\RolesAndPermissions;
use Illuminate\Http\JsonResponse;
use App\DTO\PermissionCollectionDTO;
use App\Models\Permission;
use App\Http\Requests\CreatePermissionRequest;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    protected $changeLogsController;

    public function __construct(ChangeLogsController $changeLogsController)
    {
        $this->changeLogsController = $changeLogsController;
    }

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

        DB::beginTransaction();

        try {
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

            $this->changeLogsController->createLogs('permissions', $newPermission->id, null, $newPermission, $user->id);
            DB::commit();

            return response()->json(new PermissionCreateDTO($newPermission->id, $newPermission->name, $newPermission->description, $newPermission->code, $newPermission->created_by), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updatePermission(UpdatePermissionRequest $request, $id): JsonResponse
    {
        $user = $request->user();
        $permission = Permission::findOrFail($id);

        $before = $permission->replicate();
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

        DB::beginTransaction();

        try {
            $permission->update($updateData);
            $this->changeLogsController->createLogs('permissions', $permission->id, $before, $permission, $user->id);
            DB::commit();

            return response()->json(new PermissionDTO($permission->id, $permission->name, $permission->description, $permission->code, $permission->created_by, $permission->deleted_by));
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function hardDeletePermission($id): JsonResponse
    {
        $user = request()->user();
        $permission = Permission::withTrashed()->findOrFail($id);

        DB::beginTransaction();

        try {
            $before = $permission->replicate();
            $permission->forceDelete();
            $this->changeLogsController->createLogs('permissions', $permission->id, $before, null, $user->id);
            DB::commit();

            return response()->json(['message' => 'Разрешение жестко удалено']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function softDeletePermission($id): JsonResponse
    {
        $user = request()->user();
        $permission = Permission::findOrFail($id);

        DB::beginTransaction();

        try {
            $before = $permission->replicate();
            $permission->delete();
            $this->changeLogsController->createLogs('permissions', $permission->id, $before, $permission, $user->id);
            DB::commit();

            return response()->json(['message' => 'Разрешение мягко удалено'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function restoreDeletedPermission($id): JsonResponse
    {
        $user = request()->user();
        $permission = Permission::withTrashed()->findOrFail($id);

        DB::beginTransaction();

        try {
            $before = $permission->replicate();
            $permission->restore();
            $this->changeLogsController->createLogs('permissions', $permission->id, $before, $permission, $user->id);
            DB::commit();

            return response()->json(new PermissionDTO($permission->id, $permission->name, $permission->code, $permission->description, $permission->created_by, $permission->deleted_by));
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}

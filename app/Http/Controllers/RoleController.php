<?php

namespace App\Http\Controllers;

use App\DTO\RoleAndPermissionCollectionDTO;
use App\DTO\RoleAndPermissionDTO;
use App\DTO\RoleCreateDTO;
use App\Models\Role;
use App\Http\Requests\CreateRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\DTO\RoleDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    protected $changeLogsController;

    public function __construct(ChangeLogsController $changeLogsController)
    {
        $this->changeLogsController = $changeLogsController;
    }

    public function getRoles(): JsonResponse
    {
        $roles = Role::all();
        return response()->json(new RoleAndPermissionCollectionDTO($roles));
    }

    public function getTargetRole($id): JsonResponse
    {
        $role = Role::withTrashed()->findOrFail($id);
        $rolesCollection = collect([$role]);
        return response()->json(new RoleAndPermissionDTO($rolesCollection));
    }

    public function createRole(CreateRoleRequest $request): JsonResponse
    {
        $user = $request->user();
        $roleDTO = $request->createDTO();

        DB::beginTransaction();

        try {
            $role = Role::create([
                'name' => $roleDTO->name,
                'code' => $roleDTO->code,
                'description' => $roleDTO->description,
                'created_by' => $user->id,
            ]);

            $this->changeLogsController->createLogs('roles', $role->id, null, $role, $user->id);
            DB::commit();

            return response()->json(new RoleCreateDTO($role->description, $role->name, $role->code), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateRole(UpdateRoleRequest $request, $id): JsonResponse
    {
        $user = $request->user();
        $role = Role::findOrFail($id);

        $before = $role->replicate();
        $roleDTO = $request->createDTO($role->id);

        $updateData = array_filter([
            'name' => $roleDTO->name,
            'code' => $roleDTO->code,
            'description' => $roleDTO->description,
            'created_by' => $roleDTO->created_by,
            'deleted_by' => $roleDTO->deleted_by,
        ], function($value) {
            return !is_null($value);
        });

        DB::beginTransaction();

        try {
            $role->update($updateData);
            $this->changeLogsController->createLogs('roles', $role->id, $before, $role, $user->id);
            DB::commit();

            return response()->json(new RoleDTO($role->id, $role->name, $role->description, $role->code, $role->created_by, $role->deleted_by));
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function hardDeleteRole($id): JsonResponse
    {
        $user = request()->user();
        $role = Role::withTrashed()->findOrFail($id);

        DB::beginTransaction();

        try {
            $before = $role->replicate();
            $role->forceDelete();
            $this->changeLogsController->createLogs('roles', $role->id, $before, null, $user->id);
            DB::commit();

            return response()->json('Роль ликвидирована', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function softDeleteRole($id): JsonResponse
    {
        $user = request()->user();
        $role = Role::findOrFail($id);

        DB::beginTransaction();

        try {
            $before = $role->replicate();
            $role->delete();
            $this->changeLogsController->createLogs('roles', $role->id, $before, $role, $user->id);
            DB::commit();

            return response()->json('Роль мягко удалена', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function restoreDeletedRole($id): JsonResponse
    {
        $user = request()->user();
        $role = Role::withTrashed()->findOrFail($id);

        DB::beginTransaction();

        try {
            $before = $role->replicate();
            $role->restore();
            $this->changeLogsController->createLogs('roles', $role->id, $before, $role, $user->id);
            DB::commit();

            return response()->json(new RoleDTO($role->id, $role->name, $role->code, $role->description, $role->created_by, $role->deleted_by), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}

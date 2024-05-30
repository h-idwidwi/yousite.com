<?php

namespace App\Http\Controllers;

use App\DTO\RoleAndPermissionCollectionDTO;
use App\DTO\RoleAndPermissionDTO;
use App\DTO\RoleCreateDTO;
use App\Models\Role;
use App\Http\Requests\CreateRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\DTO\RoleDTO;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class RoleController extends Controller
{
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
        $roleDTO = $request->createDTO();
        $role = Role::create([
            'name' => $roleDTO->name,
            'code' => $roleDTO->code,
            'description' => $roleDTO->description,
        ]);
        return response()->json(new RoleCreateDTO($role->description, $role->name, $role->code), 201);
    }

    public function updateRole(UpdateRoleRequest $request, $id): JsonResponse
    {
        $role = Role::findOrFail($id);

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

        $role->update($updateData);

        return response()->json(new RoleDTO($role->id, $role->name, $role->description, $role->code, $role->created_by, $role->deleted_by));
    }

    public function hardDeleteRole($id): JsonResponse
    {
        $role = Role::withTrashed()->findOrFail($id);
        $role->forceDelete();
        return response()->json('Роль ликвидирована', 201);
    }

    public function softDeleteRole($id, Request $request): JsonResponse
    {
        $role = Role::findOrFail($id);
        $role->deleted_by = $request->user()->id;
        $role->save();
        $role->deleted_at = Carbon::now();
        $role->delete();
        return response()->json('Роль мягко удалена', 201);
    }

    public function restoreDeletedRole($id): JsonResponse
    {
        $role = Role::withTrashed()->findOrFail($id);
        $role->restore();
        return response()->json(new RoleDTO($role->id, $role->name, $role->code, $role->description, $role->created_by, $role->deleted_by), 201);
    }
}

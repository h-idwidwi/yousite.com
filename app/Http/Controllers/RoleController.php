<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Http\Requests\CreateRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\DTO\RoleCollectionDTO;
use App\DTO\RoleDTO;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::all();
        return response()->json(new RoleCollectionDTO($roles));
    }

    public function store(CreateRoleRequest $request)
    {
        $roleDTO = $request->createDTO();
        $role = Role::create([
            'name' => $roleDTO->name,
            'code' => $roleDTO->code,
            'description' => $roleDTO->description,
        ]);
        return response()->json(new RoleDTO($role->id, $role->name, $role->code, $role->description), 201);
    }

    public function show(Role $role)
    {
        return response()->json(new RoleDTO($role->id, $role->name, $role->code, $role->description));
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        $roleDTO = $request->createDTO();
        $role->update([
            'name' => $roleDTO->name,
            'code' => $roleDTO->code,
            'description' => $roleDTO->description,
        ]);
        return response()->json(new RoleDTO($role->id, $role->name, $role->code, $role->description));
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return response()->json(null, 204);
    }

    public function attachPermission(Request $request, Role $role)
    {
        $role->permissions()->attach($request->input('permission_id'));
        return response()->json(null, 204);
    }

    public function detachPermission(Request $request, Role $role, $permissionId)
    {
        $role->permissions()->detach($permissionId);
        return response()->json(null, 204);
    }
}

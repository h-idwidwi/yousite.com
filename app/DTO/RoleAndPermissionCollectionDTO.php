<?php

namespace App\DTO;

use Illuminate\Support\Collection;

class RoleAndPermissionCollectionDTO
{
    public $roles = [];

    public function __construct(Collection $roles)
    {
        $this->roles = $roles->map(function ($role) {
            return [
                'role' => new RoleDTO(
                    $role->id,
                    $role->name,
                    $role->description,
                    $role->code,
                    $role->created_by,
                    $role->deleted_by
                ),
                'permissions' => $role->permissions->map(function ($permission) {
                    return new PermissionDTO(
                        $permission->id,
                        $permission->name,
                        $permission->description,
                        $permission->code,
                        $permission->created_by,
                        $permission->deleted_by
                    );
                }),
            ];
        });
    }
}

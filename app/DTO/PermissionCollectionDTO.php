<?php

namespace App\DTO;

use Illuminate\Support\Collection;

class PermissionCollectionDTO
{
    public Collection $permissions;

    public function __construct(Collection $permissions)
    {
        $this->permissions = $permissions->map(function ($permission) {
            return new PermissionDTO(
                $permission->id,
                $permission->name,
                $permission->description,
                $permission->code,
                $permission->created_by,
                $permission->deleted_by ?? null
            );
        });
    }
}

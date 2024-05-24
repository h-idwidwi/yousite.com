<?php

namespace App\DTO;

use Illuminate\Support\Collection;

class RoleCollectionDTO
{
    public Collection $roles;
    public function __construct(Collection $roles)
    {
        $this->roles = $roles->map(function ($role) {
            return new RoleDTO(
                $role->id,
                $role->name,
                $role->description,
                $role->code,
                $role->created_by,
                $role->deleted_by ?? null,
            );
        });
    }
}

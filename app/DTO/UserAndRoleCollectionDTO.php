<?php

namespace App\DTO;

class UserAndRoleCollectionDTO
{
    public $roles = [];

    public function __construct($roles)
    {
        $this->roles = $roles;
    }
}

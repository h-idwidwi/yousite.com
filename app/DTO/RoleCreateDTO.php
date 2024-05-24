<?php

namespace App\DTO;

class RoleCreateDTO
{
    public $name;
    public $description;
    public $code;

    public function __construct($name, $description, $code)
    {
        $this->name = $name;
        $this->description = $description;
        $this->code = $code;
    }
}

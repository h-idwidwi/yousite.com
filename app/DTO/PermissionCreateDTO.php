<?php

namespace App\DTO;

class PermissionCreateDTO
{
    public $id;
    public $name;
    public $description;
    public $code;
    public $created_by;


    public function __construct($id, $name, $description, $code, $created_by)
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->code = $code;
        $this->created_by = $created_by;
    }
}

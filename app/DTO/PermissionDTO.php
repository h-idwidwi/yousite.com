<?php

namespace App\DTO;

class PermissionDTO
{
    public $id;
    public $name;
    public $description;
    public $code;
    public $created_by;
    public $deleted_by;


    public function __construct($id, $name, $description, $code, $created_by, $deleted_by)
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->code = $code;
        $this->created_by = $created_by;
        $this->deleted_by = $deleted_by;
    }
}

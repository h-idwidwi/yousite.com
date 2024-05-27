<?php

namespace App\DTO;

class UpdateUserDTO
{
    public $id;
    public $username;
    public $email;
    public $password;
    public $birthday;
    public $created_at;

    public function __construct($id, $username, $email, $password, $birthday, $created_at)
    {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->birthday = $birthday;
        $this->created_at = $created_at;
    }
}

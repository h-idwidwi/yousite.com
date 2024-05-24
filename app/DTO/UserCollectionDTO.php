<?php

namespace App\DTO;

use Illuminate\Support\Collection;

class UserCollectionDTO
{
    public Collection|\Illuminate\Database\Eloquent\Collection $users;
    public function __construct(Collection $users)
    {
        $this->users = $users->map(function ($user) {
            return new UserDTO(
                $user->id,
                $user->username,
                $user->email,
                $user->birthday,
                $user->created_at
            );
        });
    }
}

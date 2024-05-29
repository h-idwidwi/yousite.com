<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'username',
        'email',
        'password',
        'birthday',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Определим отношение "многие ко многим" с моделью Role
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'users_and_roles', 'user_id', 'role_id');
    }
    public function twoFactorCodes()
    {
        return $this->hasMany(TwoFactorCode::class);
    }
}

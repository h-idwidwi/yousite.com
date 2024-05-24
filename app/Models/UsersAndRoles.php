<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UsersAndRoles extends Model
{
    use HasFactory, SoftDeletes;

    public $table = 'users_and_roles';

    protected $fillable = [
        'name',
        'user_id',
        'role_id',
        'created_by',
        'deleted_by'
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItIsNotToken extends Model
{
    use HasFactory;
    public $table = 'it_is_not_tokens';

    protected $fillable = ['user_id', 'token_id', 'it_is_not_token'];
}

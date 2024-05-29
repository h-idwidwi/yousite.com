<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class TwoFactorCode extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'code', 'expires_at'];

    /**
     * @throws \Exception
     */
    public static function generateCode()
    {
        return random_int(100000, 999999);
    }

    public static function generateExpiration()
    {
        return Carbon::now()->addMinutes(config('auth.two_factor_expires', 10));
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogRequests extends Model
{
    use HasFactory;

    protected $table = 'logs_requests';

    protected $fillable = [
        'url',
        'method',
        'controller',
        'controller_method',
        'request_body',
        'request_headers',
        'user_id',
        'user_ip',
        'user_agent',
        'response_status',
        'response_body',
        'response_headers',
        'called_at',
    ];

    protected $casts = [
        'request_body' => 'array',
        'request_headers' => 'array',
        'response_body' => 'array',
        'response_headers' => 'array',
        'called_at' => 'datetime',
    ];
}

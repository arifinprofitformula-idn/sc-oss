<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntegrationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'integration',
        'endpoint',
        'method',
        'request_payload',
        'response_body',
        'status_code',
        'duration_ms',
        'ip_address',
    ];
}

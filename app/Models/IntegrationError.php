<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntegrationError extends Model
{
    use HasFactory;

    protected $fillable = [
        'integration',
        'error_code',
        'message',
        'details',
        'status',
        'recommended_action',
    ];

    protected $casts = [
        'details' => 'array',
    ];
}

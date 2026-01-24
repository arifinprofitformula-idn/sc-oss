<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiLog extends Model
{
    protected $fillable = [
        'external_api_id',
        'status_code',
        'response_time',
        'request_payload',
        'response_payload',
        'error_message',
    ];

    public function externalApi(): BelongsTo
    {
        return $this->belongsTo(ExternalApi::class);
    }
}

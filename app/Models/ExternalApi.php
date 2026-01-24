<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExternalApi extends Model
{
    protected $fillable = [
        'name',
        'endpoint_url',
        'method',
        'parameters',
        'auth_type',
        'auth_credentials',
        'rate_limit_requests',
        'rate_limit_period',
        'is_active',
        'description',
    ];

    protected $casts = [
        'parameters' => 'array',
        'auth_credentials' => 'encrypted:array',
        'is_active' => 'boolean',
        'rate_limit_requests' => 'integer',
        'rate_limit_period' => 'integer',
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(ApiLog::class)->latest();
    }
}

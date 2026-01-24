<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'distributor_name',
        'distributor_address',
        'distributor_phone',
        'province_id',
        'city_id',
        'subdistrict_id',
        'logo_path',
        'bank_info',
        'allowed_couriers',
        'unique_code_enabled',
        'unique_code_range_start',
        'unique_code_range_end',
    ];

    protected $casts = [
        'unique_code_enabled' => 'boolean',
        'unique_code_range_start' => 'integer',
        'unique_code_range_end' => 'integer',
        'bank_info' => 'array', // Allow storing multiple bank accounts as JSON
        'allowed_couriers' => 'array',
    ];
}

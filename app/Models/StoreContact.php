<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreContact extends Model
{
    protected $fillable = [
        'store_id', 'address', 'province_id', 'city_id', 'subdistrict_id', 'postal_code',
        'phone', 'whatsapp', 'email', 'social_links',
    ];

    protected $casts = [
        'social_links' => 'array',
    ];
}


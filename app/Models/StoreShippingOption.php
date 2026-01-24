<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreShippingOption extends Model
{
    protected $fillable = [
        'store_id', 'couriers',
    ];

    protected $casts = [
        'couriers' => 'array',
    ];
}


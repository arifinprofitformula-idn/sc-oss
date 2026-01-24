<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreOperatingHour extends Model
{
    protected $fillable = [
        'store_id', 'day', 'open', 'close', 'is_closed',
    ];
}


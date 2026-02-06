<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EpiProductMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'epi_brand_id',
        'epi_level_id',
        'epi_gramasi',
        'last_synced_price',
        'last_synced_at',
        'is_active',
    ];

    protected $casts = [
        'epi_gramasi' => 'decimal:3',
        'last_synced_price' => 'decimal:2',
        'last_synced_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

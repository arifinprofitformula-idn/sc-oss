<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'image',
        'description',
        'price',
        'weight',
        'original_price',
        'duration_days',
        'benefits',
        'is_active',
    ];

    protected $casts = [
        'benefits' => 'array',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'weight' => 'integer',
        'original_price' => 'decimal:2',
        'duration_days' => 'integer',
    ];

    // Helper scope for active packages
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

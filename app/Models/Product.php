<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'brand_id',
        'category_id',
        'name',
        'slug',
        'sku',
        'description',
        'price_msrp',
        'price_silverchannel',
        'weight',
        'stock',
        'image',
        'is_active',
    ];

    protected $casts = [
        'price_msrp' => 'decimal:2',
        'price_silverchannel' => 'decimal:2',
        'weight' => 'integer',
        'is_active' => 'boolean',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function stockLogs()
    {
        return $this->hasMany(ProductStockLog::class);
    }
}

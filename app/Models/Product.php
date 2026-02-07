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
        'price_customer',
        'weight',
        'stock',
        'image',
        'is_active',
        'commission_enabled',
        'commission_type',
        'commission_value',
        'last_price_update_at',
        'price_source',
    ];

    protected $casts = [
        'price_msrp' => 'decimal:2',
        'price_silverchannel' => 'decimal:2',
        'price_customer' => 'decimal:2',
        'commission_value' => 'decimal:2',
        'weight' => 'integer',
        'is_active' => 'boolean',
        'commission_enabled' => 'boolean',
        'last_price_update_at' => 'datetime',
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

    public function packages()
    {
        return $this->belongsToMany(Package::class, 'package_products')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function epiMapping()
    {
        return $this->hasOne(EpiProductMapping::class);
    }

    public function priceHistory()
    {
        return $this->hasMany(ProductPriceHistory::class);
    }
}

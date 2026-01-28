<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;

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
        'promo_start_date',
        'promo_end_date',
        'terms',
        'commission_type',
        'commission_value',
    ];

    protected $casts = [
        'benefits' => 'array',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'weight' => 'integer',
        'original_price' => 'decimal:2',
        'duration_days' => 'integer',
        'commission_value' => 'decimal:2',
        'promo_start_date' => 'date',
        'promo_end_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($package) {
            if ($package->is_active) {
                // Deactivate other active packages
                static::where('id', '!=', $package->id)
                    ->where('is_active', true)
                    ->update(['is_active' => false]);
            }
        });

        static::saved(function ($package) {
            Cache::forget('active_silver_package');
        });
    }

    // Helper scope for active packages
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'package_products')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    /**
     * Calculate total value of bundled products
     */
    public function getProductsTotalAttribute()
    {
        return $this->products->sum(function ($product) {
            return $product->price_silverchannel * $product->pivot->quantity;
        });
    }

    /**
     * Calculate total weight of package including products
     */
    public function getTotalWeightAttribute()
    {
        $productsWeight = $this->products->sum(function ($product) {
            return $product->weight * $product->pivot->quantity;
        });

        return $this->weight + $productsWeight;
    }

    /**
     * Calculate insurance cost based on system settings
     * Only "Logam Mulia" and "Perak" categories are insurable.
     */
    public function getInsuranceCostAttribute()
    {
        // Cache setting for 1 hour to avoid repeated DB calls
        // Uses "system_setting_" prefix to match IntegrationService cache key
        $isActive = Cache::remember('system_setting_shipping_insurance_active', 3600, function () {
            $setting = SystemSetting::where('key', 'shipping_insurance_active')->first();
            return $setting ? (bool)$setting->value : false;
        });

        if (!$isActive) {
            return 0;
        }

        $percentage = Cache::remember('system_setting_shipping_insurance_percentage', 3600, function () {
            $setting = SystemSetting::where('key', 'shipping_insurance_percentage')->first();
            return $setting ? (float)$setting->value : 0;
        });

        if ($percentage <= 0) {
            return 0;
        }

        $insurableTotal = 0;

        foreach ($this->products as $product) {
            // Ensure category is loaded or accessed
            $categorySlug = $product->category ? $product->category->slug : '';
            
            // Strict category validation: Only "Logam Mulia" or "Perak" (slugs)
            if (in_array($categorySlug, ['logam-mulia', 'perak'])) {
                $insurableTotal += $product->price_silverchannel * $product->pivot->quantity;
            }
        }

        // Base package price is NOT included in insurance calculation as it's a membership fee
        
        return round($insurableTotal * ($percentage / 100));
    }

    /**
     * Get the total base price (package + products) excluding shipping/insurance
     */
    public function getBaseTotalAttribute()
    {
        return $this->price + $this->products_total;
    }
}

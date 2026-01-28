<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\Product;
use App\Models\Category;
use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Tests\TestCase;

class SilverChannelRegistrationInsuranceTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_page_shows_insurance_breakdown()
    {
        // 1. Setup Settings
        SystemSetting::create(['key' => 'shipping_insurance_active', 'value' => '1', 'group' => 'shipping']);
        SystemSetting::create(['key' => 'shipping_insurance_percentage', 'value' => '0.5', 'group' => 'shipping']); // 0.5%
        
        // 2. Setup Data
        $category = Category::create(['name' => 'Logam Mulia', 'slug' => 'logam-mulia']);
        
        $product = Product::factory()->create([
            'name' => 'Gold Bar 1g',
            'price_silverchannel' => 1000000, // 1 Million
            'weight' => 1,
            'category_id' => $category->id,
        ]);

        $package = Package::factory()->create([
            'name' => 'Starter Pack',
            'price' => 100000, // 100k Base Fee
            'is_active' => true,
        ]);

        $package->products()->attach($product->id, ['quantity' => 1]);

        // 3. Simulate Registration Data in Cache
        $token = Str::random(40);
        $cacheKey = 'silver_reg_' . $token;
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'whatsapp' => '+628123456789',
            'package_id' => $package->id,
            'shipping_cost' => 20000,
            'shipping_courier' => 'jne',
            'shipping_service' => 'REG',
            'address' => 'Jl. Test No. 123',
            'city_name' => 'Jakarta Selatan',
            'province_name' => 'DKI Jakarta',
        ];

        Cache::put($cacheKey, $data, 3600);

        // 4. Visit Checkout Page
        $response = $this->get(route('register.silver.checkout', ['token' => $token]));

        // 5. Assertions
        $response->assertStatus(200);
        
        // Check text presence
        $response->assertSee('Asuransi Pengiriman (LM)');
        $response->assertSee('Produk Tambahan (Bundling)');
        
        // Check values
        // Insurance: 0.5% of 1,000,000 = 5,000
        $response->assertSee(number_format(5000, 0, ',', '.')); 
        
        // Product Total: 1,000,000
        $response->assertSee(number_format(1000000, 0, ',', '.'));

        // Base Package: 100,000
        $response->assertSee(number_format(100000, 0, ',', '.'));

        // Total: 100k + 1M + 5k + 20k (shipping) = 1,125,000
        $response->assertSee(number_format(1125000, 0, ',', '.'));
    }

    public function test_insurance_excludes_non_precious_metals()
    {
        // 1. Setup Settings
        SystemSetting::create(['key' => 'insurance_active', 'value' => '1', 'group' => 'insurance']);
        SystemSetting::create(['key' => 'insurance_percentage', 'value' => '0.5', 'group' => 'insurance']);

        // 2. Setup Data (Non-Insured Category)
        $category = Category::create(['name' => 'Aksesoris', 'slug' => 'aksesoris']);
        
        $product = Product::factory()->create([
            'name' => 'Box',
            'price_silverchannel' => 50000,
            'category_id' => $category->id,
        ]);

        $package = Package::factory()->create([
            'price' => 100000,
            'is_active' => true,
        ]);

        $package->products()->attach($product->id, ['quantity' => 1]);

        // 3. Cache
        $token = Str::random(40);
        Cache::put('silver_reg_' . $token, [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'package_id' => $package->id,
            'shipping_cost' => 10000,
            'address' => 'Test',
            'city_name' => 'Test',
            'province_name' => 'Test',
            'whatsapp' => '+62812345678',
        ], 3600);

        // 4. Visit
        $response = $this->get(route('register.silver.checkout', ['token' => $token]));

        // 5. Assertions
        $response->assertStatus(200);
        $response->assertDontSee('Biaya Asuransi'); // Should not appear if 0
    }
}

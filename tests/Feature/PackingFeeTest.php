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

class PackingFeeTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_silver_registration_page_calculates_total_components_correctly()
    {
        // 1. Setup Packing Fee
        SystemSetting::create(['key' => 'shipping_packing_fee', 'value' => '5000', 'group' => 'shipping']);
        
        // 2. Setup Package with specific price
        $package = Package::factory()->create([
            'name' => 'Premium Pack',
            'price' => 200000,
            'is_active' => true,
        ]);

        // 3. Get page
        $response = $this->get(route('register.silver'));
        
        $response->assertStatus(200);
        
        // 4. Assert components are present in the view data/alpine config
        // Check for Packing Fee value in Alpine data
        $response->assertSee("packingFee: 5000");
        
        // Check for Package Price in HTML (formatted)
        $response->assertSee("200.000");
        
        // Check for "Biaya Packing" label
        $response->assertSee("Biaya Packing");
    }

    public function test_silver_registration_store_adds_packing_fee_to_cache()
    {
        // 1. Setup Settings
        SystemSetting::create(['key' => 'shipping_packing_fee', 'value' => '5000', 'group' => 'shipping']);

        // 2. Setup Package
        $package = Package::factory()->create([
            'name' => 'Starter Pack',
            'price' => 100000,
            'is_active' => true,
        ]);

        // 3. Post Data
        $data = [
            'name' => 'Test User',
            'nik' => '1234567890123456',
            'email' => 'test@example.com',
            'whatsapp' => '+628123456789',
            'province_id' => '1',
            'province_name' => 'Bali',
            'city_id' => '1',
            'city_name' => 'Denpasar',
            'subdistrict_id' => '1',
            'subdistrict_name' => 'Denpasar Selatan',
            'postal_code' => '80222',
            'address' => 'Jl. Test No. 123',
            'package_id' => $package->id,
            'shipping_service' => 'REG',
            'shipping_cost' => 20000,
            'shipping_courier' => 'jne',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->post(route('register.silver.store'), $data);

        // 4. Assert Redirect
        $response->assertRedirect();
        
        // 5. Check Cache
        $redirectUrl = $response->headers->get('Location');
        // URL structure: http://localhost/register-silver/checkout/{token}
        // Extract token from the end of the URL
        $parts = explode('/', $redirectUrl);
        $token = end($parts);
        
        $cachedData = Cache::get('silver_reg_' . $token);
        
        $this->assertNotNull($cachedData);
        $this->assertEquals(5000, $cachedData['packing_fee']);
    }

    public function test_silver_registration_checkout_page_shows_packing_fee()
    {
        // 1. Setup Settings
        SystemSetting::create(['key' => 'shipping_packing_fee', 'value' => '5000', 'group' => 'shipping']);
        
        // 2. Setup Data
        $package = Package::factory()->create([
            'name' => 'Starter Pack',
            'price' => 100000,
            'is_active' => true,
        ]);
        
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
            'packing_fee' => 5000, // This comes from store() usually
        ];

        Cache::put($cacheKey, $data, 3600);

        // 4. Visit Checkout Page
        $response = $this->get(route('register.silver.checkout', ['token' => $token]));

        // 5. Assertions
        $response->assertStatus(200);
        
        // Check text presence
        $response->assertSee('Biaya Packing');
        
        // Check value
        $response->assertSee(number_format(5000, 0, ',', '.')); 
        
        // Check Total Calculation
        // Base (100k) + Shipping (20k) + Packing (5k) = 125,000
        $total = 100000 + 20000 + 5000;
        $response->assertSee(number_format($total, 0, ',', '.'));
    }

    public function test_silver_registration_page_passes_packing_fee_variable()
    {
         // 1. Setup Settings
         SystemSetting::create(['key' => 'shipping_packing_fee', 'value' => '7500', 'group' => 'shipping']);
         
         // 2. Setup Package (Required to show form)
         Package::factory()->create([
             'name' => 'Starter Pack',
             'price' => 100000,
             'is_active' => true,
         ]);

         // 3. Visit Page
         $response = $this->get(route('register.silver'));
         
         $response->assertStatus(200);
         $response->assertViewHas('packingFee', 7500);
         
         // Check if the UI element exists
         $response->assertSee('Biaya Packing');
    }
}

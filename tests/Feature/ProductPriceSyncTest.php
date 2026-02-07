<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\EpiProductMapping;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class ProductPriceSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup permissions
        $role = Role::firstOrCreate(['name' => 'SUPER_ADMIN']);
        $user = User::factory()->create();
        $user->assignRole($role);
        $this->actingAs($user);

        // Enable Integration
        $service = app(\App\Services\EpiAutoPriceService::class);
        $service->updateSettings([
            'active' => true,
            'api_key' => 'test-key',
            'base_url' => 'https://api.example.com',
            'update_interval' => 60,
            'notify_email' => 'test@example.com'
        ]);
    }

    public function test_sync_price_endpoint_updates_product_prices()
    {
        // Arrange
        $product = Product::factory()->create([
            'price_silverchannel' => 100000,
            'price_customer' => 120000,
        ]);

        EpiProductMapping::create([
            'product_id' => $product->id,
            'epi_brand_id' => 1,
            'epi_level_id' => 5, // Silverchannel
            'epi_level_id_customer' => 7, // Customer
            'epi_gramasi' => 1,
            'is_active' => true
        ]);

        // Mock API response
        Http::fake([
            '*/brands/1/levels/5/price*' => Http::response(['price' => 105000], 200),
            '*/brands/1/levels/7/price*' => Http::response(['price' => 125000], 200),
        ]);

        // Act
        $response = $this->postJson(route('admin.products.sync-price', $product));

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'price_silverchannel' => 105000,
                'price_customer' => 125000,
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'price_silverchannel' => 105000,
            'price_customer' => 125000,
        ]);
    }

    public function test_sync_price_endpoint_returns_error_if_no_mapping()
    {
        $product = Product::factory()->create();

        $response = $this->postJson(route('admin.products.sync-price', $product));

        $response->assertJson([
            'success' => false,
            'message' => 'Product is not mapped to EPI APE.',
        ]);
    }

    public function test_sync_price_handles_api_failure()
    {
        $product = Product::factory()->create();
        EpiProductMapping::create([
            'product_id' => $product->id,
            'epi_brand_id' => 1,
            'epi_level_id' => 5,
            'epi_level_id_customer' => 7,
            'epi_gramasi' => 1,
            'is_active' => true
        ]);

        Http::fake([
            '*' => Http::response('Server Error', 500),
        ]);

        $response = $this->postJson(route('admin.products.sync-price', $product));

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
            ]);
    }
}

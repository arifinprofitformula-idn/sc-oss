<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\EpiProductMapping;
use App\Services\EpiAutoPriceService;
use App\Services\IntegrationService;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

class EpiApeIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup admin user with role
        $this->user = User::factory()->create();
        Role::create(['name' => 'SUPER_ADMIN', 'guard_name' => 'web']);
        $this->user->assignRole('SUPER_ADMIN');

        // Initialize IntegrationService settings in DB
        $integrationService = app(IntegrationService::class);
        $integrationService->set('epi_ape_active', 1, 'epi_ape', 'boolean');
        $integrationService->set('epi_ape_api_key', 'test_key', 'epi_ape', 'text');
        $integrationService->set('epi_ape_base_url', 'https://test.api', 'epi_ape', 'text');
        $integrationService->set('epi_ape_update_interval', 60, 'epi_ape', 'integer');
        $integrationService->set('epi_ape_notify_email', 'admin@example.com', 'epi_ape', 'text');

        $this->service = app(EpiAutoPriceService::class);
    }

    public function test_sync_product_price_updates_customer_price()
    {
        $product = Product::factory()->create([
            'price_silverchannel' => 100000,
            'price_customer' => 110000,
            'sku' => 'TEST-SKU'
        ]);

        EpiProductMapping::create([
            'product_id' => $product->id,
            'epi_brand_id' => 1,
            'epi_level_id' => 5, // Silverchannel
            'epi_level_id_customer' => 7, // Customer
            'epi_gramasi' => 1,
            'is_active' => true
        ]);

        Http::fake([
            'test.api/brands/1/levels/5/price*' => Http::response(['price' => 105000], 200),
            'test.api/brands/1/levels/7/price*' => Http::response(['price' => 115000], 200),
        ]);

        $result = $this->service->syncProductPrice($product);

        $this->assertTrue($result['success']);
        
        $product->refresh();
        $this->assertEquals(105000, $product->price_silverchannel);
        $this->assertEquals(115000, $product->price_customer);
    }

    public function test_sync_handles_api_failure_gracefully()
    {
        $product = Product::factory()->create();
        EpiProductMapping::create([
            'product_id' => $product->id,
            'epi_brand_id' => 1,
            'epi_level_id' => 5,
            'epi_gramasi' => 1,
            'is_active' => true
        ]);

        Http::fake([
            '*' => Http::response(['message' => 'Server Error'], 500),
        ]);

        $result = $this->service->syncProductPrice($product);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('500', $result['message']);
    }

    public function test_preview_price_endpoint()
    {
        $this->actingAs($this->user);

        Http::fake([
            'test.api/brands/1/levels/7/price*' => Http::response(['price' => 120000], 200),
        ]);

        $response = $this->postJson(route('admin.integrations.epi-ape.preview-price'), [
            'brand_id' => 1,
            'level_id' => 7,
            'gramasi' => 1
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'price' => 120000
                 ]);
    }

    public function test_realtime_sync_on_mapping_update()
    {
        $this->actingAs($this->user);
        $product = Product::factory()->create(['price_customer' => 0]);

        Http::fake([
            'test.api/brands/1/levels/7/price*' => Http::response(['price' => 125000], 200),
            'test.api/brands/1/levels/5/price*' => Http::response(['price' => 100000], 200),
        ]);

        $response = $this->post(route('admin.integrations.epi-ape.mapping.update'), [
            'product_id' => $product->id,
            'epi_brand_id' => 1,
            'epi_level_id' => 5,
            'epi_level_id_customer' => 7,
            'epi_gramasi' => 1,
            'is_active' => 1
        ]);

        $response->assertRedirect();
        
        $product->refresh();
        $this->assertEquals(125000, $product->price_customer);
    }
}

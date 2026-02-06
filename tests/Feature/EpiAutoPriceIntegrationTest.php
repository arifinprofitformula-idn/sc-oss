<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\EpiProductMapping;
use App\Models\Product;
use App\Models\User;
use App\Services\EpiAutoPriceService;
use App\Services\IntegrationService;
use App\Mail\EpiApeSyncReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EpiAutoPriceIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected $service;
    protected $integrationService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->integrationService = new IntegrationService();
        $this->service = new EpiAutoPriceService($this->integrationService);
        
        // Setup default settings
        $this->service->updateSettings([
            'active' => true,
            'api_key' => 'test-api-key',
            'base_url' => 'https://api.test.com',
            'update_interval' => 60,
            'notify_email' => 'admin@test.com',
        ]);
    }

    public function test_sync_prices_updates_product_price_and_logs_audit()
    {
        Mail::fake();
        
        // Create Product
        $product = Product::factory()->create([
            'sku' => 'TEST-SKU-001',
            'price_silverchannel' => 100000,
        ]);

        // Create Mapping
        EpiProductMapping::create([
            'product_id' => $product->id,
            'epi_brand_id' => 1,
            'epi_level_id' => 1,
            'is_active' => true,
        ]);

        // Mock API
        Http::fake([
            'api.test.com/brands/1/levels/1/price' => Http::response([
                'price' => 150000,
            ], 200),
        ]);

        // Run Sync
        $result = $this->service->syncPrices();

        // Assert Product Updated
        $product->refresh();
        $this->assertEquals(150000, $product->price_silverchannel);

        // Assert Audit Log Created
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'UPDATE_PRICE_EPI_APE',
            'model_type' => Product::class,
            'model_id' => $product->id,
            'new_values' => json_encode(['price_silverchannel' => 150000]),
        ]);

        // Assert Email Sent
        Mail::assertSent(EpiApeSyncReport::class, function ($mail) {
            return $mail->updates[0]['sku'] === 'TEST-SKU-001' &&
                   $mail->updates[0]['old_price'] == 100000 &&
                   $mail->updates[0]['new_price'] == 150000;
        });

        $this->assertEquals(1, $result['updated']);
        $this->assertEmpty($result['errors']);
    }

    public function test_sync_prices_handles_errors_and_sends_email()
    {
        Mail::fake();

        // Create Product & Mapping
        $product = Product::factory()->create(['price_silverchannel' => 100000]);
        EpiProductMapping::create([
            'product_id' => $product->id,
            'epi_brand_id' => 99,
            'epi_level_id' => 99,
            'is_active' => true,
        ]);

        // Mock API Error
        Http::fake([
            'api.test.com/brands/99/levels/99/price' => Http::response([], 500),
        ]);

        // Run Sync
        $result = $this->service->syncPrices();

        // Assert Product Not Updated
        $product->refresh();
        $this->assertEquals(100000, $product->price_silverchannel);

        // Assert Email Sent (with errors)
        Mail::assertSent(EpiApeSyncReport::class, function ($mail) {
            return !empty($mail->errors) && str_contains($mail->errors[0], '500');
        });

        $this->assertNotEmpty($result['errors']);
    }
}

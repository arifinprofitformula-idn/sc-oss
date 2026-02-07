<?php

namespace Tests\Unit\Services;

use App\Models\Product;
use App\Models\ProductPriceHistory;
use App\Services\EpiApePriceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class EpiApePriceServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new EpiApePriceService();
    }

    public function test_it_validates_negative_price()
    {
        $result = $this->service->processUpdate('SKU-001', -5000, now());
        $this->assertEquals('error', $result['status']);
        $this->assertEquals('Invalid price', $result['message']);
    }

    public function test_it_skips_if_product_not_found()
    {
        $result = $this->service->processUpdate('NON-EXISTENT', 10000, now());
        $this->assertEquals('skipped', $result['status']);
        $this->assertEquals('Product not found', $result['message']);
    }

    public function test_it_updates_price_successfully()
    {
        $product = Product::factory()->create([
            'sku' => 'SKU-TEST',
            'price_silverchannel' => 100000,
            'last_price_update_at' => Carbon::yesterday(),
        ]);

        $newPrice = 110000;
        $timestamp = Carbon::now();

        $result = $this->service->processUpdate('SKU-TEST', $newPrice, $timestamp);

        $this->assertEquals('success', $result['status']);
        
        $product->refresh();
        $this->assertEquals($newPrice, $product->price_silverchannel);
        $this->assertEquals($timestamp->toIso8601String(), $product->last_price_update_at->toIso8601String());

        $this->assertDatabaseHas('product_price_histories', [
            'product_id' => $product->id,
            'old_price' => 100000,
            'new_price' => 110000,
            'source' => 'API_EPI',
        ]);
    }

    public function test_it_prevents_older_update()
    {
        $product = Product::factory()->create([
            'sku' => 'SKU-OLD',
            'price_silverchannel' => 100000,
            'last_price_update_at' => Carbon::now(),
        ]);

        $oldTimestamp = Carbon::yesterday();
        $result = $this->service->processUpdate('SKU-OLD', 90000, $oldTimestamp);

        $this->assertEquals('skipped', $result['status']);
        $this->assertEquals('Older or same version', $result['message']);

        $product->refresh();
        $this->assertEquals(100000, $product->price_silverchannel); // Price should not change
    }

    public function test_it_logs_significant_change()
    {
        Log::shouldReceive('info')->andReturnNull(); // Ignore normal info logs
        Log::shouldReceive('alert')->once(); // Expect alert for >10% change

        $product = Product::factory()->create([
            'sku' => 'SKU-ALERT',
            'price_silverchannel' => 100000,
        ]);

        // 20% increase
        $this->service->processUpdate('SKU-ALERT', 120000, Carbon::now());
    }
}

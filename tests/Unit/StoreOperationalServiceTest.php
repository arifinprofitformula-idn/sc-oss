<?php

namespace Tests\Unit;

use App\Models\Store;
use App\Services\StoreOperationalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class StoreOperationalServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that add to cart is enabled when store is open (no schedule).
     */
    public function test_add_to_cart_enabled_when_store_open_no_schedule()
    {
        $store = Store::factory()->create([
            'is_open' => true,
            'operating_hours' => null, // No specific schedule
        ]);

        $service = new StoreOperationalService();
        // Reflection to access protected method if needed, but we can test via getStatus/refreshStatus
        // However, calculateStatus is protected. We should test public methods or make it testable.
        // Let's use getStatus/refreshStatus which call calculateStatus.
        
        $status = $service->refreshStatus();

        $this->assertEquals('OPEN', $status['status']);
        $this->assertTrue($status['is_open']);
        $this->assertTrue($status['can_add_to_cart']);
        $this->assertNull($status['reason']);
    }

    /**
     * Test that add to cart is disabled when store is closed (toggle off).
     */
    public function test_add_to_cart_disabled_when_store_closed_toggle()
    {
        $store = Store::factory()->create([
            'is_open' => false,
            'operating_hours' => null,
        ]);

        $service = new StoreOperationalService();
        $status = $service->refreshStatus();

        $this->assertEquals('CLOSED', $status['status']);
        $this->assertFalse($status['is_open']);
        $this->assertFalse($status['can_add_to_cart']);
        $this->assertEquals('store_closed_toggle', $status['reason']);
    }

    /**
     * Test responsiveness: cache is updated when status changes.
     */
    public function test_responsiveness_cache_updates_on_refresh()
    {
        $store = Store::factory()->create([
            'is_open' => true,
        ]);

        $service = new StoreOperationalService();
        
        // Initial state
        $status = $service->getStatus();
        $this->assertTrue($status['is_open']);

        // Change store state directly in DB
        $store->update(['is_open' => false]);

        // getStatus should still return cached 'true' if called immediately without refresh (depending on cache driver/time)
        // But here we want to test that refreshStatus() sees the change immediately.
        
        $newStatus = $service->refreshStatus();
        $this->assertFalse($newStatus['is_open']);
        $this->assertEquals('CLOSED', $newStatus['status']);
    }

    /**
     * Test visual state data consistency.
     * Although we can't test CSS classes in PHPUnit, we verify the data driving them is correct.
     */
    public function test_visual_state_data_consistency()
    {
        // Case 1: Open
        $store = Store::factory()->create(['is_open' => true]);
        $service = new StoreOperationalService();
        $status = $service->refreshStatus();
        
        // When open, can_add_to_cart should be true, which drives the "enabled" visual state
        $this->assertTrue($status['can_add_to_cart'], 'Button should be enabled (data-wise) when store is open');

        // Case 2: Closed
        $store->update(['is_open' => false]);
        $status = $service->refreshStatus();
        
        // When closed, can_add_to_cart should be false, which drives the "disabled" visual state
        $this->assertFalse($status['can_add_to_cart'], 'Button should be disabled (data-wise) when store is closed');
    }
}

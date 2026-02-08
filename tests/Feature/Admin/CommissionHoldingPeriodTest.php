<?php

namespace Tests\Feature\Admin;

use App\Events\OrderPaid;
use App\Listeners\DistributeOrderCommission;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\Commission\CommissionService;
use App\Services\IntegrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CommissionHoldingPeriodTest extends TestCase
{
    use RefreshDatabase;

    public function test_commission_distribution_uses_configured_holding_period()
    {
        // 1. Setup Data
        $referrer = User::factory()->create();
        $user = User::factory()->create(['referrer_id' => $referrer->id]);
        
        $product = Product::factory()->create([
            'price' => 100000,
            'commission_type' => 'percentage',
            'commission_value' => 10, // 10% = 10.000
            'commission_enabled' => true,
        ]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'PAID',
            'total_amount' => 100000,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 100000,
        ]);

        // 2. Mock IntegrationService to return a custom holding period (e.g., 30 days)
        $integrationService = $this->mock(IntegrationService::class);
        $integrationService->shouldReceive('get')
            ->with('commission_holding_period', 7)
            ->andReturn(30);

        // 3. Trigger Listener
        $listener = new DistributeOrderCommission(
            app(CommissionService::class),
            $integrationService
        );

        $event = new OrderPaid($order);
        $listener->handle($event);

        // 4. Verify Ledger Entry has correct available_at date
        $this->assertDatabaseHas('commission_ledgers', [
            'user_id' => $referrer->id,
            'amount' => 10000,
            'status' => 'PENDING',
        ]);

        $ledger = \App\Models\CommissionLedger::where('user_id', $referrer->id)->first();
        
        // Check if available_at is approx 30 days from now
        // We use a small delta because 'now()' might differ slightly
        $expectedDate = now()->addDays(30);
        
        $this->assertEquals(
            $expectedDate->format('Y-m-d'), 
            $ledger->available_at->format('Y-m-d')
        );
    }
}

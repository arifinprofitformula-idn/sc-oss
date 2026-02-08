<?php

namespace Tests\Feature\Admin;

use App\Models\CommissionLog;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Store;
use App\Events\OrderPaid;
use App\Listeners\DistributeOrderCommission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class CommissionDistributionTest extends TestCase
{
    use RefreshDatabase;

    protected $referrer;
    protected $silverchannel;
    protected $productPercentage;
    protected $productFixed;
    protected $productNoCommission;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Roles
        Role::firstOrCreate(['name' => 'SUPER_ADMIN', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'SILVERCHANNEL', 'guard_name' => 'web']);

        // 1. Create Referrer (The one who gets commission)
        $this->referrer = User::factory()->create(['name' => 'Referrer']);
        $this->referrer->assignRole('SILVERCHANNEL');
        // Create Store for referrer (needed for commission wallet typically, depending on logic)
        Store::factory()->create(['user_id' => $this->referrer->id]);

        // 2. Create Silverchannel (The one who buys)
        $this->silverchannel = User::factory()->create([
            'name' => 'Buyer',
            'referrer_id' => $this->referrer->id
        ]);
        $this->silverchannel->assignRole('SILVERCHANNEL');
        Store::factory()->create(['user_id' => $this->silverchannel->id]);

        // 3. Create Products
        $this->productPercentage = Product::factory()->create([
            'price_silverchannel' => 100000,
            'commission_enabled' => true,
            'commission_type' => 'percentage',
            'commission_value' => 10, // 10% = 10,000
        ]);

        $this->productFixed = Product::factory()->create([
            'price_silverchannel' => 200000,
            'commission_enabled' => true,
            'commission_type' => 'fixed',
            'commission_value' => 25000, // 25,000
        ]);

        $this->productNoCommission = Product::factory()->create([
            'price_silverchannel' => 300000,
            'commission_enabled' => false,
            'commission_type' => 'percentage',
            'commission_value' => 0,
        ]);
    }

    public function test_commission_is_distributed_correctly_when_order_is_paid()
    {
        // Create Order
        $order = Order::factory()->create([
            'user_id' => $this->silverchannel->id,
            'status' => 'PAID',
            'total_amount' => 700000,
        ]);

        // Add Items
        // 1. Percentage: 2 items * 100,000 * 10% = 20,000
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->productPercentage->id,
            'product_name' => $this->productPercentage->name,
            'price' => $this->productPercentage->price_silverchannel,
            'quantity' => 2,
            'total' => $this->productPercentage->price_silverchannel * 2,
        ]);

        // 2. Fixed: 1 item * 25,000 = 25,000
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->productFixed->id,
            'product_name' => $this->productFixed->name,
            'price' => $this->productFixed->price_silverchannel,
            'quantity' => 1,
            'total' => $this->productFixed->price_silverchannel,
        ]);

        // 3. No Commission: 1 item
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->productNoCommission->id,
            'product_name' => $this->productNoCommission->name,
            'price' => $this->productNoCommission->price_silverchannel,
            'quantity' => 1,
            'total' => $this->productNoCommission->price_silverchannel,
        ]);

        // Total Expected Commission: 20,000 + 25,000 = 45,000

        // Fire Event
        // We can manually trigger the listener or fire the event
        $listener = new DistributeOrderCommission(app(\App\Services\Commission\CommissionService::class));
        $event = new OrderPaid($order);
        $listener->handle($event);

        // Assert Commission Logs
        $this->assertDatabaseHas('commission_logs', [
            'user_id' => $this->referrer->id,
            'order_id' => $order->id,
            'product_id' => $this->productPercentage->id,
            'commission_amount' => 20000,
        ]);

        $this->assertDatabaseHas('commission_logs', [
            'user_id' => $this->referrer->id,
            'order_id' => $order->id,
            'product_id' => $this->productFixed->id,
            'commission_amount' => 25000,
        ]);

        $this->assertDatabaseMissing('commission_logs', [
            'product_id' => $this->productNoCommission->id,
        ]);

        // Assert Ledger Entry (ReferralCommission)
        $this->assertDatabaseHas('commission_ledgers', [
            'user_id' => $this->referrer->id,
            'amount' => 45000,
            'type' => 'TRANSACTION',
            'status' => 'PENDING',
        ]);
    }

    public function test_no_commission_if_no_referrer()
    {
        // Update user to have no referrer
        $this->silverchannel->update(['referrer_id' => null]);

        // Create Order
        $order = Order::factory()->create([
            'user_id' => $this->silverchannel->id,
            'status' => 'waiting_payment',
            'total_amount' => 100000,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->productPercentage->id,
            'product_name' => $this->productPercentage->name,
            'price' => $this->productPercentage->price_silverchannel,
            'quantity' => 1,
            'total' => $this->productPercentage->price_silverchannel,
        ]);

        // Fire Event
        $listener = new DistributeOrderCommission(app(\App\Services\Commission\CommissionService::class));
        $event = new OrderPaid($order);
        $listener->handle($event);

        // Assert No Logs
        $this->assertDatabaseCount('commission_logs', 0);
        $this->assertDatabaseCount('commission_ledgers', 0);
    }
}

<?php

namespace Tests\Feature;

use App\Events\OrderStatusChanged;
use App\Events\SilverchannelApproved;
use App\Models\AuditLog;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SilverchannelAutomationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create Role
        Role::firstOrCreate(['name' => 'SILVERCHANNEL']);
    }

    public function test_user_activated_when_order_paid()
    {
        Event::fake([SilverchannelApproved::class]);

        $user = User::factory()->create([
            'status' => 'WAITING_VERIFICATION',
            'name' => 'Test User',
        ]);
        $user->assignRole('SILVERCHANNEL');

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'WAITING_VERIFICATION',
        ]);

        $orderService = app(OrderService::class);
        $orderService->updateStatus($order, 'PAID', 'Payment verified', null);

        $user->refresh();
        
        $this->assertEquals('ACTIVE', $user->status);
        $this->assertNotNull($user->silver_channel_id);
        $this->assertEquals($user->silver_channel_id, $user->referral_code);

        Event::assertDispatched(SilverchannelApproved::class, function ($event) use ($user) {
            return $event->user->id === $user->id;
        });

        $this->assertDatabaseHas('audit_logs', [
            'model_type' => User::class,
            'model_id' => $user->id,
            'action' => 'AUTO_ACTIVATE_SILVERCHANNEL',
        ]);
    }

    public function test_user_rejected_when_order_cancelled()
    {
        $user = User::factory()->create([
            'status' => 'WAITING_VERIFICATION',
        ]);
        $user->assignRole('SILVERCHANNEL');

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'WAITING_VERIFICATION',
        ]);

        $orderService = app(OrderService::class);
        $orderService->updateStatus($order, 'CANCELLED', 'Order cancelled', null);

        $user->refresh();
        
        $this->assertEquals('REJECTED', $user->status);

        $this->assertDatabaseHas('audit_logs', [
            'model_type' => User::class,
            'model_id' => $user->id,
            'action' => 'AUTO_REJECT_SILVERCHANNEL',
        ]);
    }

    public function test_user_rejected_when_order_refunded()
    {
        $user = User::factory()->create([
            'status' => 'WAITING_VERIFICATION',
        ]);
        $user->assignRole('SILVERCHANNEL');

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'PAID', // Was paid
        ]);

        $orderService = app(OrderService::class);
        $orderService->updateStatus($order, 'REFUNDED', 'Order refunded', null);

        $user->refresh();
        
        $this->assertEquals('REJECTED', $user->status);
    }

    public function test_active_user_rejected_when_order_cancelled()
    {
        $user = User::factory()->create([
            'status' => 'ACTIVE',
            'silver_channel_id' => 'EPISC001',
            'referral_code' => 'EPISC001',
        ]);
        $user->assignRole('SILVERCHANNEL');

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'PAID',
        ]);

        $orderService = app(OrderService::class);
        $orderService->updateStatus($order, 'CANCELLED', 'Order cancelled', null);

        $user->refresh();
        
        $this->assertEquals('REJECTED', $user->status);
        
        $this->assertDatabaseHas('audit_logs', [
            'model_type' => User::class,
            'model_id' => $user->id,
            'action' => 'AUTO_REJECT_SILVERCHANNEL',
        ]);
    }

    public function test_rejected_user_reactivated_when_order_paid_again()
    {
        Event::fake([SilverchannelApproved::class]);

        $user = User::factory()->create([
            'status' => 'REJECTED',
        ]);
        $user->assignRole('SILVERCHANNEL');

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'CANCELLED',
        ]);

        $orderService = app(OrderService::class);
        $orderService->updateStatus($order, 'PAID', 'Payment recovered', null);

        $user->refresh();
        
        $this->assertEquals('ACTIVE', $user->status);
        Event::assertDispatched(SilverchannelApproved::class);
    }
}

<?php

namespace Tests\Feature;

use App\Events\OrderStatusChanged;
use App\Listeners\ProcessSilverchannelStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

use Spatie\Permission\Models\Role;

class SilverchannelStatusLogicTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create role if not exists
        Role::firstOrCreate(['name' => 'SILVERCHANNEL', 'guard_name' => 'web']);
    }

    /** @test */
    public function active_silverchannel_should_not_be_rejected_when_order_is_cancelled()
    {
        // 1. Create Active Silverchannel
        $user = User::factory()->create([
            'status' => 'ACTIVE',
            'silver_channel_id' => 'SC001',
            'referral_code' => 'SC001',
        ]);
        $user->assignRole('SILVERCHANNEL');

        // 2. Create Order
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'WAITING_PAYMENT',
        ]);

        // 3. Simulate Order Cancellation
        $event = new OrderStatusChanged($order, 'WAITING_PAYMENT', 'CANCELLED');
        $listener = new ProcessSilverchannelStatus();
        
        // Act
        $listener->handle($event);

        // Assert
        $user->refresh();
        
        // This is the desired behavior (fix), but currently it should fail (be REJECTED)
        // So initially we expect this to fail if the bug exists.
        $this->assertEquals('ACTIVE', $user->status, 'User status should remain ACTIVE after order cancellation');
    }

    /** @test */
    public function waiting_verification_silverchannel_should_be_rejected_when_registration_order_is_cancelled()
    {
        // This tests that we didn't break the logic for NEW registrations that haven't been approved yet.
        // If a user is WAITING_VERIFICATION (registration flow), and their order is cancelled, they MIGHT need to be rejected or stay waiting?
        // The original logic was: WAITING_VERIFICATION -> REJECTED.
        // The user request says: "Hapus atau modifikasi logic yang mengubah status silverchannel menjadi REJECTED pada saat pembatalan"
        // But specifically mentions "Saat ini terdapat bug pada logic status Silverchannels dimana ketika akun silverchannel yang berstatus ACTIVE..."
        // So for ACTIVE users, it must not change.
        // For WAITING_VERIFICATION users, usually that means they haven't paid registration fee. If they cancel, they probably should be rejected or stay waiting.
        // Let's assume for now we only protect ACTIVE users.
        
        $user = User::factory()->create([
            'status' => 'WAITING_VERIFICATION',
        ]);
        $user->assignRole('SILVERCHANNEL');

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'WAITING_PAYMENT',
        ]);

        $event = new OrderStatusChanged($order, 'WAITING_PAYMENT', 'CANCELLED');
        $listener = new ProcessSilverchannelStatus();
        
        $listener->handle($event);

        $user->refresh();
        // Original behavior preserved for non-active users?
        // User instruction: "Pastikan validasi untuk memastikan status silverchannel tetap ACTIVE jika sebelumnya sudah ACTIVE"
        // It implies we should only protect ACTIVE users.
        $this->assertEquals('REJECTED', $user->status);
    }
}

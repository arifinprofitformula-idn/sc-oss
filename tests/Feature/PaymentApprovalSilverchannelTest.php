<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PaymentApprovalSilverchannelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create roles
        Role::firstOrCreate(['name' => 'SUPER_ADMIN', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'SILVERCHANNEL', 'guard_name' => 'web']);
    }

    /** @test */
    public function payment_approval_should_activate_silverchannel()
    {
        // 1. Create Admin
        $admin = User::factory()->create();
        $admin->assignRole('SUPER_ADMIN');

        // 2. Create Silverchannel User (Waiting Verification)
        $user = User::factory()->create([
            'status' => 'WAITING_VERIFICATION',
            'silver_channel_id' => 'SC001',
        ]);
        $user->assignRole('SILVERCHANNEL');

        // 3. Create Order
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'WAITING_VERIFICATION',
            'total_amount' => 100000,
        ]);

        // 4. Create Payment
        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_number' => 'PAY-001',
            'amount' => 100000,
            'method' => 'manual_transfer',
            'status' => 'PENDING_VERIFICATION',
            'proof_file' => 'proof.jpg',
        ]);

        // 5. Admin Approves Payment
        $response = $this->actingAs($admin)->patch(route('admin.payments.verify', $payment));

        // 6. Assertions
        $response->assertRedirect();
        
        $order->refresh();
        $this->assertEquals('PAID', $order->status);

        $payment->refresh();
        $this->assertEquals('PAID', $payment->status);

        $user->refresh();
        // This assertion will fail if the bug exists
        $this->assertEquals('ACTIVE', $user->status, 'Silverchannel user should be active after payment approval');
    }
}

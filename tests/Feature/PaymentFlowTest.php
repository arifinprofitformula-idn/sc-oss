<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $silverchannel;
    protected $order;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Roles
        $roleAdmin = Role::create(['name' => 'SUPER_ADMIN']);
        $roleSC = Role::create(['name' => 'SILVERCHANNEL']);

        // Create Admin
        $this->admin = User::factory()->create(['status' => 'ACTIVE']);
        $this->admin->assignRole($roleAdmin);

        // Create Silverchannel
        $this->silverchannel = User::factory()->create(['status' => 'ACTIVE']);
        $this->silverchannel->assignRole($roleSC);

        // Create Order
        $this->order = Order::create([
            'user_id' => $this->silverchannel->id,
            'order_number' => 'ORD-TEST-001',
            'total_amount' => 100000,
            'status' => 'DRAFT',
            'shipping_address' => 'Test Address'
        ]);
    }

    public function test_silverchannel_can_view_checkout_page()
    {
        $response = $this->actingAs($this->silverchannel)
                         ->get(route('payment.checkout', $this->order));

        $response->assertStatus(200);
        $response->assertSee('IDR 100.000');
    }

    public function test_silverchannel_can_submit_manual_payment()
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('proof.jpg');

        $response = $this->actingAs($this->silverchannel)
                         ->post(route('payment.process', $this->order), [
                             'payment_method' => 'manual',
                             'proof_file' => $file
                         ]);

        $response->assertRedirect(route('silverchannel.orders.show', $this->order));
        
        $this->assertEquals('WAITING_VERIFICATION', $this->order->fresh()->status);
        
        $this->assertDatabaseHas('payments', [
            'order_id' => $this->order->id,
            'amount' => 100000,
            'status' => 'PENDING_VERIFICATION',
            'method' => 'manual_transfer'
        ]);

        Storage::disk('public')->assertExists('payment-proofs/' . $file->hashName());
    }

    public function test_admin_can_verify_payment()
    {
        // Create Payment
        $payment = Payment::create([
            'order_id' => $this->order->id,
            'payment_number' => 'PAY-TEST-001',
            'amount' => 100000,
            'method' => 'manual_transfer',
            'status' => 'PENDING_VERIFICATION',
        ]);
        $this->order->update(['status' => 'WAITING_VERIFICATION']);

        $response = $this->actingAs($this->admin)
                         ->post(route('admin.payments.verify', $payment));

        $response->assertRedirect();
        
        $this->assertEquals('PAID', $payment->fresh()->status);
        $this->assertEquals('PAID', $this->order->fresh()->status);
    }

    public function test_admin_can_reject_payment()
    {
        // Create Payment
        $payment = Payment::create([
            'order_id' => $this->order->id,
            'payment_number' => 'PAY-TEST-002',
            'amount' => 100000,
            'method' => 'manual_transfer',
            'status' => 'PENDING_VERIFICATION',
        ]);
        $this->order->update(['status' => 'WAITING_VERIFICATION']);

        $response = $this->actingAs($this->admin)
                         ->post(route('admin.payments.reject', $payment), [
                             'reason' => 'Blurry image'
                         ]);

        $response->assertRedirect();
        
        $this->assertEquals('FAILED', $payment->fresh()->status);
        $this->assertEquals('DRAFT', $this->order->fresh()->status);
    }
}

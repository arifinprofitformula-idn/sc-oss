<?php

namespace Tests\Feature\Admin;

use App\Models\CommissionLedger;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Payout;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup Admin
        $this->admin = User::factory()->create();
        $role = Role::create(['name' => 'SUPER_ADMIN']);
        $this->admin->assignRole($role);
    }

    public function test_admin_can_view_reports_page()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.reports.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.reports.index');
        $response->assertSee('Reports & Analytics');
        $response->assertSee('Total Sales (Paid)');
        $response->assertSee('Total Orders');
        $response->assertSee('Commissions Paid');
        $response->assertSee('Pending Liability');
    }

    public function test_report_stats_are_displayed_correctly()
    {
        // Seed Data
        $user = User::factory()->create();
        
        // 1. Paid Order
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'PAID', 'total_amount' => 100000]);
        Payment::create([
            'order_id' => $order->id, 
            'payment_number' => 'PAY-001', 
            'amount' => 100000, 
            'status' => 'PAID', 
            'method' => 'manual', 
            'paid_at' => now()
        ]);

        // 2. Pending Commission
        CommissionLedger::create([
            'user_id' => $user->id,
            'type' => 'TRANSACTION',
            'amount' => 5000,
            'status' => 'PENDING',
            'description' => 'Comm 1',
            'available_at' => now()->addDays(14)
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.reports.index'));

        $response->assertStatus(200);
        // Using string match for formatted numbers might depend on locale, 
        // but number_format default is usually English or user defined.
        // In the view I used number_format(..., 0, ',', '.') which is Indonesian format (dot thousand separator).
        
        $response->assertSee('100.000'); // Total Sales
        $response->assertSee('5.000');   // Pending Liability (Ledger Pending)
    }

    public function test_integrity_warning_displayed_on_discrepancy()
    {
        // Create Discrepancy: Paid Order WITHOUT Payment
        $user = User::factory()->create();
        Order::factory()->create(['user_id' => $user->id, 'status' => 'PAID', 'total_amount' => 500000]);

        $response = $this->actingAs($this->admin)->get(route('admin.reports.index'));

        $response->assertStatus(200);
        $response->assertSee('Data Integrity Warning');
        $response->assertSee('Orders vs Payments discrepancy');
    }
}

<?php

namespace Tests\Unit;

use App\Models\CommissionLedger;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Payout;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $reportService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reportService = new ReportService();
    }

    public function test_get_total_sales_calculates_correctly()
    {
        // Create orders with different statuses
        Order::factory()->create(['status' => 'PAID', 'total_amount' => 100000]);
        Order::factory()->create(['status' => 'DELIVERED', 'total_amount' => 150000]);
        Order::factory()->create(['status' => 'PENDING_PAYMENT', 'total_amount' => 200000]); // Should be ignored
        Order::factory()->create(['status' => 'CANCELLED', 'total_amount' => 50000]); // Should be ignored

        $totalSales = $this->reportService->getTotalSales();

        $this->assertEquals(250000, $totalSales);
    }

    public function test_get_total_orders_breakdown()
    {
        Order::factory()->count(2)->create(['status' => 'PAID']);
        Order::factory()->count(3)->create(['status' => 'PENDING_PAYMENT']);
        Order::factory()->count(1)->create(['status' => 'CANCELLED']);

        $result = $this->reportService->getTotalOrders();

        $this->assertEquals(6, $result['total']);
        $this->assertEquals(2, $result['breakdown']['PAID']);
        $this->assertEquals(3, $result['breakdown']['PENDING_PAYMENT']);
        $this->assertEquals(1, $result['breakdown']['CANCELLED']);
    }

    public function test_get_commissions_paid()
    {
        $user = User::factory()->create();
        
        Payout::create([
            'payout_number' => 'PO-001',
            'user_id' => $user->id,
            'amount' => 50000,
            'status' => 'PAID',
            'processed_at' => now(),
        ]);

        Payout::create([
            'payout_number' => 'PO-002',
            'user_id' => $user->id,
            'amount' => 25000,
            'status' => 'REQUESTED', // Should be ignored
        ]);

        $total = $this->reportService->getCommissionsPaid();

        $this->assertEquals(50000, $total);
    }

    public function test_get_pending_payouts_breakdown()
    {
        $user = User::factory()->create();

        // 1. Ledger Pending
        CommissionLedger::create([
            'user_id' => $user->id,
            'type' => 'TRANSACTION',
            'amount' => 10000,
            'status' => 'PENDING',
            'description' => 'Test',
            'available_at' => now()->addDays(14)
        ]);

        // 2. Ledger Available
        CommissionLedger::create([
            'user_id' => $user->id,
            'type' => 'TRANSACTION',
            'amount' => 20000,
            'status' => 'AVAILABLE',
            'description' => 'Test',
            'available_at' => now()->subDay()
        ]);

        // 3. Payout Requested
        Payout::create([
            'payout_number' => 'PO-REQ',
            'user_id' => $user->id,
            'amount' => 30000,
            'status' => 'REQUESTED',
        ]);

        $result = $this->reportService->getPendingPayouts();

        $this->assertEquals(10000, $result['ledger_pending']);
        $this->assertEquals(20000, $result['ledger_available']);
        $this->assertEquals(30000, $result['payouts_requested']);
        $this->assertEquals(60000, $result['total_pending_liability']);
    }

    public function test_check_integrity()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'PAID', 'total_amount' => 100000]);
        
        Payment::create([
            'order_id' => $order->id,
            'payment_number' => 'PAY-001',
            'amount' => 100000,
            'method' => 'manual',
            'status' => 'PAID',
            'paid_at' => now(),
        ]);

        $result = $this->reportService->checkIntegrity();

        $this->assertEquals('OK', $result['orders_vs_payments']['status']);
        $this->assertEquals(0, $result['orders_vs_payments']['diff']);
    }
}

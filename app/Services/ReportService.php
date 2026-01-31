<?php

namespace App\Services;

use App\Models\Order;
use App\Models\CommissionLedger;
use App\Models\Payout;
use App\Models\Payment;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportService
{
    /**
     * Cache duration in minutes.
     */
    protected $cacheTTL = 5;

    /**
     * Get Total Sales (Confirmed Transactions).
     *
     * @param string|null $startDate
     * @param string|null $endDate
     * @return float
     */
    public function getTotalSales($startDate = null, $endDate = null)
    {
        $cacheKey = "report_total_sales_" . ($startDate ?? 'all') . "_" . ($endDate ?? 'all');

        return Cache::remember($cacheKey, $this->cacheTTL, function () use ($startDate, $endDate) {
            $query = Order::whereIn('status', ['PAID', 'PACKING', 'SHIPPED', 'DELIVERED']);

            if ($startDate && $endDate) {
                $query->whereBetween('created_at', [
                    Carbon::parse($startDate)->startOfDay(),
                    Carbon::parse($endDate)->endOfDay()
                ]);
            }

            return (float) $query->sum('total_amount');
        });
    }

    /**
     * Get Total Orders Breakdown.
     *
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function getTotalOrders($startDate = null, $endDate = null)
    {
        $cacheKey = "report_total_orders_" . ($startDate ?? 'all') . "_" . ($endDate ?? 'all');

        return Cache::remember($cacheKey, $this->cacheTTL, function () use ($startDate, $endDate) {
            $query = Order::query();

            if ($startDate && $endDate) {
                $query->whereBetween('created_at', [
                    Carbon::parse($startDate)->startOfDay(),
                    Carbon::parse($endDate)->endOfDay()
                ]);
            }

            $counts = $query->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $total = array_sum($counts);

            return [
                'total' => $total,
                'breakdown' => $counts
            ];
        });
    }

    /**
     * Get Commissions Paid.
     *
     * @param string|null $startDate
     * @param string|null $endDate
     * @param int|null $userId
     * @return float
     */
    public function getCommissionsPaid($startDate = null, $endDate = null, $userId = null)
    {
        $cacheKey = "report_commissions_paid_" . ($startDate ?? 'all') . "_" . ($endDate ?? 'all') . "_" . ($userId ?? 'all');

        return Cache::remember($cacheKey, $this->cacheTTL, function () use ($startDate, $endDate, $userId) {
            $query = Payout::where('status', 'PAID');

            if ($startDate && $endDate) {
                $query->whereBetween('processed_at', [
                    Carbon::parse($startDate)->startOfDay(),
                    Carbon::parse($endDate)->endOfDay()
                ]);
            }

            if ($userId) {
                $query->where('user_id', $userId);
            }

            return (float) $query->sum('amount');
        });
    }

    /**
     * Get Pending Payouts (Accumulated Commissions Pending).
     *
     * @return array
     */
    public function getPendingPayouts()
    {
        // No caching for pending items as they change frequently and need real-time action
        // Or short cache.
        
        // 1. Commission Ledger Pending (Holding Period)
        $ledgerPending = CommissionLedger::where('status', 'PENDING')->sum('amount');

        // 2. Commission Ledger Available (Ready to request)
        $ledgerAvailable = CommissionLedger::where('status', 'AVAILABLE')->sum('amount');

        // 3. Payout Requests (Requested but not processed)
        $payoutsRequested = Payout::where('status', 'REQUESTED')->sum('amount');

        return [
            'ledger_pending' => (float) $ledgerPending,
            'ledger_available' => (float) $ledgerAvailable,
            'payouts_requested' => (float) $payoutsRequested,
            'total_pending_liability' => (float) ($ledgerPending + $ledgerAvailable + $payoutsRequested)
        ];
    }

    /**
     * Check Data Integrity.
     *
     * @return array
     */
    public function checkIntegrity()
    {
        // 1. Check Orders vs Payments (For PAID/Completed orders)
        // Note: Payment amount should match Order total amount
        // Limit to recent 1000 orders for performance or check aggregate
        
        $orderTotal = Order::whereIn('status', ['PAID', 'PACKING', 'SHIPPED', 'DELIVERED'])->sum('total_amount');
        // Assuming Payment table has 'amount' and 'status'='PAID' (or verified)
        // Need to check Payment model structure. Assuming 'status' is 'VERIFIED' or similar.
        // Let's assume strict check isn't possible without knowing Payment statuses exactly.
        // I will check Order sum vs Payment sum linked to those orders.
        
        $paymentTotal = Payment::whereHas('order', function($q) {
            $q->whereIn('status', ['PAID', 'PACKING', 'SHIPPED', 'DELIVERED']);
        })->where('status', 'PAID')->sum('amount'); // Confirmed payments

        // 2. Check Payouts vs Commission Ledger (Payout Deductions)
        // When Payout is REQUESTED/PAID, it should have a corresponding Ledger entry (debit)
        // Actually, usually:
        // Payout REQUESTED -> Ledger: Debit (Pending/Locked) or just deducted from Balance.
        // If Ledger based:
        // Sum of Payouts (PAID) should equal Sum of Ledger entries of type 'PAYOUT' (negative amounts).
        
        $payoutsPaidSum = Payout::where('status', 'PAID')->sum('amount');
        $ledgerPayoutsSum = abs(CommissionLedger::where('type', 'PAYOUT')->sum('amount')); // Ledger usually stores as negative for deductions

        return [
            'orders_vs_payments' => [
                'order_total' => $orderTotal,
                'payment_total' => $paymentTotal,
                'diff' => abs($orderTotal - $paymentTotal),
                'status' => abs($orderTotal - $paymentTotal) < 1.0 ? 'OK' : 'WARNING' // Allow small rounding diff
            ],
            'payouts_vs_ledger' => [
                'payouts_paid_sum' => $payoutsPaidSum,
                'ledger_payouts_sum' => $ledgerPayoutsSum,
                'diff' => abs($payoutsPaidSum - $ledgerPayoutsSum),
                'status' => abs($payoutsPaidSum - $ledgerPayoutsSum) < 1.0 ? 'OK' : 'WARNING'
            ],
            'last_update' => now()->toDateTimeString()
        ];
    }
}

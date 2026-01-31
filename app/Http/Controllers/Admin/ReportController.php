<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommissionLedger;
use App\Models\Order;
use App\Models\Payout;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    protected $reportService;

    public function __construct(\App\Services\ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Core Metrics from Service
        $totalSales = $this->reportService->getTotalSales($startDate, $endDate);
        $totalOrders = $this->reportService->getTotalOrders($startDate, $endDate);
        $commissionsPaid = $this->reportService->getCommissionsPaid($startDate, $endDate);
        $pendingPayouts = $this->reportService->getPendingPayouts();
        $integrity = $this->reportService->checkIntegrity();

        $stats = [
            'total_sales' => $totalSales,
            'total_orders' => $totalOrders,
            'total_commission_paid' => $commissionsPaid,
            'pending_payouts' => $pendingPayouts,
            'integrity' => $integrity,
        ];

        // Monthly Sales Chart Data (Last 12 months)
        $monthlySales = Order::where('status', 'PAID')
            ->select(
                DB::raw('sum(total_amount) as total'), 
                DB::raw("DATE_FORMAT(created_at,'%Y-%m') as month")
            )
            ->where('created_at', '>=', now()->subYear())
            ->groupBy('month')
            ->orderBy('month')
            ->get();
            
        return view('admin.reports.index', compact('stats', 'monthlySales', 'startDate', 'endDate'));
    }

    public function export(Request $request)
    {
        $type = $request->query('type', 'orders');
        
        if ($type === 'orders') {
            return $this->exportOrders();
        } elseif ($type === 'commissions') {
            return $this->exportCommissions();
        }
        
        return back()->with('error', 'Invalid report type.');
    }

    private function exportOrders()
    {
        $filename = "orders-report-" . date('Y-m-d') . ".csv";
        $handle = fopen('php://output', 'w');
        
        return response()->stream(function () use ($handle) {
            fputcsv($handle, ['Order Number', 'Date', 'Customer', 'Items', 'Total Amount', 'Status', 'Payment Date', 'Courier', 'Service', 'Tracking Number']);
            
            Order::with('user')->chunk(100, function($orders) use ($handle) {
                foreach ($orders as $order) {
                    fputcsv($handle, [
                        $order->order_number,
                        $order->created_at->format('Y-m-d H:i'),
                        $order->user->name,
                        $order->items_count ?? $order->items()->count(),
                        $order->total_amount,
                        $order->status,
                        $order->paid_at ? $order->paid_at->format('Y-m-d H:i') : '-',
                        $order->shipping_courier,
                        $order->shipping_service,
                        $order->shipping_tracking_number,
                    ]);
                }
            });
            
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }

    private function exportCommissions()
    {
        $filename = "commissions-report-" . date('Y-m-d') . ".csv";
        $handle = fopen('php://output', 'w');
        
        return response()->stream(function () use ($handle) {
            fputcsv($handle, ['ID', 'Date', 'User', 'Type', 'Amount', 'Status', 'Reference', 'Description']);
            
            CommissionLedger::with('user')->chunk(100, function($ledgers) use ($handle) {
                foreach ($ledgers as $ledger) {
                    fputcsv($handle, [
                        $ledger->id,
                        $ledger->created_at->format('Y-m-d H:i'),
                        $ledger->user->name,
                        $ledger->type,
                        $ledger->amount,
                        $ledger->status,
                        $ledger->reference_type . ' #' . $ledger->reference_id,
                        $ledger->description,
                    ]);
                }
            });
            
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }
}

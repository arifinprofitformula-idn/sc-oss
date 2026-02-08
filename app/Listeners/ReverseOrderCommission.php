<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Models\CommissionLedger;
use App\Models\AuditLog;
use App\Notifications\CommissionCancelledNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ReverseOrderCommission implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderStatusChanged $event): void
    {
        $order = $event->order;
        $newStatus = $order->status;

        // Check if status is one of the cancellation triggers
        if (!in_array($newStatus, ['CANCELLED', 'REFUNDED', 'RETURNED'])) {
            return;
        }

        Log::info("Processing commission reversal for Order #{$order->order_number} due to status change: {$newStatus}");

        // Find all active commissions for this order
        $commissions = CommissionLedger::where('reference_type', get_class($order))
            ->where('reference_id', $order->id)
            ->whereIn('status', ['PENDING', 'AVAILABLE']) // Only reverse unpaid commissions
            ->get();

        if ($commissions->isEmpty()) {
            Log::info("No reversible commissions found for Order #{$order->order_number}");
            return;
        }

        foreach ($commissions as $ledger) {
            DB::transaction(function () use ($ledger, $order, $newStatus) {
                // Double check status to prevent race conditions
                $ledger->refresh();
                if (!in_array($ledger->status, ['PENDING', 'AVAILABLE'])) {
                    return;
                }

                $oldStatus = $ledger->status;
                
                // Update status to CANCELLED
                $ledger->update([
                    'status' => 'CANCELLED',
                    'description' => $ledger->description . " [Cancelled due to Order {$newStatus}]"
                ]);

                // Log Audit
                AuditLog::log(
                    'COMMISSION_CANCELLED',
                    $ledger,
                    ['status' => $oldStatus],
                    ['status' => 'CANCELLED', 'reason' => "Order {$newStatus}"]
                );

                Log::info("Commission #{$ledger->id} cancelled for Order #{$order->order_number}");

                // Notify Referrer
                try {
                    $referrer = $ledger->user;
                    $reason = $this->getReasonMessage($newStatus);
                    $referrer->notify(new CommissionCancelledNotification($order, $ledger, $reason));
                } catch (\Exception $e) {
                    Log::error("Failed to send commission cancellation notification: " . $e->getMessage());
                }
            });
        }
    }

    protected function getReasonMessage(string $status): string
    {
        switch ($status) {
            case 'CANCELLED':
                return 'Pesanan dibatalkan oleh pembeli atau sistem.';
            case 'REFUNDED':
                return 'Pesanan telah dikembalikan dananya (refund).';
            case 'RETURNED':
                return 'Pesanan dikembalikan (retur).';
            default:
                return 'Status pesanan berubah menjadi ' . $status;
        }
    }
}

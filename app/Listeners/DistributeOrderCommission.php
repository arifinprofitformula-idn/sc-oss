<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Models\CommissionLog;
use App\Services\Commission\CommissionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class DistributeOrderCommission implements ShouldQueue
{
    use InteractsWithQueue;

    protected $commissionService;

    /**
     * Create the event listener.
     */
    public function __construct(CommissionService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

    /**
     * Handle the event.
     */
    public function handle(OrderPaid $event): void
    {
        $order = $event->order;
        $user = $order->user;
        
        // Ensure order is actually PAID
        if ($order->status !== 'PAID') {
            return;
        }

        // Check if user has a referrer (Upline)
        if (!$user->referrer_id) {
            return;
        }

        $referrer = $user->referrer;
        
        // Calculate Commission based on Product Settings
        $totalCommission = 0;
        $commissionLogs = [];

        // Eager load products to avoid N+1 if not already loaded
        $order->loadMissing('items.product');

        foreach ($order->items as $item) {
            $product = $item->product;
            
            if (!$product || !$product->commission_enabled) {
                continue;
            }

            $itemCommission = 0;
            $quantity = $item->quantity;
            $price = $item->price; // Price at time of purchase

            if ($product->commission_type === 'percentage') {
                // Percentage of (Price * Qty)
                // e.g. 10% of (100.000 * 2) = 20.000
                $baseAmount = $price * $quantity;
                $itemCommission = $baseAmount * ($product->commission_value / 100);
            } else {
                // Fixed amount per item * Qty
                // e.g. 5.000 * 2 = 10.000
                $itemCommission = $product->commission_value * $quantity;
            }

            if ($itemCommission > 0) {
                $totalCommission += $itemCommission;
                $commissionLogs[] = [
                    'user_id' => $referrer->id,
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'commission_amount' => $itemCommission,
                    'commission_type' => $product->commission_type,
                    'commission_value' => $product->commission_value,
                    'product_price' => $price,
                    'quantity' => $quantity,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if ($totalCommission <= 0) {
            return;
        }

        // Holding period: 14 days
        $availableAt = now()->addDays(14);

        try {
            // 1. Record Ledger Entry (Aggregated)
            $this->commissionService->recordEntry(
                $referrer,
                $totalCommission,
                'TRANSACTION',
                $order,
                "Commission for Order #{$order->order_number} from {$user->name}",
                'PENDING',
                $availableAt
            );
            
            // 2. Insert Commission Logs (Detailed)
            CommissionLog::insert($commissionLogs);

            Log::info("Transaction commission distributed for Order #{$order->order_number} to User #{$referrer->id}. Total: {$totalCommission}");

        } catch (\Exception $e) {
            Log::error("Failed to distribute commission for Order #{$order->order_number}: " . $e->getMessage());
        }
    }
}

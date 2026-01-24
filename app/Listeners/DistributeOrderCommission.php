<?php

namespace App\Listeners;

use App\Events\OrderPaid;
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
        
        // Calculate Commission (e.g., 5% of Total Amount)
        // In a real system, this might come from a configuration or product-specific rate
        $commissionRate = 0.05; 
        $amount = $order->total_amount * $commissionRate;

        // Holding period: 14 days
        $availableAt = now()->addDays(14);

        try {
            $this->commissionService->recordEntry(
                $referrer,
                $amount,
                'TRANSACTION',
                $order,
                "Commission for Order #{$order->order_number} from {$user->name}",
                'PENDING',
                $availableAt
            );
            
            Log::info("Transaction commission distributed for Order #{$order->order_number} to User #{$referrer->id}");

        } catch (\Exception $e) {
            Log::error("Failed to distribute commission for Order #{$order->order_number}: " . $e->getMessage());
        }
    }
}

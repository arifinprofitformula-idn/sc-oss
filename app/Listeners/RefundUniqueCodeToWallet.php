<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Services\Commission\CommissionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class RefundUniqueCodeToWallet implements ShouldQueue
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

        // Check if order has unique code
        if ($order->unique_code <= 0) {
            return;
        }

        $amount = $order->unique_code;

        try {
            // Check if refund already exists to prevent double refund
            $exists = $user->commissionLedgers()
                ->where('reference_type', get_class($order))
                ->where('reference_id', $order->id)
                ->where('type', 'UNIQUE_CODE_REFUND')
                ->exists();

            if ($exists) {
                return;
            }

            $this->commissionService->recordEntry(
                $user,
                $amount,
                'UNIQUE_CODE_REFUND',
                $order,
                "Refund Kode Unik Order #{$order->order_number}",
                'AVAILABLE', // Immediately available
                now()
            );
            
            Log::info("Unique code refund distributed for Order #{$order->order_number} to User #{$user->id}");

        } catch (\Exception $e) {
            Log::error("Failed to distribute unique code refund for Order #{$order->order_number}: " . $e->getMessage());
        }
    }
}

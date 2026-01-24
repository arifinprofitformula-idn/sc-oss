<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpireOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancel orders that have passed their expiration time';

    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        parent::__construct();
        $this->orderService = $orderService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for expired orders...');

        $expiredOrders = Order::where('status', OrderService::STATUS_WAITING_PAYMENT)
            ->where('expires_at', '<', now())
            ->get();

        $count = 0;
        foreach ($expiredOrders as $order) {
            try {
                $this->orderService->updateStatus(
                    $order, 
                    OrderService::STATUS_CANCELLED, 
                    'Order expired automatically by system.',
                    null // System action
                );
                
                // Return stock
                foreach ($order->items as $item) {
                    $product = $item->product;
                    if ($product) {
                        $product->increment('stock', $item->quantity);
                    }
                }
                
                $count++;
                $this->info("Order {$order->order_number} cancelled.");
            } catch (\Exception $e) {
                Log::error("Failed to expire order {$order->id}: " . $e->getMessage());
                $this->error("Failed to cancel order {$order->order_number}");
            }
        }

        $this->info("Process completed. {$count} orders cancelled.");
    }
}

<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Services\Email\EmailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\View;

class SendOrderShippedToCustomer implements ShouldQueue
{
    use InteractsWithQueue;

    protected EmailService $emails;

    public function __construct(EmailService $emails)
    {
        $this->emails = $emails;
    }

    public function handle(OrderStatusChanged $event): void
    {
        if (strtoupper($event->newStatus) !== \App\Services\OrderService::STATUS_SHIPPED) {
            return;
        }
        $order = $event->order->load('user');
        $subject = "Pesanan #{$order->order_number} Sedang Dikirim";
        $html = View::make('emails.orders.shipped', compact('order'))->render();
        $this->emails->sendRaw($order->user->email, $subject, $html, [], null, [
            'type' => 'order_shipped',
            'user_id' => $order->user_id,
            'related_type' => \App\Models\Order::class,
            'related_id' => $order->id,
        ]);
    }
}

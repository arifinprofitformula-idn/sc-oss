<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Services\Email\EmailService;
use App\Services\IntegrationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendTransactionCommissionEmail implements ShouldQueue
{
    use InteractsWithQueue;

    protected EmailService $emails;
    protected IntegrationService $integration;

    public function __construct(EmailService $emails, IntegrationService $integration)
    {
        $this->emails = $emails;
        $this->integration = $integration;
    }

    public function handle(OrderStatusChanged $event): void
    {
        $order = $event->order;
        if (strtoupper($event->newStatus) !== 'DELIVERED') {
            return;
        }
        $buyer = $order->user;
        if (!$buyer || !$buyer->referrer_id) {
            return;
        }
        $referrer = $buyer->referrer;
        $holdingDays = (int) $this->integration->get('commission_holding_period', 7);

        $this->emails->send('commission_transaction_pending', $referrer, [
            'order_number' => $order->order_number,
            'buyer_name' => $buyer->name,
            'holding_days' => $holdingDays,
            'available_date' => now()->addDays($holdingDays)->format('d M Y'),
        ], 'ID');
    }
}


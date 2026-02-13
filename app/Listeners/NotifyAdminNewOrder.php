<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Services\Email\EmailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\View;

class NotifyAdminNewOrder implements ShouldQueue
{
    use InteractsWithQueue;

    protected EmailService $emails;

    public function __construct(EmailService $emails)
    {
        $this->emails = $emails;
    }

    public function handle(OrderCreated $event): void
    {
        $order = $event->order->load('items', 'user');
        $subject = "[SC-OSS] Order Baru #{$order->order_number} — {$order->user->name}";
        $html = View::make('emails.admin.new_order', compact('order'))->render();
        $recipients = config('scoss.admin_order_emails') ?? [];
        foreach ($recipients as $email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            $this->emails->sendRaw($email, $subject, $html, []);
        }
    }
}

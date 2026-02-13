<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Services\Email\EmailService;
use App\Services\Pdf\PdfServiceInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\View;

class SendInvoicePaidToCustomer implements ShouldQueue
{
    use InteractsWithQueue;

    protected EmailService $emails;
    protected PdfServiceInterface $pdf;

    public function __construct(EmailService $emails, PdfServiceInterface $pdf)
    {
        $this->emails = $emails;
        $this->pdf = $pdf;
    }

    public function handle(OrderStatusChanged $event): void
    {
        if (strtoupper($event->newStatus) !== \App\Services\OrderService::STATUS_PAID) {
            return;
        }
        $order = $event->order->load('items', 'user');
        $subject = "Pembayaran Diterima — Invoice #{$order->order_number} Lunas";
        $html = View::make('emails.orders.invoice_paid', compact('order'))->render();
        $attachments = [];
        $att = $this->pdf->generateOrderInvoice($order);
        if ($att) {
            $attachments[] = $att;
        }
        $this->emails->sendRaw($order->user->email, $subject, $html, $attachments, null, [
            'type' => 'order_invoice_paid',
            'user_id' => $order->user_id,
            'related_type' => \App\Models\Order::class,
            'related_id' => $order->id,
        ]);
    }
}

<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Services\Email\EmailService;
use App\Services\Pdf\PdfServiceInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\View;

class SendInvoiceUnpaidToCustomer implements ShouldQueue
{
    use InteractsWithQueue;

    protected EmailService $emails;
    protected PdfServiceInterface $pdf;

    public function __construct(EmailService $emails, PdfServiceInterface $pdf)
    {
        $this->emails = $emails;
        $this->pdf = $pdf;
    }

    public function handle(OrderCreated $event): void
    {
        $order = $event->order->load('items', 'user');
        if (($order->payment_method ?? 'transfer') !== 'transfer') {
            return;
        }
        if (strtoupper($order->status) !== \App\Services\OrderService::STATUS_WAITING_PAYMENT) {
            return;
        }
        $subject = "Invoice #{$order->order_number} — Menunggu Pembayaran";
        $html = View::make('emails.orders.invoice_unpaid', compact('order'))->render();
        $attachments = [];
        $att = $this->pdf->generateOrderInvoice($order);
        if ($att) {
            $attachments[] = $att;
        }
        $this->emails->sendRaw($order->user->email, $subject, $html, $attachments, null, [
            'type' => 'order_invoice_unpaid',
            'user_id' => $order->user_id,
            'related_type' => \App\Models\Order::class,
            'related_id' => $order->id,
        ]);
    }
}

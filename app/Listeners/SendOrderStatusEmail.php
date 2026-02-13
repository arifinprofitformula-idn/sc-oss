<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Services\Email\EmailService;
use App\Services\Pdf\PdfServiceInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendOrderStatusEmail implements ShouldQueue
{
    use InteractsWithQueue;

    protected EmailService $emails;
    protected ?PdfServiceInterface $pdf;

    public function __construct(EmailService $emails, ?PdfServiceInterface $pdf = null)
    {
        $this->emails = $emails;
        $this->pdf = $pdf;
    }

    public function handle(OrderStatusChanged $event): void
    {
        $order = $event->order;
        $user = $order->user;

        if (!$user) {
            return;
        }

        $status = strtoupper($event->newStatus);

        if (in_array($status, ['PAID', 'SHIPPED'], true)) {
            return;
        }

        $data = [
            'order_number' => $order->order_number,
            'order_date' => optional($order->created_at)->format('d M Y H:i'),
            'total_amount' => 'Rp ' . number_format((float) $order->total_amount, 0, ',', '.'),
            'payment_method' => $order->payment_method ?? 'transfer',
            'shipping_courier' => $order->shipping_courier ?? '-',
            'shipping_estimation' => $order->payload['shipping_detail']['etd'] ?? ($order->shipping_service ?? ''),
            'tracking_url' => url('/silverchannel/orders/' . $order->id),
        ];

        $attachments = [];
        if ($this->pdf && $status === 'PAID') {
            try {
                $att = $this->pdf->generateOrderInvoice($order);
                if ($att) {
                    $attachments[] = $att;
                }
            } catch (\Throwable $e) {
                Log::warning('Invoice PDF generation failed: ' . $e->getMessage());
            }
        }

        $this->emails->orderStatusUpdate($user, $status, $data, $attachments);
    }
}

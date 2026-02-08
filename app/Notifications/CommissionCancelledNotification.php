<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Order;
use App\Models\CommissionLedger;

class CommissionCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;
    protected $ledger;
    protected $reason;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order, CommissionLedger $ledger, string $reason)
    {
        $this->order = $order;
        $this->ledger = $ledger;
        $this->reason = $reason;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Pembatalan Komisi - Order #' . $this->order->order_number)
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Kami ingin menginformasikan bahwa komisi Anda sebesar Rp ' . number_format($this->ledger->amount, 0, ',', '.') . ' untuk pesanan #' . $this->order->order_number . ' telah dibatalkan.')
            ->line('Alasan pembatalan: ' . $this->reason)
            ->line('Status Pesanan Saat Ini: ' . $this->order->status)
            ->line('Jika Anda memiliki pertanyaan, silakan hubungi tim support kami.')
            ->action('Lihat Dashboard', url('/dashboard'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'commission_cancelled',
            'title' => 'Komisi Dibatalkan',
            'message' => 'Komisi Rp ' . number_format($this->ledger->amount, 0, ',', '.') . ' untuk Order #' . $this->order->order_number . ' dibatalkan. Alasan: ' . $this->reason,
            'order_id' => $this->order->id,
            'ledger_id' => $this->ledger->id,
        ];
    }
}

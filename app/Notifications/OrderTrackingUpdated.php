<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderTrackingUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;
    protected $trackingNumber;
    protected $courier;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order, string $trackingNumber, string $courier)
    {
        $this->order = $order;
        $this->trackingNumber = $trackingNumber;
        $this->courier = $courier;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Update Resi Pengiriman Order #' . $this->order->order_number)
                    ->greeting('Halo ' . $notifiable->name . ',')
                    ->line('Pesanan Anda #' . $this->order->order_number . ' telah dikirim.')
                    ->line('Kurir: ' . strtoupper($this->courier))
                    ->line('No. Resi: ' . $this->trackingNumber)
                    ->action('Lacak Pesanan', route('silverchannel.orders.show', $this->order->id))
                    ->line('Terima kasih telah berbelanja di EPI Order System.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'tracking_number' => $this->trackingNumber,
            'courier' => $this->courier,
            'message' => 'Resi pengiriman untuk Order #' . $this->order->order_number . ' telah diperbarui: ' . $this->trackingNumber
        ];
    }
}

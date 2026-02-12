<?php

namespace App\Notifications;

use App\Models\EmailTemplate;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

use App\Services\Email\EmailRoutingService;

class OrderConfirmation extends Notification implements ShouldQueue
{
    use Queueable;

    public $order;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $routing = app(EmailRoutingService::class);
        $mailer = $routing->getMailer('order');

        $template = EmailTemplate::where('key', 'order_confirmation')->first();

        if (!$template) {
            Log::warning("Email template 'order_confirmation' not found. Sending default notification.");
            $mail = (new MailMessage)
                ->subject('Order Confirmation #' . $this->order->order_number)
                ->line('Your order has been confirmed.')
                ->action('View Order', url('/orders/' . $this->order->id));
            
            if ($mailer) {
                $mail->mailer($mailer);
            }
            return $mail;
        }

        // Prepare variables
        $productList = '';
        foreach ($this->order->items as $item) {
            $productList .= '<tr>';
            $productList .= '<td style="padding: 10px; border-bottom: 1px solid #e5e7eb;"><strong>' . e($item->product_name) . '</strong></td>';
            $productList .= '<td style="padding: 10px; border-bottom: 1px solid #e5e7eb; text-align: center;">' . $item->quantity . '</td>';
            $productList .= '<td style="padding: 10px; border-bottom: 1px solid #e5e7eb; text-align: right;">Rp ' . number_format($item->total, 0, ',', '.') . '</td>';
            $productList .= '</tr>';
        }

        $variables = [
            '{{logo_url}}' => asset('images/logo.png'), // Ensure this exists or use a placeholder
            '{{app_name}}' => config('app.name'),
            '{{customer_name}}' => $notifiable->name,
            '{{order_number}}' => $this->order->order_number,
            '{{order_date}}' => $this->order->created_at->format('d M Y, H:i'),
            '{{product_list}}' => $productList,
            '{{total_amount}}' => 'Rp ' . number_format($this->order->total_amount, 0, ',', '.'),
            '{{payment_method}}' => $this->order->payment_method ?? 'Bank Transfer',
            '{{shipping_courier}}' => $this->order->shipping_courier ?? '-',
            '{{shipping_estimation}}' => '-', // You might want to pass this if available
            '{{tracking_url}}' => url('/orders/' . $this->order->id), // Fallback to order detail
            '{{support_email}}' => config('mail.from.address'),
            '{{support_phone}}' => '+62 812 3456 7890', // Should be in config
            '{{year}}' => date('Y'),
        ];

        $subject = $template->subject;
        $body = $template->body;

        foreach ($variables as $key => $value) {
            $subject = str_replace($key, $value, $subject);
            $body = str_replace($key, $value, $body);
        }

        $mail = (new MailMessage)
            ->subject($subject)
            ->view('emails.html-template', ['content' => $body]);

        if ($mailer) {
            $mail->mailer($mailer);
        }

        return $mail;
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
        ];
    }
}

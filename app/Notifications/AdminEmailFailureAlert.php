<?php
declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminEmailFailureAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected int $failCount,
        protected \Carbon\CarbonInterface $windowStart,
        protected \Carbon\CarbonInterface $windowEnd
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'email_failure_alert',
            'fail_count' => $this->failCount,
            'window_start' => $this->windowStart->toDateTimeString(),
            'window_end' => $this->windowEnd->toDateTimeString(),
            'message' => "Terdeteksi {$this->failCount} kegagalan pengiriman email dalam 10 menit terakhir",
        ];
    }
}

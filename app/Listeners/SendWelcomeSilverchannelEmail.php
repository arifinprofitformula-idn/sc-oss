<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\SilverchannelApproved;
use App\Services\Email\EmailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendWelcomeSilverchannelEmail implements ShouldQueue
{
    use InteractsWithQueue;

    protected EmailService $emails;

    public function __construct(EmailService $emails)
    {
        $this->emails = $emails;
    }

    public function handle(SilverchannelApproved $event): void
    {
        $user = $event->user;
        $order = \App\Models\Order::where('user_id', $user->id)
            ->where('order_number', 'like', 'REG-%')
            ->latest('id')
            ->first();

        $data = [
            'login_url' => url('/login'),
            'order_number' => $order?->order_number ?? '',
            'silver_channel_id' => $user->silver_channel_id ?? '',
            'referral_code' => $user->referral_code ?? '',
        ];

        $this->emails->send('registration_approved', $user, $data, 'ID');
    }
}

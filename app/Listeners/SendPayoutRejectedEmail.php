<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\PayoutRejected;
use App\Services\Email\EmailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendPayoutRejectedEmail implements ShouldQueue
{
    use InteractsWithQueue;

    protected EmailService $emails;

    public function __construct(EmailService $emails)
    {
        $this->emails = $emails;
    }

    public function handle(PayoutRejected $event): void
    {
        $payout = $event->payout;
        $user = $payout->user;

        $this->emails->send('payout_rejected', $user, [
            'payout_number' => $payout->payout_number,
            'amount' => 'Rp ' . number_format((float) $payout->amount, 0, ',', '.'),
            'reason' => $event->reason,
        ], 'ID');
    }
}


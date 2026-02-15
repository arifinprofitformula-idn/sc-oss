<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\PayoutRequested;
use App\Models\User;
use App\Services\Email\EmailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\View;

class NotifyAdminPayoutRequested implements ShouldQueue
{
    use InteractsWithQueue;

    protected EmailService $emails;

    public function __construct(EmailService $emails)
    {
        $this->emails = $emails;
    }

    public function handle(PayoutRequested $event): void
    {
        $payout = $event->payout->load('user');
        $user = $payout->user;

        $subject = "[SC-OSS] Withdraw Requested {$payout->payout_number} — {$user->name}";
        $html = View::make('emails.admin.payout-requested', compact('payout', 'user'))->render();

        $recipients = config('scoss.admin_payout_emails') ?? [];
        if (empty($recipients) || !is_array($recipients)) {
            $recipients = User::role('SUPER_ADMIN')->pluck('email')->all();
        }

        foreach ($recipients as $email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            $this->emails->sendRaw($email, $subject, $html, []);
        }
    }
}

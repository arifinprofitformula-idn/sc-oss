<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\SilverchannelRegistered;
use App\Services\Email\EmailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\View;

class NotifyAdminSilverchannelRegistered implements ShouldQueue
{
    use InteractsWithQueue;

    protected EmailService $emails;

    public function __construct(EmailService $emails)
    {
        $this->emails = $emails;
    }

    public function handle(SilverchannelRegistered $event): void
    {
        $user = $event->user;
        $subject = "[SC-OSS] Pendaftaran Silverchannel Baru — {$user->name}";
        $html = View::make('emails.admin.new_silverchannel', compact('user'))->render();
        $recipients = config('scoss.admin_order_emails') ?? [];
        foreach ($recipients as $email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            $this->emails->sendRaw($email, $subject, $html, []);
        }
    }
}

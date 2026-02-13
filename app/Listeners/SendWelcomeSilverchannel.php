<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\SilverchannelRegistered;
use App\Services\Email\EmailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\View;

class SendWelcomeSilverchannel implements ShouldQueue
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
        $subject = "Selamat Datang di Silverchannel — Akun Anda Aktif";
        $html = View::make('emails.silverchannel.welcome', compact('user'))->render();
        $this->emails->sendRaw($user->email, $subject, $html, []);
    }
}

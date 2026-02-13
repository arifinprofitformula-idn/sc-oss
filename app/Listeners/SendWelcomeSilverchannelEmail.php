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
        $data = [
            'login_url' => url('/login'),
        ];
        // Template key/type: welcome_silverchannel
        $this->emails->send('welcome_silverchannel', $user, $data, 'ID');
    }
}


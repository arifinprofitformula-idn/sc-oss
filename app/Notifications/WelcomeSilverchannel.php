<?php

namespace App\Notifications;

use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

use App\Services\Email\EmailRoutingService;

class WelcomeSilverchannel extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
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
        $mailer = $routing->getMailer('auth');

        $template = EmailTemplate::where('key', 'welcome_silverchannel')->first();

        if (!$template) {
            Log::warning("Email template 'welcome_silverchannel' not found. Sending default notification.");
            $mail = (new MailMessage)
                ->subject('Welcome to ' . config('app.name'))
                ->greeting('Hello ' . $notifiable->name . '!')
                ->line('Welcome to our platform. We are excited to have you on board.')
                ->action('Login to Dashboard', url('/login'));
            
            if ($mailer) {
                $mail->mailer($mailer);
            }
            return $mail;
        }

        $variables = [
            '{{logo_url}}' => asset('images/logo.png'),
            '{{app_name}}' => config('app.name'),
            '{{name}}' => $notifiable->name,
            '{{login_url}}' => url('/login'),
            '{{support_email}}' => config('mail.from.address'),
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
            //
        ];
    }
}

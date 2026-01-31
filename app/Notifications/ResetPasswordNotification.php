<?php

namespace App\Notifications;

use App\Models\EmailTemplate;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;

class ResetPasswordNotification extends ResetPassword implements ShouldQueue
{
    use Queueable;

    public function toMail($notifiable)
    {
        $template = EmailTemplate::where('key', 'forgot_password')->first();
        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        Log::info("Sending Password Reset to: {$notifiable->email}");

        if ($template) {
            $content = $template->body;
            $variables = [
                '{{name}}' => $notifiable->name,
                '{{reset_url}}' => $resetUrl,
                '{{count}}' => config('auth.passwords.users.expire'),
                '{{app_name}}' => config('app.name'),
                '{{user_name}}' => $notifiable->name, // Alias
                '{{reset_link}}' => $resetUrl, // Alias
                '{{expiry_time}}' => config('auth.passwords.users.expire') . ' minutes',
            ];

            foreach ($variables as $key => $value) {
                $content = str_replace($key, $value, $content);
            }

            return (new MailMessage)
                ->subject($template->subject)
                ->view('emails.html-template', ['content' => $content]);
        }

        // Fallback to default
        return parent::toMail($notifiable);
    }
}

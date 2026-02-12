<?php

namespace App\Notifications;

use App\Models\EmailTemplate;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;

use App\Services\Email\EmailRoutingService;

class ResetPasswordNotification extends ResetPassword implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var array<int>
     */
    public $backoff = [10, 60, 360];

    public function toMail($notifiable)
    {
        try {
            $routing = app(EmailRoutingService::class);
            $mailer = $routing->getMailer('auth');
            
            $template = EmailTemplate::where('key', 'forgot_password')->first();
            $resetUrl = url(route('password.reset', [
                'token' => $this->token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            Log::info("Preparing Password Reset Email for: {$notifiable->email}");

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
                    '{{logo_url}}' => asset('images/logo.png'), // Default logo path
                    '{{support_email}}' => config('mail.from.address'), // Fallback to from address
                    '{{year}}' => date('Y'),
                ];

                foreach ($variables as $key => $value) {
                    $content = str_replace($key, $value, $content);
                }

                $mail = (new MailMessage)
                    ->subject($template->subject)
                    ->view('emails.html-template', ['content' => $content]);
                
                if ($mailer) {
                    $mail->mailer($mailer);
                }
                
                return $mail;
            }

            Log::warning("Email template 'forgot_password' not found. Using default Laravel reset template.");
            
            // Fallback to default
            $mail = parent::toMail($notifiable);
            if ($mailer) {
                $mail->mailer($mailer);
            }
            return $mail;

        } catch (\Exception $e) {
            Log::error("Error generating ResetPasswordNotification: " . $e->getMessage());
            throw $e; // Re-throw to trigger queue retry
        }
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        Log::critical("ResetPasswordNotification completely failed after retries. Error: " . $exception->getMessage());
        // Optionally: Send a system alert to admin here
    }
}

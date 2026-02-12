<?php
declare(strict_types=1);

namespace App\Services\Email;

use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use App\Jobs\SendEmailJob;

class EmailService
{
    protected EmailRoutingService $routing;
    protected TemplateEngine $templates;
    protected RateLimiterService $rateLimiter;

    public function __construct(
        EmailRoutingService $routing,
        TemplateEngine $templates,
        RateLimiterService $rateLimiter
    ) {
        $this->routing = $routing;
        $this->templates = $templates;
        $this->rateLimiter = $rateLimiter;
    }

    public function resetPassword(User $user, string $token, int $expiryMinutes = 60): void
    {
        $resetUrl = url(route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ], false));
        $signed = url()->signedRoute('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]);

        $data = [
            'user_name' => $user->name,
            'reset_url' => $signed ?? $resetUrl,
            'expiry_time' => "{$expiryMinutes} minutes",
            'support_email' => config('mail.from.address'),
            'count' => $expiryMinutes,
            'url' => $signed ?? $resetUrl,
        ];
        // Use 'forgot_password' to match existing seeder/template key
        $this->send('forgot_password', $user, $data, 'ID');
    }

    public function registrationConfirm(User $user, array $attachments = []): void
    {
        $data = [
            'user_name' => $user->name,
            'activation_link' => route('verification.notice'),
        ];
        $this->send('registration_confirm', $user, $data, 'ID', $attachments);
    }

    public function orderNotification(User $user, array $orderData, array $attachments = []): void
    {
        $data = array_merge($orderData, [
            'cta_url' => route('dashboard'),
        ]);
        $this->send('order_notification', $user, $data, 'ID', $attachments);
    }

    public function orderStatusUpdate(User $user, string $status, array $data = [], array $attachments = []): void
    {
        $type = match ($status) {
            'PAID' => 'order_status_paid',
            'PACKING' => 'order_status_packing',
            'SHIPPED' => 'order_status_shipped',
            'DELIVERED' => 'order_status_delivered',
            'CANCELLED' => 'order_status_cancelled',
            'RETURNED' => 'order_status_returned',
            'REFUNDED' => 'order_status_refunded',
            default => 'order_status',
        };
        $this->send($type, $user, $data, 'ID', $attachments);
    }

    public function commissionPayment(User $user, array $commissionData, array $attachments = []): void
    {
        $this->send('commission_payment', $user, $commissionData, 'ID', $attachments);
    }

    public function send(string $type, User $user, array $data = [], string $language = 'ID', array $attachments = []): void
    {
        $key = $this->rateKey($user->id, $type);
        // if (!$this->rateLimiter->allow($key)) {
        //     Log::warning("Rate limited email '{$type}' for user {$user->id}");
        //     return;
        // }

        $globalData = [
            'app_name' => config('app.name', 'EPI OSS'),
            'year' => date('Y'),
            'logo_url' => config('app.url') . '/img/logo.png',
            'support_email' => config('mail.from.address'),
            'name' => $user->name ?? 'User',
            'email' => $user->email,
        ];
        $data = array_merge($globalData, $data);
        
        Log::info("EmailService sending '{$type}' to '{$user->email}'", ['data_keys' => array_keys($data)]);

        $template = EmailTemplate::query()
            ->where('type', $type)
            ->where(function ($q) use ($language) {
                $q->where('language', $language)
                  ->orWhereNull('language')
                  ->orWhere('language', '');
            })
            ->where('is_active', true)
            ->first();

        if (!$template) {
            Log::warning("Email template for type '{$type}' not found");
            return;
        }

        $subject = $this->templates->renderString($template->subject, $data);
        $html = $this->templates->renderHtml($template->body, $data);

        $mailer = $this->routing->getMailer($this->scenarioFromType($type));

        $log = \App\Models\EmailLog::create([
            'user_id' => $user->id,
            'type' => $type,
            'template_id' => $template->id,
            'subject' => $subject,
            'content' => $html,
            'to' => $user->email,
            'status' => 'queued',
            'queued_at' => now(),
            'metadata' => [
                'language' => $language,
            ],
        ]);

        $payload = [
            'message_id' => (string) Str::uuid(),
            'log_id' => $log->id,
            'to' => $user->email,
            'subject' => $subject,
            'html' => $html,
            'attachments' => $attachments,
            'mailer' => $mailer,
            'type' => $type,
            'user_id' => $user->id,
            'metadata' => [
                'language' => $language,
            ],
        ];

        SendEmailJob::dispatch($payload)->onQueue($this->priorityQueue($type));
    }

    public function sendRaw(string $to, string $subject, string $html, array $attachments = [], ?string $mailer = null): void
    {
        if (!$mailer) {
             $mailer = $this->routing->getMailer('default'); 
        }

        $log = \App\Models\EmailLog::create([
            'to' => $to,
            'subject' => $subject,
            'content' => $html,
            'status' => 'queued',
            'queued_at' => now(),
            'type' => 'test_email',
        ]);

        $payload = [
            'message_id' => (string) Str::uuid(),
            'log_id' => $log->id,
            'to' => $to,
            'subject' => $subject,
            'html' => $html,
            'attachments' => $attachments,
            'mailer' => $mailer,
            'type' => 'test_email',
        ];

        SendEmailJob::dispatch($payload)->onQueue('default');
    }

    protected function rateKey(int $userId, string $type): string
    {
        return "email_rate:{$userId}:{$type}";
    }

    protected function scenarioFromType(string $type): string
    {
        return match ($type) {
            'forgot_password', 'reset_password' => 'auth',
            'registration_confirm', 'registration_welcome' => 'auth',
            'order_notification', 'order_tracking', 'order_status' => 'order',
            'commission_payment' => 'marketing',
            default => 'order',
        };
    }

    protected function priorityQueue(string $type): string
    {
        return match ($type) {
            'forgot_password', 'reset_password', 'order_status_paid', 'order_status_shipped' => 'high',
            default => 'default',
        };
    }
}

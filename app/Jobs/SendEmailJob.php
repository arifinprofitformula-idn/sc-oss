<?php
declare(strict_types=1);

namespace App\Jobs;

use App\Services\Email\TrackingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Notification;
use App\Models\EmailLog;
use App\Notifications\AdminEmailFailureAlert;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [5, 30, 180];

    protected array $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function handle(TrackingService $tracking): void
    {
        $logId = $this->payload['log_id'] ?? null;
        $to = $this->payload['to'];
        $subject = $this->payload['subject'];
        $html = $this->payload['html'];
        $mailer = $this->payload['mailer'] ?? null;
        $attachments = $this->payload['attachments'] ?? [];
        $messageId = $this->payload['message_id'] ?? null;

        if ($logId) {
            EmailLog::where('id', $logId)->update([
                'retry_count' => max(0, $this->attempts() - 1),
            ]);
        }

        if ($messageId) {
            $html = $tracking->injectPixel($html, $messageId);
            $html = $tracking->rewriteLinks($html, $messageId);
        }

        $m = Mail::mailer($mailer ?? config('mail.default'));
        try {
            $m->send(new class($html, $to, $subject, $attachments) extends \Illuminate\Mail\Mailable {
                protected string $htmlContent;
                protected string $recipient;
                protected string $msgSubject;
                protected array $msgAttachments;

                public function __construct(string $html, string $to, string $subject, array $attachments)
                {
                    $this->htmlContent = $html;
                    $this->recipient = $to;
                    $this->msgSubject = $subject;
                    $this->msgAttachments = $attachments;
                }

                public function build()
                {
                    $this->to($this->recipient)
                         ->subject($this->msgSubject)
                         ->html($this->htmlContent);

                    foreach ($this->msgAttachments as $att) {
                        if (isset($att['base64']) && isset($att['name'])) {
                            $this->attachData(base64_decode($att['base64']), $att['name']);
                        } elseif (isset($att['path'])) {
                            $this->attach($att['path'], ['as' => $att['name'] ?? null]);
                        }
                    }

                    return $this;
                }
            });

            if ($logId) {
                EmailLog::where('id', $logId)->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'provider_message_id' => $messageId,
                ]);
            }
        } catch (\Exception $e) {
            if ($logId) {
                EmailLog::where('id', $logId)->update([
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ]);

                $windowStart = now()->subMinutes(10);
                $failCount = EmailLog::where('status', 'failed')
                    ->where('created_at', '>=', $windowStart)
                    ->count();
                if ($failCount >= 5) {
                    $admins = \App\Models\User::role('SUPER_ADMIN')->get();
                    Notification::send($admins, new AdminEmailFailureAlert($failCount, $windowStart, now()));
                }
            }
            throw $e;
        }
    }
}

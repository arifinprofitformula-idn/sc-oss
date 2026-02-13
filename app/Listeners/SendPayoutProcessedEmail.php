<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\PayoutProcessed;
use App\Services\Email\EmailService;
use App\Services\Pdf\PdfServiceInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendPayoutProcessedEmail implements ShouldQueue
{
    use InteractsWithQueue;

    protected EmailService $emails;
    protected ?PdfServiceInterface $pdf;

    public function __construct(EmailService $emails, ?PdfServiceInterface $pdf = null)
    {
        $this->emails = $emails;
        $this->pdf = $pdf;
    }

    public function handle(PayoutProcessed $event): void
    {
        $payout = $event->payout;
        $user = $payout->user;

        $attachments = [];
        if ($this->pdf) {
            $att = $this->pdf->generatePayoutReceipt($payout);
            if ($att) {
                $attachments[] = $att;
            }
        }

        $this->emails->send('payout_processed', $user, [
            'payout_number' => $payout->payout_number,
            'amount' => 'Rp ' . number_format((float) $payout->amount, 0, ',', '.'),
        ], 'ID', $attachments);
    }
}


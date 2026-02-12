<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Models\EmailLog;

class EmailStatusUpdateListener
{
    public function handle(array $payload): void
    {
        $providerId = $payload['message_id'] ?? null;
        $status = $payload['event'] ?? null;
        if (!$providerId || !$status) {
            return;
        }
        $log = EmailLog::where('provider_message_id', $providerId)->first();
        if (!$log) {
            return;
        }
        $log->status = strtoupper($status);
        if ($status === 'delivered') {
            $log->delivered_at = now();
        } elseif ($status === 'bounce') {
            $log->bounced_at = now();
        } elseif ($status === 'open') {
            $log->opens_count = ($log->opens_count ?? 0) + 1;
        } elseif ($status === 'click') {
            $log->clicks_count = ($log->clicks_count ?? 0) + 1;
        }
        $log->save();
    }
}


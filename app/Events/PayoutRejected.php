<?php
declare(strict_types=1);

namespace App\Events;

use App\Models\Payout;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PayoutRejected
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Payout $payout;
    public string $reason;

    public function __construct(Payout $payout, string $reason)
    {
        $this->payout = $payout;
        $this->reason = $reason;
    }
}


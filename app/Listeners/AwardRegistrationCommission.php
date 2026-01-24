<?php

namespace App\Listeners;

use App\Events\SilverchannelApproved;
use App\Services\Commission\CommissionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class AwardRegistrationCommission implements ShouldQueue
{
    use InteractsWithQueue;

    protected $commissionService;

    /**
     * Create the event listener.
     */
    public function __construct(CommissionService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

    /**
     * Handle the event.
     */
    public function handle(SilverchannelApproved $event): void
    {
        $user = $event->user;

        // Check if user has a referrer
        if (!$user->referrer_id) {
            return;
        }

        $referrer = $user->referrer;

        // Fixed Registration Commission (CPL) - e.g. Rp 50,000
        $amount = 50000;
        
        // Holding period: 14 days
        $availableAt = now()->addDays(14);

        try {
            // Check if commission already awarded for this user
            $exists = $referrer->commissionLedgers()
                ->where('reference_type', get_class($user))
                ->where('reference_id', $user->id)
                ->where('type', 'REGISTRATION')
                ->exists();

            if ($exists) {
                return;
            }

            $this->commissionService->recordEntry(
                $referrer,
                $amount,
                'REGISTRATION',
                $user,
                "Registration Bonus for referring {$user->name}",
                'PENDING',
                $availableAt
            );

            Log::info("Registration commission awarded to User #{$referrer->id} for referral #{$user->id}");

        } catch (\Exception $e) {
            Log::error("Failed to award registration commission for User #{$user->id}: " . $e->getMessage());
        }
    }
}

<?php

namespace App\Listeners;

use App\Events\SilverchannelApproved;
use App\Services\Commission\CommissionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use App\Models\AuditLog;
use App\Models\Package;
use App\Models\ReferralCommission;

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

        // Find Package from AuditLog (Best Effort to get the exact package used at registration)
        $auditLog = AuditLog::where('action', 'REGISTER_SILVERCHANNEL_WITH_PAYMENT')
            ->where('model_id', $user->id)
            ->first();
        
        $packageId = $auditLog && isset($auditLog->new_values['package_id']) 
            ? $auditLog->new_values['package_id'] 
            : null;

        $package = null;
        if ($packageId) {
            $package = Package::find($packageId);
        }

        // Fallback: Use current active package if exact package not found
        if (!$package) {
             $package = Package::active()->first();
             Log::warning("Exact registration package not found for User #{$user->id}. Using active package #{$package->id} for commission.");
        }

        if (!$package) {
             Log::error("No package found for user #{$user->id} commission calculation.");
             return;
        }

        // Calculate Commission
        $amount = 0;
        if ($package->commission_type === 'percentage') {
            $amount = $package->price * ($package->commission_value / 100);
        } else {
            $amount = $package->commission_value;
        }

        // Commission Status: AVAILABLE (Real-time release for Registration)
        // Per instruction: "setiap kali ada order paket registrasi... status 'PAID', maka komisi... berubah... menjadi 'Available Balance'."
        $status = 'AVAILABLE';
        $availableAt = now();

        try {
            // Check if commission already awarded
            $exists = ReferralCommission::where('referred_user_id', $user->id)
                ->where('referrer_id', $referrer->id)
                ->exists();

            if ($exists) {
                return;
            }

            // 1. Create ReferralCommission Record
            ReferralCommission::create([
                'referrer_id' => $referrer->id,
                'referred_user_id' => $user->id,
                'package_id' => $package->id,
                'amount' => $amount,
                'commission_type' => $package->commission_type,
                'commission_base_amount' => $package->price,
                'status' => $status,
            ]);

            // 2. Create Ledger Entry
            $this->commissionService->recordEntry(
                $referrer,
                $amount,
                'REGISTRATION',
                $user, // Source is the referred user
                "Referral Commission for {$user->name} (Package: {$package->name})",
                $status,
                $availableAt
            );
            
            Log::info("Registration commission awarded to User #{$referrer->id} for referring User #{$user->id}. Status: {$status}.");

        } catch (\Exception $e) {
            Log::error("Failed to award registration commission for User #{$user->id}: " . $e->getMessage());
        }
    }
}

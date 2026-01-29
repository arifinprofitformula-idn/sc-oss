<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Events\SilverchannelApproved;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProcessSilverchannelStatus
{
    public function handle(OrderStatusChanged $event)
    {
        $order = $event->order;
        $newStatus = $event->newStatus;
        $user = $order->user;

        // Ensure we are dealing with a Silverchannel user
        if (!$user || !$user->hasRole('SILVERCHANNEL')) {
            return;
        }

        // Logic for Activation (Paid)
        if (in_array($newStatus, ['PAID', 'PACKING', 'SHIPPED', 'DELIVERED'])) {
            // Check for WAITING_VERIFICATION, PENDING_REVIEW, or REJECTED (Re-activation)
            if (in_array($user->status, ['WAITING_VERIFICATION', 'PENDING_REVIEW', 'REJECTED'])) {
                $this->activateUser($user);
            }
        }

        // Logic for Rejection (Cancelled/Refunded)
        if (in_array($newStatus, ['CANCELLED', 'REFUNDED', 'RETURNED'])) {
            // Check for WAITING_VERIFICATION, PENDING_REVIEW, or ACTIVE (Revocation)
            if (in_array($user->status, ['WAITING_VERIFICATION', 'PENDING_REVIEW', 'ACTIVE'])) {
                $this->rejectUser($user, $newStatus);
            }
        }
    }

    protected function activateUser(User $user)
    {
        $oldStatus = $user->status;
        $user->status = 'ACTIVE';

        // Ensure Silverchannel ID exists (Fallback logic)
        if (empty($user->silver_channel_id)) {
            $user->silver_channel_id = $this->generateSilverChannelId($user->name);
        }
        
        // Ensure Referral Code matches ID
        if ($user->referral_code !== $user->silver_channel_id) {
             $user->referral_code = $user->silver_channel_id;
        }

        $user->save();

        // Log Audit
        AuditLog::create([
            'user_id' => auth()->id() ?? $user->id, // If triggered by system/job, might not have auth
            'action' => 'AUTO_ACTIVATE_SILVERCHANNEL',
            'model_type' => User::class,
            'model_id' => $user->id,
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => 'ACTIVE'],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Fire Approval Event (for commissions/emails)
        SilverchannelApproved::dispatch($user);
    }

    protected function rejectUser(User $user, string $reasonStatus)
    {
        $oldStatus = $user->status;
        $user->status = 'REJECTED';
        $user->save();

        // Log Audit
        AuditLog::create([
            'user_id' => auth()->id() ?? $user->id,
            'action' => 'AUTO_REJECT_SILVERCHANNEL',
            'model_type' => User::class,
            'model_id' => $user->id,
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => 'REJECTED', 'reason' => "Order $reasonStatus"],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    private function generateSilverChannelId($name)
    {
        // Logic copied/adapted from SilverchannelController
        $namePart = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $name), 0, 2));
        if (strlen($namePart) < 2) {
            $namePart = str_pad($namePart, 2, 'X');
        }

        $maxRetries = 3;
        $attempt = 0;

        do {
            $randomChars = strtoupper(Str::random(2));
            $randomNums = str_pad(rand(0, 99), 2, '0', STR_PAD_LEFT);
            $id = 'EPISC' . $namePart . $randomChars . $randomNums;
            
            if (!User::where('silver_channel_id', $id)->exists() && !User::where('referral_code', $id)->exists()) {
                return $id;
            }
            $attempt++;
        } while ($attempt < $maxRetries);

        return 'EPISC' . $namePart . strtoupper(Str::random(4));
    }
}

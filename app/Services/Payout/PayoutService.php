<?php

namespace App\Services\Payout;

use App\Models\Payout;
use App\Models\User;
use App\Models\AuditLog;
use App\Services\Commission\CommissionService;
use Illuminate\Support\Facades\DB;
use Exception;

class PayoutService
{
    protected $commissionService;

    public function __construct(CommissionService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

    public function createRequest(User $user, float $amount): Payout
    {
        if ($amount <= 0) {
            throw new Exception("Amount must be greater than 0.");
        }

        if ($user->wallet_balance < $amount) {
            throw new Exception("Insufficient balance. Available: " . number_format($user->wallet_balance, 0));
        }

        return DB::transaction(function () use ($user, $amount) {
            // Create Payout Record
            $payout = Payout::create([
                'payout_number' => 'PO-' . strtoupper(uniqid()),
                'user_id' => $user->id,
                'amount' => $amount,
                'status' => 'REQUESTED',
            ]);

            // Create Ledger Entry (Lock funds)
            $this->commissionService->recordEntry(
                $user,
                -$amount,
                'WITHDRAW', // Label as Withdraw
                $payout,
                'Withdraw Request ' . $payout->payout_number,
                'PENDING' // Included in debits calculation
            );

            // Audit Log
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'WITHDRAW',
                'model_type' => Payout::class,
                'model_id' => $payout->id,
                'new_values' => [
                    'amount' => $amount,
                    'status' => 'REQUESTED',
                    'payout_number' => $payout->payout_number
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return $payout;
        });
    }

    public function approve(Payout $payout, ?string $proofPath = null): Payout
    {
        return DB::transaction(function () use ($payout, $proofPath) {
            $payout->update([
                'status' => 'PROCESSED',
                'processed_at' => now(),
                'proof_file' => $proofPath,
            ]);

            $ledger = $payout->commissionLedger;
            if ($ledger) {
                $ledger->update(['status' => 'PAID']);
            }

            return $payout;
        });
    }

    public function reject(Payout $payout, string $reason): Payout
    {
        return DB::transaction(function () use ($payout, $reason) {
            $payout->update([
                'status' => 'REJECTED',
                'rejection_reason' => $reason,
                'processed_at' => now(),
            ]);

            $ledger = $payout->commissionLedger;
            if ($ledger) {
                // Cancel ledger so it doesn't count as debit anymore
                $ledger->update(['status' => 'CANCELLED']);
            }

            return $payout;
        });
    }
}

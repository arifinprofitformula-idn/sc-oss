<?php

namespace App\Services\Commission;

use App\Models\CommissionLedger;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CommissionService
{
    public function recordEntry(
        User $user,
        float $amount,
        string $type,
        Model $reference,
        string $description,
        string $status = 'PENDING',
        ?string $availableAt = null
    ): CommissionLedger {
        return CommissionLedger::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'type' => $type,
            'reference_type' => get_class($reference),
            'reference_id' => $reference->id,
            'description' => $description,
            'status' => $status,
            'available_at' => $availableAt,
        ]);
    }

    public function calculateBalance(User $user): float
    {
        return $user->wallet_balance;
    }
}

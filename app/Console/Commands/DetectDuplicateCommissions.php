<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CommissionLedger;
use Illuminate\Support\Facades\DB;

class DetectDuplicateCommissions extends Command
{
    protected $signature = 'commission:detect-duplicates';
    protected $description = 'Detect duplicate commission ledger entries';

    public function handle()
    {
        $this->info('Scanning for duplicate commissions...');

        $duplicates = CommissionLedger::select('user_id', 'type', 'reference_type', 'reference_id', DB::raw('count(*) as count'))
            ->where('type', 'TRANSACTION')
            ->groupBy('user_id', 'type', 'reference_type', 'reference_id')
            ->having('count', '>', 1)
            ->get();

        if ($duplicates->isEmpty()) {
            $this->info('No duplicates found.');
            return;
        }

        $this->error("Found {$duplicates->count()} duplicate groups.");

        foreach ($duplicates as $dup) {
            $this->line("User: {$dup->user_id}, Type: {$dup->type}, RefType: {$dup->reference_type}, RefID: {$dup->reference_id} -> Count: {$dup->count}");
        }
    }
}

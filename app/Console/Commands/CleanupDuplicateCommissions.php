<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CommissionLedger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanupDuplicateCommissions extends Command
{
    protected $signature = 'commission:cleanup-duplicates {--dry-run : Run without deleting}';
    protected $description = 'Cleanup duplicate commission ledger entries, keeping the first one.';

    public function handle()
    {
        $this->info('Scanning for duplicate commissions...');
        $dryRun = $this->option('dry-run');

        $duplicates = CommissionLedger::select('user_id', 'type', 'reference_type', 'reference_id', DB::raw('count(*) as count'))
            ->where('type', 'TRANSACTION')
            ->groupBy('user_id', 'type', 'reference_type', 'reference_id')
            ->having('count', '>', 1)
            ->get();

        if ($duplicates->isEmpty()) {
            $this->info('No duplicates found.');
            return;
        }

        $this->info("Found {$duplicates->count()} duplicate groups.");
        $totalDeleted = 0;

        foreach ($duplicates as $dup) {
            $entries = CommissionLedger::where('user_id', $dup->user_id)
                ->where('type', 'TRANSACTION')
                ->where('reference_type', $dup->reference_type)
                ->where('reference_id', $dup->reference_id)
                ->orderBy('created_at', 'asc')
                ->get();

            // Keep the first one
            $keep = $entries->shift();
            
            $this->info("Processing User: {$dup->user_id}, RefID: {$dup->reference_id}. Keeping ID: {$keep->id}");

            foreach ($entries as $remove) {
                if ($dryRun) {
                    $this->line("  [Dry Run] Would delete ID: {$remove->id} (Amount: {$remove->amount})");
                } else {
                    $remove->delete();
                    $this->line("  Deleted ID: {$remove->id}");
                    Log::info("Deleted duplicate commission ledger ID: {$remove->id} for User: {$dup->user_id}");
                    $totalDeleted++;
                }
            }
        }

        if (!$dryRun) {
            $this->info("Cleanup complete. Deleted {$totalDeleted} records.");
        } else {
            $this->info("Dry run complete.");
        }
    }
}

<?php

namespace App\Console\Commands;

use App\Services\EpiAutoPriceService;
use Illuminate\Console\Command;

class EpiApeSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'epi:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync prices from EPI Auto Price Engine';

    /**
     * Execute the console command.
     */
    public function handle(EpiAutoPriceService $service)
    {
        $settings = $service->getSettings();

        if (!$settings['active']) {
            $this->info('EPI APE is disabled.');
            return;
        }

        // Check interval
        $lastRun = cache()->get('epi_ape_last_run');
        $intervalMinutes = (int) $settings['update_interval'];
        
        if ($lastRun && now()->diffInMinutes($lastRun) < $intervalMinutes) {
            $this->info("Skipping sync. Last run was " . $lastRun->diffForHumans() . ". Interval: {$intervalMinutes}m.");
            return;
        }

        $this->info('Starting EPI APE Price Sync...');
        
        try {
            $result = $service->syncPrices();
            
            cache()->put('epi_ape_last_run', now());
            
            $this->info("Sync completed. Updated: {$result['updated']}");
            
            if (!empty($result['errors'])) {
                $this->error("Errors encountered: " . count($result['errors']));
                foreach ($result['errors'] as $error) {
                    $this->error("- $error");
                }
            }
        } catch (\Exception $e) {
            $this->error("Sync failed: " . $e->getMessage());
        }
    }
}

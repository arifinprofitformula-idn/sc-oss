<?php

namespace App\Console\Commands;

use App\Jobs\UpdateProductPriceJob;
use App\Services\EpiApePriceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FetchEpiPricesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-epi-prices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch product prices from EPI APE API and update local database';

    /**
     * Execute the console command.
     */
    public function handle(EpiApePriceService $service)
    {
        $this->info('Fetching prices from EPI APE API...');
        
        try {
            $prices = $service->fetchPrices();
            $count = count($prices);
            
            $this->info("Received {$count} price updates.");
            
            foreach ($prices as $data) {
                // Dispatch job for each item to handle concurrency and retries
                UpdateProductPriceJob::dispatch($data);
            }
            
            $this->info("Dispatched {$count} jobs.");
            
        } catch (\Exception $e) {
            $this->error('Failed to fetch prices: ' . $e->getMessage());
            Log::error('FetchEpiPricesCommand Error: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}

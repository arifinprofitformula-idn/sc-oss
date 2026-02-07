<?php

namespace App\Jobs;

use App\Services\EpiApePriceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateProductPriceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [10, 30, 60]; // Retry after 10s, 30s, 60s

    protected $data;

    /**
     * Create a new job instance.
     *
     * @param array $data ['sku' => string, 'price' => float, 'updated_at' => string]
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(EpiApePriceService $service): void
    {
        $sku = $this->data['sku'];
        $price = $this->data['price'];
        $timestamp = $this->data['updated_at'];
        
        Log::info("Job started for SKU: $sku");

        try {
            $service->processUpdate($sku, $price, $timestamp);
        } catch (\Exception $e) {
            Log::error("Job failed for SKU: $sku. Error: " . $e->getMessage());
            throw $e; // Trigger retry
        }
    }
}

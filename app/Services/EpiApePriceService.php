<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductPriceHistory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EpiApePriceService
{
    /**
     * Fetch prices from EPI APE API (Mocked).
     * 
     * @return array
     */
    public function fetchPrices()
    {
        // Mocking external API call
        // In real implementation, use Http::get('https://api.epi.ape/prices');
        return [
            [
                'sku' => 'G-10',
                'price' => 13500000,
                'updated_at' => Carbon::now()->toIso8601String(),
            ],
            [
                'sku' => 'S-01',
                'price' => 15000,
                'updated_at' => Carbon::now()->toIso8601String(),
            ],
            // Add a case for older timestamp to test versioning
            [
                'sku' => 'OLD-SKU',
                'price' => 10000,
                'updated_at' => Carbon::now()->subDays(1)->toIso8601String(),
            ]
        ];
    }

    /**
     * Process price update for a single SKU.
     * 
     * @param string $sku
     * @param float $newPrice
     * @param string $timestamp
     * @param string $source
     * @return array Result status
     */
    public function processUpdate($sku, $newPrice, $timestamp, $source = 'API_EPI')
    {
        // 1. Validation
        if (!is_numeric($newPrice) || $newPrice <= 0) {
            Log::warning("Invalid price for SKU: $sku", ['price' => $newPrice]);
            return ['status' => 'error', 'message' => 'Invalid price'];
        }

        $product = Product::where('sku', $sku)->first();

        if (!$product) {
            Log::info("Product not found for SKU: $sku");
            return ['status' => 'skipped', 'message' => 'Product not found'];
        }

        // 2. Versioning Check
        $newTimestamp = Carbon::parse($timestamp);
        if ($product->last_price_update_at && $newTimestamp->lte($product->last_price_update_at)) {
            Log::info("Skipping update for SKU: $sku. New timestamp ($newTimestamp) <= Current ($product->last_price_update_at)");
            return ['status' => 'skipped', 'message' => 'Older or same version'];
        }

        // 3. Update & Audit Trail
        DB::beginTransaction();
        try {
            $oldPrice = $product->price_silverchannel;

            // Check significant change (>10%)
            if ($oldPrice > 0) {
                $percentageChange = abs(($newPrice - $oldPrice) / $oldPrice) * 100;
                if ($percentageChange > 10) {
                    $this->notifyStakeholders($product, $oldPrice, $newPrice);
                }
            }

            // Update Product
            $product->price_silverchannel = $newPrice;
            $product->last_price_update_at = $newTimestamp;
            $product->price_source = $source;
            $product->save();

            // Create Audit Log
            ProductPriceHistory::create([
                'product_id' => $product->id,
                'old_price' => $oldPrice,
                'new_price' => $newPrice,
                'source' => $source,
                'price_updated_at' => $newTimestamp,
            ]);

            DB::commit();
            
            Log::info("Price updated for SKU: $sku", ['old' => $oldPrice, 'new' => $newPrice]);
            return ['status' => 'success', 'message' => 'Price updated'];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to update price for SKU: $sku", ['error' => $e->getMessage()]);
            throw $e; // Rethrow to trigger job retry
        }
    }

    protected function notifyStakeholders(Product $product, $oldPrice, $newPrice)
    {
        $message = "Significant price change detected for {$product->sku}: " .
                   "Old: " . number_format($oldPrice) . " -> New: " . number_format($newPrice);
        
        Log::alert($message);
        // Here you would implement email/slack notification
    }
}

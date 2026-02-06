<?php

namespace App\Services;

use App\Models\EpiProductMapping;
use App\Models\Product;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\EpiApeSyncReport;

class EpiAutoPriceService
{
    protected $integrationService;

    public function __construct(IntegrationService $integrationService)
    {
        $this->integrationService = $integrationService;
    }

    public function getSettings()
    {
        return [
            'active' => $this->integrationService->get('epi_ape_active', false),
            'api_key' => $this->integrationService->get('epi_ape_api_key'),
            'base_url' => $this->integrationService->get('epi_ape_base_url', 'https://autoprice.bisnisemasperak.com/api/v1'),
            'update_interval' => $this->integrationService->get('epi_ape_update_interval', 60), // minutes
            'notify_email' => $this->integrationService->get('epi_ape_notify_email'),
        ];
    }

    public function updateSettings(array $data)
    {
        $this->integrationService->set('epi_ape_active', isset($data['active']) ? 1 : 0, 'epi_ape', 'boolean');
        $this->integrationService->set('epi_ape_api_key', $data['api_key'], 'epi_ape', 'text');
        $this->integrationService->set('epi_ape_base_url', $data['base_url'], 'epi_ape', 'text');
        $this->integrationService->set('epi_ape_update_interval', $data['update_interval'], 'epi_ape', 'integer');
        $this->integrationService->set('epi_ape_notify_email', $data['notify_email'], 'epi_ape', 'text');
    }

    protected function request($method, $endpoint, $params = [])
    {
        $settings = $this->getSettings();
        $apiKey = $settings['api_key'];
        $baseUrl = rtrim($settings['base_url'], '/');
        $url = "{$baseUrl}/{$endpoint}";

        $startTime = microtime(true);

        try {
            $response = Http::withOptions(['verify' => false])->withHeaders([
                'X-Api-Key' => $apiKey,
                'Accept' => 'application/json',
            ])->$method($url, $params);

            $duration = round((microtime(true) - $startTime) * 1000, 2);

            $this->integrationService->log(
                'epi_ape',
                $endpoint,
                strtoupper($method),
                $params,
                $response->json() ?? $response->body(),
                $response->status(),
                $duration
            );

            return $response;
        } catch (\Exception $e) {
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            
            $this->integrationService->log(
                'epi_ape',
                $endpoint,
                strtoupper($method),
                $params,
                ['error' => $e->getMessage()],
                500,
                $duration
            );

            throw $e;
        }
    }

    public function testConnection()
    {
        try {
            $response = $this->request('get', 'prices', ['limit' => 1]);
            
            if ($response->successful()) {
                return ['success' => true, 'message' => 'Connection successful'];
            }
            
            return [
                'success' => false, 
                'message' => 'Connection failed: ' . $response->status() . ' - ' . ($response->json()['message'] ?? 'Unknown error')
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Connection error: ' . $e->getMessage()];
        }
    }

    public function fetchAllPrices()
    {
        // This method fetches available pricing structures to help with mapping
        // Since we don't have a dedicated "brands" endpoint, we'll fetch prices
        // and extract unique brands/levels from the response if possible,
        // or just return the raw list for the UI to handle.
        $response = $this->request('get', 'prices', ['limit' => 100]);
        if ($response->successful()) {
            return $response->json();
        }
        return [];
    }

    protected function getBrandName($id)
    {
        $map = [
            1 => 'Goldgram',
            2 => 'Silvergram',
            3 => 'Meezan Gold',
            5 => 'NEW BRAND',
        ];
        return $map[$id] ?? null;
    }

    protected function getLevelName($id)
    {
        $map = [
            4 => 'Buyback',
            5 => 'Silverchannel',
            7 => 'Konsumen',
            8 => 'Epi-store',
            9 => 'Epi-channel',
            10 => 'Harga-standar-perak',
        ];
        return $map[$id] ?? null;
    }

    public function syncPrices()
    {
        $settings = $this->getSettings();
        if (!$settings['active']) {
            throw new \Exception('EPI APE integration is disabled.');
        }

        $mappings = EpiProductMapping::where('is_active', true)->with('product')->get();
        $updates = [];
        $errors = [];
        $priceCache = []; // Cache responses for Brand+Level combinations

        foreach ($mappings as $mapping) {
            try {
                $brandName = $this->getBrandName($mapping->epi_brand_id);
                $levelName = $this->getLevelName($mapping->epi_level_id);
                $targetGramasi = (float) ($mapping->epi_gramasi ?? 1);
                $price = null;

                if ($brandName && $levelName) {
                    // Use prices endpoint with filtering
                    $cacheKey = "{$brandName}|{$levelName}";
                    
                    if (!isset($priceCache[$cacheKey])) {
                        $endpoint = "prices?brand=" . urlencode($brandName) . "&level=" . urlencode($levelName);
                        $response = $this->request('get', $endpoint);
                        
                        if ($response->successful()) {
                            $priceCache[$cacheKey] = $response->json()['data'] ?? [];
                        } else {
                            $errors[] = "Failed to fetch prices for {$brandName}/{$levelName}: " . $response->status();
                            continue;
                        }
                    }

                    // Search in cache
                    foreach ($priceCache[$cacheKey] as $item) {
                        $itemGramasi = (float) ($item['product']['gramasi'] ?? 0);
                        
                        // Strict check for Brand and Level (Case-insensitive) to prevent mismatch
                        $itemBrand = $item['brand'] ?? '';
                        $itemLevel = $item['level']['name'] ?? '';

                        if (strcasecmp($itemBrand, $brandName) !== 0 || strcasecmp($itemLevel, $levelName) !== 0) {
                            continue;
                        }

                        if (abs($itemGramasi - $targetGramasi) < 0.001) {
                            $price = $item['price'];
                            break;
                        }
                    }
                } else {
                    // Fallback to singular endpoint (legacy support, defaults to 1g)
                    // Endpoint: GET /brands/{brand_id}/levels/{level_id}/price
                    $endpoint = "brands/{$mapping->epi_brand_id}/levels/{$mapping->epi_level_id}/price";
                    $response = $this->request('get', $endpoint);

                    if ($response->successful()) {
                        $data = $response->json();
                        $price = $data['price'] ?? ($data['data']['price'] ?? null);
                    }
                }

                if ($price) {
                    // Update Product Price (Silverchannel Price)
                    $oldPrice = $mapping->product->price_silverchannel;
                    
                    // Only update if changed
                    if ($oldPrice != $price) {
                        $mapping->product->price_silverchannel = $price;
                        $mapping->product->save();

                        $mapping->last_synced_price = $price;
                        $mapping->last_synced_at = Carbon::now();
                        $mapping->save();
                        
                        $updates[] = [
                            'sku' => $mapping->product->sku,
                            'old_price' => $oldPrice,
                            'new_price' => $price,
                            'change' => $price - $oldPrice,
                        ];
                        
                        // Log price change
                        Log::info("EPI APE: Product {$mapping->product->sku} price updated from {$oldPrice} to {$price}");

                        // Create Audit Log
                        AuditLog::create([
                            'user_id' => null, // System action
                            'action' => 'UPDATE_PRICE_EPI_APE',
                            'model_type' => Product::class,
                            'model_id' => $mapping->product->id,
                            'old_values' => ['price_silverchannel' => $oldPrice],
                            'new_values' => ['price_silverchannel' => $price],
                            'ip_address' => '127.0.0.1', // Localhost/System
                            'user_agent' => 'EPI Auto Price Engine Sync',
                        ]);
                    } else {
                        // Just update sync time
                        $mapping->last_synced_at = Carbon::now();
                        $mapping->save();
                    }
                } else {
                    $errors[] = "Price not found for mapping ID {$mapping->id} (Brand: {$mapping->epi_brand_id}, Level: {$mapping->epi_level_id}, Gram: {$targetGramasi})";
                }
            } catch (\Exception $e) {
                $errors[] = "Exception for mapping ID {$mapping->id}: " . $e->getMessage();
            }
        }

        if (($settings['notify_email']) && (!empty($errors) || !empty($updates))) {
            try {
                Mail::to($settings['notify_email'])->send(new EpiApeSyncReport($updates, $errors));
            } catch (\Exception $e) {
                Log::error("EPI APE: Failed to send sync report email: " . $e->getMessage());
            }
        }
        
        if (!empty($errors)) {
            Log::error('EPI APE Sync Errors: ' . implode(', ', $errors));
        }

        return ['updated' => count($updates), 'errors' => $errors];
    }
}

<?php

namespace App\Services;

use App\Models\EpiProductMapping;
use App\Models\Product;
use App\Models\AuditLog;
use App\Models\IntegrationError;
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
            $response = Http::retry(3, 100)->withOptions(['verify' => false])->withHeaders([
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

    public function syncProductPrice(Product $product)
    {
        $settings = $this->getSettings();
        if (!$settings['active']) {
            return ['success' => false, 'message' => 'Integration disabled'];
        }

        $mapping = $product->epiMapping;
        if (!$mapping || !$mapping->is_active) {
            return ['success' => false, 'message' => 'No active mapping found'];
        }

        $updates = [];
        $errors = [];
        $targetGramasi = (float) ($mapping->epi_gramasi ?? 1);

        // Sync Silverchannel Price
        if ($mapping->epi_level_id) {
            $this->processPriceSync($mapping, $mapping->epi_level_id, 'price_silverchannel', $targetGramasi, $updates, $errors);
        }

        // Sync Customer Price
        if ($mapping->epi_level_id_customer) {
            $this->processPriceSync($mapping, $mapping->epi_level_id_customer, 'price_customer', $targetGramasi, $updates, $errors);
        }

        $mapping->last_synced_at = Carbon::now();
        $mapping->save();

        if (!empty($errors)) {
            return ['success' => false, 'message' => implode(', ', $errors)];
        }

        return ['success' => true, 'updates' => $updates];
    }

    public function getPricePreview($brandId, $levelId, $gramasi)
    {
        try {
            $endpoint = "brands/{$brandId}/levels/{$levelId}/price";
            $response = $this->request('get', $endpoint, ['size' => $gramasi]);

            if ($response->successful()) {
                $data = $response->json();
                // Support both direct price and nested data.price structure
                $price = $data['price'] ?? ($data['data']['price'] ?? 0);
                
                return [
                    'success' => true,
                    'price' => $price,
                    'raw' => $data
                ];
            }

            return [
                'success' => false,
                'message' => 'API Error: ' . $response->status(),
                'details' => $response->json()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
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

        foreach ($mappings as $mapping) {
            $targetGramasi = (float) ($mapping->epi_gramasi ?? 1);

            // Sync Silverchannel Price
            if ($mapping->epi_level_id) {
                $this->processPriceSync($mapping, $mapping->epi_level_id, 'price_silverchannel', $targetGramasi, $updates, $errors);
            }

            // Sync Customer Price
            if ($mapping->epi_level_id_customer) {
                $this->processPriceSync($mapping, $mapping->epi_level_id_customer, 'price_customer', $targetGramasi, $updates, $errors);
            }
            
            // Update last synced time
            $mapping->last_synced_at = Carbon::now();
            $mapping->save();
        }

        if (($settings['notify_email']) && (!empty($errors) || !empty($updates))) {
            try {
                Mail::to($settings['notify_email'])->send(new EpiApeSyncReport($updates, $errors));
            } catch (\Exception $e) {
                $msg = "EPI APE: Failed to send sync report email: " . $e->getMessage();
                Log::error($msg);
                IntegrationError::create([
                    'integration' => 'epi_ape',
                    'error_code' => 'EMAIL_NOTIFICATION_FAILED',
                    'message' => $msg,
                    'details' => ['email' => $settings['notify_email']],
                    'recommended_action' => 'Check mail server configuration.',
                ]);
            }
        }
        
        if (!empty($errors)) {
            Log::error('EPI APE Sync Errors: ' . implode(', ', $errors));
        }

        return ['updated' => count($updates), 'errors' => $errors, 'error_count' => count($errors)];
    }

    private function processPriceSync($mapping, $levelId, $priceField, $targetGramasi, &$updates, &$errors)
    {
        try {
            // Endpoint: GET /brands/{brand_id}/levels/{level_id}/price?size={gramasi}
            $endpoint = "brands/{$mapping->epi_brand_id}/levels/{$levelId}/price";
            
            $response = $this->request('get', $endpoint, ['size' => $targetGramasi]);

            if ($response->successful()) {
                $data = $response->json();
                $price = $data['price'] ?? ($data['data']['price'] ?? null);
            } else {
                $msg = "Failed to fetch price for mapping ID {$mapping->id} ({$priceField}): " . $response->status() . " - " . ($response->json()['message'] ?? 'Unknown error');
                $errors[] = $msg;
                
                IntegrationError::create([
                    'integration' => 'epi_ape',
                    'error_code' => 'PRICE_FETCH_ERROR',
                    'message' => $msg,
                    'details' => [
                        'mapping_id' => $mapping->id,
                        'brand_id' => $mapping->epi_brand_id,
                        'level_id' => $levelId,
                        'price_field' => $priceField,
                        'gramasi' => $targetGramasi,
                        'response' => $response->body()
                    ],
                    'recommended_action' => 'Check if the Brand ID and Level ID are correct, and if the size exists.',
                ]);
                return;
            }

            if ($price) {
                // Update Product Price
                $oldPrice = $mapping->product->{$priceField};
                
                // Only update if changed
                if ($oldPrice != $price) {
                    $mapping->product->{$priceField} = $price;
                    $mapping->product->save();

                    // Update last synced price only if it's the main silverchannel price
                    if ($priceField === 'price_silverchannel') {
                        $mapping->last_synced_price = $price;
                        $mapping->save();
                    }
                    
                    $updates[] = [
                        'sku' => $mapping->product->sku,
                        'field' => $priceField,
                        'old_price' => $oldPrice,
                        'new_price' => $price,
                        'change' => $price - $oldPrice,
                    ];
                    
                    // Log price change
                    Log::info("EPI APE: Product {$mapping->product->sku} ({$priceField}) updated from {$oldPrice} to {$price}");

                    // Create Audit Log
                    AuditLog::create([
                        'user_id' => null, // System action
                        'action' => 'UPDATE_PRICE_EPI_APE',
                        'model_type' => Product::class,
                        'model_id' => $mapping->product->id,
                        'old_values' => [$priceField => $oldPrice],
                        'new_values' => [$priceField => $price],
                        'ip_address' => '127.0.0.1', // Localhost/System
                        'user_agent' => 'EPI Auto Price Engine Sync',
                    ]);
                }
            } else {
                $msg = "Price data missing in response for mapping ID {$mapping->id} (Brand: {$mapping->epi_brand_id}, Level: {$levelId}, Size: {$targetGramasi})";
                $errors[] = $msg;
                
                IntegrationError::create([
                    'integration' => 'epi_ape',
                    'error_code' => 'PRICE_NOT_FOUND',
                    'message' => $msg,
                    'details' => [
                        'mapping_id' => $mapping->id,
                        'product_sku' => $mapping->product->sku,
                        'brand_id' => $mapping->epi_brand_id,
                        'level_id' => $levelId,
                        'gramasi' => $targetGramasi,
                        'response' => $response->body()
                    ],
                    'recommended_action' => 'Verify that the product exists in EPI APE with the specified Brand, Level, and Size.',
                ]);
            }
        } catch (\Exception $e) {
            $msg = "Exception for mapping ID {$mapping->id} ({$priceField}): " . $e->getMessage();
            $errors[] = $msg;
            
            IntegrationError::create([
                'integration' => 'epi_ape',
                'error_code' => 'SYNC_EXCEPTION',
                'message' => $msg,
                'details' => ['mapping_id' => $mapping->id, 'trace' => $e->getTraceAsString()],
                'recommended_action' => 'Check system logs for detailed stack trace.',
            ]);
        }
    }
}

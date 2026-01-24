<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Client\Response;
use App\Services\IntegrationService;
use App\Contracts\ShippingProviderInterface;

class RajaOngkirService implements ShippingProviderInterface
{
    protected $baseUrl;
    protected $apiKey;
    protected $integrationService;

    public function __construct(IntegrationService $integrationService)
    {
        $this->integrationService = $integrationService;
        
        // Priority: DB Setting > Config
        $this->apiKey = $this->integrationService->get('rajaongkir_api_key') ?: config('services.rajaongkir.api_key');
        $this->baseUrl = rtrim($this->integrationService->get('rajaongkir_base_url') ?: config('services.rajaongkir.base_url', 'https://api.rajaongkir.com/starter'), '/');
    }

    public function getProvinces()
    {
        return Cache::remember('rajaongkir_provinces', 86400, function () {
            $isV2 = str_contains($this->baseUrl, 'komerce.id');
            $path = $isV2 ? '/destination/province' : '/province';
            $startTime = microtime(true);
            
            try {
                /** @var Response $response */
                $response = Http::withoutVerifying()->withHeaders([
                    'key' => $this->apiKey
                ])->get($this->baseUrl . $path);

                $duration = round((microtime(true) - $startTime) * 1000);
                
                // Log only if not cached (inside closure)
                $this->integrationService->log(
                    'rajaongkir',
                    $path,
                    'GET',
                    [],
                    $response->body(),
                    $response->status(),
                    $duration
                );

                if (!$response->successful()) {
                    $json = $response->json() ?? [];
                    $msg = $json['rajaongkir']['status']['description'] ?? 'API Error ' . $response->status();
                    throw new \Exception($msg);
                }

                $json = $response->json() ?? [];
                $data = $json['data'] ?? $json['rajaongkir']['results'] ?? [];

                if (empty($data)) {
                    return [];
                }

                if ($isV2) {
                    return array_map(function($item) {
                        return [
                            'province_id' => $item['id'],
                            'province' => $item['name']
                        ];
                    }, $data);
                }

                return $data;
            } catch (\Exception $e) {
                // Log failure
                $duration = round((microtime(true) - $startTime) * 1000);
                $this->integrationService->log('rajaongkir', $path, 'GET', [], $e->getMessage(), 500, $duration);
                throw $e;
            }
        });
    }

    public function getCities($provinceId)
    {
        return Cache::remember("rajaongkir_cities_{$provinceId}", 86400, function () use ($provinceId) {
            $isV2 = str_contains($this->baseUrl, 'komerce.id');
            $startTime = microtime(true);
            
            try {
                if ($isV2) {
                    $path = "/destination/city/{$provinceId}";
                    /** @var Response $response */
                    $response = Http::withoutVerifying()->withHeaders(['key' => $this->apiKey])
                        ->get($this->baseUrl . $path);
                    
                    $duration = round((microtime(true) - $startTime) * 1000);
                    $this->integrationService->log('rajaongkir', $path, 'GET', [], $response->body(), $response->status(), $duration);

                    $json = $response->json() ?? [];
                    $data = $json['data'] ?? [];
                    
                    return array_map(function($item) {
                        return [
                            'city_id' => $item['id'],
                            'city_name' => $item['name'],
                            'type' => 'Kota', // Default as V2 list doesn't explicitly separate Type in basic list
                            'postal_code' => '', 
                        ];
                    }, $data);
                }
                
                // Legacy V1
                $path = '/city';
                /** @var Response $response */
                $response = Http::withoutVerifying()->withHeaders([
                    'key' => $this->apiKey
                ])->get($this->baseUrl . $path, [
                    'province' => $provinceId
                ]);

                $duration = round((microtime(true) - $startTime) * 1000);
                $this->integrationService->log('rajaongkir', $path, 'GET', ['province' => $provinceId], $response->body(), $response->status(), $duration);

                $json = $response->json() ?? [];
                return $json['rajaongkir']['results'] ?? [];
            } catch (\Exception $e) {
                $duration = round((microtime(true) - $startTime) * 1000);
                $this->integrationService->log('rajaongkir', 'get_cities', 'GET', ['province' => $provinceId], $e->getMessage(), 500, $duration);
                throw $e;
            }
        });
    }

    public function getSubdistricts($cityId)
    {
        return Cache::remember("rajaongkir_subdistricts_{$cityId}", 86400, function () use ($cityId) {
            // ... (existing implementation) ...
            $isV2 = str_contains($this->baseUrl, 'komerce.id');
            $startTime = microtime(true);

            try {
                if ($isV2) {
                    // In V2, Kecamatan is 'District'
                    $path = "/destination/district/{$cityId}";
                    /** @var Response $response */
                    $response = Http::withoutVerifying()->withHeaders(['key' => $this->apiKey])
                        ->get($this->baseUrl . $path);
                    
                    $duration = round((microtime(true) - $startTime) * 1000);
                    $this->integrationService->log('rajaongkir', $path, 'GET', [], $response->body(), $response->status(), $duration);

                    $json = $response->json() ?? [];
                    $data = $json['data'] ?? [];
                    
                    return array_map(function($item) {
                        return [
                            'subdistrict_id' => $item['id'],
                            'subdistrict_name' => $item['name'],
                        ];
                    }, $data);
                }

                // Legacy V1 (Subdistrict = Kecamatan)
                $path = '/subdistrict';
                /** @var Response $response */
                $response = Http::withoutVerifying()->withHeaders([
                    'key' => $this->apiKey
                ])->get($this->baseUrl . $path, [
                    'city' => $cityId
                ]);

                $duration = round((microtime(true) - $startTime) * 1000);
                $this->integrationService->log('rajaongkir', $path, 'GET', ['city' => $cityId], $response->body(), $response->status(), $duration);

                $json = $response->json() ?? [];
                return $json['rajaongkir']['results'] ?? [];
            } catch (\Exception $e) {
                $duration = round((microtime(true) - $startTime) * 1000);
                $this->integrationService->log('rajaongkir', 'get_subdistricts', 'GET', ['city' => $cityId], $e->getMessage(), 500, $duration);
                throw $e;
            }
        });
    }

    public function getVillages($subdistrictId)
    {
        // RajaOngkir Basic usually stops at Subdistrict (Kecamatan)
        return [];
    }

    public function searchDestination($query)
    {
        // V2 Direct Search (POST)
        // Ref: https://rajaongkir.com/docs/shipping-cost/endpoint-rajaongkir-for-search-base/search-destination-rajaongkir
        $startTime = microtime(true);
        $isV2 = str_contains($this->baseUrl, 'komerce.id');

        if ($isV2) {
            $path = '/destination/domestic-destination';
            try {
                /** @var Response $response */
                $response = Http::withoutVerifying()->withHeaders([
                    'key' => $this->apiKey
                ])->post($this->baseUrl . $path, [
                    'search' => $query,
                    'limit' => 20
                ]);
                
                $duration = round((microtime(true) - $startTime) * 1000);
                $this->integrationService->log('rajaongkir', $path, 'POST', ['search' => $query], $response->body(), $response->status(), $duration);

                $json = $response->json() ?? [];
                return $json['data'] ?? [];
            } catch (\Exception $e) {
                $duration = round((microtime(true) - $startTime) * 1000);
                $this->integrationService->log('rajaongkir', $path, 'POST', ['search' => $query], $e->getMessage(), 500, $duration);
                return [];
            }
        }
        
        // Fallback for Legacy (Starter/Basic/Pro)
        // Since there is no search endpoint, we search within All Cities (cached)
        // Note: For Pro account, this limits search to City level only (no subdistricts), which is acceptable for "Test" feature.
        try {
            $cities = $this->getAllCities();
            
            $query = strtolower($query);
            $results = [];
            $count = 0;
            
            foreach ($cities as $city) {
                if ($count >= 20) break;
                
                $cityName = strtolower($city['type'] . ' ' . $city['city_name']);
                if (str_contains($cityName, $query)) {
                    $results[] = [
                        'subdistrict_id' => $city['city_id'], // Map to subdistrict_id for frontend compatibility
                        'subdistrict_name' => null,
                        'city_name' => $city['type'] . ' ' . $city['city_name'],
                        'province_name' => $city['province'],
                        'type' => 'city'
                    ];
                    $count++;
                }
            }
            
            return $results;
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getAllCities()
    {
        // Don't use Cache::remember to avoid caching empty results on failure
        $cached = Cache::get('rajaongkir_all_cities');
        if ($cached && is_array($cached) && count($cached) > 0) {
            return $cached;
        }

        $startTime = microtime(true);
        $path = '/city';
        
        try {
            /** @var Response $response */
            $response = Http::withoutVerifying()->withHeaders([
                'key' => $this->apiKey
            ])->get($this->baseUrl . $path);

            $duration = round((microtime(true) - $startTime) * 1000);
            $this->integrationService->log('rajaongkir', $path, 'GET', [], 'FETCH_ALL_CITIES', $response->status(), $duration);

            if (!$response->successful()) {
                return [];
            }

            $json = $response->json() ?? [];
            $results = $json['rajaongkir']['results'] ?? [];

            // Only cache if we actually got results
            if (!empty($results)) {
                Cache::put('rajaongkir_all_cities', $results, 86400);
            }

            return $results;
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getCost($origin, $destination, $weight, $courier = null)
    {
        $isV2 = str_contains($this->baseUrl, 'komerce.id');
        $path = $isV2 ? '/calculate/domestic-cost' : '/cost';

        // Handle 'all' couriers
        if ($courier === 'all') {
            $supportedCouriers = ['jne', 'pos', 'tiki', 'sicepat', 'jnt', 'anteraja', 'lion', 'ninja'];
            // Filter only enabled couriers from settings if possible, otherwise use defaults
            // For now, we try all common ones supported by the account type
            // Note: Starter only supports jne, pos, tiki. 
            // We should ideally check config/settings.
            
            // For efficiency, we'll try to use Http pool
            $responses = Http::pool(function ($pool) use ($supportedCouriers, $origin, $destination, $weight, $path, $isV2) {
                $requests = [];
                foreach ($supportedCouriers as $c) {
                    $payload = [
                        'origin' => $origin,
                        'destination' => $destination,
                        'weight' => $weight,
                        'courier' => $c,
                    ];
                    if ($isV2) {
                        $payload['originType'] = 'district'; 
                        $payload['destinationType'] = 'district';
                    }
                    
                    $requests[] = $pool->asForm()
                        ->withHeaders(['key' => $this->apiKey])
                        ->post($this->baseUrl . $path, $payload);
                }
                return $requests;
            });

            $allResults = [];
            foreach ($responses as $index => $response) {
                if ($response->successful()) {
                    $json = $response->json() ?? [];
                    
                    // Normalize V2 (Komerce)
                    if ($isV2) {
                        $rawResults = $json['data'] ?? [];
                        if (!empty($rawResults)) {
                            $cCode = $supportedCouriers[$index];
                            $cName = $rawResults[0]['name'] ?? strtoupper($cCode);
                            
                            $costs = array_map(function($item) {
                                return [
                                    'service' => $item['service'],
                                    'description' => $item['description'] ?? '',
                                    'cost' => [
                                        [
                                            'value' => $item['cost'],
                                            'etd' => $item['etd'] ?? '',
                                            'note' => ''
                                        ]
                                    ]
                                ];
                            }, $rawResults);
                            
                            $allResults[] = [
                                'code' => $cCode,
                                'name' => $cName,
                                'costs' => $costs
                            ];
                        }
                    } else {
                        // V1
                        $results = $json['rajaongkir']['results'] ?? [];
                        if (!empty($results)) {
                            $allResults = array_merge($allResults, $results);
                        }
                    }
                }
            }
            
            return $allResults;
        }

        $payload = [
            'origin' => $origin,
            'destination' => $destination,
            'weight' => $weight,
            'courier' => $courier,
        ];

        if ($isV2) {
            $payload['originType'] = 'district'; 
            $payload['destinationType'] = 'district';
        }
        $cacheKey = sprintf(
            'rajaongkir_cost_%s_%s_%s_%s_%s',
            md5($this->baseUrl),
            $origin,
            $destination,
            $weight,
            $courier
        );

        return Cache::remember($cacheKey, 600, function () use ($path, $payload) {
            $maxAttempts = (int) config('services.rajaongkir.max_retries', 3);
            $delayMs = (int) config('services.rajaongkir.retry_delay', 200);

            $attempt = 0;

            while ($attempt < $maxAttempts) {
                $attempt++;
                $startTime = microtime(true);

                try {
                    /** @var Response $response */
                    $response = Http::withoutVerifying()->withHeaders([
                        'key' => $this->apiKey,
                        'content-type' => 'application/x-www-form-urlencoded'
                    ])->asForm()
                        ->timeout((int) config('services.rajaongkir.timeout', 5))
                        ->post($this->baseUrl . $path, $payload);

                    $duration = round((microtime(true) - $startTime) * 1000);

                    $this->integrationService->log(
                        'rajaongkir',
                        $path,
                        'POST',
                        array_merge($payload, ['attempt' => $attempt]),
                        $response->body(),
                        $response->status(),
                        $duration
                    );

                    if ($response->failed()) {
                        $json = $response->json() ?? [];
                        $msg = $json['meta']['message'] ?? $json['rajaongkir']['status']['description'] ?? 'API Request Failed';

                        if ($response->serverError() && $attempt < $maxAttempts) {
                            usleep($delayMs * 1000);
                            continue;
                        }

                        throw new \Exception($msg, $response->status());
                    }

                    $json = $response->json() ?? [];

                    // Normalize V2 (Komerce) to match V1 Structure
                    if (str_contains($this->baseUrl, 'komerce.id')) {
                        $rawResults = $json['data'] ?? [];
                        if (empty($rawResults)) return [];
                        
                        $courierCode = strtolower($payload['courier'] ?? '');
                        $courierName = $rawResults[0]['name'] ?? strtoupper($courierCode);
                        
                        $costs = array_map(function($item) {
                            return [
                                'service' => $item['service'],
                                'description' => $item['description'] ?? '',
                                'cost' => [
                                    [
                                        'value' => $item['cost'],
                                        'etd' => $item['etd'] ?? '',
                                        'note' => ''
                                    ]
                                ]
                            ];
                        }, $rawResults);
                        
                        return [
                            [
                                'code' => $courierCode,
                                'name' => $courierName,
                                'costs' => $costs
                            ]
                        ];
                    }

                    return $json['rajaongkir']['results'] ?? [];
                } catch (\Exception $e) {
                    $duration = round((microtime(true) - $startTime) * 1000);

                    $this->integrationService->log(
                        'rajaongkir',
                        $path,
                        'POST',
                        array_merge($payload, ['attempt' => $attempt]),
                        $e->getMessage(),
                        500,
                        $duration
                    );

                    if ($attempt >= $maxAttempts) {
                        throw $e;
                    }

                    usleep($delayMs * 1000);
                }
            }

            throw new \Exception('API Request Failed');
        });
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Client\Response;
use App\Services\IntegrationService;
use App\Contracts\ShippingProviderInterface;

class ApiIdService implements ShippingProviderInterface
{
    protected $baseUrl;
    protected $regionalBaseUrl;
    protected $apiKey;
    protected $integrationService;

    public function __construct(IntegrationService $integrationService)
    {
        $this->integrationService = $integrationService;
        
        $this->apiKey = $this->integrationService->get('api_id_key');
        // Shipping API Base URL - Default to use.api.co.id
        $this->baseUrl = rtrim($this->integrationService->get('api_id_base_url', 'https://use.api.co.id'), '/');
        // Regional API Base URL - Usually the same
        $this->regionalBaseUrl = 'https://use.api.co.id';
    }

    public function testConnection($apiKey = null, $baseUrl = null)
    {
        $apiKey = $apiKey ?: $this->apiKey;
        $baseUrl = $baseUrl ?: $this->baseUrl;
        $baseUrl = rtrim($baseUrl, '/');

        // Use the shipping cost endpoint with dummy data to test connection
        // Origin: Pademangan, Jakarta (3172051003)
        // Destination: Rancabali, Bandung (3204402005)
        // Weight: 1 kg
        $path = '/expedition/shipping-cost';
        $params = [
            'origin_village_code' => '3172051003',
            'destination_village_code' => '3204402005',
            'weight' => 1
        ];

        $startTime = microtime(true);
        
        try {
            /** @var Response $response */
            $response = Http::withoutVerifying()->withHeaders([
                'x-api-co-id' => $apiKey
            ])->get($baseUrl . $path, $params);

            $duration = round((microtime(true) - $startTime) * 1000);
            
            // Log the attempt
            $this->integrationService->log('api_id', $path, 'GET', $params, $response->body(), $response->status(), $duration);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Connection Successful!',
                    'data' => $response->json(),
                    'status_code' => $response->status(),
                    'duration' => $duration
                ];
            }

            if ($response->status() === 401) {
                 return [
                    'success' => false,
                    'message' => 'Connection Failed: Invalid API Key',
                    'status_code' => 401,
                    'duration' => $duration
                ];
            }

            return [
                'success' => false,
                'message' => 'Connection Failed: ' . $response->status() . ' - ' . ($response->json()['message'] ?? 'Unknown error'),
                'status_code' => $response->status(),
                'duration' => $duration
            ];

        } catch (\Exception $e) {
             $duration = round((microtime(true) - $startTime) * 1000);
             $this->integrationService->log('api_id', $path, 'GET', $params, $e->getMessage(), 500, $duration);
             
             return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage(),
                'status_code' => 500
            ];
        }
    }

    public function getProvinces()
    {
        $path = '/regional/indonesia/provinces';
        $data = $this->fetchLocationData($path, 'code', 'name');
        
        return array_map(function($item) {
            return array_merge($item, [
                'province_id' => $item['code'],
                'province' => $item['name']
            ]);
        }, $data);
    }

    public function getCities($provinceId)
    {
        $path = '/regional/indonesia/regencies';
        $data = $this->fetchLocationData($path, 'code', 'name', ['province_code' => $provinceId]);
        
        return array_map(function($item) {
            $name = $item['name'];
            $type = 'Kabupaten';
            $cityName = $name;
            
            if (str_starts_with(strtoupper($name), 'KAB. ')) {
                $type = 'Kabupaten';
                $cityName = substr($name, 5);
            } elseif (str_starts_with(strtoupper($name), 'KOTA ')) {
                $type = 'Kota';
                $cityName = substr($name, 5);
            }
            
            return array_merge($item, [
                'city_id' => $item['code'],
                'city_name' => $cityName,
                'type' => $type,
                'postal_code' => '' // API ID might not provide postal code at city level
            ]);
        }, $data);
    }

    public function getSubdistricts($cityId)
    {
        // In API ID context, Subdistrict = District (Kecamatan)
        $path = '/regional/indonesia/districts';
        $data = $this->fetchLocationData($path, 'code', 'name', ['regency_code' => $cityId]);
        
        return array_map(function($item) {
            return array_merge($item, [
                'subdistrict_id' => $item['code'],
                'subdistrict_name' => $item['name']
            ]);
        }, $data);
    }

    public function getVillages($subdistrictId)
    {
        // Village (Kelurahan)
        $path = '/regional/indonesia/villages';
        $data = $this->fetchLocationData($path, 'code', 'name', ['district_code' => $subdistrictId]);
        
        return array_map(function($item) {
            return array_merge($item, [
                'village_id' => $item['code'],
                'village_name' => $item['name']
            ]);
        }, $data);
    }

    protected function fetchLocationData($path, $idKey, $nameKey, $params = [])
    {
        try {
            /** @var Response $response */
            $response = Http::withoutVerifying()->withHeaders([
                'x-api-co-id' => $this->apiKey
            ])->get($this->regionalBaseUrl . $path, $params);
            
            if ($response->successful()) {
                $data = $response->json('data') ?? [];
                
                return array_map(function($item) use ($idKey, $nameKey) {
                    // Robust ID/Code handling
                    $rawCode = $item['code'] ?? $item['id'] ?? '';
                    $rawId = $item[$idKey] ?? $item['id'] ?? $item['code'] ?? '';

                    return [
                        'id' => $rawId,
                        'name' => $item[$nameKey] ?? '',
                        'code' => $rawCode,
                        'full_name' => $item['name'] ?? '', 
                        // Additional fields for frontend compatibility if needed
                        'province_id' => $item['province_code'] ?? '',
                        'city_id' => $item['regency_code'] ?? '',
                        'subdistrict_id' => $item['district_code'] ?? '',
                    ];
                }, $data);
            }
            
            // Log error if failed
            $this->integrationService->log('api_id_regional', $path, 'GET', $params, $response->body(), $response->status(), 0);
            
        } catch (\Exception $e) {
            $this->integrationService->log('api_id_regional', $path, 'GET', $params, $e->getMessage(), 500, 0);
        }
        return [];
    }

    public function searchDestination($query)
    {
        // 1. Search in Villages directly
        $villages = [];
        try {
            $path = '/regional/indonesia/villages';
            $params = ['name' => $query];
            /** @var Response $response */
            $response = Http::withoutVerifying()->withHeaders(['x-api-co-id' => $this->apiKey])
                            ->get($this->regionalBaseUrl . $path, $params);
            
            if ($response->successful()) {
                $villages = $response->json('data') ?? [];
            }
        } catch (\Exception $e) {
            // silent fail
        }

        // 2. Search in Districts (Kecamatan) to find villages in matching districts
        // This covers cases where user searches for "Coblong" (a district) but no village is named "Coblong"
        $districtVillages = [];
        try {
            $path = '/regional/indonesia/districts';
            $params = ['name' => $query];
            /** @var Response $response */
            $response = Http::withoutVerifying()->withHeaders(['x-api-co-id' => $this->apiKey])
                            ->get($this->regionalBaseUrl . $path, $params);
            
            if ($response->successful()) {
                 $districts = $response->json('data') ?? [];
                 
                 // Fallback: If no districts found and query has multiple words, try searching for the last word
                 // This helps with "Bandung Coblong" where "Coblong" is the district
                 if (empty($districts) && str_contains($query, ' ')) {
                     $words = explode(' ', $query);
                     $lastWord = end($words);
                     if (strlen($lastWord) >= 3) {
                         try {
                            /** @var Response $res2 */
                            $res2 = Http::withoutVerifying()->withHeaders(['x-api-co-id' => $this->apiKey])
                                            ->get($this->regionalBaseUrl . $path, ['name' => $lastWord]);
                             if ($res2->successful()) {
                                 $districts = $res2->json('data') ?? [];
                             }
                         } catch (\Exception $e) {}
                     }
                 }

                 // Limit to top 3 districts to avoid too many requests
                 $districts = array_slice($districts, 0, 3);
                
                foreach ($districts as $district) {
                    // Fetch villages for this district manually to ensure we get parent names
                    try {
                        /** @var Response $vResponse */
                        $vResponse = Http::withoutVerifying()->withHeaders(['x-api-co-id' => $this->apiKey])
                                         ->get($this->regionalBaseUrl . '/regional/indonesia/villages', ['district_code' => $district['code']]);
                        
                        if ($vResponse->successful()) {
                            $vs = $vResponse->json('data') ?? [];
                            
                            foreach ($vs as $v) {
                                $subdistrictName = $v['name'] . ', ' . ($v['district'] ?? $district['name']);
                                
                                $districtVillages[] = [
                                    'subdistrict_id' => $v['code'],
                                    'subdistrict_name' => $subdistrictName,
                                    'city_name' => $v['regency'] ?? ($district['regency'] ?? 'City'),
                                    'province_name' => $v['province'] ?? ($district['province'] ?? 'Province'),
                                    'type' => 'Village'
                                ];
                            }
                        }
                    } catch (\Exception $e) {}
                }
            }
        } catch (\Exception $e) {
            // silent fail
        }

        // 3. Normalize Villages result
        $normalizedVillages = array_map(function($item) {
             $subdistrictName = $item['name'] . ', ' . ($item['district'] ?? '');
             return [
                'subdistrict_id' => $item['code'],
                'subdistrict_name' => $subdistrictName,
                'city_name' => $item['regency'] ?? 'City',
                'province_name' => $item['province'] ?? 'Province',
                'type' => 'Village'
             ];
        }, $villages);

        // Merge and unique
        $results = array_merge($normalizedVillages, $districtVillages);
        
        // Remove duplicates by subdistrict_id (Village Code)
        $uniqueResults = [];
        foreach ($results as $item) {
            $uniqueResults[$item['subdistrict_id']] = $item;
        }
        
        return array_values(array_slice($uniqueResults, 0, 30));
    }

    public function getCost($origin, $destination, $weight, $courier = null)
    {
        $path = '/expedition/shipping-cost';
        
        // Convert weight from grams to kg (rounding up) because API ID expects kg
        // Input $weight is in grams (e.g. 1000 = 1kg)
        $weightKg = ceil($weight / 1000);
        $weightKg = $weightKg > 0 ? $weightKg : 1;

        $payload = [
            'origin_village_code' => $origin,
            'destination_village_code' => $destination,
            'weight' => $weightKg, 
        ];

        $startTime = microtime(true);
        
        try {
            /** @var Response $response */
            $response = Http::withoutVerifying()->withHeaders([
                'x-api-co-id' => $this->apiKey,
                'Accept' => 'application/json'
            ])->get($this->baseUrl . $path, $payload);

            $duration = round((microtime(true) - $startTime) * 1000);
            $this->integrationService->log('api_id', $path, 'GET', $payload, $response->body(), $response->status(), $duration);

            if ($response->failed()) {
                throw new \Exception('API ID Request Failed: ' . $response->status() . ' - ' . ($response->json()['message'] ?? ''));
            }

            $json = $response->json();
            $data = $json['data'] ?? [];
            // API ID returns 'couriers' array inside data
            $couriers = $data['couriers'] ?? [];
            
            // Normalize to RajaOngkir structure for frontend compatibility
            // Target: [{ code: 'jne', name: 'JNE Express', costs: [{ service: 'REG', description: '...', cost: [{ value: 1000, etd: '1-2' }] }] }]
            
            $normalized = [];
            
            foreach ($couriers as $row) {
                $code = strtolower($row['courier_code'] ?? '');
                
                // Filter courier if specified
                if ($courier && $courier !== 'all' && strtolower($courier) !== $code) {
                    continue;
                }

                $serviceName = $row['service'] ?? 'Standard';
                $description = $row['description'] ?? $serviceName;
                $price = $row['price'] ?? 0;
                $etd = $row['estimation'] ?? '-';
                $courierName = $row['courier_name'] ?? strtoupper($code);

                // Group by courier code
                if (!isset($normalized[$code])) {
                    $normalized[$code] = [
                        'code' => $code,
                        'name' => $courierName,
                        'costs' => []
                    ];
                }

                $normalized[$code]['costs'][] = [
                    'service' => $serviceName,
                    'description' => $description,
                    'cost' => [
                        [
                            'value' => $price,
                            'etd' => $etd,
                            'note' => ''
                        ]
                    ]
                ];
            }
            
            return array_values($normalized);

        } catch (\Exception $e) {
            // Log is already handled in catch block above or by integrationService inside log
            // But we need to ensure we don't double log if we re-throw
            // Just return empty or re-throw for controller to handle
            throw $e;
        }
    }
}

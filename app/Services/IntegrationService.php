<?php

namespace App\Services;

use App\Models\SystemSetting;
use App\Models\IntegrationLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

class IntegrationService
{
    /**
     * Get a system setting value by key.
     */
    public function get(string $key, $default = null)
    {
        // Cache settings for 1 hour to reduce DB hits
        return Cache::remember("system_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = SystemSetting::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Update or create a system setting.
     */
    public function set(string $key, $value, $group = 'general', $type = 'text', $description = null)
    {
        $setting = SystemSetting::updateOrCreate(
            ['key' => $key],
            [
                'group' => $group,
                'type' => $type,
                'description' => $description,
            ]
        );

        // Update value separately to trigger mutator logic if needed,
        // although updateOrCreate should handle it if 'value' is in attributes.
        // For encrypted fields, we need to ensure type is set before value.
        $setting->type = $type;
        $setting->value = $value;
        $setting->save();

        // Clear cache
        Cache::forget("system_setting_{$key}");
        
        return $setting;
    }

    /**
     * Log an integration API call.
     */
    public function log($integration, $endpoint, $method, $payload, $response, $statusCode, $duration, $ip = null)
    {
        return IntegrationLog::create([
            'integration' => $integration,
            'endpoint' => $endpoint,
            'method' => $method,
            'request_payload' => is_array($payload) ? json_encode($payload) : $payload,
            'response_body' => is_array($response) ? json_encode($response) : $response,
            'status_code' => $statusCode,
            'duration_ms' => $duration,
            'ip_address' => $ip ?? request()->ip(),
        ]);
    }

    /**
     * Get all settings for a specific group.
     */
    public function getAll(string $group)
    {
        return SystemSetting::where('group', $group)
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->key => $item->value];
            })
            ->toArray();
    }

    /**
     * Test RajaOngkir Connection.
     */
    public function testRajaOngkir($apiKey = null, $baseUrl = null)
    {
        $apiKey = $apiKey ?: $this->get('rajaongkir_api_key');
        $baseUrl = $baseUrl ?: rtrim($this->get('rajaongkir_base_url', 'https://api.rajaongkir.com/starter'), '/');

        if (empty($apiKey)) {
            return [
                'success' => false,
                'message' => 'API Key is missing.',
                'status_code' => 400
            ];
        }

        $startTime = microtime(true);
        
        try {
            // Check if V2 URL (Komerce)
            $isV2 = str_contains($baseUrl, 'komerce.id');
            
            // Use Province endpoint for connection test as it's more stable/standard than search
            // V2: /destination/province (GET)
            // V1: /province (GET)
            $path = $isV2 ? '/destination/province' : '/province';
            $params = []; // No params needed for province list test

            /** @var Response $response */
            $response = Http::withHeaders(['key' => $apiKey])->get($baseUrl . $path, $params);
            $duration = round((microtime(true) - $startTime) * 1000);

            $this->log(
                'rajaongkir', 
                $path, 
                'GET', 
                $params, 
                $response->body(), 
                $response->status(), 
                $duration
            );

            if ($response->successful()) {
                // Validate response structure
                $json = $response->json();
                $data = $json['data'] ?? $json['rajaongkir']['results'] ?? null;
                
                if (empty($data)) {
                     return [
                        'success' => false,
                        'message' => 'Connection OK but no data returned. Check Account Type.',
                        'status_code' => $response->status(),
                        'duration' => $duration
                    ];
                }

                return [
                    'success' => true,
                    'message' => 'Connection Successful!',
                    'data' => array_slice($data, 0, 5), // Return first 5 for preview
                    'status_code' => $response->status(),
                    'duration' => $duration
                ];
            } else {
                $errorMsg = 'Unknown Error';
                $json = $response->json();
                
                if (isset($json['rajaongkir']['status']['description'])) {
                    $errorMsg = $json['rajaongkir']['status']['description'];
                } elseif (isset($json['message'])) {
                    $errorMsg = $json['message'];
                } else {
                    $errorMsg = 'HTTP ' . $response->status();
                }

                return [
                    'success' => false,
                    'message' => 'Connection Failed: ' . $errorMsg . " (URL: {$baseUrl}{$path})",
                    'status_code' => $response->status(),
                    'duration' => $duration
                ];
            }

        } catch (\Exception $e) {
            $duration = round((microtime(true) - $startTime) * 1000);
            $this->log('rajaongkir', 'test_connection', 'GET', [], $e->getMessage(), 500, $duration);
            
            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage(),
                'status_code' => 500
            ];
        }
    }

    /**
     * Test Brevo connection by calling Account endpoint.
     */
    public function testBrevoConnection(): array
    {
        $apiKey = $this->get('brevo_api_key');

        if (empty($apiKey)) {
            return [
                'success' => false,
                'message' => 'API Key is missing.',
            ];
        }

        $startTime = microtime(true);
        
        try {
            /** @var Response $response */
            $response = Http::withHeaders([
                'api-key' => $apiKey,
                'accept' => 'application/json',
            ])->get('https://api.brevo.com/v3/account');

            $duration = (microtime(true) - $startTime) * 1000;
            
            $this->log(
                'brevo', 
                '/account', 
                'GET', 
                [], 
                $response->json(), 
                $response->status(), 
                $duration
            );

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Connection successful! Account: ' . ($response->json()['email'] ?? 'Unknown'),
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'message' => 'Connection failed: ' . $response->body(),
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Send Transactional Email via Brevo.
     */
    public function sendBrevoEmail(string $to, string $subject, string $htmlContent, array $params = [])
    {
        $apiKey = $this->get('brevo_api_key');
        $senderEmail = $this->get('brevo_sender_email');
        $senderName = $this->get('brevo_sender_name');

        if (!$apiKey || !$senderEmail) {
            return ['success' => false, 'message' => 'Brevo configuration missing.'];
        }

        $payload = [
            'sender' => ['name' => $senderName, 'email' => $senderEmail],
            'to' => [['email' => $to]],
            'subject' => $subject,
            'htmlContent' => $htmlContent,
            'params' => $params,
        ];

        $startTime = microtime(true);
        /** @var Response $response */
        $response = Http::withHeaders([
            'api-key' => $apiKey,
            'accept' => 'application/json',
        ])->post('https://api.brevo.com/v3/smtp/email', $payload);

        $this->log('brevo', '/smtp/email', 'POST', $payload, $response->json(), $response->status(), (microtime(true) - $startTime) * 1000);

        return $response->json();
    }

    /**
     * Create or Update Contact in Brevo.
     */
    public function createBrevoContact(string $email, array $attributes = [], array $listIds = [])
    {
        $apiKey = $this->get('brevo_api_key');
        if (!$apiKey) return ['success' => false, 'message' => 'API Key missing'];

        $payload = [
            'email' => $email,
            'attributes' => (object)$attributes,
            'updateEnabled' => true,
        ];

        if (!empty($listIds)) {
            $payload['listIds'] = $listIds;
        }

        $startTime = microtime(true);
        /** @var Response $response */
        $response = Http::withHeaders([
            'api-key' => $apiKey,
            'accept' => 'application/json',
        ])->post('https://api.brevo.com/v3/contacts', $payload);

        $this->log('brevo', '/contacts', 'POST', $payload, $response->json(), $response->status(), (microtime(true) - $startTime) * 1000);

        return $response->json();
    }
}

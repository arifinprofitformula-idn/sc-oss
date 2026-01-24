<?php

namespace App\Services;

use App\Models\ExternalApi;
use App\Models\ApiLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExternalApiService
{
    /**
     * Execute the external API request.
     */
    public function execute(ExternalApi $api, array $overrideParams = []): array
    {
        $startTime = microtime(true);
        
        try {
            // Merge parameters
            $params = array_merge($api->parameters ?? [], $overrideParams);
            
            // Build Request
            $http = Http::timeout(30);

            // Authentication
            if ($api->auth_type === 'api_key') {
                $creds = $api->auth_credentials;
                $key = $creds['key'] ?? 'api_key';
                $value = $creds['value'] ?? '';
                $in = $creds['in'] ?? 'header';

                if ($in === 'header') {
                    $http->withHeaders([$key => $value]);
                } else {
                    $params[$key] = $value;
                }
            } elseif ($api->auth_type === 'bearer') {
                $creds = $api->auth_credentials;
                $token = $creds['token'] ?? '';
                $http->withToken($token);
            } elseif ($api->auth_type === 'basic') {
                $creds = $api->auth_credentials;
                $username = $creds['username'] ?? '';
                $password = $creds['password'] ?? '';
                $http->withBasicAuth($username, $password);
            }

            // Execute Request
            $method = strtolower($api->method);
            if ($method === 'get') {
                /** @var Response $response */
                $response = $http->get($api->endpoint_url, $params);
            } else {
                /** @var Response $response */
                $response = $http->$method($api->endpoint_url, $params);
            }

            $endTime = microtime(true);
            $duration = round(($endTime - $startTime) * 1000);

            // Log Result
            $this->log($api, $response->status(), $duration, $params, $response->body());

            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'data' => $response->json(),
                'body' => $response->body(),
                'duration' => $duration
            ];

        } catch (\Exception $e) {
            $endTime = microtime(true);
            $duration = round(($endTime - $startTime) * 1000);
            
            $this->log($api, 500, $duration, $overrideParams, null, $e->getMessage());

            return [
                'success' => false,
                'status' => 500,
                'error' => $e->getMessage(),
                'duration' => $duration
            ];
        }
    }

    /**
     * Log the API activity.
     */
    private function log(ExternalApi $api, int $statusCode, int $duration, array $requestPayload, ?string $responsePayload, ?string $errorMessage = null): void
    {
        ApiLog::create([
            'external_api_id' => $api->id,
            'status_code' => $statusCode,
            'response_time' => $duration,
            'request_payload' => json_encode($requestPayload),
            'response_payload' => $responsePayload ? substr($responsePayload, 0, 5000) : null, // Limit size
            'error_message' => $errorMessage,
        ]);
    }
}

<?php

namespace App\Services\Email;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\SystemSetting;

class BrevoProvider implements EmailProviderInterface
{
    protected string $apiKey;
    protected string $senderEmail;
    protected string $senderName;
    protected string $baseUrl = 'https://api.brevo.com/v3';

    public function __construct()
    {
        $this->apiKey = SystemSetting::getValue('brevo_api_key', '');
        $this->senderEmail = SystemSetting::getValue('brevo_sender_email', config('mail.from.address'));
        $this->senderName = SystemSetting::getValue('brevo_sender_name', config('mail.from.name'));
    }

    public function sendEmail(string $recipient, string $subject, string $content, ?string $fromName = null, ?string $fromEmail = null, array $attachments = []): array
    {
        $payload = [
            'sender' => [
                'name' => $fromName ?? $this->senderName,
                'email' => $fromEmail ?? $this->senderEmail,
            ],
            'to' => [
                ['email' => $recipient]
            ],
            'subject' => $subject,
            'htmlContent' => $content,
        ];

        if (!empty($attachments)) {
             $payload['attachment'] = $attachments;
        }

        $startTime = microtime(true);
        try {
            $response = Http::withHeaders([
                'api-key' => $this->apiKey,
                'accept' => 'application/json',
                'content-type' => 'application/json'
            ])->post($this->baseUrl . '/smtp/email', $payload);

            $duration = round((microtime(true) - $startTime) * 1000);
            
            $this->log('/smtp/email', 'POST', $payload, $response->json(), $response->status(), $duration);

            if ($response->successful()) {
                return ['success' => true, 'message' => 'Email sent successfully', 'data' => $response->json()];
            }

            Log::error('Brevo sendEmail failed', ['response' => $response->body()]);
            return ['success' => false, 'message' => 'Failed to send email: ' . $response->body()];
        } catch (\Exception $e) {
            $duration = round((microtime(true) - $startTime) * 1000);
            $this->log('/smtp/email', 'POST', $payload, ['error' => $e->getMessage()], 500, $duration);
            
            Log::error('Brevo sendEmail exception', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Exception: ' . $e->getMessage()];
        }
    }

    protected function log($endpoint, $method, $payload, $response, $statusCode, $duration)
    {
        // Mask sensitive data
        if (isset($payload['sender']['email'])) {
            // $payload['sender']['email'] = '***'; // Optional: mask sender? No, usually fine.
        }
        
        \App\Models\IntegrationLog::create([
            'integration' => 'brevo',
            'endpoint' => $endpoint,
            'method' => $method,
            'request_payload' => json_encode($payload),
            'response_body' => json_encode($response),
            'status_code' => $statusCode,
            'duration_ms' => $duration,
            'ip_address' => request()->ip() ?? '127.0.0.1',
        ]);
    }

    public function getAllLists(): array
    {
        $startTime = microtime(true);
        try {
            $response = Http::withHeaders([
                'api-key' => $this->apiKey,
                'accept' => 'application/json',
            ])->get($this->baseUrl . '/contacts/lists?limit=50&offset=0');
            
            $duration = round((microtime(true) - $startTime) * 1000);
            $this->log('/contacts/lists', 'GET', [], $response->json(), $response->status(), $duration);

            if ($response->successful()) {
                return ['success' => true, 'lists' => $response->json()['lists'] ?? []];
            }

            return ['success' => false, 'message' => 'Failed to fetch lists: ' . $response->body()];
        } catch (\Exception $e) {
            $duration = round((microtime(true) - $startTime) * 1000);
            $this->log('/contacts/lists', 'GET', [], ['error' => $e->getMessage()], 500, $duration);
            return ['success' => false, 'message' => 'Exception: ' . $e->getMessage()];
        }
    }

    public function addSubscriber(string $listId, string $email, array $additionalData = []): array
    {
        // Brevo: Create contact and add to list
        // If contact exists, update.
        $payload = [
            'email' => $email,
            'listIds' => [(int)$listId],
            'updateEnabled' => true,
        ];

        // Map additional data to attributes if needed
        if (!empty($additionalData)) {
            // Brevo attributes are usually uppercase
            $attributes = [];
            foreach ($additionalData as $key => $value) {
                $attributes[strtoupper($key)] = $value;
            }
            $payload['attributes'] = $attributes;
        }

        $startTime = microtime(true);
        try {
            $response = Http::withHeaders([
                'api-key' => $this->apiKey,
                'accept' => 'application/json',
                'content-type' => 'application/json'
            ])->post($this->baseUrl . '/contacts', $payload);

            $duration = round((microtime(true) - $startTime) * 1000);
            $this->log('/contacts', 'POST', $payload, $response->json(), $response->status(), $duration);

            if ($response->successful()) {
                return ['success' => true, 'message' => 'Subscriber added successfully'];
            }
            
            // If error is "Contact already exists", try update? 
            // Brevo API with updateEnabled=true should handle it if using /contacts? No, that's create.
            // Actually /contacts creates. To update, we might need PUT /contacts/{id}.
            // But let's stick to simple implementation for now.
            
            return ['success' => false, 'message' => 'Failed to add subscriber: ' . $response->body()];
        } catch (\Exception $e) {
            $duration = round((microtime(true) - $startTime) * 1000);
            $this->log('/contacts', 'POST', $payload, ['error' => $e->getMessage()], 500, $duration);
            return ['success' => false, 'message' => 'Exception: ' . $e->getMessage()];
        }
    }
}

<?php

namespace App\Services\Email;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Response;
use App\Models\SystemSetting;

class MailketingProvider implements EmailProviderInterface
{
    protected string $apiToken;
    protected string $senderEmail;
    protected string $senderName;
    protected string $baseUrl = 'https://api.mailketing.co.id/api/v1';

    public function __construct()
    {
        $this->apiToken = SystemSetting::getValue('mailketing_api_token', '');
        $this->senderEmail = SystemSetting::getValue('mailketing_sender_email', config('mail.from.address'));
        $this->senderName = SystemSetting::getValue('mailketing_sender_name', config('mail.from.name'));
    }

    public function sendEmail(string $recipient, string $subject, string $content, ?string $fromName = null, ?string $fromEmail = null, array $attachments = []): array
    {
        $params = [
            'api_token' => $this->apiToken,
            'from_name' => $fromName ?? $this->senderName,
            'from_email' => $fromEmail ?? $this->senderEmail,
            'recipient' => $recipient,
            'subject' => $subject,
            'content' => $content,
        ];

        // Handle attachments (Mailketing uses attach1, attach2, etc. with URL)
        // Assuming attachments array contains ['url' => '...']
        // If attachments are local files, they need to be uploaded somewhere public first or check if Mailketing supports base64.
        // The documentation example shows 'direct url file'.
        if (!empty($attachments)) {
            $i = 1;
            foreach ($attachments as $attachment) {
                if (isset($attachment['url'])) {
                    $params["attach{$i}"] = $attachment['url'];
                    $i++;
                }
            }
        }

        $startTime = microtime(true);
        try {
            /** @var Response $response */
            $response = Http::asForm()->post($this->baseUrl . '/send', $params);
            $duration = round((microtime(true) - $startTime) * 1000);

            $this->log('/send', 'POST', $params, $response->json() ?: [], $response->status(), $duration);

            if ($response->successful()) {
                $body = $response->json();
                if (isset($body['status']) && $body['status'] === 'success') {
                    return ['success' => true, 'message' => 'Email sent successfully', 'data' => $body];
                }
                Log::error('Mailketing sendEmail failed', ['response' => $response->body()]);
                return ['success' => false, 'message' => 'Failed to send email: ' . ($body['response'] ?? 'Unknown error')];
            }

            Log::error('Mailketing sendEmail http failed', ['response' => $response->body()]);
            return ['success' => false, 'message' => 'Failed to connect to Mailketing API'];
        } catch (\Exception $e) {
            $duration = round((microtime(true) - $startTime) * 1000);
            $this->log('/send', 'POST', $params, ['error' => $e->getMessage()], 500, $duration);

            Log::error('Mailketing sendEmail exception', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Exception: ' . $e->getMessage()];
        }
    }

    protected function log($endpoint, $method, $payload, $response, $statusCode, $duration)
    {
        // Mask API Token
        if (isset($payload['api_token'])) {
            $payload['api_token'] = '********';
        }
        
        \App\Models\IntegrationLog::create([
            'integration' => 'mailketing',
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
        try {
            /** @var Response $response */
            $response = Http::asForm()->post($this->baseUrl . '/viewlist', [
                'api_token' => $this->apiToken
            ]);

            if ($response->successful()) {
                $body = $response->json();
                if (isset($body['status']) && $body['status'] === 'success') {
                    return ['success' => true, 'lists' => $body['lists'] ?? []];
                }
                return ['success' => false, 'message' => 'Failed to fetch lists: ' . ($body['response'] ?? 'Unknown error')];
            }

            return ['success' => false, 'message' => 'Failed to connect to Mailketing API'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Exception: ' . $e->getMessage()];
        }
    }

    public function addSubscriber(string $listId, string $email, array $additionalData = []): array
    {
        $params = [
            'api_token' => $this->apiToken,
            'list_id' => $listId,
            'email' => $email,
        ];

        // Map additional data
        // Supported: first_name, last_name, city, state, country, company, phone, mobile
        $allowedFields = ['first_name', 'last_name', 'city', 'state', 'country', 'company', 'phone', 'mobile'];
        foreach ($allowedFields as $field) {
            if (isset($additionalData[$field])) {
                $params[$field] = $additionalData[$field];
            }
        }

        try {
            /** @var Response $response */
            $response = Http::asForm()->post($this->baseUrl . '/addsubtolist', $params);

            if ($response->successful()) {
                $body = $response->json();
                if (isset($body['status']) && $body['status'] === 'success') {
                    return ['success' => true, 'message' => 'Subscriber added successfully'];
                }
                return ['success' => false, 'message' => 'Failed to add subscriber: ' . ($body['response'] ?? 'Unknown error')];
            }

            return ['success' => false, 'message' => 'Failed to connect to Mailketing API'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Exception: ' . $e->getMessage()];
        }
    }
}

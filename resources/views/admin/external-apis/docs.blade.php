<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('External API Module Documentation') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mx-[10px] sm:mx-0 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8 text-gray-900 dark:text-gray-100 prose dark:prose-invert max-w-none">
                    
                    <h1>External API Management Module</h1>
                    <p>This module allows the Super Admin to configure, manage, and monitor external API integrations for the SC OSS application.</p>

                    <h2>1. Getting Started</h2>
                    <p>To add a new API integration:</p>
                    <ol>
                        <li>Navigate to the <strong>External APIs</strong> dashboard.</li>
                        <li>Click <strong>Add New API</strong>.</li>
                        <li>Fill in the required details:
                            <ul>
                                <li><strong>Name</strong>: A unique identifier for the API (e.g., "Payment Gateway X").</li>
                                <li><strong>Endpoint URL</strong>: The full URL of the API endpoint.</li>
                                <li><strong>Method</strong>: HTTP method (GET, POST, PUT, DELETE).</li>
                                <li><strong>Auth Type</strong>: Choose the authentication method required by the external service.</li>
                            </ul>
                        </li>
                        <li>Configure <strong>Rate Limits</strong> to prevent abuse or exceeding quotas.</li>
                    </ol>

                    <h2>2. Authentication Methods</h2>
                    <ul>
                        <li><strong>None</strong>: Public APIs.</li>
                        <li><strong>API Key</strong>: Supports keys in Headers or Query Parameters. Specify the Key Name (e.g., <code>X-API-KEY</code>) and Value.</li>
                        <li><strong>Bearer Token</strong>: Standard Bearer token authentication.</li>
                        <li><strong>Basic Auth</strong>: Username and Password authentication (Base64 encoded automatically).</li>
                    </ul>

                    <h2>3. Testing & Monitoring</h2>
                    <p>Each API configuration includes a <strong>Test Console</strong>:</p>
                    <ul>
                        <li>Go to the API detail view.</li>
                        <li>Enter JSON parameters in the override box to test specific scenarios.</li>
                        <li>Click <strong>Run Test Request</strong> to execute a real HTTP request.</li>
                        <li>View the response status, time, and body immediately.</li>
                    </ul>
                    <p><strong>Logs:</strong> Every request made through this service is logged in the database, tracking status codes, response times, and errors.</p>

                    <h2>4. Backup & Restore</h2>
                    <p>You can export all API configurations to a JSON file for backup or migration purposes using the <strong>Backup / Export</strong> button. To restore, upload a valid JSON file using the import form.</p>

                    <h2>5. Developer Implementation</h2>
                    <p>To use these configured APIs in the codebase, inject the <code>ExternalApiService</code>:</p>
                    <pre><code class="language-php">
use App\Services\ExternalApiService;
use App\Models\ExternalApi;

public function processPayment(ExternalApiService $apiService) {
    $api = ExternalApi::where('name', 'Payment Gateway X')->first();
    
    $response = $apiService->execute($api, [
        'amount' => 10000,
        'order_id' => 'ORD-123'
    ]);

    if ($response['success']) {
        // Handle success
    } else {
        // Handle error: $response['error']
    }
}
                    </code></pre>

                    <h2>6. Troubleshooting & Error Codes</h2>
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr>
                                <th class="border-b p-2">Error</th>
                                <th class="border-b p-2">Possible Cause</th>
                                <th class="border-b p-2">Solution</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="border-b p-2">500 Internal Server Error</td>
                                <td class="border-b p-2">Exception during request execution (e.g., DNS failure, timeout).</td>
                                <td class="border-b p-2">Check the Endpoint URL and server connectivity. Increase timeout in service if needed.</td>
                            </tr>
                            <tr>
                                <td class="border-b p-2">401 Unauthorized</td>
                                <td class="border-b p-2">Invalid credentials.</td>
                                <td class="border-b p-2">Verify the API Key or Token in the configuration.</td>
                            </tr>
                            <tr>
                                <td class="border-b p-2">429 Too Many Requests</td>
                                <td class="border-b p-2">External service rate limit hit.</td>
                                <td class="border-b p-2">Adjust the rate limit settings or wait before retrying.</td>
                            </tr>
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>

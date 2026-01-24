<?php

use Illuminate\Contracts\Console\Kernel;
use App\Services\IntegrationService;
use App\Services\ApiIdService;

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';

$app->make(Kernel::class)->bootstrap();

echo "Bootstrapped successfully.\n";

try {
    $integrationService = $app->make(IntegrationService::class);
    $apiIdService = $app->make(ApiIdService::class);
    
    $apiKey = $integrationService->get('api_id_key');
    $baseUrl = $integrationService->get('api_id_base_url', 'https://use.api.co.id');
    
    echo "API Key: " . ($apiKey ? substr($apiKey, 0, 5) . '...' : 'NULL') . "\n";
    echo "Base URL: " . $baseUrl . "\n";
    
    echo "Testing Connection...\n";
    $test = $apiIdService->testConnection();
    print_r($test);
    
    if ($test['success']) {
        echo "Testing getProvinces()...\n";
        $provinces = $apiIdService->getProvinces();
        echo "Found " . count($provinces) . " provinces.\n";
    } else {
        echo "Connection failed, skipping getProvinces.\n";
    }

} catch (\Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}

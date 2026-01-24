<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = app(App\Services\IntegrationService::class);
$provider = $service->get('shipping_provider');

echo "Current Shipping Provider: " . $provider . "\n";

<?php

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$provider = SystemSetting::where('key', 'shipping_provider')->first();
echo "Current Shipping Provider: " . ($provider ? $provider->value : 'Not Set (Default: rajaongkir)') . "\n";

$apiKey = SystemSetting::where('key', 'rajaongkir_api_key')->first();
echo "RajaOngkir Key: " . ($apiKey ? substr($apiKey->value, 0, 5) . '...' : 'Not Set') . "\n";

$apiIdKey = SystemSetting::where('key', 'api_id_key')->first();
echo "ApiId Key: " . ($apiIdKey ? substr($apiIdKey->value, 0, 5) . '...' : 'Not Set') . "\n";

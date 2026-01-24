<?php

use App\Services\IntegrationService;
use App\Services\RajaOngkirService;
use App\Services\ApiIdService;
use Illuminate\Support\Facades\Cache;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$integrationService = app(IntegrationService::class);
$rajaOngkir = new RajaOngkirService($integrationService);
$apiId = new ApiIdService($integrationService);

$provider = $integrationService->get('shipping_provider', 'rajaongkir');
echo "Active Provider: $provider\n\n";

if ($provider === 'api_id') {
    echo "--- Testing ApiIdService Chain ---\n";
    try {
        // 1. Provinces
        echo "1. Fetching Provinces...\n";
        $provinces = $apiId->getProvinces();
        echo "   Count: " . count($provinces) . "\n";
        if (empty($provinces)) die("No provinces found.\n");
        $prov = $provinces[0];
        echo "   Selected Province: " . $prov['province'] . " (ID: " . $prov['province_id'] . ")\n";

        // 2. Cities
        echo "2. Fetching Cities for Province ID {$prov['province_id']}...\n";
        $cities = $apiId->getCities($prov['province_id']);
        echo "   Count: " . count($cities) . "\n";
        if (empty($cities)) die("No cities found.\n");
        $city = $cities[0];
        echo "   Selected City: " . $city['city_name'] . " (ID: " . $city['city_id'] . ")\n";

        // 3. Subdistricts
        echo "3. Fetching Subdistricts for City ID {$city['city_id']}...\n";
        $subdistricts = $apiId->getSubdistricts($city['city_id']);
        echo "   Count: " . count($subdistricts) . "\n";
        if (empty($subdistricts)) die("No subdistricts found.\n");
        $sub = $subdistricts[0];
        echo "   Selected Subdistrict: " . $sub['subdistrict_name'] . " (ID: " . $sub['subdistrict_id'] . ")\n";

        // 4. Villages
        echo "4. Fetching Villages for Subdistrict ID {$sub['subdistrict_id']}...\n";
        $villages = $apiId->getVillages($sub['subdistrict_id']);
        echo "   Count: " . count($villages) . "\n";
        if (empty($villages)) {
            echo "   [WARNING] No villages found. This might be expected for some areas, or API issue.\n";
        } else {
            $village = $villages[0];
            echo "   Selected Village: " . $village['village_name'] . " (ID: " . $village['village_id'] . ")\n";
        }

    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        echo $e->getTraceAsString();
    }
} else {
    echo "--- Testing RajaOngkirService Chain ---\n";
    try {
        // 1. Provinces
        echo "1. Fetching Provinces...\n";
        $provinces = $rajaOngkir->getProvinces();
        echo "   Count: " . count($provinces) . "\n";
        if (empty($provinces)) die("No provinces found.\n");
        $prov = $provinces[0];
        echo "   Selected Province: " . $prov['province'] . " (ID: " . $prov['province_id'] . ")\n";

        // 2. Cities
        echo "2. Fetching Cities for Province ID {$prov['province_id']}...\n";
        $cities = $rajaOngkir->getCities($prov['province_id']);
        echo "   Count: " . count($cities) . "\n";
        if (empty($cities)) die("No cities found.\n");
        $city = $cities[0];
        echo "   Selected City: " . $city['city_name'] . " (ID: " . $city['city_id'] . ")\n";

        // 3. Subdistricts
        echo "3. Fetching Subdistricts for City ID {$city['city_id']}...\n";
        $subdistricts = $rajaOngkir->getSubdistricts($city['city_id']);
        echo "   Count: " . count($subdistricts) . "\n";
        if (empty($subdistricts)) die("No subdistricts found.\n");
        $sub = $subdistricts[0];
        echo "   Selected Subdistrict: " . $sub['subdistrict_name'] . " (ID: " . $sub['subdistrict_id'] . ")\n";

        // 4. Villages (RajaOngkir usually doesn't support villages in basic plan, but method exists)
        echo "4. Fetching Villages for Subdistrict ID {$sub['subdistrict_id']}...\n";
        try {
            $villages = $rajaOngkir->getVillages($sub['subdistrict_id']);
            echo "   Count: " . count($villages) . "\n";
        } catch (\Exception $e) {
            echo "   [INFO] Village fetch failed (expected for Basic plan): " . $e->getMessage() . "\n";
        }

    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

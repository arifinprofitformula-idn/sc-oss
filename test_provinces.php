<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $controller = $app->make(\App\Http\Controllers\UserProfileController::class);

    echo "Testing UserProfileController::getProvinces() directly...\n";
    $response = $controller->getProvinces();

    if ($response instanceof \Illuminate\Http\JsonResponse) {
        echo "Status: " . $response->getStatusCode() . "\n";
        $data = $response->getData(true);
        echo "Count: " . count($data) . "\n";
        if (count($data) > 0) {
            echo "First Item: " . print_r($data[0], true) . "\n";
        }
    } else {
        echo "Response is not JSON\n";
    }

    echo "\n--------------------------------------------------\n";

    echo "Testing UserProfileController::getCities(11) directly...\n";
    $responseCities = $controller->getCities(11);

    if ($responseCities instanceof \Illuminate\Http\JsonResponse) {
        echo "Status: " . $responseCities->getStatusCode() . "\n";
        $data = $responseCities->getData(true);
        echo "Count: " . count($data) . "\n";
        if (count($data) > 0) {
            echo "First Item: " . print_r($data[0], true) . "\n";
        }
    } else {
        echo "Response is not JSON\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}

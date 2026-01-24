<?php

use Illuminate\Support\Facades\Schema;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$columns = Schema::getColumnListing('users');
$hasBankName = in_array('bank_name', $columns);
$hasSocialMedia = in_array('social_media', $columns);

echo "Has bank_name: " . ($hasBankName ? 'YES' : 'NO') . "\n";
echo "Has social_media: " . ($hasSocialMedia ? 'YES' : 'NO') . "\n";

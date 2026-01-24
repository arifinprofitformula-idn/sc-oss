<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "--- Schema Check ---\n";
echo "1. Checking 'users' table columns:\n";
$columns = Schema::getColumnListing('users');
$needed = ['bank_name', 'bank_account_no', 'bank_account_name', 'social_media', 'gender', 'birth_place'];
foreach ($needed as $col) {
    echo "   - $col: " . (in_array($col, $columns) ? "EXISTS" : "MISSING") . "\n";
}

echo "\n2. Checking 'user_profiles' table:\n";
echo "   - user_profiles: " . (Schema::hasTable('user_profiles') ? "EXISTS (Potential Double DB)" : "DOES NOT EXIST") . "\n";

echo "\n3. Checking 'migrations' status for '2026_01_12_231713_add_profile_fields_to_users_table':\n";
$migration = DB::table('migrations')->where('migration', '2026_01_12_231713_add_profile_fields_to_users_table')->first();
echo "   - Status: " . ($migration ? "Ran (Batch " . $migration->batch . ")" : "NOT RUN") . "\n";

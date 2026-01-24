<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

// Create or retrieve a test user
$email = 'test_persistence@example.com';
$user = User::where('email', $email)->first();
if (!$user) {
    $user = User::create([
        'name' => 'Test Persistence',
        'email' => $email,
        'password' => Hash::make('password'),
        'phone' => '081234567890',
        'address' => 'Test Address',
        'city_name' => 'Test City',
        'postal_code' => '12345',
        'gender' => 'Laki-laki',
        'job' => 'Tester',
        'religion' => 'Islam',
    ]);
}

echo "User ID: " . $user->id . "\n";

// 1. Update Bank Data & Social Media
echo "\n--- Updating Data ---\n";
$bankName = 'BCA';
$bankAccountNo = '1234567890';
$bankAccountName = 'Test Saver';
$socialMedia = [
    'facebook' => 'testfb',
    'instagram' => 'testig',
    'tiktok' => 'testtt',
    'threads' => 'testth'
];

$user->bank_name = $bankName;
$user->bank_account_no = $bankAccountNo;
$user->bank_account_name = $bankAccountName;
$user->social_media = $socialMedia;
$user->save();

echo "Saved.\n";

// 2. Clear Model Cache (Re-retrieve from DB)
$user = $user->fresh();

echo "\n--- Verifying Data ---\n";
echo "Bank Name: " . ($user->bank_name === $bankName ? "MATCH" : "MISMATCH ($user->bank_name)") . "\n";
echo "Account No: " . ($user->bank_account_no === $bankAccountNo ? "MATCH" : "MISMATCH ($user->bank_account_no)") . "\n";
echo "Account Name: " . ($user->bank_account_name === $bankAccountName ? "MATCH" : "MISMATCH ($user->bank_account_name)") . "\n";

echo "Social Media (Is Array?): " . (is_array($user->social_media) ? "YES" : "NO") . "\n";
echo "Facebook: " . (($user->social_media['facebook'] ?? '') === 'testfb' ? "MATCH" : "MISMATCH") . "\n";
echo "Instagram: " . (($user->social_media['instagram'] ?? '') === 'testig' ? "MATCH" : "MISMATCH") . "\n";

// 3. Check Raw DB JSON
$raw = DB::select('SELECT social_media FROM users WHERE id = ?', [$user->id])[0];
echo "\nRaw DB social_media: " . $raw->social_media . "\n";

// Clean up
$user->delete();

<?php
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Attempting to send email via Brevo...\n";
    Mail::raw('This is a test email to verify SMTP connectivity for SC-OSS.', function($msg) {
        $msg->to('email.epiteam@gmail.com')
            ->subject('SC-OSS SMTP Test ' . date('Y-m-d H:i:s'));
    });
    echo "Email sent successfully.\n";
} catch (\Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    Log::error('SMTP Test Failed: ' . $e->getMessage());
}

<?php
declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Email\EmailService;

class ScossTestEmail extends Command
{
    protected $signature = 'scoss:test-email {email}';
    protected $description = 'Kirim email uji ke alamat tertentu untuk cek konfigurasi mailer';

    public function handle(EmailService $emails): int
    {
        $to = (string) $this->argument('email');
        $subject = 'SC-OSS Test Email';
        $html = '<html><body><h3>SC-OSS Mailer OK</h3><p>Pesan ini dikirim dari sistem SC-OSS.</p></body></html>';
        $emails->sendRaw($to, $subject, $html, []);
        $this->info("Email uji dikirim ke {$to}");
        return self::SUCCESS;
    }
}

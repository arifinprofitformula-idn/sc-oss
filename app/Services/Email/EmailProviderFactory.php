<?php

namespace App\Services\Email;

use App\Models\SystemSetting;
use InvalidArgumentException;

class EmailProviderFactory
{
    public static function create(): EmailProviderInterface
    {
        $provider = SystemSetting::getValue('email_provider', 'brevo');

        switch ($provider) {
            case 'brevo':
                return new BrevoProvider();
            case 'mailketing':
                return new MailketingProvider();
            default:
                throw new InvalidArgumentException("Unsupported email provider: {$provider}");
        }
    }
}

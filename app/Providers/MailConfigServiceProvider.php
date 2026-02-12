<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use App\Mail\Transport\MailketingTransport;
use App\Services\Email\MailketingProvider;

class MailConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Only run if table exists to avoid errors during migration
        if (!Schema::hasTable('system_settings')) {
            return;
        }

        try {
            // Retrieve all relevant settings
            $settings = DB::table('system_settings')
                ->whereIn('key', [
                    'email_provider',
                    'brevo_active', 
                    'brevo_api_key', 
                    'brevo_sender_email', 
                    'brevo_sender_name',
                    'brevo_smtp_login',
                    'mailketing_api_token',
                    'mailketing_sender_email',
                    'mailketing_sender_name'
                ])
                ->pluck('value', 'key');

            // Configure Brevo if credentials exist
            $this->configureBrevo($settings);
            
            // Configure Mailketing if credentials exist
            $this->configureMailketing($settings);

            // Set default provider
            $provider = $settings['email_provider'] ?? ($settings['brevo_active'] ? 'brevo' : 'log');
            
            // Fallback to log if explicit 'log' or invalid
            if (!in_array($provider, ['brevo', 'mailketing'])) {
                $provider = 'log';
            }

            Config::set('mail.default', $provider);

        } catch (\Exception $e) {
            // Log error but don't crash app (e.g. if encryption key changes or DB issue)
            Log::error('Failed to load mail config from DB: ' . $e->getMessage());
        }
    }

    protected function configureBrevo($settings)
    {
        // Register custom driver using HTTP API
        Mail::extend('brevo', function () {
            return new \App\Mail\Transport\BrevoTransport(new \App\Services\Email\BrevoProvider());
        });

        $apiKey = isset($settings['brevo_api_key']) ? Crypt::decryptString($settings['brevo_api_key']) : null;
        
        if ($apiKey) {
            // Define mailer config using custom transport
            Config::set('mail.mailers.brevo', [
                'transport' => 'brevo',
            ]);
            
            // Only set global from if this is the default driver
            if (Config::get('mail.default') === 'brevo') {
                $this->setGlobalFrom($settings['brevo_sender_email'] ?? null, $settings['brevo_sender_name'] ?? null);
            }
        }
    }

    protected function configureMailketing($settings)
    {
        // Register custom driver
        Mail::extend('mailketing', function () {
            return new MailketingTransport(new MailketingProvider());
        });
        
        // Define mailer config
        Config::set('mail.mailers.mailketing', [
            'transport' => 'mailketing',
        ]);

        // Only set global from if this is the default driver
        if (Config::get('mail.default') === 'mailketing') {
             $this->setGlobalFrom($settings['mailketing_sender_email'] ?? null, $settings['mailketing_sender_name'] ?? null);
        }
    }

    protected function setGlobalFrom($email, $name)
    {
        if ($email) {
            Config::set('mail.from.address', $email);
        }
        if ($name) {
            Config::set('mail.from.name', $name);
        }
    }
}

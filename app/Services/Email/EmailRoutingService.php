<?php

namespace App\Services\Email;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;

class EmailRoutingService
{
    /**
     * Get the mailer name for a specific scenario.
     *
     * @param string $scenario 'auth', 'order', 'marketing'
     * @return string|null Returns null to use default mailer
     */
    public function getMailer(string $scenario): ?string
    {
        // Cache routing settings for 1 hour
        $routes = Cache::remember('email_routes', 3600, function () {
            return SystemSetting::whereIn('key', [
                'email_route_auth',
                'email_route_order',
                'email_route_marketing'
            ])->pluck('value', 'key');
        });

        $key = "email_route_{$scenario}";
        $provider = $routes[$key] ?? 'default';

        if ($provider === 'default') {
            return null; // Let Laravel use default mailer
        }

        // Return the provider string directly (e.g., 'smtp', 'mailgun', 'brevo')
        // The Job will use this as the mailer connection name.
        return $provider;
    }
}

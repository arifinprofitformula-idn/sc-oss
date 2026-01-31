<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Contracts\ShippingServiceInterface::class, function ($app) {
            $integrationService = $app->make(\App\Services\IntegrationService::class);
            $provider = $integrationService->get('shipping_provider', 'rajaongkir');
            
            if ($provider === 'api_id') {
                return new \App\Services\ApiIdService($integrationService);
            }
            
            return new \App\Services\RajaOngkirService($integrationService);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        \Illuminate\Support\Facades\Event::listen(
            \App\Events\OrderPaid::class,
            \App\Listeners\DistributeOrderCommission::class,
        );

        \Illuminate\Support\Facades\Event::listen(
            \App\Events\OrderPaid::class,
            \App\Listeners\RefundUniqueCodeToWallet::class,
        );

        \Illuminate\Support\Facades\Event::listen(
            \App\Events\SilverchannelApproved::class,
            \App\Listeners\AwardRegistrationCommission::class,
        );

        \Illuminate\Support\Facades\RateLimiter::for('password.reset', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perHour(3)->by($request->email);
        });
    }
}

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

        // PDF service binding (lightweight HTML fallback)
        $this->app->bind(\App\Services\Pdf\PdfServiceInterface::class, \App\Services\Pdf\HtmlToPdfService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if($this->app->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        Schema::defaultStringLength(191);

        \Illuminate\Support\Facades\Event::listen(
            \App\Events\OrderStatusChanged::class,
            \App\Listeners\DistributeOrderCommission::class,
        );

        \Illuminate\Support\Facades\Event::listen(
            \App\Events\OrderStatusChanged::class,
            \App\Listeners\ReverseOrderCommission::class,
        );

        \Illuminate\Support\Facades\Event::listen(
            \App\Events\OrderPaid::class,
            \App\Listeners\RefundUniqueCodeToWallet::class,
        );

        

        // Email notifications
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\OrderCreated::class,
            \App\Listeners\SendInvoiceUnpaidToCustomer::class,
        );

        \Illuminate\Support\Facades\Event::listen(
            \App\Events\OrderCreated::class,
            \App\Listeners\NotifyAdminNewOrder::class,
        );

        \Illuminate\Support\Facades\Event::listen(
            \App\Events\OrderStatusChanged::class,
            \App\Listeners\SendInvoicePaidToCustomer::class,
        );

        \Illuminate\Support\Facades\Event::listen(
            \App\Events\OrderStatusChanged::class,
            \App\Listeners\SendOrderShippedToCustomer::class,
        );

        \Illuminate\Support\Facades\Event::listen(
            \App\Events\SilverchannelApproved::class,
            \App\Listeners\SendWelcomeSilverchannelEmail::class,
        );

        \Illuminate\Support\Facades\Event::listen(
            \App\Events\SilverchannelRegistered::class,
            \App\Listeners\SendWelcomeSilverchannel::class,
        );

        \Illuminate\Support\Facades\Event::listen(
            \App\Events\SilverchannelRegistered::class,
            \App\Listeners\NotifyAdminSilverchannelRegistered::class,
        );

        \Illuminate\Support\Facades\Event::listen(
            \App\Events\OrderStatusChanged::class,
            \App\Listeners\SendTransactionCommissionEmail::class,
        );

        \Illuminate\Support\Facades\Event::listen(
            \App\Events\SilverchannelApproved::class,
            \App\Listeners\AwardRegistrationCommission::class,
        );

        // Payout email notifications
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\PayoutRequested::class,
            \App\Listeners\SendPayoutRequestedEmail::class,
        );
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\PayoutRequested::class,
            \App\Listeners\NotifyAdminPayoutRequested::class,
        );

        \Illuminate\Support\Facades\Event::listen(
            \App\Events\PayoutProcessed::class,
            \App\Listeners\SendPayoutProcessedEmail::class,
        );

        \Illuminate\Support\Facades\Event::listen(
            \App\Events\PayoutRejected::class,
            \App\Listeners\SendPayoutRejectedEmail::class,
        );

        \Illuminate\Support\Facades\RateLimiter::for('password.reset', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perHour(3)->by($request->email);
        });
    }
}

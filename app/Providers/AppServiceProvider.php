<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use App\Models\Rbac\Permission as PermissionModel;
use App\Models\Rbac\Role as RoleModel;
use App\Observers\PermissionObserver;
use App\Observers\RoleObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Events\RoleAttached;
use Spatie\Permission\Events\RoleDetached;
use Spatie\Permission\Events\PermissionAttached;
use Spatie\Permission\Events\PermissionDetached;

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

        \Illuminate\Support\Facades\RateLimiter::for('role-management', function (\Illuminate\Http\Request $request) {
            $userId = optional($request->user())->id;
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(30)->by($userId ?: $request->ip());
        });

        // RBAC Observers & Activity Logs
        PermissionModel::observe(PermissionObserver::class);
        RoleModel::observe(RoleObserver::class);

        Event::listen(RoleAttached::class, function (RoleAttached $event) {
            DB::table('admin_activity_logs')->insert([
                'user_id' => Auth::id(),
                'action' => 'role_attached',
                'entity_type' => $event->model::class,
                'entity_id' => $event->model->getKey(),
                'meta' => json_encode(['event' => 'RoleAttached']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        Event::listen(RoleDetached::class, function (RoleDetached $event) {
            DB::table('admin_activity_logs')->insert([
                'user_id' => Auth::id(),
                'action' => 'role_detached',
                'entity_type' => $event->model::class,
                'entity_id' => $event->model->getKey(),
                'meta' => json_encode(['event' => 'RoleDetached']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        Event::listen(PermissionAttached::class, function (PermissionAttached $event) {
            DB::table('admin_activity_logs')->insert([
                'user_id' => Auth::id(),
                'action' => 'permission_attached',
                'entity_type' => $event->model::class,
                'entity_id' => $event->model->getKey(),
                'meta' => json_encode(['event' => 'PermissionAttached']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        Event::listen(PermissionDetached::class, function (PermissionDetached $event) {
            DB::table('admin_activity_logs')->insert([
                'user_id' => Auth::id(),
                'action' => 'permission_detached',
                'entity_type' => $event->model::class,
                'entity_id' => $event->model->getKey(),
                'meta' => json_encode(['event' => 'PermissionDetached']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }
}

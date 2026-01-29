<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\SilverchannelController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

// Admin Domain Root Access
Route::domain(env('ADMIN_DOMAIN'))->group(function () {
    Route::get('/', function () {
        if (Auth::check()) {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            if ($user && $user->hasRole('SUPER_ADMIN')) {
                return redirect()->route('admin.silverchannels.index');
            }
            return redirect()->route('dashboard');
        }
        return redirect()->route('login');
    });

    Route::get('/admin', function () {
        return redirect()->route('admin.silverchannels.index');
    })->middleware(['auth', 'role:SUPER_ADMIN']);
});

Route::get('/', function () {
    // Log redirect activity
    Log::info('Root access redirect', [
        'ip' => request()->ip(),
        'user_agent' => request()->userAgent(),
        'is_authenticated' => Auth::check(),
        'user_id' => Auth::id()
    ]);

    if (Auth::check()) {
        // Jika user sudah login, arahkan ke dashboard
        return redirect()->route('dashboard');
    }

    // Jika user belum login, arahkan ke login
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    // Approval Notice (Always accessible for authenticated users)
    Route::get('/approval-notice', function () {
        return view('auth.approval-notice');
    })->name('approval.notice');
});

Route::middleware(['auth', 'profile.completed'])->group(function () {
    // ----------------------------------------------------------------------
    // ACTIVE USER ROUTES
    // All routes below this middleware require the user to be ACTIVE.
    // ----------------------------------------------------------------------
    Route::middleware('active')->group(function () {
        
        Route::get('/dashboard', function () {
            return view('dashboard');
        })->name('dashboard');

        // Profile Routes
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile/photo', [\App\Http\Controllers\UserProfileController::class, 'updatePhoto'])->name('profile.photo.update');
        Route::patch('/profile/details', [\App\Http\Controllers\UserProfileController::class, 'update'])->name('profile.details.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        // Location Data (Authenticated & Active Users)
        Route::get('/profile/locations/provinces', [\App\Http\Controllers\UserProfileController::class, 'getProvinces'])->name('profile.locations.provinces');
        Route::get('/profile/locations/cities/{province}', [\App\Http\Controllers\UserProfileController::class, 'getCities'])->name('profile.locations.cities');
        Route::get('/profile/locations/subdistricts/{city}', [\App\Http\Controllers\UserProfileController::class, 'getSubdistricts'])->name('profile.locations.subdistricts');
        Route::get('/profile/locations/villages/{subdistrict}', [\App\Http\Controllers\UserProfileController::class, 'getVillages'])->name('profile.locations.villages');

        // Admin Routes
        Route::domain(env('ADMIN_DOMAIN'))->middleware(['role:SUPER_ADMIN'])->prefix('admin')->name('admin.')->group(function () {
            // ... Admin routes ...
            Route::get('/silverchannels/import', [\App\Http\Controllers\Admin\ImportSilverchannelController::class, 'create'])->name('silverchannels.import');
            Route::post('/silverchannels/import/preview', [\App\Http\Controllers\Admin\ImportSilverchannelController::class, 'preview'])->name('silverchannels.import.preview');
            Route::post('/silverchannels/import', [\App\Http\Controllers\Admin\ImportSilverchannelController::class, 'store'])->name('silverchannels.import.store');
            Route::post('/silverchannels/import/process', [\App\Http\Controllers\Admin\ImportSilverchannelController::class, 'process'])->name('silverchannels.import.process');
            Route::get('/silverchannels/import/cancel', [\App\Http\Controllers\Admin\ImportSilverchannelController::class, 'cancel'])->name('silverchannels.import.cancel');
            Route::get('/silverchannels/import/template', [\App\Http\Controllers\Admin\ImportSilverchannelController::class, 'downloadTemplate'])->name('silverchannels.import.template');
            Route::get('/silverchannels', [SilverchannelController::class, 'index'])->name('silverchannels.index');
            Route::post('/silverchannels', [SilverchannelController::class, 'store'])->name('silverchannels.store');
            Route::put('/silverchannels/{user}', [SilverchannelController::class, 'update'])->name('silverchannels.update');
            Route::patch('/silverchannels/{user}/password', [SilverchannelController::class, 'updatePassword'])->name('silverchannels.update-password');
            Route::delete('/silverchannels/{user}', [SilverchannelController::class, 'destroy'])->name('silverchannels.destroy');
            Route::post('/silverchannels/{user}/approve', [SilverchannelController::class, 'approve'])->name('silverchannels.approve');
            Route::post('/silverchannels/{user}/reject', [SilverchannelController::class, 'reject'])->name('silverchannels.reject');
            
            // Location Helpers
            Route::get('/silverchannels/locations/provinces', [SilverchannelController::class, 'getProvinces'])->name('silverchannels.locations.provinces');
            Route::get('/silverchannels/locations/cities/{province}', [SilverchannelController::class, 'getCities'])->name('silverchannels.locations.cities');

            // Catalog Management
            Route::resource('brands', \App\Http\Controllers\Admin\BrandController::class);
            Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class);
            Route::patch('products/{product}/active', [\App\Http\Controllers\Admin\ProductController::class, 'updateActive'])
                ->name('products.update-active');
            Route::resource('products', \App\Http\Controllers\Admin\ProductController::class);
            Route::post('packages/{package}/restore', [\App\Http\Controllers\Admin\PackageController::class, 'restore'])->name('packages.restore');
            Route::resource('packages', \App\Http\Controllers\Admin\PackageController::class);

            // Order Management
            Route::get('/orders', [\App\Http\Controllers\Admin\OrderController::class, 'index'])->name('orders.index');
            Route::get('/orders/{order}', [\App\Http\Controllers\Admin\OrderController::class, 'show'])->name('orders.show');
            Route::patch('/orders/{order}/status', [\App\Http\Controllers\Admin\OrderController::class, 'updateStatus'])->name('orders.update-status');
            Route::patch('/orders/{order}/tracking', [\App\Http\Controllers\Admin\OrderController::class, 'updateTracking'])->name('orders.update-tracking');
            Route::post('/orders/{order}/note', [\App\Http\Controllers\Admin\OrderController::class, 'storeNote'])->name('orders.store-note');

            // Payment Management
            Route::get('/payments', [\App\Http\Controllers\Admin\PaymentController::class, 'index'])->name('payments.index');
            Route::get('/payments/{payment}', [\App\Http\Controllers\Admin\PaymentController::class, 'show'])->name('payments.show');
            Route::patch('/payments/{payment}/verify', [\App\Http\Controllers\Admin\PaymentController::class, 'verify'])->name('payments.verify');
            Route::patch('/payments/{payment}/reject', [\App\Http\Controllers\Admin\PaymentController::class, 'reject'])->name('payments.reject');

            // Payout Management
            Route::get('/payouts', [\App\Http\Controllers\Admin\PayoutController::class, 'index'])->name('payouts.index');
            Route::get('/payouts/{payout}', [\App\Http\Controllers\Admin\PayoutController::class, 'show'])->name('payouts.show');
            Route::patch('/payouts/{payout}/approve', [\App\Http\Controllers\Admin\PayoutController::class, 'approve'])->name('payouts.approve');
            Route::patch('/payouts/{payout}/reject', [\App\Http\Controllers\Admin\PayoutController::class, 'reject'])->name('payouts.reject');

            // Reports
            Route::get('/reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
            Route::get('/reports/export', [\App\Http\Controllers\Admin\ReportController::class, 'export'])->name('reports.export');

            // Global Store Settings
            Route::get('/settings/store', [\App\Http\Controllers\Admin\GlobalStoreSettingController::class, 'edit'])->name('settings.store');
            Route::get('/settings/store/{tab}', [\App\Http\Controllers\Admin\GlobalStoreSettingController::class, 'edit'])
                ->where('tab', 'identity|contact|hours|payment')
                ->name('settings.store.tab');
            Route::patch('/settings/store', [\App\Http\Controllers\Admin\GlobalStoreSettingController::class, 'update'])->name('settings.store.update');
            Route::patch('/settings/store/toggle', [\App\Http\Controllers\Admin\GlobalStoreSettingController::class, 'updateToggle'])->name('settings.store.toggle');
            Route::patch('/settings/store/identity', [\App\Http\Controllers\Admin\GlobalStoreSettingController::class, 'updateIdentity'])->name('settings.store.identity');
            Route::patch('/settings/store/contact', [\App\Http\Controllers\Admin\GlobalStoreSettingController::class, 'updateContact'])->name('settings.store.contact');
            Route::patch('/settings/store/hours', [\App\Http\Controllers\Admin\GlobalStoreSettingController::class, 'updateHours'])->name('settings.store.hours');
            Route::patch('/settings/store/payment', [\App\Http\Controllers\Admin\GlobalStoreSettingController::class, 'updatePayment'])->name('settings.store.payment');
            Route::post('/settings/store/bank-logo', [\App\Http\Controllers\Admin\GlobalStoreSettingController::class, 'uploadBankLogo'])->name('settings.store.bank-logo');
            
            // Integration System
            Route::prefix('integrations')->name('integrations.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\IntegrationController::class, 'index'])->name('index');
                Route::get('/shipping', [\App\Http\Controllers\Admin\IntegrationController::class, 'shipping'])->name('shipping');
                Route::get('/payment', [\App\Http\Controllers\Admin\IntegrationController::class, 'payment'])->name('payment');
                Route::get('/docs', [\App\Http\Controllers\Admin\IntegrationController::class, 'docs'])->name('docs');
                Route::post('/update', [\App\Http\Controllers\Admin\IntegrationController::class, 'update'])->name('update');
                
                Route::post('/test/rajaongkir', [\App\Http\Controllers\Admin\IntegrationController::class, 'testRajaOngkir'])
                    ->middleware('throttle:10,1')
                    ->name('test.rajaongkir');
                Route::post('/test/api-id', [\App\Http\Controllers\Admin\IntegrationController::class, 'testApiId'])
                    ->middleware('throttle:10,1')
                    ->name('test.api_id');
                
                // Location Data (Unified)
                Route::get('/shipping/provinces', [\App\Http\Controllers\Admin\IntegrationController::class, 'getProvinces'])->name('shipping.provinces');
                Route::get('/shipping/cities/{province}', [\App\Http\Controllers\Admin\IntegrationController::class, 'getCities'])->name('shipping.cities');
                Route::get('/shipping/subdistricts/{city}', [\App\Http\Controllers\Admin\IntegrationController::class, 'getSubdistricts'])->name('shipping.subdistricts');
                Route::get('/shipping/villages/{subdistrict}', [\App\Http\Controllers\Admin\IntegrationController::class, 'getVillages'])->name('shipping.villages');

                Route::get('/shipping/search-destination', [\App\Http\Controllers\Admin\IntegrationController::class, 'searchDestination'])->name('shipping.search');
                Route::post('/shipping/test-cost', [\App\Http\Controllers\Admin\IntegrationController::class, 'testShippingCost'])->name('shipping.test-cost');
                Route::post('/shipping/store-update', [\App\Http\Controllers\Admin\IntegrationController::class, 'updateStoreShipping'])->name('shipping.store-update');
                
                Route::get('/brevo', [\App\Http\Controllers\Admin\IntegrationController::class, 'brevo'])->name('brevo');
                Route::post('/test/brevo', [\App\Http\Controllers\Admin\IntegrationController::class, 'testBrevo'])
                    ->middleware('throttle:10,1')
                    ->name('test.brevo');
            });

            // External API Management
            Route::get('external-apis/export', [\App\Http\Controllers\Admin\ExternalApiController::class, 'export'])->name('external-apis.export');
            Route::post('external-apis/import', [\App\Http\Controllers\Admin\ExternalApiController::class, 'import'])->name('external-apis.import');
            Route::post('external-apis/{external_api}/test', [\App\Http\Controllers\Admin\ExternalApiController::class, 'test'])->name('external-apis.test');
            Route::get('external-apis/docs', [\App\Http\Controllers\Admin\ExternalApiController::class, 'docs'])->name('external-apis.docs');
            Route::resource('external-apis', \App\Http\Controllers\Admin\ExternalApiController::class);
        });

        // Payment Routes
        Route::middleware(['role:SILVERCHANNEL'])->prefix('payment')->name('payment.')->group(function () {
            Route::get('/{order}/checkout', [\App\Http\Controllers\PaymentController::class, 'checkout'])->name('checkout');
            Route::post('/{order}/process', [\App\Http\Controllers\PaymentController::class, 'process'])->name('process');
            Route::get('/{order}/success', [\App\Http\Controllers\PaymentController::class, 'success'])->name('success');
        });

        // Payout Routes (Silverchannel)
        Route::middleware(['role:SILVERCHANNEL'])->group(function () {
            Route::get('/payouts', [\App\Http\Controllers\PayoutController::class, 'index'])->name('payouts.index');
            Route::post('/payouts', [\App\Http\Controllers\PayoutController::class, 'store'])->name('payouts.store');
        });

        // Silverchannel Routes
        Route::middleware(['role:SILVERCHANNEL'])->prefix('silverchannel')->name('silverchannel.')->group(function () {
            // Catalog
            Route::get('/products', [\App\Http\Controllers\Silverchannel\ProductController::class, 'index'])->name('products.index');

            Route::get('/store/operational-status', [\App\Http\Controllers\Silverchannel\StoreStatusController::class, 'show'])->name('store.operational-status');

            // Cart
            Route::get('/cart/items', [\App\Http\Controllers\Silverchannel\CartController::class, 'getItems'])->name('cart.items');
            Route::get('/cart', [\App\Http\Controllers\Silverchannel\CartController::class, 'index'])->name('cart.index');
            Route::post('/cart', [\App\Http\Controllers\Silverchannel\CartController::class, 'store'])->name('cart.store');
            Route::patch('/cart/{cart}', [\App\Http\Controllers\Silverchannel\CartController::class, 'update'])->name('cart.update');
            Route::delete('/cart/{cart}', [\App\Http\Controllers\Silverchannel\CartController::class, 'destroy'])->name('cart.destroy');
            Route::post('/cart/validate', [\App\Http\Controllers\Silverchannel\CartController::class, 'validateCheckout'])->name('cart.validate');

            // Checkout
            Route::get('/checkout', [\App\Http\Controllers\Silverchannel\CheckoutController::class, 'index'])->name('checkout.index');
            Route::get('/checkout/order-received/{order}', [\App\Http\Controllers\Silverchannel\CheckoutController::class, 'orderReceived'])->name('checkout.order-received');
            Route::post('/checkout/shipping-cost', [\App\Http\Controllers\Silverchannel\CheckoutController::class, 'calculateShipping'])->name('checkout.shipping-cost');
            Route::post('/checkout/process', [\App\Http\Controllers\Silverchannel\CheckoutController::class, 'store'])->name('checkout.process');

            // Orders
            Route::get('/orders', [\App\Http\Controllers\Silverchannel\OrderController::class, 'index'])->name('orders.index');
            Route::post('/orders', [\App\Http\Controllers\Silverchannel\OrderController::class, 'store'])->name('orders.store');
            Route::get('/orders/{order}', [\App\Http\Controllers\Silverchannel\OrderController::class, 'show'])->name('orders.show');
            Route::post('/orders/{order}/cancel', [\App\Http\Controllers\Silverchannel\OrderController::class, 'cancel'])->name('orders.cancel');

            // My Referrals
            Route::get('/referrals', [\App\Http\Controllers\Silverchannel\ReferralController::class, 'index'])->name('referrals.index');
            Route::post('/referrals/{referredUser}/follow-up', [\App\Http\Controllers\Silverchannel\ReferralController::class, 'updateFollowUp'])->name('referrals.follow-up');
            Route::get('/referrals/export', [\App\Http\Controllers\Silverchannel\ReferralController::class, 'export'])->name('referrals.export');

            // Store Settings
            Route::get('/store/settings', [\App\Http\Controllers\Silverchannel\StoreSettingController::class, 'edit'])->name('store.settings');
            Route::put('/store/settings', [\App\Http\Controllers\Silverchannel\StoreSettingController::class, 'update'])->name('store.settings.update');
            Route::get('/store/locations/cities/{province}', [\App\Http\Controllers\Silverchannel\StoreSettingController::class, 'getCities']);
            Route::get('/store/locations/subdistricts/{city}', [\App\Http\Controllers\Silverchannel\StoreSettingController::class, 'getSubdistricts']);
        });
    });
});

require __DIR__.'/auth.php';

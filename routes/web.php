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
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('dashboard');
        }
        return redirect()->route('login');
    });

    Route::get('/admin', function () {
        return redirect()->route('admin.dashboard');
    })->middleware(['auth', 'role:SUPER_ADMIN']);

    Route::middleware(['auth', 'role:SUPER_ADMIN'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/email-templates', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'index'])->name('email-templates.index');
        Route::get('/email-templates/create', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'create'])->name('email-templates.create');
        Route::post('/email-templates', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'store'])->name('email-templates.store');
        Route::get('/email-templates/{emailTemplate}/edit', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'edit'])->name('email-templates.edit');
        Route::put('/email-templates/{emailTemplate}', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'update'])->name('email-templates.update');
        Route::delete('/email-templates/{emailTemplate}', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'destroy'])->name('email-templates.destroy');
        Route::get('/email-templates/{emailTemplate}/preview', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'preview'])->name('email-templates.preview');
        Route::post('/email-templates/{emailTemplate}/duplicate', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'duplicate'])->name('email-templates.duplicate');
        Route::get('/email-templates/{emailTemplate}/export', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'export'])->name('email-templates.export');
        Route::post('/email-templates/{emailTemplate}/sync', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'sync'])->name('email-templates.sync');
    });
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
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user && $user->hasRole('SUPER_ADMIN')) {
            return redirect()->route('admin.dashboard');
        }
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
        
        Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

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

        // Silverchannel Support Center
        Route::prefix('silverchannel')->name('silverchannel.')->group(function () {
            Route::get('/support', [\App\Http\Controllers\Silverchannel\SupportController::class, 'index'])->name('support.index');
            Route::get('/support/conversations', [\App\Http\Controllers\Silverchannel\SupportController::class, 'getConversations'])->name('support.conversations');
            Route::get('/support/messages/{order}', [\App\Http\Controllers\Silverchannel\SupportController::class, 'getMessages'])->name('support.messages');
            Route::post('/support/messages/{order}', [\App\Http\Controllers\Silverchannel\SupportController::class, 'sendMessage'])->name('support.send');
        });

        // Admin Routes
        Route::domain(env('ADMIN_DOMAIN'))->middleware(['role:SUPER_ADMIN'])->prefix('admin')->name('admin.')->group(function () {
            Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
            // ... Admin routes ...
            Route::get('/silverchannels/import', [\App\Http\Controllers\Admin\ImportSilverchannelController::class, 'create'])->name('silverchannels.import');
            Route::post('/silverchannels/import/preview', [\App\Http\Controllers\Admin\ImportSilverchannelController::class, 'preview'])->name('silverchannels.import.preview');
            Route::post('/silverchannels/import', [\App\Http\Controllers\Admin\ImportSilverchannelController::class, 'store'])->name('silverchannels.import.store');
            Route::post('/silverchannels/import/process', [\App\Http\Controllers\Admin\ImportSilverchannelController::class, 'process'])->name('silverchannels.import.process');
            Route::get('/silverchannels/import/cancel', [\App\Http\Controllers\Admin\ImportSilverchannelController::class, 'cancel'])->name('silverchannels.import.cancel');
            Route::get('/silverchannels/import/template', [\App\Http\Controllers\Admin\ImportSilverchannelController::class, 'downloadTemplate'])->name('silverchannels.import.template');
            Route::get('/silverchannels', [SilverchannelController::class, 'index'])->name('silverchannels.index');
            Route::post('/silverchannels', [SilverchannelController::class, 'store'])->name('silverchannels.store');
            Route::get('/silverchannels/{user}/edit', [SilverchannelController::class, 'edit'])->name('silverchannels.edit');
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
            
            // Product Import Routes
            Route::get('products/import', [\App\Http\Controllers\Admin\ProductImportController::class, 'create'])->name('products.import');
            Route::get('products/import/template', [\App\Http\Controllers\Admin\ProductImportController::class, 'downloadTemplate'])->name('products.import.template');
            Route::post('products/import/preview', [\App\Http\Controllers\Admin\ProductImportController::class, 'preview'])->name('products.import.preview');
            Route::post('products/import', [\App\Http\Controllers\Admin\ProductImportController::class, 'store'])->name('products.import.store');
            Route::get('products/import/log/{filename}', [\App\Http\Controllers\Admin\ProductImportController::class, 'downloadErrorLog'])->name('products.import.log');
            Route::get('products/import/cancel', [\App\Http\Controllers\Admin\ProductImportController::class, 'cancel'])->name('products.import.cancel');

            Route::post('products/{product}/sync-price', [\App\Http\Controllers\Admin\ProductController::class, 'syncPrice'])->name('products.sync-price');
            Route::resource('products', \App\Http\Controllers\Admin\ProductController::class);
            Route::post('packages/{package}/restore', [\App\Http\Controllers\Admin\PackageController::class, 'restore'])->name('packages.restore');
            Route::resource('packages', \App\Http\Controllers\Admin\PackageController::class);

            // Order Management
            Route::get('/orders', [\App\Http\Controllers\Admin\OrderController::class, 'index'])->name('orders.index');
            Route::get('/orders/{order}', [\App\Http\Controllers\Admin\OrderController::class, 'show'])->name('orders.show');
            Route::patch('/orders/{order}/status', [\App\Http\Controllers\Admin\OrderController::class, 'updateStatus'])->name('orders.update-status');
            Route::patch('/orders/{order}/tracking', [\App\Http\Controllers\Admin\OrderController::class, 'updateTracking'])->name('orders.update-tracking');
            Route::post('/orders/{order}/note', [\App\Http\Controllers\Admin\OrderController::class, 'storeNote'])->name('orders.store-note');
            Route::get('/orders/{order}/chat', [\App\Http\Controllers\Admin\ChatController::class, 'index'])->name('orders.chat');
            Route::get('/orders/{order}/messages', [\App\Http\Controllers\Admin\ChatController::class, 'getMessages'])->name('orders.messages');
            Route::post('/orders/{order}/messages', [\App\Http\Controllers\Admin\ChatController::class, 'store'])->name('orders.messages.store');

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

            // Email Templates
            Route::prefix('email-templates')->name('email-templates.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'index'])->name('index');
                Route::get('/{id}', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'show'])->name('show');
                Route::put('/{id}', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'update'])->name('update');
                Route::post('/preview', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'preview'])->name('preview');
                Route::post('/history/{historyId}/revert', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'revert'])->name('revert');
            });

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

            Route::patch('/settings/store/hours', [\App\Http\Controllers\Admin\GlobalStoreSettingController::class, 'updateHours'])->name('settings.store.hours');
            Route::patch('/settings/store/payment', [\App\Http\Controllers\Admin\GlobalStoreSettingController::class, 'updatePayment'])->name('settings.store.payment');
            Route::post('/settings/store/bank-logo', [\App\Http\Controllers\Admin\GlobalStoreSettingController::class, 'uploadBankLogo'])->name('settings.store.bank-logo');
            
            // Chat Center Management
            Route::get('/chats', [\App\Http\Controllers\Admin\ChatManagementController::class, 'index'])->name('chats.index');

            // Chat API
            Route::prefix('api/chats')->name('chats.')->group(function () {
                Route::get('/conversations', [\App\Http\Controllers\Admin\ChatManagementController::class, 'getConversations'])->name('conversations');
                Route::get('/{order}/messages', [\App\Http\Controllers\Admin\ChatManagementController::class, 'getMessages'])->name('messages');
                Route::post('/{order}/send', [\App\Http\Controllers\Admin\ChatManagementController::class, 'sendMessage'])->name('send');
                Route::post('/{order}/assign', [\App\Http\Controllers\Admin\ChatManagementController::class, 'assignChat'])->name('assign');
                Route::post('/{order}/priority', [\App\Http\Controllers\Admin\ChatManagementController::class, 'updatePriority'])->name('priority');
                Route::patch('/{order}/status', [\App\Http\Controllers\Admin\ChatManagementController::class, 'updateStatus'])->name('status');
                Route::post('/{order}/tags', [\App\Http\Controllers\Admin\ChatManagementController::class, 'updateTags'])->name('tags');
                Route::get('/quick-replies', [\App\Http\Controllers\Admin\ChatManagementController::class, 'getQuickReplies'])->name('quick-replies');
                Route::post('/quick-replies', [\App\Http\Controllers\Admin\ChatManagementController::class, 'storeQuickReply'])->name('quick-replies.store');
                Route::get('/unread-count', [\App\Http\Controllers\Admin\ChatManagementController::class, 'unreadCount'])->name('unread-count');
                Route::get('/stats', [\App\Http\Controllers\Admin\ChatManagementController::class, 'getStats'])->name('stats');
                Route::get('/export', [\App\Http\Controllers\Admin\ChatManagementController::class, 'export'])->name('export');
            });

            // Integration System
            Route::prefix('integrations')->name('integrations.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\IntegrationController::class, 'index'])->name('index');
                Route::get('/shipping', [\App\Http\Controllers\Admin\IntegrationController::class, 'shipping'])->name('shipping');
                Route::get('/payment', [\App\Http\Controllers\Admin\IntegrationController::class, 'payment'])->name('payment');
                Route::get('/epi-ape', [\App\Http\Controllers\Admin\IntegrationController::class, 'epiApe'])->name('epi-ape');
                Route::post('/epi-ape/sync', [\App\Http\Controllers\Admin\IntegrationController::class, 'syncEpiApe'])->name('epi-ape.sync');
                Route::post('/epi-ape/preview-price', [\App\Http\Controllers\Admin\IntegrationController::class, 'previewEpiPrice'])->name('epi-ape.preview-price');
                Route::post('/epi-ape/errors/{id}/resolve', [\App\Http\Controllers\Admin\IntegrationController::class, 'resolveEpiApeError'])->name('epi-ape.errors.resolve');
                Route::get('/epi-ape/errors/export', [\App\Http\Controllers\Admin\IntegrationController::class, 'exportEpiApeErrors'])->name('epi-ape.errors.export');
                Route::post('/epi-ape/mapping', [\App\Http\Controllers\Admin\IntegrationController::class, 'updateEpiMapping'])->name('epi-ape.mapping.update');
                Route::delete('/epi-ape/mapping/{id}', [\App\Http\Controllers\Admin\IntegrationController::class, 'deleteEpiMapping'])->name('epi-ape.mapping.delete');
                Route::post('/test/epi-ape', [\App\Http\Controllers\Admin\IntegrationController::class, 'testEpiApe'])->name('test.epi-ape');
                
                Route::get('/docs', [\App\Http\Controllers\Admin\IntegrationController::class, 'docs'])->name('docs');
                Route::post('/update', [\App\Http\Controllers\Admin\IntegrationController::class, 'update'])->name('update');
                
                // Email Template Editor API
                Route::get('/email/templates', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'index'])->name('email.templates.index');
                Route::get('/email/templates/{id}', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'show'])->name('email.templates.show');
                Route::put('/email/templates/{id}', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'update'])->name('email.templates.update');
                Route::post('/email/templates/revert/{historyId}', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'revert'])->name('email.templates.revert');

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
                
                Route::get('/email', [\App\Http\Controllers\Admin\IntegrationController::class, 'email'])->name('email');
                Route::post('/test/brevo', [\App\Http\Controllers\Admin\IntegrationController::class, 'testBrevo'])
                    ->middleware('throttle:10,1')
                    ->name('test.brevo');
                Route::post('/test/mailketing', [\App\Http\Controllers\Admin\IntegrationController::class, 'testMailketing'])
                    ->middleware('throttle:10,1')
                    ->name('test.mailketing');
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
            Route::post('/products/check-prices', [\App\Http\Controllers\Silverchannel\ProductController::class, 'checkPrices'])->name('products.check-prices');

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
            Route::post('/orders/{order}/mark-delivered', [\App\Http\Controllers\Silverchannel\OrderController::class, 'markAsDelivered'])->name('orders.mark-delivered');
            Route::get('/orders/{order}/chat', [\App\Http\Controllers\Silverchannel\ChatController::class, 'index'])->name('orders.chat');
            Route::get('/orders/{order}/messages', [\App\Http\Controllers\Silverchannel\ChatController::class, 'getMessages'])->name('orders.messages');
            Route::post('/orders/{order}/messages', [\App\Http\Controllers\Silverchannel\ChatController::class, 'store'])->name('orders.messages.store');

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

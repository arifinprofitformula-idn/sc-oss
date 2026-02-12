<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\SilverChannelRegistrationController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    // Standard Registration - REPLACED by SilverChannel Registration with Referral Validation
    Route::get('register', [SilverChannelRegistrationController::class, 'create'])
        ->name('register')
        ->middleware('validate.referral');

    // SilverChannel Registration (Explicit Route)
    Route::get('register-silver', [SilverChannelRegistrationController::class, 'create'])
        ->name('register.silver')
        ->middleware('validate.referral');

    Route::post('register', [SilverChannelRegistrationController::class, 'store'])
        ->name('register.store')
        ->middleware('validate.referral');

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

        
    // Silverchannel Registration (Legacy Routes - Redirect or Keep as Alias?)
    // Keeping checkout/payment routes as they are part of the flow
    Route::get('register-silver/checkout/{token}', [SilverChannelRegistrationController::class, 'checkout'])
        ->name('register.silver.checkout');
    Route::post('register-silver/payment/{token}', [SilverChannelRegistrationController::class, 'payment'])
        ->name('register.silver.payment');
        
    // Location API
    Route::get('api/provinces', [SilverChannelRegistrationController::class, 'getProvinces'])
        ->name('api.provinces');
    Route::get('api/cities/{province}', [SilverChannelRegistrationController::class, 'getCities'])
        ->name('api.cities');
    Route::get('api/subdistricts/{city}', [SilverChannelRegistrationController::class, 'getSubdistricts'])
        ->name('api.subdistricts');
    Route::get('api/villages/{subdistrict}', [SilverChannelRegistrationController::class, 'getVillages'])
        ->name('api.villages');
    Route::post('register-silver/shipping-services', [SilverChannelRegistrationController::class, 'getShippingServices'])
        ->name('register.silver.shipping-services');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});

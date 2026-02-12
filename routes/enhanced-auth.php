<?php

use App\Http\Controllers\Auth\EnhancedPasswordResetLinkController;
use App\Http\Controllers\Auth\EnhancedNewPasswordController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    // Enhanced Password Reset Routes
    Route::get('forgot-password/enhanced', [EnhancedPasswordResetLinkController::class, 'create'])
        ->name('password.request.enhanced');

    Route::post('forgot-password/enhanced', [EnhancedPasswordResetLinkController::class, 'store'])
        ->name('password.email.enhanced');

    Route::get('password-reset-confirmation', [EnhancedPasswordResetLinkController::class, 'confirmation'])
        ->name('password.request.confirmation');

    Route::get('password-reset-error', [EnhancedPasswordResetLinkController::class, 'error'])
        ->name('password.request.error');

    Route::get('reset-password/enhanced/{token}', [EnhancedNewPasswordController::class, 'create'])
        ->name('password.reset.enhanced');

    Route::post('reset-password/enhanced', [EnhancedNewPasswordController::class, 'store'])
        ->name('password.store.enhanced');
});
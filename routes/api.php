<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\ProductController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(['auth:sanctum', 'role:SUPER_ADMIN'])->prefix('admin')->name('api.admin.')->group(function () {
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::get('/products/{product}/commission', [ProductController::class, 'getCommission'])->name('products.commission');
});

Route::middleware(['auth:sanctum'])->prefix('user')->name('api.user.')->group(function () {
    Route::get('/email-preferences', [\App\Http\Controllers\API\UserEmailPreferenceController::class, 'index'])->name('email-preferences.index');
    Route::put('/email-preferences', [\App\Http\Controllers\API\UserEmailPreferenceController::class, 'update'])->name('email-preferences.update');
    Route::get('/email-history', [\App\Http\Controllers\API\UserEmailHistoryController::class, 'index'])->name('email-history.index');
    Route::get('/email-history/{id}', [\App\Http\Controllers\API\UserEmailHistoryController::class, 'show'])->name('email-history.show');
});

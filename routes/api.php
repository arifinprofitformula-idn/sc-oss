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

    // RBAC CRUD
    Route::prefix('rbac')->name('rbac.')->middleware('throttle:role-management')->group(function () {
        Route::get('/roles', [\App\Http\Controllers\Api\Admin\Rbac\RoleApiController::class, 'index'])->name('roles.index');
        Route::post('/roles', [\App\Http\Controllers\Api\Admin\Rbac\RoleApiController::class, 'store'])->name('roles.store');
        Route::put('/roles/{role}', [\App\Http\Controllers\Api\Admin\Rbac\RoleApiController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{role}', [\App\Http\Controllers\Api\Admin\Rbac\RoleApiController::class, 'destroy'])->name('roles.destroy');

        Route::get('/permissions', [\App\Http\Controllers\Api\Admin\Rbac\PermissionApiController::class, 'index'])->name('permissions.index');
        Route::post('/permissions', [\App\Http\Controllers\Api\Admin\Rbac\PermissionApiController::class, 'store'])->name('permissions.store');
        Route::put('/permissions/{permission}', [\App\Http\Controllers\Api\Admin\Rbac\PermissionApiController::class, 'update'])->name('permissions.update');
        Route::delete('/permissions/{permission}', [\App\Http\Controllers\Api\Admin\Rbac\PermissionApiController::class, 'destroy'])->name('permissions.destroy');

        Route::post('/users/{user}/roles', [\App\Http\Controllers\Api\Admin\Rbac\UserRoleApiController::class, 'assign'])->name('users.roles.assign');
        Route::put('/users/{user}/roles', [\App\Http\Controllers\Api\Admin\Rbac\UserRoleApiController::class, 'update'])->name('users.roles.update');
        Route::delete('/users/{user}/roles/{role}', [\App\Http\Controllers\Api\Admin\Rbac\UserRoleApiController::class, 'destroy'])->name('users.roles.destroy');
    });
});

Route::middleware(['auth:sanctum'])->prefix('user')->name('api.user.')->group(function () {
    Route::get('/email-preferences', [\App\Http\Controllers\API\UserEmailPreferenceController::class, 'index'])->name('email-preferences.index');
    Route::put('/email-preferences', [\App\Http\Controllers\API\UserEmailPreferenceController::class, 'update'])->name('email-preferences.update');
    Route::get('/email-history', [\App\Http\Controllers\API\UserEmailHistoryController::class, 'index'])->name('email-history.index');
    Route::get('/email-history/{id}', [\App\Http\Controllers\API\UserEmailHistoryController::class, 'show'])->name('email-history.show');
});

// Public webhooks (provider-specific signatures should be validated per provider)
Route::post('/webhooks/email/{provider}', [\App\Http\Controllers\Webhook\EmailWebhookController::class, 'handle'])
    ->name('webhooks.email');

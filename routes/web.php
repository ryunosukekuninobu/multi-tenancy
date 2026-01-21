<?php

use Illuminate\Support\Facades\Route;
use Calema\MultiTenancy\Http\Controllers\TenantController;

/*
|--------------------------------------------------------------------------
| Multi-Tenancy Web Routes
|--------------------------------------------------------------------------
|
| Here are the routes for tenant management.
| These routes require authentication and proper permissions.
|
*/

// Tenant Dashboard (requires authentication)
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/dashboard', [TenantController::class, 'dashboard'])
        ->name('tenant.dashboard');
});

// Tenant Management Routes
Route::middleware(['web'])->group(function () {
    // Public tenant creation (can be accessed without auth for new tenant registration)
    Route::get('/tenants/create', [TenantController::class, 'create'])
        ->name('tenants.create');

    Route::post('/tenants', [TenantController::class, 'store'])
        ->name('tenants.store');
});

// Authenticated Tenant Routes
Route::middleware(['web', 'auth'])->prefix('tenants')->group(function () {
    // Admin-only: List all tenants
    Route::get('/', [TenantController::class, 'index'])
        ->name('tenants.index');

    // View tenant details
    Route::get('/{tenant}', [TenantController::class, 'show'])
        ->name('tenants.show');

    // Edit tenant
    Route::get('/{tenant}/edit', [TenantController::class, 'edit'])
        ->name('tenants.edit');

    Route::put('/{tenant}', [TenantController::class, 'update'])
        ->name('tenants.update');

    // Switch to different tenant (admin only)
    Route::get('/{tenant}/switch', [TenantController::class, 'switch'])
        ->name('tenants.switch');
});

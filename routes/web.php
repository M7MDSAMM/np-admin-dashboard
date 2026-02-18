<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserDevicesController;
use App\Http\Controllers\UserPreferencesController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Dashboard — Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/health', function () {
    $version = env('APP_VERSION') ?: trim((string) shell_exec('git rev-parse --short HEAD')) ?: 'unknown';

    return response()->json([
        'service'   => config('app.name'),
        'status'    => 'ok',
        'time'      => now()->toIso8601String(),
        'version'   => $version,
    ]);
});

// ── Guest routes ────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// ── Authenticated routes ────────────────────────────────────────────────
Route::middleware(['admin.auth', 'remote.unauthorized'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Admin Management (super_admin only)
    Route::prefix('admins')->middleware('admin.super')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('admins.index');
        Route::get('/create', [AdminController::class, 'create'])->name('admins.create');
        Route::post('/', [AdminController::class, 'store'])->name('admins.store');
        Route::get('/{uuid}/edit', [AdminController::class, 'edit'])->name('admins.edit');
        Route::put('/{uuid}', [AdminController::class, 'update'])->name('admins.update');
        Route::delete('/{uuid}', [AdminController::class, 'destroy'])->name('admins.destroy');
        Route::patch('/{uuid}/toggle-active', [AdminController::class, 'toggleActive'])->name('admins.toggle-active');
    });

    // Recipient User Management (any admin)
    Route::prefix('users')->group(function () {
        Route::get('/', [UsersController::class, 'index'])->name('users.index');
        Route::get('/create', [UsersController::class, 'create'])->name('users.create');
        Route::post('/', [UsersController::class, 'store'])->name('users.store');
        Route::get('/{uuid}', [UsersController::class, 'show'])->name('users.show');
        Route::get('/{uuid}/edit', [UsersController::class, 'edit'])->name('users.edit');
        Route::put('/{uuid}', [UsersController::class, 'update'])->name('users.update');
        Route::delete('/{uuid}', [UsersController::class, 'destroy'])->name('users.destroy');

        Route::get('/{uuid}/preferences', [UserPreferencesController::class, 'show'])->name('users.preferences');
        Route::put('/{uuid}/preferences', [UserPreferencesController::class, 'update'])->name('users.preferences.update');

        Route::get('/{uuid}/devices', [UserDevicesController::class, 'index'])->name('users.devices');
        Route::post('/{uuid}/devices', [UserDevicesController::class, 'store'])->name('users.devices.store');
        Route::delete('/{uuid}/devices/{deviceUuid}', [UserDevicesController::class, 'destroy'])->name('users.devices.destroy');
    });
});

<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Dashboard — Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/health', fn () => response()->json([
    'service' => config('app.name'),
    'status'  => 'ok',
    'time'    => now()->toIso8601String(),
]));

// ── Guest routes ────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// ── Authenticated routes ────────────────────────────────────────────────
Route::middleware('admin.auth')->group(function () {
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
});

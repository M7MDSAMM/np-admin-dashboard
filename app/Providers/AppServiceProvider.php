<?php

namespace App\Providers;

use App\Auth\NullUserProvider;
use App\Services\Contracts\AdminAuthServiceInterface;
use App\Services\Contracts\AdminManagementServiceInterface;
use App\Services\Contracts\TemplateManagementServiceInterface;
use App\Services\Contracts\TemplateServiceClientInterface;
use App\Services\Contracts\UserManagementServiceInterface;
use App\Services\Contracts\UserServiceClientInterface;
use App\Services\Implementations\AdminAuthService;
use App\Services\Implementations\AdminManagementService;
use App\Services\Implementations\TemplateManagementService;
use App\Services\Implementations\TemplateServiceClient;
use App\Services\Implementations\UserManagementService;
use App\Services\Implementations\UserServiceClient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

/**
 * Central place for all service container bindings.
 *
 * The pattern is simple:
 *   Interface → Implementation → bind here → inject via constructor anywhere.
 *
 * Laravel's service container resolves these automatically when a class
 * type-hints the interface in its constructor. For example:
 *   public function __construct(AdminAuthServiceInterface $auth) { ... }
 * The container sees the type-hint, looks up the binding below, and injects
 * the correct implementation.
 *
 * All bindings are singletons — one instance per request, shared everywhere.
 * This is efficient (no duplicate HTTP clients) and ensures consistent state.
 */
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // HTTP client for User Service API calls.
        $this->app->singleton(
            UserServiceClientInterface::class,
            UserServiceClient::class,
        );
        $this->app->singleton(
            TemplateServiceClientInterface::class,
            TemplateServiceClient::class,
        );

        // Admin authentication & session management.
        $this->app->singleton(
            AdminAuthServiceInterface::class,
            AdminAuthService::class,
        );

        // Admin CRUD business logic.
        $this->app->singleton(
            AdminManagementServiceInterface::class,
            AdminManagementService::class,
        );

        // Recipient user management business logic.
        $this->app->singleton(
            UserManagementServiceInterface::class,
            UserManagementService::class,
        );
        $this->app->singleton(
            TemplateManagementServiceInterface::class,
            TemplateManagementService::class,
        );
    }

    public function boot(): void
    {
        Auth::provider('null', function () {
            return new NullUserProvider();
        });
    }
}

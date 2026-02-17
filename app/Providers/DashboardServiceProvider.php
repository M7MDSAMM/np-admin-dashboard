<?php

namespace App\Providers;

use App\Application\Admin\AdminManagementService;
use App\Application\Auth\AdminSessionService;
use App\Domain\Admin\AdminClientInterface;
use App\Domain\Auth\AdminAuthClientInterface;
use App\Infrastructure\Clients\UserServiceAdminAuthClient;
use App\Infrastructure\Clients\UserServiceAdminClient;
use App\Infrastructure\Http\SafeHttpClient;
use Illuminate\Support\ServiceProvider;

class DashboardServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SafeHttpClient::class);

        $this->app->singleton(AdminAuthClientInterface::class, function ($app) {
            return new UserServiceAdminAuthClient(
                $app->make(SafeHttpClient::class),
                config('services.user_service.base_url'),
            );
        });

        $this->app->singleton(AdminClientInterface::class, function ($app) {
            return new UserServiceAdminClient(
                $app->make(SafeHttpClient::class),
                config('services.user_service.base_url'),
            );
        });

        $this->app->singleton(AdminSessionService::class);
        $this->app->singleton(AdminManagementService::class);
    }
}

<?php

use App\Http\Middleware\CorrelationIdMiddleware;
use App\Http\Middleware\RequireAdminSessionMiddleware;
use App\Http\Middleware\RequireSuperAdminMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        App\Providers\DashboardServiceProvider::class,
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(CorrelationIdMiddleware::class);

        $middleware->alias([
            'admin.auth'  => RequireAdminSessionMiddleware::class,
            'admin.super' => RequireSuperAdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

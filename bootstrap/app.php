<?php

use App\Http\Middleware\CorrelationIdMiddleware;
use App\Http\Middleware\RequireAdminSessionMiddleware;
use App\Http\Middleware\RequireSuperAdminMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Runs on EVERY request — assigns/forwards a correlation ID for
        // distributed tracing across microservices.
        $middleware->append(CorrelationIdMiddleware::class);

        // Route-level middleware aliases used in routes/web.php:
        //   Route::middleware('admin.auth')  → checks JWT session exists
        //   Route::middleware('admin.super') → checks role === super_admin
        $middleware->alias([
            'admin.auth'  => RequireAdminSessionMiddleware::class,
            'admin.super' => RequireSuperAdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

<?php

use App\Http\Middleware\CorrelationIdMiddleware;
use App\Http\Middleware\HandleUnauthorizedRemoteMiddleware;
use App\Http\Middleware\RequireAdminSessionMiddleware;
use App\Http\Middleware\RequireSuperAdminMiddleware;
use App\Http\Middleware\RequestTimingMiddleware;
use App\Services\Contracts\AdminAuthServiceInterface;
use App\Services\Exceptions\UnauthorizedRemoteException;
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
        $middleware->append(RequestTimingMiddleware::class);
        $middleware->appendToGroup('web', HandleUnauthorizedRemoteMiddleware::class);

        // Route-level middleware aliases used in routes/web.php:
        //   Route::middleware('admin.auth')  → checks JWT session exists
        //   Route::middleware('admin.super') → checks role === super_admin
        $middleware->alias([
            'admin.auth'  => RequireAdminSessionMiddleware::class,
            'admin.super' => RequireSuperAdminMiddleware::class,
            'remote.unauthorized' => HandleUnauthorizedRemoteMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (UnauthorizedRemoteException $e, $request) {
            // 401 → invalidate session
            if ($e->statusCode === 401) {
                $auth = app(AdminAuthServiceInterface::class);
                $auth->logout();
                $message = 'Session expired, please login again';

                if ($request->expectsJson()) {
                    return response()->json([
                        'success'        => false,
                        'message'        => $message,
                        'error_code'     => $e->errorCode ?? 'AUTH_INVALID',
                        'correlation_id' => $e->correlationId ?? $request->header('X-Correlation-Id', ''),
                    ], 401);
                }

                return redirect()->route('login')->with('error', $message);
            }

            // 403 → keep session, show forbidden
            if ($request->expectsJson()) {
                return response()->json([
                    'success'        => false,
                    'message'        => $e->getMessage(),
                    'error_code'     => $e->errorCode ?? 'FORBIDDEN',
                    'correlation_id' => $e->correlationId ?? $request->header('X-Correlation-Id', ''),
                ], 403);
            }

            abort(403, 'Forbidden');
        });
    })->create();

<?php

namespace App\Http\Middleware;

use App\Services\Contracts\AdminAuthServiceInterface;
use App\Services\Exceptions\UnauthorizedRemoteException;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Catches 401/403 responses from User Service (surfaced as UnauthorizedRemoteException),
 * logs out the admin, and redirects to login with a flash message.
 */
class HandleUnauthorizedRemoteMiddleware
{
    public function __construct(
        private readonly AdminAuthServiceInterface $auth,
    ) {}

    public function handle(Request $request, Closure $next): Response|JsonResponse|RedirectResponse
    {
        try {
            return $next($request);
        } catch (UnauthorizedRemoteException $e) {
            $this->auth->logout();

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
    }
}

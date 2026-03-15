<?php

namespace App\Http\Middleware;

use App\Services\Contracts\AdminAuthServiceInterface;
use App\Services\Exceptions\UnauthorizedRemoteException;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Catches 401/403 responses from remote services (surfaced as UnauthorizedRemoteException).
 *
 * 401 → session is invalid: logout, redirect to login.
 * 403 → session is valid but access denied: keep session, show forbidden.
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
            // 401 → session is invalid; logout and redirect to login.
            if ($e->statusCode === Response::HTTP_UNAUTHORIZED) {
                Log::warning('auth.remote_unauthorized', [
                    'admin_uuid'     => $this->auth->getAdmin()['uuid'] ?? null,
                    'service'        => $e->serviceName,
                    'error_code'     => $e->errorCode,
                    'correlation_id' => $e->correlationId ?? $request->header('X-Correlation-Id', ''),
                ]);

                $this->auth->logout();
                $message = 'Your session is no longer valid. Please sign in again.';

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

            // 403 → keep session; show forbidden.
            Log::warning('auth.remote_forbidden', [
                'admin_uuid'     => $this->auth->getAdmin()['uuid'] ?? null,
                'service'        => $e->serviceName,
                'error_code'     => $e->errorCode,
                'correlation_id' => $e->correlationId ?? $request->header('X-Correlation-Id', ''),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success'        => false,
                    'message'        => $e->getMessage(),
                    'error_code'     => $e->errorCode ?? 'FORBIDDEN',
                    'correlation_id' => $e->correlationId ?? $request->header('X-Correlation-Id', ''),
                ], 403);
            }

            abort(403, 'Forbidden');
        }
    }
}

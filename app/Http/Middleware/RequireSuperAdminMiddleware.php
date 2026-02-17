<?php

namespace App\Http\Middleware;

use App\Application\Auth\AdminSessionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireSuperAdminMiddleware
{
    public function __construct(
        private readonly AdminSessionService $sessionService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->sessionService->isSuperAdmin()) {
            abort(403, 'Super Admin access required.');
        }

        return $next($request);
    }
}

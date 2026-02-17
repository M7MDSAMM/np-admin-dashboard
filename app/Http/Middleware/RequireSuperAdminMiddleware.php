<?php

namespace App\Http\Middleware;

use App\Services\Contracts\AdminAuthServiceInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate: blocks non-super_admin users with a 403 Forbidden.
 *
 * Applied to routes that only super admins should access (e.g. /admins).
 * This middleware always runs AFTER RequireAdminSessionMiddleware, so
 * we can safely assume the admin is authenticated at this point.
 */
class RequireSuperAdminMiddleware
{
    public function __construct(
        private readonly AdminAuthServiceInterface $auth,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->auth->isSuperAdmin()) {
            abort(403, 'Super Admin access required.');
        }

        return $next($request);
    }
}

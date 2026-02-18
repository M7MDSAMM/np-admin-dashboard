<?php

namespace App\Http\Middleware;

use App\Services\Contracts\AdminAuthServiceInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate: redirects unauthenticated visitors to the login page.
 *
 * Also shares the current admin profile with all Blade views via
 * view()->share(), so templates can access {{ $currentAdmin['name'] }}
 * without every controller having to pass it manually.
 */
class RequireAdminSessionMiddleware
{
    public function __construct(
        private readonly AdminAuthServiceInterface $auth,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->auth->isTokenExpired()) {
            $this->auth->logout();

            return redirect()->route('login')->with('error', 'Session expired, please login again');
        }

        if (! $this->auth->isAuthenticated()) {
            return redirect()->route('login')->with('error', 'Please log in to continue.');
        }

        // Share admin profile with all views (used in layout sidebar/topbar).
        view()->share('currentAdmin', $this->auth->getAdmin());

        return $next($request);
    }
}

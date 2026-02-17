<?php

namespace App\Http\Middleware;

use App\Application\Auth\AdminSessionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireAdminSessionMiddleware
{
    public function __construct(
        private readonly AdminSessionService $sessionService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->sessionService->isAuthenticated()) {
            return redirect()->route('login')->with('error', 'Please log in to continue.');
        }

        view()->share('currentAdmin', $this->sessionService->getAdmin());

        return $next($request);
    }
}

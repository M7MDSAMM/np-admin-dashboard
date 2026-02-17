<?php

namespace App\Http\Controllers\Auth;

use App\Application\Auth\AdminSessionService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function __construct(
        private readonly AdminSessionService $sessionService,
    ) {}

    public function showLoginForm(): View|RedirectResponse
    {
        if ($this->sessionService->isAuthenticated()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($this->sessionService->attemptLogin($request->input('email'), $request->input('password'))) {
            return redirect()->route('dashboard');
        }

        return back()
            ->withInput($request->only('email'))
            ->with('error', 'Invalid credentials or account is inactive.');
    }

    public function logout(): RedirectResponse
    {
        $this->sessionService->logout();

        return redirect()->route('login')->with('success', 'You have been logged out.');
    }
}

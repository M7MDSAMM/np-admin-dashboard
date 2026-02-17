<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Contracts\AdminAuthServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function __construct(
        private readonly AdminAuthServiceInterface $auth,
    ) {}

    public function showLoginForm(): View|RedirectResponse
    {
        // Already logged in? Skip straight to the dashboard.
        if ($this->auth->isAuthenticated()) {
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

        if ($this->auth->attemptLogin($request->input('email'), $request->input('password'))) {
            return redirect()->route('dashboard');
        }

        return back()
            ->withInput($request->only('email'))
            ->with('error', 'Invalid credentials or account is inactive.');
    }

    public function logout(): RedirectResponse
    {
        $this->auth->logout();

        return redirect()->route('login')->with('success', 'You have been logged out.');
    }
}

<?php

namespace App\Application\Auth;

use App\Domain\Auth\AdminAuthClientInterface;
use App\Domain\Exceptions\ExternalServiceException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class AdminSessionService
{
    private const SESSION_TOKEN_KEY = 'admin_jwt_token';
    private const SESSION_ADMIN_KEY = 'admin_profile';

    public function __construct(
        private readonly AdminAuthClientInterface $authClient,
    ) {}

    public function attemptLogin(string $email, string $password): bool
    {
        try {
            $tokenData = $this->authClient->login($email, $password);
            $profile = $this->authClient->me($tokenData['access_token']);

            Session::put(self::SESSION_TOKEN_KEY, $tokenData['access_token']);
            Session::put(self::SESSION_ADMIN_KEY, $profile);
            Session::regenerate();

            Log::info('admin.login_success', [
                'admin_uuid' => $profile['uuid'] ?? null,
                'email'      => $email,
            ]);

            return true;
        } catch (ExternalServiceException $e) {
            Log::warning('admin.login_failed', [
                'email'  => $email,
                'status' => $e->statusCode,
            ]);

            return false;
        }
    }

    public function logout(): void
    {
        $profile = Session::get(self::SESSION_ADMIN_KEY);

        Log::info('admin.logout', ['admin_uuid' => $profile['uuid'] ?? null]);

        Session::forget(self::SESSION_TOKEN_KEY);
        Session::forget(self::SESSION_ADMIN_KEY);
        Session::invalidate();
        Session::regenerateToken();
    }

    public function isAuthenticated(): bool
    {
        return Session::has(self::SESSION_TOKEN_KEY) && Session::has(self::SESSION_ADMIN_KEY);
    }

    public function getToken(): ?string
    {
        return Session::get(self::SESSION_TOKEN_KEY);
    }

    public function getAdmin(): ?array
    {
        return Session::get(self::SESSION_ADMIN_KEY);
    }

    public function isSuperAdmin(): bool
    {
        return ($this->getAdmin()['role'] ?? '') === 'super_admin';
    }
}

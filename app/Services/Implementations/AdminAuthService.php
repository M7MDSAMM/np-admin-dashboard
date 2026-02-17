<?php

namespace App\Services\Implementations;

use App\Services\Contracts\AdminAuthServiceInterface;
use App\Services\Contracts\UserServiceClientInterface;
use App\Services\Exceptions\ExternalServiceException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

/**
 * Manages the admin's JWT-based session.
 *
 * Login flow:
 *   1. POST credentials to User Service → receive JWT access_token.
 *   2. GET /admin/me with the JWT → receive admin profile (uuid, role, etc.).
 *   3. Store both in Laravel's server-side session.
 *   4. As a safety net, also decode the JWT payload (base64, no crypto needed)
 *      to ensure we always have the admin's role — even if the /me call
 *      returns incomplete data.
 *
 * Why server-side session?
 *   The JWT is never exposed to the browser. This prevents XSS from
 *   stealing the token. The browser only holds a session cookie.
 */
class AdminAuthService implements AdminAuthServiceInterface
{
    /**
     * Session keys — centralised here so they're easy to find.
     */
    private const SESSION_TOKEN_KEY = 'admin_jwt_token';
    private const SESSION_ADMIN_KEY = 'admin_profile';

    public function __construct(
        private readonly UserServiceClientInterface $client,
    ) {}

    public function attemptLogin(string $email, string $password): bool
    {
        try {
            // Step 1 — Authenticate with User Service and get JWT.
            $tokenData = $this->client->login($email, $password);
            $token = $tokenData['access_token'];

            // Step 2 — Fetch admin profile using the JWT.
            $profile = $this->client->me($token);

            // Step 3 — Safety net: decode JWT payload to guarantee role is present.
            //   JWT structure: header.payload.signature (each is base64url-encoded).
            //   We only need the payload — no cryptographic verification required
            //   because the User Service already signed and validated it.
            $profile['role'] = $profile['role'] ?? $this->extractRoleFromJwt($token);

            // Step 4 — Persist in session.
            Session::put(self::SESSION_TOKEN_KEY, $token);
            Session::put(self::SESSION_ADMIN_KEY, $profile);
            Session::regenerate(); // prevent session fixation attacks

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
        return Session::has(self::SESSION_TOKEN_KEY)
            && Session::has(self::SESSION_ADMIN_KEY);
    }

    public function isSuperAdmin(): bool
    {
        $admin = $this->getAdmin();

        // Handle both flat profile and legacy nested format where
        // the profile was stored as ['data' => ['role' => ...]].
        $role = $admin['role'] ?? $admin['data']['role'] ?? '';

        return $role === 'super_admin';
    }

    public function getAdmin(): ?array
    {
        $admin = Session::get(self::SESSION_ADMIN_KEY);

        // Normalize: if the profile was stored with a nested 'data' wrapper
        // (legacy format from the old client), unwrap it transparently.
        if ($admin && isset($admin['data']) && ! isset($admin['role'])) {
            $admin = $admin['data'];
        }

        return $admin;
    }

    public function getToken(): ?string
    {
        return Session::get(self::SESSION_TOKEN_KEY);
    }

    // ── Private helpers ─────────────────────────────────────────────────

    /**
     * Decode the JWT payload segment to extract the admin role.
     *
     * This is a lightweight fallback — we're reading the token's claims
     * (which the User Service already cryptographically signed) without
     * needing the public key on the dashboard side.
     */
    private function extractRoleFromJwt(string $token): string
    {
        $parts = explode('.', $token);

        if (count($parts) < 2) {
            return 'admin'; // safe default if token format is unexpected
        }

        // base64url → base64 → decode → parse JSON
        $payload = json_decode(
            base64_decode(strtr($parts[1], '-_', '+/')),
            true,
        );

        return $payload['role'] ?? 'admin';
    }
}

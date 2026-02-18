<?php

namespace App\Services\Contracts;

/**
 * Admin authentication & session management contract.
 *
 * This service owns the admin's JWT session lifecycle:
 *   1. Login  — calls User Service, stores JWT + profile in session.
 *   2. Logout — clears session data.
 *   3. Guards — isAuthenticated(), isSuperAdmin() used by middleware.
 *
 * The JWT token is stored server-side in Laravel's session (not in a
 * browser cookie or localStorage), keeping it secure from XSS attacks.
 */
interface AdminAuthServiceInterface
{
    /**
     * Attempt login via User Service. On success, stores the JWT token
     * and admin profile in the session.
     */
    public function attemptLogin(string $email, string $password): bool;

    /** Clear all session data and invalidate the session. */
    public function logout(): void;

    /** Check if a valid JWT + admin profile exist in the session. */
    public function isAuthenticated(): bool;

    /** Check if the logged-in admin has the super_admin role. */
    public function isSuperAdmin(): bool;

    /** Retrieve the stored admin profile array, or null if not logged in. */
    public function getAdmin(): ?array;

    /** Retrieve the stored JWT access token, or null if not logged in. */
    public function getToken(): ?string;

    /** Retrieve the JWT expiry timestamp if stored. */
    public function getTokenExpiresAt(): ?\Carbon\CarbonImmutable;

    /** Whether the stored JWT has expired. */
    public function isTokenExpired(): bool;
}

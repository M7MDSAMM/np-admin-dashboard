<?php

namespace App\Services\Contracts;

/**
 * HTTP client contract for communicating with the User Service API.
 *
 * This interface abstracts all HTTP calls to the User Service microservice.
 * The implementation uses Laravel's Http facade (Guzzle under the hood)
 * and parses the standardised API response envelope:
 *   { success, message, data, meta, errors, error_code, correlation_id }
 *
 * Pattern: Controller → Service → Client (this layer)
 *   - Controllers handle HTTP request/response for the browser.
 *   - Services contain business logic (session management, logging).
 *   - Clients handle outbound HTTP communication to other microservices.
 */
interface UserServiceClientInterface
{
    // ── Authentication ──────────────────────────────────────────────────

    /**
     * POST /admin/auth/login — exchange credentials for a JWT token.
     *
     * @return array{access_token: string, token_type: string, expires_in: int}
     *
     * @throws \App\Services\Exceptions\ExternalServiceException
     */
    public function login(string $email, string $password): array;

    /**
     * GET /admin/me — fetch the authenticated admin's profile.
     *
     * @return array{uuid: string, name: string, email: string, role: string, is_active: bool}
     *
     * @throws \App\Services\Exceptions\ExternalServiceException
     */
    public function me(string $token): array;

    // ── Admin Management (CRUD) ─────────────────────────────────────────

    /**
     * GET /admins — paginated list of all admins.
     *
     * @return array{data: array, pagination: array|null}
     *
     * @throws \App\Services\Exceptions\ExternalServiceException
     */
    public function listAdmins(string $token, int $page = 1, int $perPage = 15): array;

    /**
     * GET /admins/{uuid} — single admin by UUID.
     *
     * @throws \App\Services\Exceptions\ExternalServiceException
     */
    public function findAdmin(string $token, string $uuid): ?array;

    /**
     * POST /admins — create a new admin.
     *
     * @throws \App\Services\Exceptions\ExternalServiceException
     */
    public function createAdmin(string $token, array $data): array;

    /**
     * PUT /admins/{uuid} — update an existing admin.
     *
     * @throws \App\Services\Exceptions\ExternalServiceException
     */
    public function updateAdmin(string $token, string $uuid, array $data): array;

    /**
     * DELETE /admins/{uuid} — soft-delete an admin.
     *
     * @throws \App\Services\Exceptions\ExternalServiceException
     */
    public function deleteAdmin(string $token, string $uuid): bool;

    /**
     * PATCH /admins/{uuid}/toggle-active — flip is_active flag.
     *
     * @throws \App\Services\Exceptions\ExternalServiceException
     */
    public function toggleActive(string $token, string $uuid): array;
}

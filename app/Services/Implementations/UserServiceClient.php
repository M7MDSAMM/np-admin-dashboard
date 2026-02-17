<?php

namespace App\Services\Implementations;

use App\Services\Contracts\UserServiceClientInterface;
use App\Services\Exceptions\ExternalServiceException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Guzzle-based HTTP client for the User Service API.
 *
 * Every outbound call follows the same pattern:
 *   1. Build a PendingRequest with base URL, auth token, correlation ID.
 *   2. Send the HTTP request (GET / POST / PUT / PATCH / DELETE).
 *   3. Parse the standardised response envelope { success, data, meta, errors }.
 *   4. Return the `data` payload on success, or throw ExternalServiceException.
 *
 * IMPORTANT — Guzzle base_uri trailing-slash behaviour:
 *   When the base URL is "http://host/api/v1" (no trailing slash) and the
 *   relative path is "admin/me", Guzzle resolves it per RFC 3986 and replaces
 *   "v1" with "admin/me" → "http://host/api/admin/me" (missing "v1").
 *   Fix: always append a trailing slash to the base URL.
 */
class UserServiceClient implements UserServiceClientInterface
{
    private string $baseUrl;

    public function __construct()
    {
        // Ensure trailing slash so Guzzle resolves relative paths correctly.
        $this->baseUrl = rtrim(config('services.user_service.base_url'), '/').'/';
    }

    // ── Authentication ──────────────────────────────────────────────────

    public function login(string $email, string $password): array
    {
        $response = $this->request()->post('admin/auth/login', compact('email', 'password'));

        return $this->extractData($response, 'Login failed');
    }

    public function me(string $token): array
    {
        $response = $this->authenticatedRequest($token)->get('admin/me');

        return $this->extractData($response, 'Failed to fetch profile');
    }

    // ── Admin Management ────────────────────────────────────────────────

    public function listAdmins(string $token, int $page = 1, int $perPage = 15): array
    {
        $response = $this->authenticatedRequest($token)
            ->get('admins', ['page' => $page, 'per_page' => $perPage]);

        $json = $response->json();

        if ($response->successful() && ($json['success'] ?? false)) {
            return [
                'data'       => $json['data'] ?? [],
                'pagination' => $json['meta']['pagination'] ?? null,
            ];
        }

        throw new ExternalServiceException('Failed to fetch admin list', $response->status());
    }

    public function findAdmin(string $token, string $uuid): ?array
    {
        $response = $this->authenticatedRequest($token)->get("admins/{$uuid}");

        if ($response->status() === 404) {
            return null;
        }

        return $this->extractData($response, 'Failed to fetch admin');
    }

    public function createAdmin(string $token, array $data): array
    {
        $response = $this->authenticatedRequest($token)->post('admins', $data);

        return $this->extractDataOrThrowWithErrors($response, 'Failed to create admin');
    }

    public function updateAdmin(string $token, string $uuid, array $data): array
    {
        $response = $this->authenticatedRequest($token)->put("admins/{$uuid}", $data);

        return $this->extractDataOrThrowWithErrors($response, 'Failed to update admin');
    }

    public function deleteAdmin(string $token, string $uuid): bool
    {
        $response = $this->authenticatedRequest($token)->delete("admins/{$uuid}");

        return $response->successful();
    }

    public function toggleActive(string $token, string $uuid): array
    {
        $response = $this->authenticatedRequest($token)->patch("admins/{$uuid}/toggle-active", []);

        return $this->extractData($response, 'Failed to toggle admin status');
    }

    // ── Private helpers ─────────────────────────────────────────────────

    /**
     * Build a base PendingRequest with shared headers.
     *
     * Every request includes:
     *   - Accept: application/json (so Laravel returns JSON errors)
     *   - X-Correlation-Id (for distributed tracing across services)
     */
    private function request(): PendingRequest
    {
        $correlationId = request()->header('X-Correlation-Id', '');

        return Http::baseUrl($this->baseUrl)
            ->acceptJson()
            ->withHeaders(['X-Correlation-Id' => $correlationId])
            ->timeout(10)
            ->connectTimeout(5);
    }

    /**
     * Build an authenticated request — adds the Bearer token header.
     */
    private function authenticatedRequest(string $token): PendingRequest
    {
        return $this->request()->withToken($token);
    }

    /**
     * Extract `data` from the standardised response envelope.
     *
     * The User Service always returns:
     *   { "success": true, "data": { ... }, "meta": {}, "correlation_id": "..." }
     * on success. On failure it returns:
     *   { "success": false, "message": "...", "errors": { ... }, "error_code": "..." }
     *
     * @throws ExternalServiceException when success is false or HTTP status is not 2xx
     */
    private function extractData(Response $response, string $fallbackMessage): array
    {
        $json = $response->json();

        if ($response->successful() && ($json['success'] ?? false)) {
            return $json['data'] ?? [];
        }

        throw new ExternalServiceException(
            $json['message'] ?? $fallbackMessage,
            $response->status(),
        );
    }

    /**
     * Like extractData, but also forwards validation errors from the
     * remote service (e.g. "email already taken") into the exception context.
     */
    private function extractDataOrThrowWithErrors(Response $response, string $fallbackMessage): array
    {
        $json = $response->json();

        if ($response->successful() && ($json['success'] ?? false)) {
            return $json['data'] ?? [];
        }

        throw new ExternalServiceException(
            $json['message'] ?? $fallbackMessage,
            $response->status(),
            $json['errors'] ?? [],
        );
    }
}

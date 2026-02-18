<?php

namespace App\Services\Implementations;

use App\Services\Contracts\UserServiceClientInterface;
use App\Services\Exceptions\ExternalServiceException;
use App\Services\Exceptions\UnauthorizedRemoteException;
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
        $response = $this->timedRequest(
            fn () => $this->request()->post('admin/auth/login', data: ['email' => $email, 'password' => $password]),
            'admin/auth/login',
            'POST',
        );

        return $this->extractData($response, 'Login failed');
    }

    public function me(string $token): array
    {
        $response = $this->timedRequest(
            fn () => $this->authenticatedRequest($token)->get('admin/me'),
            'admin/me',
            'GET',
        );

        return $this->extractData($response, 'Failed to fetch profile');
    }

    // ── Admin Management ────────────────────────────────────────────────

    public function listAdmins(string $token, int $page = 1, int $perPage = 15): array
    {
        $response = $this->timedRequest(
            fn () => $this->authenticatedRequest($token)->get('admins', ['page' => $page, 'per_page' => $perPage]),
            'admins',
            'GET',
        );

        $this->throwIfUnauthorized($response);

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
        $response = $this->timedRequest(
            fn () => $this->authenticatedRequest($token)->get("admins/{$uuid}"),
            "admins/{$uuid}",
            'GET',
        );

        $this->throwIfUnauthorized($response);

        if ($response->status() === 404) {
            return null;
        }

        return $this->extractData($response, 'Failed to fetch admin');
    }

    public function createAdmin(string $token, array $data): array
    {
        $response = $this->timedRequest(
            fn () => $this->authenticatedRequest($token)->post('admins', $data),
            'admins',
            'POST',
        );

        return $this->extractDataOrThrowWithErrors($response, 'Failed to create admin');
    }

    public function updateAdmin(string $token, string $uuid, array $data): array
    {
        $response = $this->timedRequest(
            fn () => $this->authenticatedRequest($token)->put("admins/{$uuid}", $data),
            "admins/{$uuid}",
            'PUT',
        );

        return $this->extractDataOrThrowWithErrors($response, 'Failed to update admin');
    }

    public function deleteAdmin(string $token, string $uuid): bool
    {
        $response = $this->timedRequest(
            fn () => $this->authenticatedRequest($token)->delete("admins/{$uuid}"),
            "admins/{$uuid}",
            'DELETE',
        );

        $this->throwIfUnauthorized($response);

        return $response->successful();
    }

    public function toggleActive(string $token, string $uuid): array
    {
        $response = $this->authenticatedRequest($token)->patch("admins/{$uuid}/toggle-active", []);

        return $this->extractData($response, 'Failed to toggle admin status');
    }

    // ── Recipient User Management ───────────────────────────────────────

    public function listUsers(string $token, array $query = []): array
    {
        $response = $this->timedRequest(
            fn () => $this->authenticatedRequest($token)->get('users', $query),
            'users',
            'GET',
        );

        $this->throwIfUnauthorized($response);

        $json = $response->json();

        if ($response->successful() && ($json['success'] ?? false)) {
            return [
                'data'       => $json['data'] ?? [],
                'pagination' => $json['meta']['pagination'] ?? null,
            ];
        }

        throw new ExternalServiceException('Failed to fetch user list', $response->status());
    }

    public function findUser(string $token, string $uuid): ?array
    {
        $response = $this->timedRequest(
            fn () => $this->authenticatedRequest($token)->get("users/{$uuid}"),
            "users/{$uuid}",
            'GET',
        );

        $this->throwIfUnauthorized($response);

        if ($response->status() === 404) {
            return null;
        }

        return $this->extractData($response, 'Failed to fetch user');
    }

    public function createUser(string $token, array $data): array
    {
        $response = $this->timedRequest(
            fn () => $this->authenticatedRequest($token)->post('users', $data),
            'users',
            'POST',
        );

        return $this->extractDataOrThrowWithErrors($response, 'Failed to create user');
    }

    public function updateUser(string $token, string $uuid, array $data): array
    {
        $response = $this->timedRequest(
            fn () => $this->authenticatedRequest($token)->put("users/{$uuid}", $data),
            "users/{$uuid}",
            'PUT',
        );

        return $this->extractDataOrThrowWithErrors($response, 'Failed to update user');
    }

    public function deleteUser(string $token, string $uuid): bool
    {
        $response = $this->timedRequest(
            fn () => $this->authenticatedRequest($token)->delete("users/{$uuid}"),
            "users/{$uuid}",
            'DELETE',
        );

        $this->ensureSuccess($response, 'Failed to delete user');

        return true;
    }

    // ── User Preferences ────────────────────────────────────────────────

    public function getUserPreferences(string $token, string $uuid): array
    {
        $response = $this->timedRequest(
            fn () => $this->authenticatedRequest($token)->get("users/{$uuid}/preferences"),
            "users/{$uuid}/preferences",
            'GET',
        );

        return $this->extractData($response, 'Failed to fetch preferences');
    }

    public function updateUserPreferences(string $token, string $uuid, array $data): array
    {
        $response = $this->timedRequest(
            fn () => $this->authenticatedRequest($token)->put("users/{$uuid}/preferences", $data),
            "users/{$uuid}/preferences",
            'PUT',
        );

        return $this->extractDataOrThrowWithErrors($response, 'Failed to update preferences');
    }

    // ── User Devices ────────────────────────────────────────────────────

    public function listUserDevices(string $token, string $uuid): array
    {
        $response = $this->timedRequest(
            fn () => $this->authenticatedRequest($token)->get("users/{$uuid}/devices"),
            "users/{$uuid}/devices",
            'GET',
        );

        return $this->extractData($response, 'Failed to fetch devices');
    }

    public function addUserDevice(string $token, string $uuid, array $data): array
    {
        $response = $this->timedRequest(
            fn () => $this->authenticatedRequest($token)->post("users/{$uuid}/devices", $data),
            "users/{$uuid}/devices",
            'POST',
        );

        return $this->extractDataOrThrowWithErrors($response, 'Failed to add device');
    }

    public function deleteUserDevice(string $token, string $userUuid, string $deviceUuid): bool
    {
        $response = $this->timedRequest(
            fn () => $this->authenticatedRequest($token)->delete("users/{$userUuid}/devices/{$deviceUuid}"),
            "users/{$userUuid}/devices/{$deviceUuid}",
            'DELETE',
        );

        $this->ensureSuccess($response, 'Failed to delete device');

        return true;
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

        if (($json['success'] ?? false) === true) {
            return $json['data'] ?? [];
        }

        $this->throwServiceException($response, $json, $fallbackMessage);
    }

    /**
     * Like extractData, but also forwards validation errors from the
     * remote service (e.g. "email already taken") into the exception context.
     */
    private function extractDataOrThrowWithErrors(Response $response, string $fallbackMessage): array
    {
        $json = $response->json();

        if (($json['success'] ?? false) === true) {
            return $json['data'] ?? [];
        }

        $this->throwServiceException($response, $json, $fallbackMessage);
    }

    /**
     * Throws UnauthorizedRemoteException when the response status is 401 or 403.
     */
    private function throwIfUnauthorized(Response $response): void
    {
        if (in_array($response->status(), [401, 403], true)) {
            $json = $response->json() ?? [];

            throw new UnauthorizedRemoteException(
                $json['message'] ?? 'Unauthorized',
                $response->status(),
                $json['error_code'] ?? null,
                $json['correlation_id'] ?? $response->header('X-Correlation-Id', ''),
                $json['errors'] ?? [],
            );
        }
    }

    /**
     * Measure outbound HTTP latency and emit a structured log entry.
     *
     * @param callable(): Response $callback
     */
    private function timedRequest(callable $callback, string $endpoint, string $method): Response
    {
        $started = microtime(true);
        $response = $callback();

        $latencyMs = (microtime(true) - $started) * 1000;

        Log::info('http.outbound.user_service', [
            'service'        => env('SERVICE_NAME', config('app.name', 'admin-dashboard')),
            'endpoint'       => $endpoint,
            'method'         => $method,
            'status_code'    => $response->status(),
            'latency_ms'     => round($latencyMs, 2),
            'correlation_id' => request()->header('X-Correlation-Id', ''),
        ]);

        return $response;
    }

    private function ensureSuccess(Response $response, string $fallbackMessage): void
    {
        $json = $response->json() ?? [];

        if (($json['success'] ?? false) === true) {
            return;
        }

        $this->throwServiceException($response, $json, $fallbackMessage);
    }

    private function throwServiceException(Response $response, array $json, string $fallbackMessage): never
    {
        if (in_array($response->status(), [401, 403], true)) {
            $this->throwIfUnauthorized($response);
        }

        throw new ExternalServiceException(
            $json['message'] ?? $fallbackMessage,
            $response->status(),
            $json['errors'] ?? [],
            $json['error_code'] ?? null,
            $json['correlation_id'] ?? $response->header('X-Correlation-Id', '')
        );
    }
}

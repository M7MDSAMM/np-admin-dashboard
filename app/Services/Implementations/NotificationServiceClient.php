<?php

namespace App\Services\Implementations;

use App\Services\Contracts\AdminAuthServiceInterface;
use App\Services\Contracts\NotificationServiceClientInterface;
use App\Services\Exceptions\ExternalServiceException;
use App\Services\Exceptions\UnauthorizedRemoteException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationServiceClient implements NotificationServiceClientInterface
{
    private string $baseUrl;

    public function __construct(
        private readonly AdminAuthServiceInterface $auth,
    ) {
        $this->baseUrl = rtrim(config('services.notification_service.base_url'), '/').'/';
    }

    public function listNotifications(array $filters = [], int $page = 1, int $perPage = 15): array
    {
        $query = array_filter(array_merge($filters, [
            'page'     => $page,
            'per_page' => $perPage,
        ]));

        $response = $this->timedRequest(
            fn () => $this->authenticatedRequest()->get('notifications', $query),
            'notifications',
            'GET',
        );

        return $this->extractPayload($response, 'Failed to list notifications');
    }

    public function createNotification(array $payload): array
    {
        $response = $this->timedRequest(
            fn () => $this->authenticatedRequest()->post('notifications', $payload),
            'notifications',
            'POST',
        );

        return $this->extractPayload($response, 'Failed to create notification');
    }

    public function getNotification(string $uuid): array
    {
        $response = $this->timedRequest(
            fn () => $this->authenticatedRequest()->get("notifications/{$uuid}"),
            "notifications/{$uuid}",
            'GET',
        );

        return $this->extractPayload($response, 'Failed to fetch notification');
    }

    public function retryNotification(string $uuid): array
    {
        $response = $this->timedRequest(
            fn () => $this->authenticatedRequest()->post("notifications/{$uuid}/retry"),
            "notifications/{$uuid}/retry",
            'POST',
        );

        return $this->extractPayload($response, 'Failed to retry notification');
    }

    public function health(): array
    {
        $response = $this->timedRequest(
            fn () => $this->request()->get('health'),
            'health',
            'GET',
        );

        return $this->extractPayload($response, 'Notification service health check failed');
    }

    // ── Private helpers ─────────────────────────────────────────────────

    private function request(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->acceptJson()
            ->asJson()
            ->timeout(10)
            ->connectTimeout(5)
            ->withHeaders([
                'X-Correlation-Id' => request()->header('X-Correlation-Id', ''),
            ]);
    }

    private function authenticatedRequest(): PendingRequest
    {
        $token = $this->auth->getToken();

        if (! $token) {
            throw new UnauthorizedRemoteException('Unauthorized', 401, 'AUTH_INVALID', request()->header('X-Correlation-Id', ''), [], 'notification-service');
        }

        return $this->request()->withToken($token);
    }

    private function extractPayload(Response $response, string $fallbackMessage): array
    {
        $this->throwIfUnauthorized($response);

        $json = $response->json() ?? [];
        $correlationId = $json['correlation_id'] ?? $response->header('X-Correlation-Id', '');

        if ($response->successful() && ($json['success'] ?? false) === true) {
            return [
                'data'           => $json['data'] ?? [],
                'message'        => $json['message'] ?? null,
                'correlation_id' => $correlationId,
            ];
        }

        throw new ExternalServiceException(
            $json['message'] ?? $fallbackMessage,
            $response->status(),
            $json['errors'] ?? [],
            $json['error_code'] ?? null,
            $correlationId,
        );
    }

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
                'notification-service',
            );
        }
    }

    private function timedRequest(callable $callback, string $endpoint, string $method): Response
    {
        $started = microtime(true);
        $response = $callback();
        $latencyMs = (microtime(true) - $started) * 1000;

        Log::info('http.outbound.notification_service', [
            'endpoint'       => $endpoint,
            'method'         => $method,
            'status_code'    => $response->status(),
            'latency_ms'     => round($latencyMs, 2),
            'correlation_id' => request()->header('X-Correlation-Id', ''),
        ]);

        return $response;
    }
}

<?php

namespace App\Services\Implementations;

use App\Services\Contracts\TemplateServiceClientInterface;
use App\Services\Exceptions\ExternalServiceException;
use App\Services\Exceptions\UnauthorizedRemoteException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TemplateServiceClient implements TemplateServiceClientInterface
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.template_service.base_url'), '/').'/';
    }

    public function listTemplates(string $token, array $filters = []): array
    {
        $response = $this->timedRequest(
            fn () => $this->authenticatedRequest($token)->get('templates', $filters),
            'templates',
            'GET'
        );

        return $this->extractList($response, 'Failed to list templates');
    }

    public function createTemplate(string $token, array $data): array
    {
        $response = $this->timedRequest(
            fn () => $this->authenticatedRequest($token)->post('templates', $data),
            'templates',
            'POST'
        );

        return $this->extractDataOrThrow($response, 'Failed to create template');
    }

    public function getTemplate(string $token, string $key): array
    {
        $response = $this->timedRequest(
            fn () => $this->authenticatedRequest($token)->get("templates/{$key}"),
            "templates/{$key}",
            'GET'
        );

        return $this->extractDataOrThrow($response, 'Failed to fetch template');
    }

    public function updateTemplate(string $token, string $key, array $data): array
    {
        $response = $this->timedRequest(
            fn () => $this->authenticatedRequest($token)->put("templates/{$key}", $data),
            "templates/{$key}",
            'PUT'
        );

        return $this->extractDataOrThrow($response, 'Failed to update template');
    }

    public function deleteTemplate(string $token, string $key): bool
    {
        $response = $this->timedRequest(
            fn () => $this->authenticatedRequest($token)->delete("templates/{$key}"),
            "templates/{$key}",
            'DELETE'
        );

        $this->throwIfUnauthorized($response);

        $json = $response->json() ?? [];

        if ($response->successful() && ($json['success'] ?? false)) {
            return true;
        }

        throw new ExternalServiceException(
            $json['message'] ?? 'Failed to delete template',
            $response->status(),
            $json['errors'] ?? [],
            $json['error_code'] ?? null,
            $json['correlation_id'] ?? $response->header('X-Correlation-Id', '')
        );
    }

    public function renderTemplate(string $token, string $key, array $variables): array
    {
        $response = $this->timedRequest(
            fn () => $this->authenticatedRequest($token)->post("templates/{$key}/render", ['variables' => $variables]),
            "templates/{$key}/render",
            'POST'
        );

        return $this->extractDataOrThrow($response, 'Failed to render template');
    }

    // ──────────────────────────────────────────────────────────────────

    private function request(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->acceptJson()
            ->asJson()
            ->timeout(10)
            ->withHeaders([
                'X-Correlation-Id' => request()->header('X-Correlation-Id', ''),
            ]);
    }

    private function authenticatedRequest(string $token): PendingRequest
    {
        return $this->request()->withToken($token);
    }

    private function extractDataOrThrow(Response $response, string $fallbackMessage): array
    {
        $this->throwIfUnauthorized($response);
        $json = $response->json() ?? [];

        if ($response->successful() && ($json['success'] ?? false)) {
            return $json['data'] ?? [];
        }

        throw new ExternalServiceException(
            $json['message'] ?? $fallbackMessage,
            $response->status(),
            $json['errors'] ?? [],
            $json['error_code'] ?? null,
            $json['correlation_id'] ?? $response->header('X-Correlation-Id', '')
        );
    }

    private function extractList(Response $response, string $fallbackMessage): array
    {
        $this->throwIfUnauthorized($response);
        $json = $response->json() ?? [];

        if ($response->successful() && ($json['success'] ?? false)) {
            return [
                'data'       => $json['data'] ?? [],
                'pagination' => $json['meta']['pagination'] ?? null,
            ];
        }

        throw new ExternalServiceException(
            $json['message'] ?? $fallbackMessage,
            $response->status(),
            $json['errors'] ?? [],
            $json['error_code'] ?? null,
            $json['correlation_id'] ?? $response->header('X-Correlation-Id', '')
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
            );
        }
    }

    private function timedRequest(callable $callback, string $endpoint, string $method): Response
    {
        $started = microtime(true);
        $response = $callback();
        $latencyMs = (microtime(true) - $started) * 1000;

        Log::info('http.outbound.template_service', [
            'endpoint'       => $endpoint,
            'method'         => $method,
            'status_code'    => $response->status(),
            'latency_ms'     => round($latencyMs, 2),
            'correlation_id' => request()->header('X-Correlation-Id', ''),
        ]);

        return $response;
    }
}

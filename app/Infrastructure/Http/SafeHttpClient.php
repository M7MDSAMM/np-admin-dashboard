<?php

namespace App\Infrastructure\Http;

use App\Domain\Exceptions\ExternalServiceException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SafeHttpClient
{
    public function request(string $baseUrl): PendingRequest
    {
        $correlationId = request()->header('X-Correlation-Id', '');

        return Http::baseUrl(rtrim($baseUrl, '/').'/')
            ->acceptJson()
            ->withHeaders(['X-Correlation-Id' => $correlationId])
            ->timeout(10)
            ->connectTimeout(5);
    }

    public function send(string $method, string $baseUrl, string $path, array $options = []): Response
    {
        try {
            $path = ltrim($path, '/');
            $pending = $this->request($baseUrl);

            if (! empty($options['token'])) {
                $pending = $pending->withToken($options['token']);
            }

            /** @var Response $response */
            $response = match (strtoupper($method)) {
                'GET'    => $pending->get($path, $options['query'] ?? []),
                'POST'   => $pending->post($path, $options['json'] ?? []),
                'PUT'    => $pending->put($path, $options['json'] ?? []),
                'PATCH'  => $pending->patch($path, $options['json'] ?? []),
                'DELETE' => $pending->delete($path),
            };

            if ($response->serverError()) {
                Log::error('external_service.server_error', [
                    'method' => $method,
                    'path'   => $path,
                    'status' => $response->status(),
                ]);

                throw new ExternalServiceException(
                    $response->json('message', 'External service error'),
                    $response->status(),
                );
            }

            return $response;
        } catch (ExternalServiceException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('external_service.connection_failed', [
                'method'  => $method,
                'path'    => $path,
                'message' => $e->getMessage(),
            ]);

            throw new ExternalServiceException(
                'Failed to connect to external service: '.$e->getMessage(),
                502,
                [],
                $e,
            );
        }
    }
}

<?php

namespace App\Infrastructure\Clients;

use App\Domain\Admin\AdminClientInterface;
use App\Domain\Exceptions\ExternalServiceException;
use App\Infrastructure\Http\SafeHttpClient;

class UserServiceAdminClient implements AdminClientInterface
{
    public function __construct(
        private readonly SafeHttpClient $http,
        private readonly string $baseUrl,
    ) {}

    public function list(string $token, int $page = 1, int $perPage = 15): array
    {
        $response = $this->http->send('GET', $this->baseUrl, 'admins', [
            'token' => $token,
            'query' => ['page' => $page, 'per_page' => $perPage],
        ]);

        $json = $response->json();

        if ($response->successful() && ($json['success'] ?? false)) {
            return [
                'data'       => $json['data'] ?? [],
                'pagination' => $json['meta']['pagination'] ?? null,
            ];
        }

        throw new ExternalServiceException('Failed to fetch admin list', $response->status());
    }

    public function find(string $token, string $uuid): ?array
    {
        $response = $this->http->send('GET', $this->baseUrl, "admins/{$uuid}", [
            'token' => $token,
        ]);

        if ($response->status() === 404) {
            return null;
        }

        $json = $response->json();

        if ($response->successful() && ($json['success'] ?? false)) {
            return $json['data'];
        }

        throw new ExternalServiceException('Failed to fetch admin', $response->status());
    }

    public function create(string $token, array $data): array
    {
        $response = $this->http->send('POST', $this->baseUrl, 'admins', [
            'token' => $token,
            'json'  => $data,
        ]);

        $json = $response->json();

        if ($response->successful() && ($json['success'] ?? false)) {
            return $json['data'];
        }

        throw new ExternalServiceException(
            $json['message'] ?? 'Failed to create admin',
            $response->status(),
            $json['errors'] ?? [],
        );
    }

    public function update(string $token, string $uuid, array $data): array
    {
        $response = $this->http->send('PUT', $this->baseUrl, "admins/{$uuid}", [
            'token' => $token,
            'json'  => $data,
        ]);

        $json = $response->json();

        if ($response->successful() && ($json['success'] ?? false)) {
            return $json['data'];
        }

        throw new ExternalServiceException(
            $json['message'] ?? 'Failed to update admin',
            $response->status(),
            $json['errors'] ?? [],
        );
    }

    public function delete(string $token, string $uuid): bool
    {
        $response = $this->http->send('DELETE', $this->baseUrl, "admins/{$uuid}", [
            'token' => $token,
        ]);

        return $response->successful();
    }

    public function toggleActive(string $token, string $uuid): array
    {
        $response = $this->http->send('PATCH', $this->baseUrl, "admins/{$uuid}/toggle-active", [
            'token' => $token,
            'json'  => [],
        ]);

        $json = $response->json();

        if ($response->successful() && ($json['success'] ?? false)) {
            return $json['data'];
        }

        throw new ExternalServiceException('Failed to toggle admin status', $response->status());
    }
}

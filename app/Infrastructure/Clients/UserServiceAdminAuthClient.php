<?php

namespace App\Infrastructure\Clients;

use App\Domain\Auth\AdminAuthClientInterface;
use App\Domain\Exceptions\ExternalServiceException;
use App\Infrastructure\Http\SafeHttpClient;

class UserServiceAdminAuthClient implements AdminAuthClientInterface
{
    public function __construct(
        private readonly SafeHttpClient $http,
        private readonly string $baseUrl,
    ) {}

    public function login(string $email, string $password): array
    {
        $response = $this->http->send('POST', $this->baseUrl, 'admin/auth/login', [
            'json' => compact('email', 'password'),
        ]);

        $json = $response->json();

        if ($response->successful() && ($json['success'] ?? false)) {
            return $json['data'];
        }

        throw new ExternalServiceException(
            $json['message'] ?? 'Login failed',
            $response->status(),
            $json['errors'] ?? [],
        );
    }

    public function me(string $token): array
    {
        $response = $this->http->send('GET', $this->baseUrl, 'admin/me', [
            'token' => $token,
        ]);

        $json = $response->json();

        if ($response->successful() && ($json['success'] ?? false)) {
            return $json['data'];
        }

        throw new ExternalServiceException(
            $json['message'] ?? 'Failed to fetch profile',
            $response->status(),
        );
    }
}

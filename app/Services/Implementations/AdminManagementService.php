<?php

namespace App\Services\Implementations;

use App\Services\Contracts\AdminManagementServiceInterface;
use App\Services\Contracts\UserServiceClientInterface;
use Illuminate\Support\Facades\Log;

/**
 * Business logic layer for admin CRUD operations.
 *
 * This service delegates HTTP calls to the UserServiceClient and adds
 * cross-cutting concerns (audit logging). Controllers call this service
 * instead of the client directly, keeping controllers thin.
 *
 * Pattern:  Controller → this Service → UserServiceClient → User Service API
 */
class AdminManagementService implements AdminManagementServiceInterface
{
    public function __construct(
        private readonly UserServiceClientInterface $client,
    ) {}

    public function listAdmins(string $token, int $page = 1, int $perPage = 15): array
    {
        return $this->client->listAdmins($token, $page, $perPage);
    }

    public function findAdmin(string $token, string $uuid): ?array
    {
        return $this->client->findAdmin($token, $uuid);
    }

    public function createAdmin(string $token, array $data): array
    {
        $admin = $this->client->createAdmin($token, $data);

        Log::info('admin.created', [
            'created_uuid' => $admin['uuid'] ?? null,
            'email'        => $data['email'] ?? null,
        ]);

        return $admin;
    }

    public function updateAdmin(string $token, string $uuid, array $data): array
    {
        $admin = $this->client->updateAdmin($token, $uuid, $data);

        Log::info('admin.updated', ['updated_uuid' => $uuid]);

        return $admin;
    }

    public function deleteAdmin(string $token, string $uuid): bool
    {
        $result = $this->client->deleteAdmin($token, $uuid);

        Log::info('admin.deleted', ['deleted_uuid' => $uuid]);

        return $result;
    }

    public function toggleActive(string $token, string $uuid): array
    {
        $admin = $this->client->toggleActive($token, $uuid);

        Log::info('admin.toggled_active', [
            'toggled_uuid' => $uuid,
            'is_active'    => $admin['is_active'] ?? null,
        ]);

        return $admin;
    }
}

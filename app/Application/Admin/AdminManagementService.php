<?php

namespace App\Application\Admin;

use App\Domain\Admin\AdminClientInterface;
use Illuminate\Support\Facades\Log;

class AdminManagementService
{
    public function __construct(
        private readonly AdminClientInterface $adminClient,
    ) {}

    public function listAdmins(string $token, int $page = 1, int $perPage = 15): array
    {
        return $this->adminClient->list($token, $page, $perPage);
    }

    public function findAdmin(string $token, string $uuid): ?array
    {
        return $this->adminClient->find($token, $uuid);
    }

    public function createAdmin(string $token, array $data): array
    {
        $admin = $this->adminClient->create($token, $data);

        Log::info('admin.created', [
            'created_uuid' => $admin['uuid'] ?? null,
            'email'        => $data['email'] ?? null,
        ]);

        return $admin;
    }

    public function updateAdmin(string $token, string $uuid, array $data): array
    {
        $admin = $this->adminClient->update($token, $uuid, $data);

        Log::info('admin.updated', ['updated_uuid' => $uuid]);

        return $admin;
    }

    public function deleteAdmin(string $token, string $uuid): bool
    {
        $result = $this->adminClient->delete($token, $uuid);

        Log::info('admin.deleted', ['deleted_uuid' => $uuid]);

        return $result;
    }

    public function toggleActive(string $token, string $uuid): array
    {
        $admin = $this->adminClient->toggleActive($token, $uuid);

        Log::info('admin.toggled_active', [
            'toggled_uuid' => $uuid,
            'is_active'    => $admin['is_active'] ?? null,
        ]);

        return $admin;
    }
}

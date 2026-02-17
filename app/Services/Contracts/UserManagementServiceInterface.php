<?php

namespace App\Services\Contracts;

interface UserManagementServiceInterface
{
    /** @return array{data: array, pagination: array|null} */
    public function paginateUsers(string $token, array $filters = []): array;

    public function getUser(string $token, string $uuid): ?array;

    public function createUser(string $token, array $data): array;

    public function updateUser(string $token, string $uuid, array $data): array;

    public function deleteUser(string $token, string $uuid): bool;

    public function getPreferences(string $token, string $uuid): array;

    public function updatePreferences(string $token, string $uuid, array $data): array;

    public function listDevices(string $token, string $uuid): array;

    public function addDevice(string $token, string $uuid, array $data): array;

    public function deleteDevice(string $token, string $userUuid, string $deviceUuid): bool;
}

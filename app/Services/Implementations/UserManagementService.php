<?php

namespace App\Services\Implementations;

use App\Services\Contracts\UserManagementServiceInterface;
use App\Services\Contracts\UserServiceClientInterface;
use Illuminate\Support\Facades\Log;

class UserManagementService implements UserManagementServiceInterface
{
    public function __construct(
        private readonly UserServiceClientInterface $client,
    ) {}

    public function paginateUsers(string $token, array $filters = []): array
    {
        return $this->client->listUsers($token, $filters);
    }

    public function getUser(string $token, string $uuid): ?array
    {
        return $this->client->findUser($token, $uuid);
    }

    public function createUser(string $token, array $data): array
    {
        $user = $this->client->createUser($token, $data);

        Log::info('dashboard.user.create', [
            'user_uuid' => $user['uuid'] ?? null,
            'email'     => $data['email'] ?? null,
        ]);

        return $user;
    }

    public function updateUser(string $token, string $uuid, array $data): array
    {
        $user = $this->client->updateUser($token, $uuid, $data);

        Log::info('dashboard.user.update', ['user_uuid' => $uuid]);

        return $user;
    }

    public function deleteUser(string $token, string $uuid): bool
    {
        $result = $this->client->deleteUser($token, $uuid);

        Log::info('dashboard.user.delete', ['user_uuid' => $uuid]);

        return $result;
    }

    public function getPreferences(string $token, string $uuid): array
    {
        return $this->client->getUserPreferences($token, $uuid);
    }

    public function updatePreferences(string $token, string $uuid, array $data): array
    {
        $prefs = $this->client->updateUserPreferences($token, $uuid, $data);

        Log::info('dashboard.user.preferences.update', ['user_uuid' => $uuid]);

        return $prefs;
    }

    public function listDevices(string $token, string $uuid): array
    {
        return $this->client->listUserDevices($token, $uuid);
    }

    public function addDevice(string $token, string $uuid, array $data): array
    {
        $device = $this->client->addUserDevice($token, $uuid, $data);

        Log::info('dashboard.user.device.add', [
            'user_uuid'   => $uuid,
            'device_uuid' => $device['uuid'] ?? null,
        ]);

        return $device;
    }

    public function deleteDevice(string $token, string $userUuid, string $deviceUuid): bool
    {
        $result = $this->client->deleteUserDevice($token, $userUuid, $deviceUuid);

        Log::info('dashboard.user.device.delete', [
            'user_uuid'   => $userUuid,
            'device_uuid' => $deviceUuid,
        ]);

        return $result;
    }
}

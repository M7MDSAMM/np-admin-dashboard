<?php

namespace App\Services\Contracts;

/**
 * Admin CRUD operations contract.
 *
 * Sits between controllers and the UserServiceClient. This layer
 * exists to add cross-cutting concerns like audit logging and to
 * keep controllers thin — controllers only handle HTTP I/O, this
 * service handles the business logic.
 */
interface AdminManagementServiceInterface
{
    /**
     * @return array{data: array, pagination: array|null}
     *
     * @throws \App\Services\Exceptions\ExternalServiceException
     */
    public function listAdmins(string $token, int $page = 1, int $perPage = 15): array;

    /** @throws \App\Services\Exceptions\ExternalServiceException */
    public function findAdmin(string $token, string $uuid): ?array;

    /** @throws \App\Services\Exceptions\ExternalServiceException */
    public function createAdmin(string $token, array $data): array;

    /** @throws \App\Services\Exceptions\ExternalServiceException */
    public function updateAdmin(string $token, string $uuid, array $data): array;

    /** @throws \App\Services\Exceptions\ExternalServiceException */
    public function deleteAdmin(string $token, string $uuid): bool;

    /** @throws \App\Services\Exceptions\ExternalServiceException */
    public function toggleActive(string $token, string $uuid): array;
}

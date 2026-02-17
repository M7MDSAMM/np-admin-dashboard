<?php

namespace App\Domain\Admin;

interface AdminClientInterface
{
    public function list(string $token, int $page = 1, int $perPage = 15): array;

    public function find(string $token, string $uuid): ?array;

    public function create(string $token, array $data): array;

    public function update(string $token, string $uuid, array $data): array;

    public function delete(string $token, string $uuid): bool;

    public function toggleActive(string $token, string $uuid): array;
}

<?php

namespace App\Services\Contracts;

interface NotificationManagementServiceInterface
{
    public function listNotifications(array $filters = [], int $page = 1, int $perPage = 15): array;

    public function createNotification(array $payload): array;

    public function getNotification(string $uuid): array;

    public function retryNotification(string $uuid): array;

    public function getDelivery(string $uuid): array;

    public function retryDelivery(string $uuid): array;
}

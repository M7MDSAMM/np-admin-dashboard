<?php

namespace App\Services\Contracts;

interface NotificationManagementServiceInterface
{
    public function createNotification(array $payload): array;

    public function getNotification(string $uuid): array;

    public function retryNotification(string $uuid): array;
}

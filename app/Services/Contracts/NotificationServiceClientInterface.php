<?php

namespace App\Services\Contracts;

interface NotificationServiceClientInterface
{
    /**
     * POST /notifications
     */
    public function createNotification(array $payload): array;

    /**
     * GET /notifications/{uuid}
     */
    public function getNotification(string $uuid): array;

    /**
     * POST /notifications/{uuid}/retry
     */
    public function retryNotification(string $uuid): array;

    /**
     * GET /health
     */
    public function health(): array;
}

<?php

namespace App\Services\Contracts;

interface MessagingServiceClientInterface
{
    /**
     * GET /deliveries/{uuid}
     */
    public function getDelivery(string $uuid): array;

    /**
     * POST /deliveries/{uuid}/retry
     */
    public function retryDelivery(string $uuid): array;

    /**
     * GET /health
     */
    public function health(): array;
}

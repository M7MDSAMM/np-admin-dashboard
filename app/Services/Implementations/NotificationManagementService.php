<?php

namespace App\Services\Implementations;

use App\Services\Contracts\AdminAuthServiceInterface;
use App\Services\Contracts\MessagingServiceClientInterface;
use App\Services\Contracts\NotificationManagementServiceInterface;
use App\Services\Contracts\NotificationServiceClientInterface;
use Illuminate\Support\Facades\Log;

class NotificationManagementService implements NotificationManagementServiceInterface
{
    public function __construct(
        private readonly NotificationServiceClientInterface $client,
        private readonly MessagingServiceClientInterface $messagingClient,
        private readonly AdminAuthServiceInterface $auth,
    ) {}

    public function listNotifications(array $filters = [], int $page = 1, int $perPage = 15): array
    {
        $result = $this->client->listNotifications($filters, $page, $perPage);

        Log::info('dashboard.notification.list', [
            'admin_uuid'     => $this->auth->getAdmin()['uuid'] ?? null,
            'filters'        => $filters,
            'page'           => $page,
            'correlation_id' => $result['correlation_id'] ?? request()->header('X-Correlation-Id', ''),
        ]);

        return $result;
    }

    public function createNotification(array $payload): array
    {
        $result = $this->client->createNotification($payload);
        $notification = $result['data'] ?? [];

        Log::info('dashboard.notification.create', [
            'admin_uuid'     => $this->auth->getAdmin()['uuid'] ?? null,
            'user_uuid'      => $notification['user_uuid'] ?? $payload['user_uuid'] ?? null,
            'template_key'   => $notification['template_key'] ?? $payload['template_key'] ?? null,
            'correlation_id' => $result['correlation_id'] ?? request()->header('X-Correlation-Id', ''),
        ]);

        return $result;
    }

    public function getNotification(string $uuid): array
    {
        $result = $this->client->getNotification($uuid);
        $notification = $result['data'] ?? [];

        Log::info('dashboard.notification.view', [
            'admin_uuid'     => $this->auth->getAdmin()['uuid'] ?? null,
            'notification_uuid' => $notification['uuid'] ?? $uuid,
            'correlation_id' => $result['correlation_id'] ?? request()->header('X-Correlation-Id', ''),
        ]);

        return $result;
    }

    public function retryNotification(string $uuid): array
    {
        $result = $this->client->retryNotification($uuid);

        Log::info('dashboard.notification.retry', [
            'admin_uuid'        => $this->auth->getAdmin()['uuid'] ?? null,
            'notification_uuid' => $uuid,
            'correlation_id'    => $result['correlation_id'] ?? request()->header('X-Correlation-Id', ''),
        ]);

        return $result;
    }

    public function getDelivery(string $uuid): array
    {
        $result = $this->messagingClient->getDelivery($uuid);

        Log::info('dashboard.delivery.view', [
            'admin_uuid'     => $this->auth->getAdmin()['uuid'] ?? null,
            'delivery_uuid'  => $uuid,
            'correlation_id' => $result['correlation_id'] ?? request()->header('X-Correlation-Id', ''),
        ]);

        return $result;
    }

    public function retryDelivery(string $uuid): array
    {
        $result = $this->messagingClient->retryDelivery($uuid);

        Log::info('dashboard.delivery.retry', [
            'admin_uuid'     => $this->auth->getAdmin()['uuid'] ?? null,
            'delivery_uuid'  => $uuid,
            'correlation_id' => $result['correlation_id'] ?? request()->header('X-Correlation-Id', ''),
        ]);

        return $result;
    }
}

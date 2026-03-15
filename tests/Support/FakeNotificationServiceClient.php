<?php

namespace Tests\Support;

use App\Services\Contracts\NotificationServiceClientInterface;

class FakeNotificationServiceClient implements NotificationServiceClientInterface
{
    public array $nextListResponse = [
        'data' => [
            'data'         => [],
            'current_page' => 1,
            'last_page'    => 1,
            'from'         => 0,
            'to'           => 0,
            'total'        => 0,
        ],
        'message'        => 'Notifications retrieved.',
        'correlation_id' => 'test-corr',
    ];

    public array $nextCreateResponse = [
        'data' => [],
        'message' => 'Notification created.',
        'correlation_id' => 'test-corr',
    ];

    public array $nextGetResponse = [
        'data' => [],
        'message' => 'Fetched',
        'correlation_id' => 'test-corr',
    ];

    public array $nextRetryResponse = [
        'data' => ['status' => 'retry_accepted'],
        'message' => 'Retry accepted.',
        'correlation_id' => 'test-corr',
    ];

    public bool $listCalled = false;
    public bool $retryCalled = false;
    public bool $createCalled = false;
    public bool $getCalled = false;

    public function listNotifications(array $filters = [], int $page = 1, int $perPage = 15): array
    {
        $this->listCalled = true;
        return $this->nextListResponse;
    }

    public function createNotification(array $payload): array
    {
        $this->createCalled = true;
        return $this->nextCreateResponse;
    }

    public function getNotification(string $uuid): array
    {
        $this->getCalled = true;
        return $this->nextGetResponse;
    }

    public function retryNotification(string $uuid): array
    {
        $this->retryCalled = true;
        return $this->nextRetryResponse;
    }

    public function health(): array
    {
        return [
            'data' => ['status' => 'ok'],
            'message' => 'ok',
            'correlation_id' => 'test-corr',
        ];
    }
}

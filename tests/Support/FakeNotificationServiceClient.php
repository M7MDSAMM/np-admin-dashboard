<?php

namespace Tests\Support;

use App\Services\Contracts\NotificationServiceClientInterface;

class FakeNotificationServiceClient implements NotificationServiceClientInterface
{
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

    public bool $retryCalled = false;
    public bool $createCalled = false;
    public bool $getCalled = false;

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

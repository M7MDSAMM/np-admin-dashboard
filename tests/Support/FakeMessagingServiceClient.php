<?php

namespace Tests\Support;

use App\Services\Contracts\MessagingServiceClientInterface;

class FakeMessagingServiceClient implements MessagingServiceClientInterface
{
    public array $nextGetDeliveryResponse = [
        'data'           => [],
        'message'        => 'Delivery retrieved.',
        'correlation_id' => 'test-corr',
    ];

    public array $nextRetryDeliveryResponse = [
        'data'           => ['status' => 'retry_accepted'],
        'message'        => 'Delivery retry accepted.',
        'correlation_id' => 'test-corr',
    ];

    public bool $getDeliveryCalled = false;
    public bool $retryDeliveryCalled = false;

    public function getDelivery(string $uuid): array
    {
        $this->getDeliveryCalled = true;
        return $this->nextGetDeliveryResponse;
    }

    public function retryDelivery(string $uuid): array
    {
        $this->retryDeliveryCalled = true;
        return $this->nextRetryDeliveryResponse;
    }

    public function health(): array
    {
        return [
            'data'           => ['status' => 'ok'],
            'message'        => 'ok',
            'correlation_id' => 'test-corr',
        ];
    }
}

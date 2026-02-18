<?php

namespace Tests\Unit;

use App\Services\Exceptions\ExternalServiceException;
use App\Services\Implementations\UserServiceClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UserServiceClientTest extends TestCase
{
    public function test_success_false_throws_external_service_exception(): void
    {
        Http::fake([
            '*' => Http::response([
                'success'        => false,
                'message'        => 'fail',
                'error_code'     => 'E_FAIL',
                'correlation_id' => 'cid-123',
                'meta'           => [],
            ], 200),
        ]);

        $client = $this->app->make(UserServiceClient::class);

        try {
            $client->login('admin@local.test', 'password');
            $this->fail('Expected ExternalServiceException');
        } catch (ExternalServiceException $e) {
            $this->assertSame('fail', $e->getMessage());
            $this->assertSame(200, $e->statusCode);
            $this->assertSame('E_FAIL', $e->errorCode);
            $this->assertSame('cid-123', $e->correlationId);
        }
    }
}

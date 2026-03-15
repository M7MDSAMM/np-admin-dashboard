<?php

namespace Tests\Feature;

use App\Services\Exceptions\ExternalServiceException;
use App\Services\Exceptions\UnauthorizedRemoteException;
use App\Services\Implementations\UserServiceClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StrictClientParsingTest extends TestCase
{
    public function test_client_throws_when_success_is_false_despite_http_200(): void
    {
        Http::fake(fn () => Http::response([
            'success'        => false,
            'message'        => 'Something went wrong',
            'errors'         => [],
            'error_code'     => 'INTERNAL_ERROR',
            'correlation_id' => 'cid-test',
            'meta'           => [],
        ], 200));

        $client = $this->app->make(UserServiceClient::class);

        $this->expectException(ExternalServiceException::class);
        $this->expectExceptionMessage('Something went wrong');

        $client->listAdmins('fake-token');
    }

    public function test_client_extracts_data_when_success_is_true(): void
    {
        Http::fake(fn () => Http::response([
            'success'        => true,
            'message'        => '',
            'data'           => ['uuid' => 'admin-1', 'name' => 'Admin'],
            'meta'           => [],
            'correlation_id' => 'cid-test',
        ], 200));

        $client = $this->app->make(UserServiceClient::class);
        $result = $client->me('fake-token');

        $this->assertSame('admin-1', $result['uuid']);
    }

    public function test_client_includes_error_code_in_exception(): void
    {
        Http::fake(fn () => Http::response([
            'success'        => false,
            'message'        => 'Forbidden',
            'errors'         => [],
            'error_code'     => 'FORBIDDEN',
            'correlation_id' => 'cid-403',
            'meta'           => [],
        ], 403));

        $client = $this->app->make(UserServiceClient::class);

        try {
            $client->listAdmins('fake-token');
            $this->fail('Expected exception not thrown');
        } catch (UnauthorizedRemoteException $e) {
            $this->assertSame('FORBIDDEN', $e->errorCode);
            $this->assertSame('cid-403', $e->correlationId);
            $this->assertSame(403, $e->statusCode);
        }
    }

    public function test_delete_enforces_envelope_instead_of_http_status(): void
    {
        Http::fake(fn () => Http::response([
            'success'        => false,
            'message'        => 'Delete forbidden',
            'errors'         => [],
            'error_code'     => 'FORBIDDEN',
            'correlation_id' => 'cid-del',
            'meta'           => [],
        ], 200));

        $client = $this->app->make(UserServiceClient::class);

        $this->expectException(ExternalServiceException::class);
        $this->expectExceptionMessage('Delete forbidden');

        $client->deleteAdmin('fake-token', 'some-uuid');
    }
}

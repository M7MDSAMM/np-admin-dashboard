<?php

namespace Tests\Feature;

use App\Services\Contracts\UserManagementServiceInterface;
use App\Services\Exceptions\UnauthorizedRemoteException;
use Carbon\CarbonImmutable;
use Mockery;
use Tests\TestCase;

class JwtLifecycleTest extends TestCase
{
    public function test_unauthorized_remote_response_logs_out_and_redirects(): void
    {
        $this->withSession([
            'admin_jwt_token'       => 'token',
            'admin_profile'         => ['uuid' => 'u', 'role' => 'admin'],
            'admin_jwt_expires_at'  => CarbonImmutable::now()->addMinutes(5)->toIso8601String(),
        ]);

        $client = Mockery::mock(UserManagementServiceInterface::class);
        $client->shouldReceive('paginateUsers')
            ->once()
            ->andThrow(new UnauthorizedRemoteException('Unauthorized', 401, 'AUTH_INVALID', 'cid-123'));
        $this->app->instance(UserManagementServiceInterface::class, $client);

        $response = $this->get('/users');

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error', 'Session expired, please login again');
        $this->assertFalse(session()->has('admin_jwt_token'));
        $this->assertFalse(session()->has('admin_profile'));
        $this->assertFalse(session()->has('admin_jwt_expires_at'));
    }

    public function test_expired_token_before_request_logs_out_and_redirects(): void
    {
        $this->withSession([
            'admin_jwt_token'       => 'token',
            'admin_profile'         => ['uuid' => 'u', 'role' => 'admin'],
            'admin_jwt_expires_at'  => CarbonImmutable::now()->subMinute()->toIso8601String(),
        ]);

        $response = $this->get('/users');

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error', 'Session expired, please login again');
        $this->assertFalse(session()->has('admin_jwt_token'));
        $this->assertFalse(session()->has('admin_profile'));
        $this->assertFalse(session()->has('admin_jwt_expires_at'));
    }
}

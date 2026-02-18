<?php

namespace Tests\Feature;

use App\Services\Contracts\AdminAuthServiceInterface;
use App\Services\Contracts\UserManagementServiceInterface;
use App\Services\Exceptions\ExternalServiceException;
use Mockery;
use Tests\TestCase;

class UserServiceFailureFlashTest extends TestCase
{
    private function mockAuth(): void
    {
        $auth = Mockery::mock(AdminAuthServiceInterface::class);
        $auth->shouldReceive('isAuthenticated')->andReturn(true);
        $auth->shouldReceive('isSuperAdmin')->andReturn(true);
        $auth->shouldReceive('getAdmin')->andReturn(['uuid' => 'admin-1', 'role' => 'super_admin']);
        $auth->shouldReceive('getToken')->andReturn('token');
        $auth->shouldReceive('isTokenExpired')->andReturn(false);

        $this->app->instance(AdminAuthServiceInterface::class, $auth);
    }

    public function test_users_index_flashes_error_when_user_service_fails(): void
    {
        $this->mockAuth();

        $svc = Mockery::mock(UserManagementServiceInterface::class);
        $svc->shouldReceive('paginateUsers')
            ->once()
            ->andThrow(new ExternalServiceException('Remote failure', 500, [], 'REMOTE_FAIL', 'cid-123'));
        $this->app->instance(UserManagementServiceInterface::class, $svc);

        $response = $this->get('/users');

        $response->assertOk();
        $response->assertSessionHas('error', 'Failed to load users from User Service.');
    }
}

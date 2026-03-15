<?php

namespace Tests\Feature;

use App\Services\Contracts\UserManagementServiceInterface;
use App\Services\Contracts\UserServiceClientInterface;
use App\Services\Exceptions\UnauthorizedRemoteException;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class JwtLifecycleTest extends TestCase
{
    // ── 1. Session expired before request ─────────────────────────────

    public function test_expired_token_before_request_logs_out_and_redirects(): void
    {
        $this->withSession([
            'admin_jwt_token'      => 'token',
            'admin_profile'        => ['uuid' => 'u', 'role' => 'admin'],
            'admin_jwt_expires_at' => CarbonImmutable::now()->subMinute()->toIso8601String(),
        ]);

        $response = $this->get('/users');

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error', 'Your session has expired. Please sign in again.');
        $this->assertFalse(session()->has('admin_jwt_token'));
        $this->assertFalse(session()->has('admin_profile'));
        $this->assertFalse(session()->has('admin_jwt_expires_at'));
    }

    // ── 2. Valid session allows access ────────────────────────────────

    public function test_valid_session_allows_access_to_protected_page(): void
    {
        $this->withSession([
            'admin_jwt_token'      => 'valid-token',
            'admin_profile'        => ['uuid' => 'u', 'name' => 'Admin', 'email' => 'a@t.com', 'role' => 'admin'],
            'admin_jwt_expires_at' => CarbonImmutable::now()->addHour()->toIso8601String(),
        ]);

        // Mock the management service so the controller doesn't make real HTTP calls.
        $mock = Mockery::mock(UserManagementServiceInterface::class);
        $mock->shouldReceive('paginateUsers')->once()->andReturn([
            'data' => [], 'pagination' => null,
        ]);
        $this->app->instance(UserManagementServiceInterface::class, $mock);

        $response = $this->get('/users');

        $response->assertOk();
        // Session should still be intact
        $this->assertTrue(session()->has('admin_jwt_token'));
        $this->assertTrue(session()->has('admin_profile'));
        $this->assertTrue(session()->has('admin_jwt_expires_at'));
    }

    // ── 3. Unauthorized remote response (401) ─────────────────────────

    public function test_unauthorized_remote_response_logs_out_and_redirects(): void
    {
        $this->withSession([
            'admin_jwt_token'      => 'token',
            'admin_profile'        => ['uuid' => 'u', 'role' => 'admin'],
            'admin_jwt_expires_at' => CarbonImmutable::now()->addMinutes(5)->toIso8601String(),
        ]);

        $client = Mockery::mock(UserManagementServiceInterface::class);
        $client->shouldReceive('paginateUsers')
            ->once()
            ->andThrow(new UnauthorizedRemoteException(
                'Token expired',
                401,
                'TOKEN_EXPIRED',
                'cid-123',
                [],
                'user-service',
            ));
        $this->app->instance(UserManagementServiceInterface::class, $client);

        $response = $this->get('/users');

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error', 'Your session is no longer valid. Please sign in again.');
        $this->assertFalse(session()->has('admin_jwt_token'));
        $this->assertFalse(session()->has('admin_profile'));
        $this->assertFalse(session()->has('admin_jwt_expires_at'));
    }

    // ── 4. Forbidden remote response (403) keeps session ──────────────

    public function test_forbidden_remote_response_does_not_logout(): void
    {
        $this->withSession([
            'admin_jwt_token'      => 'token',
            'admin_profile'        => ['uuid' => 'u', 'name' => 'Admin', 'role' => 'admin'],
            'admin_jwt_expires_at' => CarbonImmutable::now()->addHour()->toIso8601String(),
        ]);

        $client = Mockery::mock(UserManagementServiceInterface::class);
        $client->shouldReceive('paginateUsers')
            ->once()
            ->andThrow(new UnauthorizedRemoteException(
                'Forbidden',
                403,
                'FORBIDDEN',
                'cid-456',
                [],
                'user-service',
            ));
        $this->app->instance(UserManagementServiceInterface::class, $client);

        $response = $this->get('/users');

        $response->assertForbidden();
        // Session should NOT be cleared for 403
        $this->assertTrue(session()->has('admin_jwt_token'));
    }

    // ── 5. Login stores expires_at ────────────────────────────────────

    public function test_login_stores_expires_at_in_session(): void
    {
        // Mock UserServiceClient to return a fake login and profile response.
        $userClient = Mockery::mock(UserServiceClientInterface::class);
        $userClient->shouldReceive('login')
            ->once()
            ->andReturn([
                'access_token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJyb2xlIjoiYWRtaW4ifQ.sig',
                'token_type'   => 'Bearer',
                'expires_in'   => 900,
            ]);
        $userClient->shouldReceive('me')
            ->once()
            ->andReturn([
                'uuid'      => 'admin-uuid',
                'name'      => 'Test Admin',
                'email'     => 'admin@test.com',
                'role'      => 'admin',
                'is_active' => true,
            ]);
        $this->app->instance(UserServiceClientInterface::class, $userClient);

        $response = $this->post('/login', [
            'email'    => 'admin@test.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));

        // Verify all three session keys are stored
        $this->assertTrue(session()->has('admin_jwt_token'));
        $this->assertTrue(session()->has('admin_profile'));
        $this->assertTrue(session()->has('admin_jwt_expires_at'));

        // Verify expires_at is in the future (roughly 900 seconds from now)
        $expiresAt = CarbonImmutable::parse(session('admin_jwt_expires_at'));
        $this->assertTrue($expiresAt->greaterThan(CarbonImmutable::now()->addSeconds(800)));
        $this->assertTrue($expiresAt->lessThan(CarbonImmutable::now()->addSeconds(1000)));
    }

    // ── 6. Missing session redirects to login ─────────────────────────

    public function test_missing_session_redirects_to_login(): void
    {
        $response = $this->get('/users');

        $response->assertRedirect(route('login'));
    }

    // ── 7. Unauthorized JSON response returns proper envelope ─────────

    public function test_unauthorized_remote_json_response_returns_401_envelope(): void
    {
        $this->withSession([
            'admin_jwt_token'      => 'token',
            'admin_profile'        => ['uuid' => 'u', 'role' => 'admin'],
            'admin_jwt_expires_at' => CarbonImmutable::now()->addMinutes(5)->toIso8601String(),
        ]);

        // Use /users which doesn't swallow UnauthorizedRemoteException in a try/catch
        $client = Mockery::mock(UserManagementServiceInterface::class);
        $client->shouldReceive('paginateUsers')
            ->once()
            ->andThrow(new UnauthorizedRemoteException(
                'Token expired',
                401,
                'TOKEN_EXPIRED',
                'cid-789',
                [],
                'user-service',
            ));
        $this->app->instance(UserManagementServiceInterface::class, $client);

        $response = $this->getJson('/users');

        $response->assertUnauthorized()
            ->assertJson([
                'success'    => false,
                'error_code' => 'TOKEN_EXPIRED',
            ]);
    }
}

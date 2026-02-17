<?php

namespace Tests\Feature;

use App\Services\Contracts\AdminAuthServiceInterface;
use App\Services\Contracts\AdminManagementServiceInterface;
use Mockery;
use Tests\TestCase;

class RouteProtectionTest extends TestCase
{
    /**
     * Helper: bind a mock AdminAuthServiceInterface into the container.
     *
     * This lets us control authentication state without hitting the
     * User Service API. The mock is bound to the interface so that
     * middleware and controllers resolve it automatically.
     */
    private function mockAuth(bool $authenticated = false, bool $superAdmin = false, array $admin = []): void
    {
        $mock = Mockery::mock(AdminAuthServiceInterface::class);
        $mock->shouldReceive('isAuthenticated')->andReturn($authenticated);
        $mock->shouldReceive('isSuperAdmin')->andReturn($superAdmin);
        $mock->shouldReceive('getAdmin')->andReturn($admin ?: null);
        $mock->shouldReceive('getToken')->andReturn($authenticated ? 'test-token' : null);

        $this->app->instance(AdminAuthServiceInterface::class, $mock);
    }

    // ── Guest ───────────────────────────────────────────────────────────

    public function test_login_page_is_accessible(): void
    {
        $this->mockAuth();

        $this->get('/login')->assertOk();
    }

    public function test_dashboard_redirects_to_login_when_unauthenticated(): void
    {
        $this->mockAuth();

        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_admins_redirects_to_login_when_unauthenticated(): void
    {
        $this->mockAuth();

        $this->get('/admins')->assertRedirect(route('login'));
    }

    // ── Authenticated (regular admin) ───────────────────────────────────

    public function test_dashboard_accessible_when_authenticated(): void
    {
        $this->mockAuth(true, false, [
            'uuid' => 'u', 'name' => 'Admin', 'email' => 'a@t.com', 'role' => 'admin',
        ]);

        $this->get('/')->assertOk();
    }

    public function test_admins_forbidden_for_regular_admin(): void
    {
        $this->mockAuth(true, false, [
            'uuid' => 'u', 'name' => 'Admin', 'email' => 'a@t.com', 'role' => 'admin',
        ]);

        $this->get('/admins')->assertForbidden();
    }

    public function test_create_admin_forbidden_for_regular_admin(): void
    {
        $this->mockAuth(true, false, [
            'uuid' => 'u', 'name' => 'Admin', 'email' => 'a@t.com', 'role' => 'admin',
        ]);

        $this->get('/admins/create')->assertForbidden();
    }

    // ── Authenticated (super_admin) ─────────────────────────────────────

    public function test_admins_accessible_for_super_admin(): void
    {
        // Mock the management service so the controller doesn't make real HTTP calls.
        $adminService = Mockery::mock(AdminManagementServiceInterface::class);
        $adminService->shouldReceive('listAdmins')->andReturn(['data' => [], 'pagination' => null]);
        $this->app->instance(AdminManagementServiceInterface::class, $adminService);

        $this->mockAuth(true, true, [
            'uuid' => 'u', 'name' => 'Super', 'email' => 's@t.com', 'role' => 'super_admin',
        ]);

        $this->get('/admins')->assertOk();
    }

    public function test_create_admin_accessible_for_super_admin(): void
    {
        $this->mockAuth(true, true, [
            'uuid' => 'u', 'name' => 'Super', 'email' => 's@t.com', 'role' => 'super_admin',
        ]);

        $this->get('/admins/create')->assertOk();
    }

    // ── Correlation ID ──────────────────────────────────────────────────

    public function test_health_returns_ok(): void
    {
        $this->getJson('/health')->assertOk()->assertJson(['status' => 'ok']);
    }

    public function test_correlation_id_returned(): void
    {
        $this->get('/health', ['X-Correlation-Id' => 'cid-123'])
            ->assertHeader('X-Correlation-Id', 'cid-123');
    }

    public function test_correlation_id_generated_when_absent(): void
    {
        $response = $this->get('/health');
        $cid = $response->headers->get('X-Correlation-Id');

        $this->assertNotEmpty($cid);
        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', $cid);
    }
}

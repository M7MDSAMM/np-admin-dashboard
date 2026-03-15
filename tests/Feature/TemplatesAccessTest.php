<?php

namespace Tests\Feature;

use App\Services\Contracts\TemplateManagementServiceInterface;
use App\Services\Exceptions\UnauthorizedRemoteException;
use Tests\Support\FakeTemplateManagementService;
use Tests\Support\SessionHelper;
use Tests\TestCase;

class TemplatesAccessTest extends TestCase
{
    use SessionHelper;

    private function fakeTemplateService(): FakeTemplateManagementService
    {
        $fake = new FakeTemplateManagementService();
        $this->app->instance(TemplateManagementServiceInterface::class, $fake);

        return $fake;
    }

    public function test_templates_redirects_when_unauthenticated(): void
    {
        $this->get('/templates')->assertRedirect(route('login'));
    }

    public function test_non_super_admin_is_forbidden(): void
    {
        $this->actingAsAdmin('admin');

        $this->get('/templates')->assertStatus(403);
    }

    public function test_super_admin_can_view_templates_page(): void
    {
        $this->actingAsSuperAdmin();

        $fake = $this->fakeTemplateService();
        $fake->nextPaginateResponse = [
            'data' => [
                ['key' => 'welcome', 'name' => 'Welcome', 'channel' => 'email', 'is_active' => true, 'version' => 1],
            ],
            'pagination' => ['current_page' => 1, 'total' => 1],
        ];

        $response = $this->get('/templates');

        $response->assertOk();
        $response->assertSee('Templates');
        $response->assertSee('Welcome');
        $this->assertTrue($fake->paginateCalled);
    }

    public function test_forbidden_remote_does_not_logout(): void
    {
        $this->actingAsSuperAdmin();

        $mock = \Mockery::mock(TemplateManagementServiceInterface::class);
        $mock->shouldReceive('paginateTemplates')
            ->once()
            ->andThrow(new UnauthorizedRemoteException('Forbidden', 403, 'FORBIDDEN', 'cid-1'));
        $this->app->instance(TemplateManagementServiceInterface::class, $mock);

        $response = $this->get('/templates');

        $response->assertStatus(403);
        $this->assertTrue(session()->has('admin_jwt_token'));
        $this->assertTrue(session()->has('admin_profile'));
    }
}

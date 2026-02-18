<?php

namespace Tests\Feature;

use App\Services\Contracts\TemplateManagementServiceInterface;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\TestCase;

class TemplatesAccessTest extends TestCase
{
    use WithFaker;

    public function test_templates_redirects_when_unauthenticated(): void
    {
        $response = $this->get('/templates');
        $response->assertRedirect(route('login'));
    }

    public function test_non_super_admin_is_forbidden(): void
    {
        $this->withSession([
            'admin_jwt_token' => 't',
            'admin_profile'   => ['uuid' => 'u1', 'role' => 'admin'],
            'admin_jwt_expires_at' => now()->addHour()->toIso8601String(),
        ]);

        $response = $this->get('/templates');
        $response->assertStatus(403);
    }

    public function test_super_admin_can_view_templates_page(): void
    {
        $this->withSession([
            'admin_jwt_token' => 't',
            'admin_profile'   => ['uuid' => 'u1', 'role' => 'super_admin'],
            'admin_jwt_expires_at' => now()->addHour()->toIso8601String(),
        ]);

        $mock = Mockery::mock(TemplateManagementServiceInterface::class);
        $mock->shouldReceive('paginateTemplates')
            ->once()
            ->andReturn([
                'data' => [
                    ['key' => 'welcome', 'name' => 'Welcome', 'channel' => 'email', 'is_active' => true, 'version' => 1],
                ],
                'pagination' => ['current_page' => 1, 'total' => 1],
            ]);
        $this->app->instance(TemplateManagementServiceInterface::class, $mock);

        $response = $this->get('/templates');

        $response->assertOk();
        $response->assertSee('Templates');
        $response->assertSee('Welcome');
    }
}

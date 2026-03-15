<?php

namespace Tests\Feature;

use App\Services\Contracts\NotificationServiceClientInterface;
use Tests\Support\FakeNotificationServiceClient;
use Tests\Support\SessionHelper;
use Tests\TestCase;

class NotificationsUiTest extends TestCase
{
    use SessionHelper;

    private function fakeClient(?FakeNotificationServiceClient $fake = null): FakeNotificationServiceClient
    {
        $client = $fake ?? new FakeNotificationServiceClient();
        $this->app->instance(NotificationServiceClientInterface::class, $client);

        return $client;
    }

    public function test_guest_redirected_from_notifications(): void
    {
        $this->get('/notifications')->assertRedirect(route('login'));
    }

    public function test_authenticated_admin_can_view_create_page(): void
    {
        $this->actingAsAdmin();
        $this->fakeClient();

        $this->get('/notifications/create')
            ->assertOk()
            ->assertSee('Create Notification');
    }

    public function test_post_create_redirects_to_show_on_success(): void
    {
        $this->actingAsAdmin();
        $fake = $this->fakeClient();
        $fake->nextCreateResponse['data'] = [
            'uuid'         => 'notif-123',
            'user_uuid'    => 'user-uuid',
            'template_key' => 'welcome_email',
            'status'       => 'queued',
        ];

        $payload = [
            'user_uuid'    => '11111111-1111-1111-1111-111111111111',
            'template_key' => 'welcome_email',
            'channels'     => ['email', 'push'],
            'variables'    => json_encode(['name' => 'Alex']),
        ];

        $response = $this->post('/notifications', $payload);

        $response->assertRedirect(route('notifications.show', 'notif-123'));
        $response->assertSessionHas('success');
        $this->assertTrue($fake->createCalled);
    }

    public function test_details_page_renders_notification_data(): void
    {
        $this->actingAsAdmin();
        $fake = $this->fakeClient();
        $fake->nextGetResponse['data'] = [
            'uuid'         => 'notif-123',
            'user_uuid'    => 'user-uuid',
            'template_key' => 'reset_password',
            'channels'     => ['email'],
            'status'       => 'queued',
            'variables'    => ['name' => 'Sam'],
        ];

        $this->get('/notifications/notif-123')
            ->assertOk()
            ->assertSee('reset_password')
            ->assertSee('user-uuid')
            ->assertSee('Email');

        $this->assertTrue($fake->getCalled);
    }

    public function test_retry_posts_and_redirects_with_flash(): void
    {
        $this->actingAsAdmin();
        $fake = $this->fakeClient();
        $fake->nextRetryResponse['data'] = ['status' => 'retry_accepted'];

        $response = $this->post('/notifications/notif-123/retry');

        $response->assertRedirect(route('notifications.show', 'notif-123'));
        $response->assertSessionHas('success');
        $this->assertTrue($fake->retryCalled);
    }

    public function test_validation_errors_when_channels_empty(): void
    {
        $this->actingAsAdmin();
        $this->fakeClient();

        $payload = [
            'user_uuid'    => 'user-uuid',
            'template_key' => 'welcome_email',
            'channels'     => [],
            'variables'    => '{}',
            '_token'       => csrf_token(),
        ];

        $this->withHeader('Accept', 'application/json')
            ->post('/notifications', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['channels']);
    }
}

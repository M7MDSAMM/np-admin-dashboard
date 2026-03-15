<?php

namespace Tests\Feature;

use App\Services\Contracts\MessagingServiceClientInterface;
use App\Services\Contracts\NotificationServiceClientInterface;
use Carbon\CarbonImmutable;
use Tests\Support\FakeMessagingServiceClient;
use Tests\Support\FakeNotificationServiceClient;
use Tests\TestCase;

class NotificationTrackingTest extends TestCase
{
    private FakeNotificationServiceClient $fakeNotification;
    private FakeMessagingServiceClient $fakeMessaging;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fakeNotification = new FakeNotificationServiceClient();
        $this->fakeMessaging = new FakeMessagingServiceClient();
        $this->app->instance(NotificationServiceClientInterface::class, $this->fakeNotification);
        $this->app->instance(MessagingServiceClientInterface::class, $this->fakeMessaging);
    }

    private function actingAsAdmin(): void
    {
        $this->withSession([
            'admin_jwt_token'      => 'test-token',
            'admin_profile'        => ['uuid' => 'admin-uuid', 'name' => 'Admin', 'role' => 'admin'],
            'admin_jwt_expires_at' => CarbonImmutable::now()->addHour()->toIso8601String(),
        ]);
    }

    // ── 1. Notifications list page ────────────────────────────────────

    public function test_notifications_index_shows_table_with_notifications(): void
    {
        $this->actingAsAdmin();

        $this->fakeNotification->nextListResponse['data'] = [
            'data' => [
                [
                    'uuid'         => 'notif-001',
                    'user_uuid'    => 'user-001',
                    'template_key' => 'welcome_email',
                    'channels'     => ['email'],
                    'status'       => 'queued',
                    'created_at'   => '2026-03-15T10:00:00Z',
                ],
                [
                    'uuid'         => 'notif-002',
                    'user_uuid'    => 'user-002',
                    'template_key' => 'reset_password',
                    'channels'     => ['email', 'push'],
                    'status'       => 'failed',
                    'created_at'   => '2026-03-15T11:00:00Z',
                ],
            ],
            'current_page' => 1,
            'last_page'    => 1,
            'from'         => 1,
            'to'           => 2,
            'total'        => 2,
        ];

        $response = $this->get('/notifications');

        $response->assertOk()
            ->assertSee('notif-001')
            ->assertSee('welcome_email')
            ->assertSee('reset_password')
            ->assertSee('Queued')
            ->assertSee('Failed');

        $this->assertTrue($this->fakeNotification->listCalled);
    }

    // ── 2. Filters pass through ───────────────────────────────────────

    public function test_notifications_index_renders_with_filters(): void
    {
        $this->actingAsAdmin();

        $response = $this->get('/notifications?status=failed&user_uuid=user-001');

        $response->assertOk();
        $this->assertTrue($this->fakeNotification->listCalled);
    }

    // ── 3. Notification details shows attempts and delivery refs ──────

    public function test_notification_show_renders_attempts_and_deliveries(): void
    {
        $this->actingAsAdmin();

        $this->fakeNotification->nextGetResponse['data'] = [
            'uuid'                => 'notif-001',
            'user_uuid'           => 'user-001',
            'template_key'        => 'welcome_email',
            'channels'            => ['email'],
            'status'              => 'queued',
            'variables'           => ['name' => 'Alex'],
            'attempts'            => [
                ['channel' => 'email', 'status' => 'pending', 'error_message' => null, 'created_at' => '2026-03-15T10:00:00Z'],
            ],
            'delivery_references' => [
                ['uuid' => 'del-001', 'channel' => 'email', 'status' => 'pending'],
            ],
        ];

        $response = $this->get('/notifications/notif-001');

        $response->assertOk()
            ->assertSee('notif-001')
            ->assertSee('welcome_email')
            ->assertSee('Notification Attempts')
            ->assertSee('Delivery Tracking')
            ->assertSee('del-001');
    }

    // ── 4. Delivery details page ──────────────────────────────────────

    public function test_delivery_page_shows_delivery_details(): void
    {
        $this->actingAsAdmin();

        $this->fakeMessaging->nextGetDeliveryResponse['data'] = [
            'uuid'              => 'del-001',
            'notification_uuid' => 'notif-001',
            'channel'           => 'email',
            'recipient'         => 'user@example.com',
            'subject'           => 'Welcome!',
            'content'           => 'Hello Alex',
            'status'            => 'sent',
            'attempts_count'    => 1,
            'max_attempts'      => 3,
            'sent_at'           => '2026-03-15T10:01:00Z',
            'delivery_attempts' => [
                [
                    'attempt_number'      => 1,
                    'status'              => 'sent',
                    'provider_message_id' => 'msg-001',
                    'error_message'       => null,
                    'created_at'          => '2026-03-15T10:01:00Z',
                ],
            ],
        ];

        $response = $this->get('/deliveries/del-001');

        $response->assertOk()
            ->assertSee('del-001')
            ->assertSee('user@example.com')
            ->assertSee('Welcome!')
            ->assertSee('msg-001')
            ->assertSee('Delivery Attempts');

        $this->assertTrue($this->fakeMessaging->getDeliveryCalled);
    }

    // ── 5. Delivery retry ─────────────────────────────────────────────

    public function test_delivery_retry_redirects_with_success(): void
    {
        $this->actingAsAdmin();

        $response = $this->post('/deliveries/del-001/retry');

        $response->assertRedirect(route('notifications.delivery', 'del-001'));
        $response->assertSessionHas('success');
        $this->assertTrue($this->fakeMessaging->retryDeliveryCalled);
    }

    // ── 6. Failed delivery shows retry button ─────────────────────────

    public function test_failed_delivery_shows_retry_button(): void
    {
        $this->actingAsAdmin();

        $this->fakeMessaging->nextGetDeliveryResponse['data'] = [
            'uuid'              => 'del-fail',
            'notification_uuid' => 'notif-001',
            'channel'           => 'email',
            'status'            => 'failed',
            'last_error'        => 'SMTP timeout',
            'attempts_count'    => 3,
            'max_attempts'      => 3,
        ];

        $response = $this->get('/deliveries/del-fail');

        $response->assertOk()
            ->assertSee('Retry Delivery')
            ->assertSee('SMTP timeout');
    }

    // ── 7. Guest cannot access delivery page ──────────────────────────

    public function test_guest_redirected_from_delivery_page(): void
    {
        $this->get('/deliveries/del-001')->assertRedirect(route('login'));
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthTest extends TestCase
{
    public function test_health_returns_correlation_and_version(): void
    {
        $response = $this->get('/health');

        $response->assertOk();
        $this->assertNotEmpty($response->headers->get('X-Correlation-Id'));
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.service', 'admin-dashboard');
        $response->assertJsonPath('data.status', 'healthy');
        $this->assertArrayHasKey('version', $response->json('data'));
        $this->assertArrayHasKey('environment', $response->json('data'));
    }
}

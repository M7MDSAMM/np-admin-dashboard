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
        $this->assertArrayHasKey('version', $response->json());
    }
}

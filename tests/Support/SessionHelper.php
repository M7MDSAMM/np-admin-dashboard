<?php

namespace Tests\Support;

use Carbon\CarbonImmutable;

trait SessionHelper
{
    private function actingAsAdmin(string $role = 'admin'): void
    {
        $this->withSession([
            'admin_jwt_token'      => 'test-token',
            'admin_profile'        => [
                'uuid'  => 'admin-uuid',
                'name'  => 'Test Admin',
                'email' => 'admin@test.com',
                'role'  => $role,
            ],
            'admin_jwt_expires_at' => CarbonImmutable::now()->addHour()->toIso8601String(),
        ]);
    }

    private function actingAsSuperAdmin(): void
    {
        $this->actingAsAdmin('super_admin');
    }
}

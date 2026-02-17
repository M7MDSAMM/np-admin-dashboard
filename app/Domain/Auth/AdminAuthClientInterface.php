<?php

namespace App\Domain\Auth;

interface AdminAuthClientInterface
{
    /** @return array{access_token: string, token_type: string, expires_in: int} */
    public function login(string $email, string $password): array;

    /** @return array{uuid: string, name: string, email: string, role: string, is_active: bool} */
    public function me(string $token): array;
}

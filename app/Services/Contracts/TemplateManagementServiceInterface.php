<?php

namespace App\Services\Contracts;

interface TemplateManagementServiceInterface
{
    public function paginateTemplates(string $token, array $filters = []): array;

    public function getTemplate(string $token, string $key): array;

    public function createTemplate(string $token, array $data): array;

    public function updateTemplate(string $token, string $key, array $data): array;

    public function deleteTemplate(string $token, string $key): bool;

    public function renderTemplate(string $token, string $key, array $variables): array;
}

<?php

namespace App\Services\Implementations;

use App\Services\Contracts\TemplateManagementServiceInterface;
use App\Services\Contracts\TemplateServiceClientInterface;
use Illuminate\Support\Facades\Log;

class TemplateManagementService implements TemplateManagementServiceInterface
{
    public function __construct(
        private readonly TemplateServiceClientInterface $client,
    ) {}

    public function paginateTemplates(string $token, array $filters = []): array
    {
        return $this->client->listTemplates($token, $filters);
    }

    public function getTemplate(string $token, string $key): array
    {
        return $this->client->getTemplate($token, $key);
    }

    public function createTemplate(string $token, array $data): array
    {
        $template = $this->client->createTemplate($token, $data);

        Log::info('dashboard.template.create', [
            'template_key'     => $template['key'] ?? null,
            'acting_admin_uuid'=> session('admin_profile.uuid'),
            'correlation_id'   => request()->header('X-Correlation-Id', ''),
        ]);

        return $template;
    }

    public function updateTemplate(string $token, string $key, array $data): array
    {
        $template = $this->client->updateTemplate($token, $key, $data);

        Log::info('dashboard.template.update', [
            'template_key'     => $key,
            'acting_admin_uuid'=> session('admin_profile.uuid'),
            'correlation_id'   => request()->header('X-Correlation-Id', ''),
        ]);

        return $template;
    }

    public function deleteTemplate(string $token, string $key): bool
    {
        $result = $this->client->deleteTemplate($token, $key);

        Log::info('dashboard.template.delete', [
            'template_key'     => $key,
            'acting_admin_uuid'=> session('admin_profile.uuid'),
            'correlation_id'   => request()->header('X-Correlation-Id', ''),
        ]);

        return $result;
    }

    public function renderTemplate(string $token, string $key, array $variables): array
    {
        $result = $this->client->renderTemplate($token, $key, $variables);

        Log::info('dashboard.template.render_preview', [
            'template_key'     => $key,
            'acting_admin_uuid'=> session('admin_profile.uuid'),
            'correlation_id'   => request()->header('X-Correlation-Id', ''),
        ]);

        return $result;
    }
}

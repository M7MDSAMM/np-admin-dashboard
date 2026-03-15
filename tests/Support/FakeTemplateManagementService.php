<?php

namespace Tests\Support;

use App\Services\Contracts\TemplateManagementServiceInterface;

class FakeTemplateManagementService implements TemplateManagementServiceInterface
{
    public array $nextPaginateResponse = [
        'data'       => [],
        'pagination' => ['current_page' => 1, 'total' => 0],
    ];

    public array $nextGetResponse = [];
    public array $nextCreateResponse = [];
    public array $nextUpdateResponse = [];
    public array $nextRenderResponse = [];
    public bool $nextDeleteResponse = true;

    public bool $paginateCalled = false;
    public bool $getCalled = false;
    public bool $createCalled = false;
    public bool $updateCalled = false;
    public bool $deleteCalled = false;
    public bool $renderCalled = false;

    public function paginateTemplates(string $token, array $filters = []): array
    {
        $this->paginateCalled = true;

        return $this->nextPaginateResponse;
    }

    public function getTemplate(string $token, string $key): array
    {
        $this->getCalled = true;

        return $this->nextGetResponse;
    }

    public function createTemplate(string $token, array $data): array
    {
        $this->createCalled = true;

        return $this->nextCreateResponse;
    }

    public function updateTemplate(string $token, string $key, array $data): array
    {
        $this->updateCalled = true;

        return $this->nextUpdateResponse;
    }

    public function deleteTemplate(string $token, string $key): bool
    {
        $this->deleteCalled = true;

        return $this->nextDeleteResponse;
    }

    public function renderTemplate(string $token, string $key, array $variables): array
    {
        $this->renderCalled = true;

        return $this->nextRenderResponse;
    }
}

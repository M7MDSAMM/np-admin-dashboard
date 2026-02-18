<?php

namespace App\Http\Controllers;

use App\Services\Contracts\AdminAuthServiceInterface;
use App\Services\Contracts\TemplateManagementServiceInterface;
use App\Services\Exceptions\ExternalServiceException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TemplatesController extends Controller
{
    public function __construct(
        private readonly AdminAuthServiceInterface $auth,
        private readonly TemplateManagementServiceInterface $templates,
    ) {}

    public function index(Request $request): View
    {
        $token = $this->auth->getToken();
        $filters = $request->only(['key', 'channel', 'is_active']);

        $result = $this->templates->paginateTemplates($token, $filters);

        return view('templates.index', [
            'templates'  => $result['data'] ?? [],
            'pagination' => $result['pagination'] ?? null,
            'filters'    => $filters,
            'currentAdmin' => $this->auth->getAdmin(),
        ]);
    }

    public function create(): View
    {
        return view('templates.create', [
            'template' => null,
            'currentAdmin' => $this->auth->getAdmin(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $token = $this->auth->getToken();
        $data = $this->validateTemplate($request, true);

        try {
            $template = $this->templates->createTemplate($token, $data);
            return redirect()->route('templates.show', $template['key'])
                ->with('success', 'Template created successfully.');
        } catch (ExternalServiceException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(string $key): View
    {
        $token = $this->auth->getToken();
        $template = $this->templates->getTemplate($token, $key);

        return view('templates.show', [
            'template' => $template,
            'currentAdmin' => $this->auth->getAdmin(),
        ]);
    }

    public function edit(string $key): View
    {
        $token = $this->auth->getToken();
        $template = $this->templates->getTemplate($token, $key);

        return view('templates.edit', [
            'template' => $template,
            'currentAdmin' => $this->auth->getAdmin(),
        ]);
    }

    public function update(Request $request, string $key): RedirectResponse
    {
        $token = $this->auth->getToken();
        $data = $this->validateTemplate($request, false);

        try {
            $this->templates->updateTemplate($token, $key, $data);
            return redirect()->route('templates.show', $key)
                ->with('success', 'Template updated successfully.');
        } catch (ExternalServiceException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroy(Request $request, string $key): JsonResponse|RedirectResponse
    {
        $token = $this->auth->getToken();
        $this->templates->deleteTemplate($token, $key);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Template deleted successfully.']);
        }

        return redirect()->route('templates.index')->with('success', 'Template deleted.');
    }

    public function renderPreview(string $key): View
    {
        $token = $this->auth->getToken();
        $template = $this->templates->getTemplate($token, $key);

        return view('templates.render', [
            'template' => $template,
            'result'   => null,
            'input'    => $this->buildExampleJson($template),
            'currentAdmin' => $this->auth->getAdmin(),
        ]);
    }

    public function renderPreviewSubmit(Request $request, string $key): View|RedirectResponse
    {
        $token = $this->auth->getToken();
        $template = $this->templates->getTemplate($token, $key);

        // Treat an empty/blank submission as an empty object rather than an error.
        $input = trim($request->input('variables_json', '')) ?: '{}';

        try {
            $variables = $this->decodeJson($input);
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        try {
            $result = $this->templates->renderTemplate($token, $key, $variables);
            return view('templates.render', [
                'template' => $template,
                'result'   => $result,
                'input'    => $input,
                'currentAdmin' => $this->auth->getAdmin(),
            ])->with('success', $result['message'] ?? 'Rendered successfully.');
        } catch (ExternalServiceException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    private function validateTemplate(Request $request, bool $isCreate): array
    {
        $rules = [
            'name'             => ['required', 'string', 'max:150'],
            'channel'          => ['required', 'in:email,whatsapp,push'],
            'subject'          => ['nullable', 'string', 'max:190'],
            'body'             => ['required', 'string'],
            'variables_schema' => ['nullable', 'string'],
            'is_active'        => ['sometimes', 'boolean'],
        ];

        if ($isCreate) {
            $rules['key'] = ['required', 'string', 'max:120'];
        }

        $data = $request->validate($rules);

        if (isset($data['variables_schema']) && $data['variables_schema'] !== null) {
            $data['variables_schema'] = $this->decodeJson($data['variables_schema']);
        } else {
            $data['variables_schema'] = [];
        }

        return $data;
    }

    private function decodeJson(string $json): array
    {
        $decoded = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
            throw new \InvalidArgumentException('Invalid JSON for variables schema/variables.');
        }

        return $decoded;
    }

    /**
     * Build a starter JSON object from the template's variables_schema so the
     * render textarea comes pre-filled with the correct keys.
     */
    private function buildExampleJson(array $template): string
    {
        $schema = $template['variables_schema'] ?? [];
        $example = [];

        foreach ($schema['required'] ?? [] as $var) {
            $example[$var] = '';
        }
        foreach ($schema['optional'] ?? [] as $var) {
            $example[$var] = '';
        }

        return json_encode($example ?: new \stdClass(), JSON_PRETTY_PRINT);
    }
}

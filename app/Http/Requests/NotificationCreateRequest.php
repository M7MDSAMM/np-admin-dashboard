<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class NotificationCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_uuid'       => ['required', 'uuid'],
            'template_key'    => ['required', 'string', 'max:120'],
            'channels'        => ['required', 'array', 'min:1'],
            'channels.*'      => ['string', Rule::in(['email', 'whatsapp', 'push'])],
            'variables'       => ['nullable', 'string'],
            'idempotency_key' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * Decode the variables JSON into an array and merge into the validated data.
     *
     * @throws ValidationException
     */
    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $variablesJson = $this->input('variables', '{}');
        $decoded = json_decode($variablesJson, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
            throw ValidationException::withMessages([
                'variables' => 'Variables must be valid JSON.',
            ]);
        }

        $data['variables'] = $decoded;

        return $data;
    }
}

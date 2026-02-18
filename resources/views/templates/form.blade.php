<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Key</label>
        <input type="text" name="key" value="{{ old('key', $template['key'] ?? '') }}" placeholder="welcome_email" @if(isset($template)) disabled @endif class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
        <input type="text" name="name" value="{{ old('name', $template['name'] ?? '') }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Channel</label>
            <select name="channel" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @foreach (['email' => 'Email', 'whatsapp' => 'WhatsApp', 'push' => 'Push'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('channel', $template['channel'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Subject (email only)</label>
            <input type="text" name="subject" value="{{ old('subject', $template['subject'] ?? '') }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Body</label>
        <textarea name="body" rows="6" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Hi {{ '{{ user_name }}' }}, ...">{{ old('body', $template['body'] ?? '') }}</textarea>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Variables Schema (JSON)</label>
        <textarea name="variables_schema" rows="4" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder='{"required":["user_name"],"optional":["support_email"],"rules":{"support_email":"email"}}'>{{ old('variables_schema', isset($template['variables_schema']) ? json_encode($template['variables_schema'], JSON_PRETTY_PRINT) : '') }}</textarea>
        <p class="mt-1 text-xs text-gray-500">Provide required/optional variables and optional validation rules.</p>
    </div>

    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
        <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" @checked(old('is_active', $template['is_active'] ?? true))>
        Active
    </label>

    <div class="flex gap-3">
        <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500">Save</button>
        <a href="{{ route('templates.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</a>
    </div>
</div>

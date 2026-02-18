{{-- ── Identity ──────────────────────────────────────────────────── --}}
<div class="px-6 py-5 space-y-5">
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div>
            <label for="key" class="block text-sm font-medium text-gray-700">Key</label>
            <input type="text" id="key" name="key"
                value="{{ old('key', $template['key'] ?? '') }}"
                placeholder="welcome_email"
                @if(isset($template)) disabled @endif
                class="mt-1 block w-full rounded-lg border px-3 py-2 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none disabled:bg-gray-50 disabled:text-gray-500 {{ $errors->has('key') ? 'border-red-300' : 'border-gray-300' }}">
            @error('key')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            @if(isset($template))
                <p class="mt-1 text-xs text-gray-400">Key cannot be changed after creation.</p>
            @endif
        </div>
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
            <input type="text" id="name" name="name"
                value="{{ old('name', $template['name'] ?? '') }}"
                placeholder="Welcome Email"
                class="mt-1 block w-full rounded-lg border px-3 py-2 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none {{ $errors->has('name') ? 'border-red-300' : 'border-gray-300' }}">
            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div>
            <label for="channel" class="block text-sm font-medium text-gray-700">Channel</label>
            <select id="channel" name="channel"
                class="mt-1 block w-full rounded-lg border px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none {{ $errors->has('channel') ? 'border-red-300' : 'border-gray-300' }}">
                @foreach (['email' => 'Email', 'whatsapp' => 'WhatsApp', 'push' => 'Push'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('channel', $template['channel'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('channel')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="subject" class="block text-sm font-medium text-gray-700">
                Subject
                <span class="text-xs font-normal text-gray-400 ml-1">email only</span>
            </label>
            <input type="text" id="subject" name="subject"
                value="{{ old('subject', $template['subject'] ?? '') }}"
                placeholder="Your account is ready"
                class="mt-1 block w-full rounded-lg border px-3 py-2 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none {{ $errors->has('subject') ? 'border-red-300' : 'border-gray-300' }}">
            @error('subject')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>

{{-- ── Divider ───────────────────────────────────────────────────── --}}
<div class="border-t border-gray-100"></div>

{{-- ── Content ───────────────────────────────────────────────────── --}}
<div class="px-6 py-5 space-y-5">
    <div>
        <label for="body" class="block text-sm font-medium text-gray-700">Body</label>
        <p class="text-xs text-gray-400 mt-0.5">Use <code class="rounded bg-gray-100 px-1 py-0.5 font-mono">@{{ variable_name }}</code> for template variables.</p>
        <textarea id="body" name="body" rows="8"
            placeholder="Hi @{{ user_name }}, ..."
            class="mt-1 block w-full rounded-lg border px-3 py-2 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none font-mono {{ $errors->has('body') ? 'border-red-300' : 'border-gray-300' }}">{{ old('body', $template['body'] ?? '') }}</textarea>
        @error('body')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="variables_schema" class="block text-sm font-medium text-gray-700">Variables Schema <span class="text-xs font-normal text-gray-400 ml-1">JSON</span></label>
        <textarea id="variables_schema" name="variables_schema" rows="4"
            placeholder='{"required":["user_name"],"optional":["support_email"],"rules":{"support_email":"email"}}'
            class="mt-1 block w-full rounded-lg border px-3 py-2 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none font-mono {{ $errors->has('variables_schema') ? 'border-red-300' : 'border-gray-300' }}">{{ old('variables_schema', isset($template['variables_schema']) ? json_encode($template['variables_schema'], JSON_PRETTY_PRINT) : '') }}</textarea>
        @error('variables_schema')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
        <p class="mt-1 text-xs text-gray-400">Declare required/optional variable names and optional validation rules.</p>
    </div>
</div>

{{-- ── Divider ───────────────────────────────────────────────────── --}}
<div class="border-t border-gray-100"></div>

{{-- ── Footer: status + actions ─────────────────────────────────── --}}
<div class="px-6 py-4 flex items-center justify-between gap-x-4">
    <label class="inline-flex items-center gap-x-3 cursor-pointer">
        <div class="relative">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" id="is_active" name="is_active" value="1"
                class="sr-only peer"
                @checked(old('is_active', $template['is_active'] ?? true))>
            <div class="w-10 h-6 bg-gray-200 peer-checked:bg-indigo-600 rounded-full transition-colors peer-focus:ring-2 peer-focus:ring-indigo-500/30"></div>
            <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform peer-checked:translate-x-4"></div>
        </div>
        <span class="text-sm font-medium text-gray-700">Active</span>
    </label>

    <div class="flex items-center gap-x-3">
        <a href="{{ route('templates.index') }}" class="rounded-lg px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">Cancel</a>
        <button type="submit" class="inline-flex items-center gap-x-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
            Save Template
        </button>
    </div>
</div>

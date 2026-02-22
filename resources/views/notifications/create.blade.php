@extends('layouts.app')

@section('title', 'Create Notification')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Create Notification</h1>
            <p class="text-sm text-gray-500">Send a notification to a user across one or more channels.</p>
        </div>
        <a href="{{ route('notifications.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500">← Back</a>
    </div>

    @if(session('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <p class="font-semibold">Error</p>
            <p>{{ session('error') }}</p>
            @if(session('error_code'))<p class="text-xs mt-1 uppercase tracking-wide">Code: {{ session('error_code') }}</p>@endif
        </div>
    @endif

    <form method="POST" action="{{ route('notifications.store') }}" class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-6">
        @csrf
        <div class="grid gap-6 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-gray-700">User UUID</label>
                <input name="user_uuid" value="{{ old('user_uuid') }}" required class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="e.g. 8c1a...">
                @error('user_uuid')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Template Key</label>
                <input name="template_key" value="{{ old('template_key') }}" required class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="welcome_email">
                @error('template_key')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Channels</label>
            <div class="mt-2 flex flex-wrap gap-4">
                @php $selected = old('channels', []); @endphp
                @foreach(['email' => 'Email', 'whatsapp' => 'WhatsApp', 'push' => 'Push'] as $value => $label)
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="channels[]" value="{{ $value }}" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" {{ in_array($value, $selected) ? 'checked' : '' }}>
                        {{ $label }}
                    </label>
                @endforeach
            </div>
            @error('channels')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            @error('channels.*')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <div class="flex items-center justify-between">
                <label class="block text-sm font-medium text-gray-700">Variables (JSON)</label>
                <span class="text-xs text-gray-500">Example: {"name":"Alex"}</span>
            </div>
            <textarea name="variables" rows="5" class="mt-1 w-full rounded-lg border-gray-300 font-mono text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder='{"name":"Alex"}'>{{ old('variables', '{}') }}</textarea>
            @error('variables')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-gray-700">Idempotency Key (optional)</label>
                <input name="idempotency_key" value="{{ old('idempotency_key') }}" class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="idem-123">
                @error('idempotency_key')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('notifications.index') }}" class="text-sm text-gray-600 hover:text-gray-800">Cancel</a>
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Send Notification
            </button>
        </div>
    </form>
</div>
@endsection

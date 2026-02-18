@extends('layouts.app')

@section('title', 'Template Details')

@section('content')
<div class="max-w-5xl space-y-6">
    <div class="flex items-start justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">{{ $template['name'] }}</h2>
            <p class="text-sm text-gray-500">Key: {{ $template['key'] }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('templates.edit', $template['key']) }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Edit</a>
            <a href="{{ route('templates.render-preview', $template['key']) }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Render</a>
            <form method="POST" action="{{ route('templates.destroy', $template['key']) }}" onsubmit="return confirm('Delete template?')">
                @csrf @method('DELETE')
                <button class="rounded-lg border border-red-200 px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-50">Delete</button>
            </form>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div class="rounded-lg bg-white p-4 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">Metadata</h3>
            <dl class="space-y-2 text-sm text-gray-700">
                <div class="flex justify-between"><dt>Channel</dt><dd class="font-medium">{{ ucfirst($template['channel']) }}</dd></div>
                <div class="flex justify-between"><dt>Status</dt><dd class="font-medium">{{ $template['is_active'] ? 'Active' : 'Inactive' }}</dd></div>
                <div class="flex justify-between"><dt>Version</dt><dd class="font-medium">{{ $template['version'] ?? '—' }}</dd></div>
                <div><dt class="text-gray-500">Variables schema</dt>
                    <pre class="mt-1 rounded bg-gray-50 p-3 text-xs text-gray-800">{{ json_encode($template['variables_schema'] ?? [], JSON_PRETTY_PRINT) }}</pre>
                </div>
            </dl>
        </div>
        <div class="rounded-lg bg-white p-4 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">Subject</h3>
            <p class="rounded bg-gray-50 p-3 text-sm text-gray-800 whitespace-pre-line">{{ $template['subject'] ?? '—' }}</p>
            <h3 class="text-sm font-semibold text-gray-700 mt-4 mb-2">Body</h3>
            <p class="rounded bg-gray-50 p-3 text-sm text-gray-800 whitespace-pre-line">{{ $template['body'] }}</p>
        </div>
    </div>
</div>
@endsection

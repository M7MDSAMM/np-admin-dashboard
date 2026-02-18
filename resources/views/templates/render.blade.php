@extends('layouts.app')

@section('title', 'Render Preview')

@section('content')
<div class="max-w-5xl space-y-6">
    <div class="flex items-start justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Render Preview</h2>
            <p class="text-sm text-gray-500">{{ $template['name'] }} ({{ $template['key'] }})</p>
        </div>
        <a href="{{ route('templates.show', $template['key']) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500">Back to details</a>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <form method="POST" action="{{ route('templates.render-preview.submit', $template['key']) }}" class="rounded-lg bg-white p-4 shadow-sm space-y-3">
            @csrf
            <label class="block text-sm font-medium text-gray-700 mb-1">Variables (JSON)</label>
            <textarea name="variables_json" rows="8" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder='{"user_name":"Alex"}'>{{ old('variables_json', $input ?? '') }}</textarea>
            <p class="text-xs text-gray-500">Provide variables required by the template schema.</p>
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Render Preview</button>
        </form>

        <div class="rounded-lg bg-white p-4 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">Rendered Output</h3>
            @if ($result)
                <div class="space-y-3 text-sm text-gray-800">
                    <div>
                        <p class="text-gray-500 text-xs mb-1">Subject</p>
                        <div class="rounded bg-gray-50 p-3">{{ $result['subject_rendered'] ?? '—' }}</div>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs mb-1">Body</p>
                        <div class="rounded bg-gray-50 p-3 whitespace-pre-line">{{ $result['body_rendered'] ?? '' }}</div>
                    </div>
                </div>
            @else
                <p class="text-sm text-gray-500">Render to see a preview.</p>
            @endif
        </div>
    </div>
</div>
@endsection

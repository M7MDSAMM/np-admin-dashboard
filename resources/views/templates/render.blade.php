@extends('layouts.app')

@section('title', 'Render Preview')

@section('content')
<div class="space-y-5">

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-sm text-gray-500">
                Live preview for
                <span class="font-mono text-xs bg-gray-100 rounded px-1.5 py-0.5 text-gray-700">{{ $template['key'] }}</span>
                — {{ $template['name'] }}
            </p>
        </div>
        <a href="{{ route('templates.show', $template['key']) }}" class="inline-flex items-center gap-x-1 text-sm text-gray-500 hover:text-gray-700 transition-colors flex-shrink-0">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            Back to Template
        </a>
    </div>

    <div class="grid gap-5 lg:grid-cols-2">

        {{-- Left: variables input --}}
        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">Variables</h3>
                <p class="mt-0.5 text-xs text-gray-400">Enter the values as a JSON object.</p>
            </div>

            @if (!empty($template['variables_schema']))
            <div class="px-5 py-3 border-b border-gray-100 bg-gray-50">
                <p class="text-xs font-medium text-gray-500 mb-1.5">Expected by this template:</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach ($template['variables_schema']['required'] ?? [] as $var)
                        <span class="inline-flex items-center gap-x-1 rounded-full bg-red-50 px-2 py-0.5 text-xs font-medium text-red-700 ring-1 ring-red-600/20">
                            <span class="h-1 w-1 rounded-full bg-red-500"></span>{{ $var }}
                        </span>
                    @endforeach
                    @foreach ($template['variables_schema']['optional'] ?? [] as $var)
                        <span class="inline-flex items-center gap-x-1 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 ring-1 ring-gray-500/20">
                            <span class="h-1 w-1 rounded-full bg-gray-400"></span>{{ $var }}
                        </span>
                    @endforeach
                </div>
                <p class="mt-1.5 text-xs text-gray-400">
                    <span class="inline-flex items-center gap-x-1"><span class="h-1.5 w-1.5 rounded-full bg-red-400"></span>required</span>
                    <span class="ml-2 inline-flex items-center gap-x-1"><span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>optional</span>
                </p>
            </div>
            @endif

            <form method="POST" action="{{ route('templates.render-preview.submit', $template['key']) }}" class="px-5 py-4 space-y-4">
                @csrf
                <div>
                    <textarea
                        name="variables_json"
                        rows="10"
                        spellcheck="false"
                        class="block w-full rounded-lg border border-gray-300 px-3 py-2 font-mono text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none {{ $errors->has('variables_json') ? 'border-red-300' : '' }}"
                        placeholder='{"user_name": "Alex"}'
                    >{{ old('variables_json', $input ?? '{}') }}</textarea>
                    @error('variables_json')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1.5 text-xs text-gray-400">Must be a valid JSON object. String values for text, numbers for numeric fields.</p>
                </div>
                <button type="submit" class="inline-flex items-center gap-x-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" /></svg>
                    Render Preview
                </button>
            </form>
        </div>

        {{-- Right: rendered output --}}
        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Rendered Output</h3>
                    <p class="mt-0.5 text-xs text-gray-400">Variables substituted into the template body.</p>
                </div>
                @if ($result)
                    <span class="inline-flex items-center gap-x-1 rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-700 ring-1 ring-green-600/20">
                        <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>Success
                    </span>
                @endif
            </div>

            <div class="px-5 py-4 space-y-4">
                @if ($result)
                    @if (!empty($result['subject_rendered']))
                    <div>
                        <p class="mb-1 text-xs font-medium text-gray-500 uppercase tracking-wide">Subject</p>
                        <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-800">{{ $result['subject_rendered'] }}</div>
                    </div>
                    @endif
                    <div>
                        <p class="mb-1 text-xs font-medium text-gray-500 uppercase tracking-wide">Body</p>
                        <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-800 whitespace-pre-line min-h-[6rem]">{{ $result['body_rendered'] ?? '' }}</div>
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-12 text-center">
                        <svg class="h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" /></svg>
                        <p class="mt-2 text-sm text-gray-400">Fill in the variables and click <span class="font-medium text-gray-600">Render Preview</span>.</p>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection

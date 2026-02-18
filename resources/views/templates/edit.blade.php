@extends('layouts.app')

@section('title', 'Edit Template')

@section('content')
<div class="mx-auto max-w-3xl">

    <div class="mb-6">
        <a href="{{ route('templates.show', $template['key']) }}" class="inline-flex items-center gap-x-1 text-sm text-gray-500 hover:text-gray-700 transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            Back to Template
        </a>
    </div>

    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5">
        <div class="px-6 py-5 border-b border-gray-100 flex items-start justify-between">
            <div>
                <h3 class="text-base font-semibold text-gray-900">Edit Template</h3>
                <p class="mt-1 text-sm text-gray-500">Update content, variables schema, or status.</p>
            </div>
            <div class="rounded-lg bg-gray-50 border border-gray-200 px-3 py-1.5 text-xs font-mono text-gray-600">
                {{ $template['key'] }}
            </div>
        </div>

        <form method="POST" action="{{ route('templates.update', $template['key']) }}">
            @csrf
            @method('PUT')
            @include('templates.form')
        </form>
    </div>

</div>
@endsection

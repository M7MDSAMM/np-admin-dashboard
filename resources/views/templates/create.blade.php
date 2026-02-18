@extends('layouts.app')

@section('title', 'New Template')

@section('content')
<div class="mx-auto max-w-3xl">

    <div class="mb-6">
        <a href="{{ route('templates.index') }}" class="inline-flex items-center gap-x-1 text-sm text-gray-500 hover:text-gray-700 transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            Back to Templates
        </a>
    </div>

    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5">
        <div class="px-6 py-5 border-b border-gray-100">
            <h3 class="text-base font-semibold text-gray-900">New Template</h3>
            <p class="mt-1 text-sm text-gray-500">Define a reusable message template for a notification channel.</p>
        </div>

        <form method="POST" action="{{ route('templates.store') }}">
            @csrf
            @include('templates.form')
        </form>
    </div>

</div>
@endsection

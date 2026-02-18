@extends('layouts.app')

@section('title', 'Edit Template')

@section('content')
<div class="max-w-4xl space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Edit Template</h2>
            <p class="text-sm text-gray-500">Update content, variables, or status.</p>
        </div>
        <a href="{{ route('templates.show', $template['key']) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500">View</a>
    </div>
    <form method="POST" action="{{ route('templates.update', $template['key']) }}" class="rounded-lg bg-white p-6 shadow-sm space-y-6">
        @csrf @method('PUT')
        @include('templates.form')
    </form>
</div>
@endsection

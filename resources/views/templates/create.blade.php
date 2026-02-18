@extends('layouts.app')

@section('title', 'Create Template')

@section('content')
<div class="max-w-4xl space-y-6">
    <div>
        <h2 class="text-xl font-semibold text-gray-900">Create Template</h2>
        <p class="text-sm text-gray-500">Define a reusable message for your channels.</p>
    </div>
    <form method="POST" action="{{ route('templates.store') }}" class="rounded-lg bg-white p-6 shadow-sm space-y-6">
        @csrf
        @include('templates.form')
    </form>
</div>
@endsection

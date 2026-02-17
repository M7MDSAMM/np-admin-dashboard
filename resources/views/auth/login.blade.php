@extends('layouts.guest')
@section('title', 'Sign In')
@section('content')
<div class="sm:mx-auto sm:w-full sm:max-w-md">
    <div class="flex justify-center">
        <div class="h-12 w-12 rounded-xl bg-indigo-600 flex items-center justify-center">
            <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" /></svg>
        </div>
    </div>
    <h2 class="mt-4 text-center text-2xl font-bold tracking-tight text-gray-900">Sign in to your account</h2>
    <p class="mt-1 text-center text-sm text-gray-500">Notification Platform — Admin Dashboard</p>
</div>
<div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
    <div class="bg-white px-6 py-8 shadow-lg ring-1 ring-gray-900/5 sm:rounded-xl sm:px-10">
        @if (session('error'))
        <div class="mb-4 rounded-lg bg-red-50 p-3 border border-red-200"><p class="text-sm text-red-700">{{ session('error') }}</p></div>
        @endif
        @if (session('success'))
        <div class="mb-4 rounded-lg bg-green-50 p-3 border border-green-200"><p class="text-sm text-green-700">{{ session('success') }}</p></div>
        @endif
        <form method="POST" action="{{ url('/login') }}" class="space-y-5">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none sm:text-sm" placeholder="admin@example.com">
                @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" id="password" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none sm:text-sm" placeholder="Enter your password">
                @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="flex w-full justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">Sign in</button>
        </form>
    </div>
</div>
@endsection

@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="space-y-6">
    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5">
        <div class="px-6 py-8">
            <h2 class="text-xl font-semibold text-gray-900">Welcome back, {{ $currentAdmin['name'] ?? 'Admin' }}</h2>
            <p class="mt-1 text-sm text-gray-500">Here's an overview of the Notification Platform.</p>
        </div>
    </div>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['User Service', '8001', 'bg-blue-50', 'text-blue-600'],
            ['Notification Service', '8002', 'bg-green-50', 'text-green-600'],
            ['Messaging Service', '8003', 'bg-purple-50', 'text-purple-600'],
            ['Template Service', '8004', 'bg-amber-50', 'text-amber-600'],
        ] as [$name, $port, $bg, $color])
        <div class="overflow-hidden rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
            <div class="flex items-center gap-x-3">
                <div class="rounded-lg {{ $bg }} p-2">
                    <svg class="h-6 w-6 {{ $color }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 0 1-3-3m3 3a3 3 0 1 0 0 6h13.5a3 3 0 1 0 0-6m-16.5-3a3 3 0 0 1 3-3h13.5a3 3 0 0 1 3 3m-19.5 0a4.5 4.5 0 0 1 .9-2.7L5.737 5.1a3.375 3.375 0 0 1 2.7-1.35h7.126c1.062 0 2.062.5 2.7 1.35l2.587 3.45a4.5 4.5 0 0 1 .9 2.7m0 0a3 3 0 0 1-3 3m0 3h.008v.008h-.008v-.008Zm0-6h.008v.008h-.008v-.008Zm-3 6h.008v.008h-.008v-.008Zm0-6h.008v.008h-.008v-.008Z" /></svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">{{ $name }}</p>
                    <p class="text-lg font-semibold text-gray-900">Port {{ $port }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @if (($currentAdmin['role'] ?? '') === 'super_admin')
    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5">
        <div class="px-6 py-5 border-b border-gray-100"><h3 class="text-base font-semibold text-gray-900">Quick Actions</h3></div>
        <div class="px-6 py-4">
            <a href="{{ route('admins.index') }}" class="flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
                Manage Admins
            </a>
        </div>
    </div>
    @endif
</div>
@endsection

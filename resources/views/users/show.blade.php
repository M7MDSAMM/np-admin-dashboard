@extends('layouts.app')
@section('title', 'User Details')
@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('users.index') }}" class="inline-flex items-center gap-x-1 text-sm text-gray-500 hover:text-gray-700">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            Back to Users
        </a>
        <div class="flex items-center gap-x-2">
            <a href="{{ route('users.preferences', $user['uuid']) }}" class="inline-flex items-center gap-x-1.5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" /></svg>
                Preferences
            </a>
            <a href="{{ route('users.devices', $user['uuid']) }}" class="inline-flex items-center gap-x-1.5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" /></svg>
                Devices
            </a>
            <a href="{{ route('users.edit', $user['uuid']) }}" class="inline-flex items-center gap-x-1.5 rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                Edit
            </a>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5">
        <div class="px-6 py-5 border-b border-gray-100">
            <div class="flex items-center gap-x-4">
                <div class="h-14 w-14 rounded-full bg-emerald-100 flex items-center justify-center">
                    <span class="text-xl font-semibold text-emerald-600">{{ strtoupper(substr($user['name'] ?? '', 0, 1)) }}</span>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ $user['name'] ?? '' }}</h3>
                    <p class="text-sm text-gray-500">{{ $user['email'] ?? '' }}</p>
                </div>
                <div class="ml-auto">
                    @if ($user['is_active'] ?? false)
                        <span class="inline-flex items-center gap-x-1 rounded-full px-3 py-1 text-xs font-medium ring-1 bg-green-50 text-green-700 ring-green-600/20">
                            <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>Active
                        </span>
                    @else
                        <span class="inline-flex items-center gap-x-1 rounded-full px-3 py-1 text-xs font-medium ring-1 bg-red-50 text-red-700 ring-red-600/20">
                            <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>Inactive
                        </span>
                    @endif
                </div>
            </div>
        </div>
        <dl class="divide-y divide-gray-100">
            <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                <dt class="text-sm font-medium text-gray-500">UUID</dt>
                <dd class="mt-1 text-sm text-gray-900 font-mono sm:col-span-2 sm:mt-0">{{ $user['uuid'] ?? '' }}</dd>
            </div>
            <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                <dt class="text-sm font-medium text-gray-500">Phone</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $user['phone_e164'] ?? '—' }}</dd>
            </div>
            <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                <dt class="text-sm font-medium text-gray-500">Locale</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $user['locale'] ?? 'en' }}</dd>
            </div>
            <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                <dt class="text-sm font-medium text-gray-500">Timezone</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $user['timezone'] ?? 'UTC' }}</dd>
            </div>
            <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                <dt class="text-sm font-medium text-gray-500">Created</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ !empty($user['created_at']) ? \Carbon\Carbon::parse($user['created_at'])->format('M d, Y H:i') : 'N/A' }}</dd>
            </div>
            <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                <dt class="text-sm font-medium text-gray-500">Updated</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ !empty($user['updated_at']) ? \Carbon\Carbon::parse($user['updated_at'])->format('M d, Y H:i') : 'N/A' }}</dd>
            </div>
        </dl>
    </div>
</div>
@endsection

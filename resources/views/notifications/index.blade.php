@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <p class="text-sm text-gray-500">Send or review notification jobs for users.</p>
    </div>
    <a href="{{ route('notifications.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        New Notification
    </a>
</div>

<div class="grid gap-6 md:grid-cols-2">
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Quick Start</h2>
                <p class="text-sm text-gray-500">Send a notification using the create form.</p>
            </div>
            <a href="{{ route('notifications.create') }}" class="text-indigo-600 text-sm font-semibold hover:text-indigo-500">Open form →</a>
        </div>
        <div class="mt-4 text-sm text-gray-600 space-y-2">
            <p>Required: user UUID, template key, at least one channel, variables JSON.</p>
            <p>Correlation IDs are forwarded automatically for tracing.</p>
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Last created</h2>
            @if($lastNotification)
                <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">Most recent</span>
            @endif
        </div>
        @if($lastNotification)
            <dl class="mt-4 grid grid-cols-2 gap-4 text-sm text-gray-700">
                <div><dt class="font-medium text-gray-500">UUID</dt><dd class="mt-1 text-gray-900">{{ $lastNotification['uuid'] ?? '—' }}</dd></div>
                <div><dt class="font-medium text-gray-500">Template</dt><dd class="mt-1 text-gray-900">{{ $lastNotification['template_key'] ?? '—' }}</dd></div>
                <div><dt class="font-medium text-gray-500">User</dt><dd class="mt-1 text-gray-900">{{ $lastNotification['user_uuid'] ?? '—' }}</dd></div>
                <div><dt class="font-medium text-gray-500">Status</dt><dd class="mt-1 text-gray-900 capitalize">{{ $lastNotification['status'] ?? '—' }}</dd></div>
            </dl>
            <div class="mt-4 flex gap-3">
                @if(isset($lastNotification['uuid']))
                <a href="{{ route('notifications.show', $lastNotification['uuid']) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500">View details</a>
                @endif
                <a href="{{ route('notifications.create') }}" class="text-sm text-gray-500 hover:text-gray-700">Create another</a>
            </div>
        @else
            <p class="mt-4 text-sm text-gray-500">No notifications created in this session yet.</p>
        @endif
    </div>
</div>
@endsection

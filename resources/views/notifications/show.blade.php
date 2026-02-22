@extends('layouts.app')

@section('title', 'Notification Details')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-500">Notification details</p>
            <h1 class="text-xl font-semibold text-gray-900">Notification {{ $notification['uuid'] ?? '' }}</h1>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('notifications.create') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500">New notification</a>
            <a href="{{ route('notifications.index') }}" class="text-sm text-gray-600 hover:text-gray-800">All notifications</a>
        </div>
    </div>

    @if(session('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <p class="font-semibold">Error</p>
            <p>{{ session('error') }}</p>
            @if(session('error_code'))<p class="text-xs mt-1 uppercase tracking-wide">Code: {{ session('error_code') }}</p>@endif
        </div>
    @endif

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-sm">
            <div>
                <dt class="text-gray-500">UUID</dt>
                <dd class="mt-1 font-semibold text-gray-900">{{ $notification['uuid'] ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">User UUID</dt>
                <dd class="mt-1 text-gray-900">{{ $notification['user_uuid'] ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Template Key</dt>
                <dd class="mt-1 text-gray-900">{{ $notification['template_key'] ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Channels</dt>
                <dd class="mt-1 text-gray-900">
                    @if(!empty($notification['channels']))
                        @foreach($notification['channels'] as $channel)
                            <span class="mr-2 rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">{{ ucfirst($channel) }}</span>
                        @endforeach
                    @else
                        —
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-gray-500">Status</dt>
                <dd class="mt-1 text-gray-900 capitalize">{{ $notification['status'] ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Created At</dt>
                <dd class="mt-1 text-gray-900">{{ $notification['created_at'] ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Scheduled At</dt>
                <dd class="mt-1 text-gray-900">{{ $notification['scheduled_at'] ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Last Error</dt>
                <dd class="mt-1 text-gray-900">{{ $notification['last_error'] ?? '—' }}</dd>
            </div>
        </dl>

        <div class="mt-6">
            <dt class="text-gray-500 text-sm">Variables</dt>
            <dd class="mt-2 rounded-lg bg-gray-50 p-4 text-xs font-mono text-gray-800 overflow-x-auto">{{ json_encode($notification['variables'] ?? new \stdClass(), JSON_PRETTY_PRINT) }}</dd>
        </div>

        <div class="mt-6 flex items-center justify-between">
            <div class="text-xs text-gray-500">
                @php $corr = session('correlation_id') ?? $correlationId ?? null; @endphp
                @if($corr)
                    Correlation ID: <span class="font-semibold text-gray-700">{{ $corr }}</span>
                @endif
            </div>
            <form method="POST" action="{{ route('notifications.retry', $notification['uuid'] ?? '') }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-amber-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.992 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" /></svg>
                    Retry
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Notification Details')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-500">Notification details &amp; delivery tracking</p>
            <h1 class="text-xl font-semibold text-gray-900">Notification {{ \Illuminate\Support\Str::limit($notification['uuid'] ?? '', 18) }}</h1>
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

    {{-- Notification Details --}}
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Overview</h2>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-sm">
            <div>
                <dt class="text-gray-500">UUID</dt>
                <dd class="mt-1 font-semibold text-gray-900 font-mono text-xs">{{ $notification['uuid'] ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">User UUID</dt>
                <dd class="mt-1 text-gray-900 font-mono text-xs">{{ $notification['user_uuid'] ?? '—' }}</dd>
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
                <dd class="mt-1">
                    @php $status = $notification['status'] ?? 'unknown'; @endphp
                    <span class="inline-flex items-center gap-x-1 rounded-full px-2.5 py-0.5 text-xs font-medium ring-1
                        {{ $status === 'queued' ? 'bg-blue-50 text-blue-700 ring-blue-600/20' : '' }}
                        {{ $status === 'sent' ? 'bg-green-50 text-green-700 ring-green-600/20' : '' }}
                        {{ $status === 'failed' ? 'bg-red-50 text-red-700 ring-red-600/20' : '' }}
                    ">
                        <span class="h-1.5 w-1.5 rounded-full
                            {{ $status === 'queued' ? 'bg-blue-500' : '' }}
                            {{ $status === 'sent' ? 'bg-green-500' : '' }}
                            {{ $status === 'failed' ? 'bg-red-500' : '' }}
                        "></span>
                        {{ ucfirst($status) }}
                    </span>
                </dd>
            </div>
            <div>
                <dt class="text-gray-500">Created At</dt>
                <dd class="mt-1 text-gray-900">{{ $notification['created_at'] ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Last Error</dt>
                <dd class="mt-1 text-gray-900">{{ $notification['last_error'] ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Idempotency Key</dt>
                <dd class="mt-1 text-gray-900 font-mono text-xs">{{ $notification['idempotency_key'] ?? '—' }}</dd>
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
            @if(($notification['status'] ?? '') === 'failed')
            <div x-data="{ confirmRetry: false }">
                <button @click="confirmRetry = true" class="inline-flex items-center gap-2 rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-amber-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.992 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" /></svg>
                    Retry Notification
                </button>

                {{-- Confirmation modal --}}
                <div x-show="confirmRetry" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-gray-600/50" x-cloak>
                    <div class="rounded-xl bg-white p-6 shadow-xl max-w-sm w-full mx-4" @click.outside="confirmRetry = false">
                        <h3 class="text-lg font-semibold text-gray-900">Confirm Retry</h3>
                        <p class="mt-2 text-sm text-gray-600">Are you sure you want to retry this notification? This will re-dispatch it through the orchestration pipeline.</p>
                        <div class="mt-4 flex justify-end gap-3">
                            <button @click="confirmRetry = false" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">Cancel</button>
                            <form method="POST" action="{{ route('notifications.retry', $notification['uuid'] ?? '') }}">
                                @csrf
                                <button type="submit" class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-400">Yes, Retry</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Notification Attempts --}}
    @if(!empty($notification['attempts']))
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Notification Attempts</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Channel</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Error</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Created</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($notification['attempts'] as $attempt)
                    <tr class="hover:bg-gray-50">
                        <td class="whitespace-nowrap px-4 py-3">
                            <span class="rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-700">{{ ucfirst($attempt['channel'] ?? '—') }}</span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3">
                            @php $aStatus = $attempt['status'] ?? 'unknown'; @endphp
                            <span class="inline-flex items-center gap-x-1 rounded-full px-2.5 py-0.5 text-xs font-medium ring-1
                                {{ $aStatus === 'pending' ? 'bg-blue-50 text-blue-700 ring-blue-600/20' : '' }}
                                {{ $aStatus === 'sent' ? 'bg-green-50 text-green-700 ring-green-600/20' : '' }}
                                {{ $aStatus === 'failed' ? 'bg-red-50 text-red-700 ring-red-600/20' : '' }}
                            ">{{ ucfirst($aStatus) }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-500 max-w-xs truncate">{{ $attempt['error_message'] ?? '—' }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-gray-500">{{ $attempt['created_at'] ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Delivery References --}}
    @if(!empty($notification['delivery_references']))
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Delivery Tracking</h2>
        <p class="text-sm text-gray-500 mb-4">These are the delivery jobs dispatched to the Messaging Service.</p>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Delivery UUID</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Channel</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($notification['delivery_references'] as $delivery)
                    <tr class="hover:bg-gray-50">
                        <td class="whitespace-nowrap px-4 py-3 font-mono text-xs text-gray-900">{{ $delivery['uuid'] ?? '—' }}</td>
                        <td class="whitespace-nowrap px-4 py-3">
                            <span class="rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-700">{{ ucfirst($delivery['channel'] ?? '—') }}</span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3">
                            @php $dStatus = $delivery['status'] ?? 'unknown'; @endphp
                            <span class="inline-flex items-center gap-x-1 rounded-full px-2.5 py-0.5 text-xs font-medium ring-1
                                {{ $dStatus === 'pending' ? 'bg-blue-50 text-blue-700 ring-blue-600/20' : '' }}
                                {{ $dStatus === 'sent' ? 'bg-green-50 text-green-700 ring-green-600/20' : '' }}
                                {{ $dStatus === 'failed' ? 'bg-red-50 text-red-700 ring-red-600/20' : '' }}
                            ">{{ ucfirst($dStatus) }}</span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-right">
                            @if(!empty($delivery['uuid']))
                            <a href="{{ route('notifications.delivery', $delivery['uuid']) }}" class="inline-flex items-center gap-x-1 rounded-md px-2.5 py-1.5 text-xs font-medium text-indigo-600 hover:bg-indigo-50 transition-colors">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                View Delivery
                            </a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection

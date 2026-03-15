@extends('layouts.app')

@section('title', 'Delivery Details')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-500">Delivery details from Messaging Service</p>
            <h1 class="text-xl font-semibold text-gray-900">Delivery {{ \Illuminate\Support\Str::limit($delivery['uuid'] ?? '', 18) }}</h1>
        </div>
        <div class="flex gap-3">
            @if(!empty($delivery['notification_uuid']))
                <a href="{{ route('notifications.show', $delivery['notification_uuid']) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500">Back to Notification</a>
            @endif
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

    {{-- Delivery Details --}}
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Delivery Overview</h2>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-sm">
            <div>
                <dt class="text-gray-500">Delivery UUID</dt>
                <dd class="mt-1 font-semibold text-gray-900 font-mono text-xs">{{ $delivery['uuid'] ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Notification UUID</dt>
                <dd class="mt-1 text-gray-900 font-mono text-xs">{{ $delivery['notification_uuid'] ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Channel</dt>
                <dd class="mt-1">
                    <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">{{ ucfirst($delivery['channel'] ?? '—') }}</span>
                </dd>
            </div>
            <div>
                <dt class="text-gray-500">Status</dt>
                <dd class="mt-1">
                    @php $status = $delivery['status'] ?? 'unknown'; @endphp
                    <span class="inline-flex items-center gap-x-1 rounded-full px-2.5 py-0.5 text-xs font-medium ring-1
                        {{ $status === 'pending' ? 'bg-blue-50 text-blue-700 ring-blue-600/20' : '' }}
                        {{ $status === 'sent' ? 'bg-green-50 text-green-700 ring-green-600/20' : '' }}
                        {{ $status === 'failed' ? 'bg-red-50 text-red-700 ring-red-600/20' : '' }}
                    ">
                        <span class="h-1.5 w-1.5 rounded-full
                            {{ $status === 'pending' ? 'bg-blue-500' : '' }}
                            {{ $status === 'sent' ? 'bg-green-500' : '' }}
                            {{ $status === 'failed' ? 'bg-red-500' : '' }}
                        "></span>
                        {{ ucfirst($status) }}
                    </span>
                </dd>
            </div>
            <div>
                <dt class="text-gray-500">Recipient</dt>
                <dd class="mt-1 text-gray-900">{{ $delivery['recipient'] ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Subject</dt>
                <dd class="mt-1 text-gray-900">{{ $delivery['subject'] ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Attempts</dt>
                <dd class="mt-1 text-gray-900">{{ $delivery['attempts_count'] ?? 0 }} / {{ $delivery['max_attempts'] ?? 3 }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Sent At</dt>
                <dd class="mt-1 text-gray-900">{{ $delivery['sent_at'] ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Last Error</dt>
                <dd class="mt-1 text-red-600">{{ $delivery['last_error'] ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Created At</dt>
                <dd class="mt-1 text-gray-900">{{ $delivery['created_at'] ?? '—' }}</dd>
            </div>
        </dl>

        @if(!empty($delivery['content']))
        <div class="mt-6">
            <dt class="text-gray-500 text-sm">Content</dt>
            <dd class="mt-2 rounded-lg bg-gray-50 p-4 text-sm text-gray-800 overflow-x-auto whitespace-pre-wrap">{{ $delivery['content'] ?? '' }}</dd>
        </div>
        @endif

        <div class="mt-6 flex items-center justify-between">
            <div class="text-xs text-gray-500">
                @php $corr = session('correlation_id') ?? $correlationId ?? null; @endphp
                @if($corr)
                    Correlation ID: <span class="font-semibold text-gray-700">{{ $corr }}</span>
                @endif
            </div>
            @if(($delivery['status'] ?? '') === 'failed')
            <div x-data="{ confirmRetry: false }">
                <button @click="confirmRetry = true" class="inline-flex items-center gap-2 rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-amber-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.992 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" /></svg>
                    Retry Delivery
                </button>

                <div x-show="confirmRetry" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-gray-600/50" x-cloak>
                    <div class="rounded-xl bg-white p-6 shadow-xl max-w-sm w-full mx-4" @click.outside="confirmRetry = false">
                        <h3 class="text-lg font-semibold text-gray-900">Confirm Delivery Retry</h3>
                        <p class="mt-2 text-sm text-gray-600">Are you sure you want to retry this delivery? It will be re-queued for processing.</p>
                        <div class="mt-4 flex justify-end gap-3">
                            <button @click="confirmRetry = false" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">Cancel</button>
                            <form method="POST" action="{{ route('notifications.delivery.retry', $delivery['uuid'] ?? '') }}">
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

    {{-- Delivery Attempts --}}
    @if(!empty($delivery['delivery_attempts']))
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Delivery Attempts</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">#</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Provider Message ID</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Error</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Attempted At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($delivery['delivery_attempts'] as $attempt)
                    <tr class="hover:bg-gray-50">
                        <td class="whitespace-nowrap px-4 py-3 text-gray-900 font-medium">{{ $attempt['attempt_number'] ?? '—' }}</td>
                        <td class="whitespace-nowrap px-4 py-3">
                            @php $aStatus = $attempt['status'] ?? 'unknown'; @endphp
                            <span class="inline-flex items-center gap-x-1 rounded-full px-2.5 py-0.5 text-xs font-medium ring-1
                                {{ $aStatus === 'sent' ? 'bg-green-50 text-green-700 ring-green-600/20' : '' }}
                                {{ $aStatus === 'failed' ? 'bg-red-50 text-red-700 ring-red-600/20' : '' }}
                            ">{{ ucfirst($aStatus) }}</span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-gray-500 font-mono text-xs">{{ $attempt['provider_message_id'] ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500 max-w-xs truncate">{{ $attempt['error_message'] ?? '—' }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-gray-500">{{ $attempt['created_at'] ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection

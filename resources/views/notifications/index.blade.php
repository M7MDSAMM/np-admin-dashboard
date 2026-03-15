@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="space-y-4">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-gray-500">Track and manage notification jobs across all users.</p>
        <a href="{{ route('notifications.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            New Notification
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('notifications.index') }}" class="flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
            <select name="status" class="rounded-lg border border-gray-300 py-2 px-3 text-sm text-gray-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none">
                <option value="">All</option>
                @foreach(['queued', 'sent', 'failed'] as $s)
                    <option value="{{ $s }}" {{ ($filters['status'] ?? '') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">User UUID</label>
            <input type="text" name="user_uuid" value="{{ $filters['user_uuid'] ?? '' }}" placeholder="Filter by user..." class="rounded-lg border border-gray-300 py-2 px-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none w-56">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Template</label>
            <input type="text" name="template_key" value="{{ $filters['template_key'] ?? '' }}" placeholder="Filter by template..." class="rounded-lg border border-gray-300 py-2 px-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none w-44">
        </div>
        <button type="submit" class="inline-flex items-center rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">Filter</button>
        @if(!empty(array_filter($filters ?? [])))
            <a href="{{ route('notifications.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Clear</a>
        @endif
    </form>

    @if(!empty($error ?? null))
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $error }}</div>
    @endif

    {{-- Table --}}
    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Notification</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Template</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Channels</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Created</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($notifications as $notification)
                    <tr class="hover:bg-gray-50">
                        <td class="whitespace-nowrap px-6 py-4">
                            <div>
                                <p class="text-sm font-medium text-gray-900 font-mono">{{ \Illuminate\Support\Str::limit($notification['uuid'] ?? '', 18) }}</p>
                                <p class="text-xs text-gray-500">{{ \Illuminate\Support\Str::limit($notification['user_uuid'] ?? '', 18) }}</p>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ $notification['template_key'] ?? '—' }}</td>
                        <td class="whitespace-nowrap px-6 py-4">
                            @foreach(($notification['channels'] ?? []) as $channel)
                                <span class="mr-1 rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-700">{{ ucfirst($channel) }}</span>
                            @endforeach
                        </td>
                        <td class="whitespace-nowrap px-6 py-4">
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
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                            {{ isset($notification['created_at']) ? \Carbon\Carbon::parse($notification['created_at'])->diffForHumans() : '—' }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-right">
                            <a href="{{ route('notifications.show', $notification['uuid'] ?? '') }}" class="inline-flex items-center gap-x-1 rounded-md px-2.5 py-1.5 text-xs font-medium text-indigo-600 hover:bg-indigo-50 transition-colors">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                View
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" /></svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No notifications found</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                @if(!empty(array_filter($filters ?? [])))
                                    No notifications match your filters. <a href="{{ route('notifications.index') }}" class="text-indigo-600 hover:text-indigo-500">Clear filters</a>
                                @else
                                    Get started by creating a new notification.
                                @endif
                            </p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    @if ($pagination && ($pagination['last_page'] ?? 1) > 1)
    <nav class="flex items-center justify-between">
        <p class="text-sm text-gray-500">
            Showing <span class="font-medium">{{ $pagination['from'] ?? 0 }}</span> to <span class="font-medium">{{ $pagination['to'] ?? 0 }}</span> of <span class="font-medium">{{ $pagination['total'] ?? 0 }}</span> notifications
        </p>
        <div class="flex gap-x-1">
            @if (($pagination['current_page'] ?? 1) > 1)
                <a href="{{ route('notifications.index', array_merge(request()->query(), ['page' => $pagination['current_page'] - 1])) }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Previous</a>
            @endif
            @if (($pagination['current_page'] ?? 1) < ($pagination['last_page'] ?? 1))
                <a href="{{ route('notifications.index', array_merge(request()->query(), ['page' => $pagination['current_page'] + 1])) }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Next</a>
            @endif
        </div>
    </nav>
    @endif
</div>
@endsection

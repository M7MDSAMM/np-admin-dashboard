@extends('layouts.app')

@section('title', 'Templates')

@section('content')
<div class="space-y-5">

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-gray-500">Manage reusable notification content across channels.</p>
        <a href="{{ route('templates.create') }}" class="inline-flex items-center gap-x-1.5 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            New Template
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('templates.index') }}" class="flex flex-wrap items-end gap-3">
        <div class="relative w-full max-w-xs">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
            <input type="text" name="key" value="{{ $filters['key'] ?? '' }}" placeholder="Search by key…" class="block w-full rounded-lg border border-gray-300 py-2 pl-9 pr-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none">
        </div>
        <select name="channel" class="rounded-lg border border-gray-300 py-2 pl-3 pr-8 text-sm text-gray-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none">
            <option value="">All channels</option>
            <option value="email"    @selected(($filters['channel'] ?? '') === 'email')>Email</option>
            <option value="whatsapp" @selected(($filters['channel'] ?? '') === 'whatsapp')>WhatsApp</option>
            <option value="push"     @selected(($filters['channel'] ?? '') === 'push')>Push</option>
        </select>
        <select name="is_active" class="rounded-lg border border-gray-300 py-2 pl-3 pr-8 text-sm text-gray-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none">
            <option value="">Any status</option>
            <option value="1" @selected(($filters['is_active'] ?? '') === '1')>Active</option>
            <option value="0" @selected(($filters['is_active'] ?? '') === '0')>Inactive</option>
        </select>
        <button type="submit" class="inline-flex items-center gap-x-1.5 rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700 transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" /></svg>
            Filter
        </button>
        @if (array_filter($filters ?? []))
            <a href="{{ route('templates.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Clear</a>
        @endif
    </form>

    {{-- Table --}}
    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Template</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Channel</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Version</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($templates as $template)
                    <tr class="hover:bg-gray-50 transition-colors" id="template-row-{{ $template['key'] }}" x-data="{ deleteLoading: false }">

                        {{-- Template key + name --}}
                        <td class="whitespace-nowrap px-6 py-4">
                            <div class="flex items-center gap-x-3">
                                <div class="h-9 w-9 rounded-lg bg-indigo-50 flex items-center justify-center flex-shrink-0">
                                    <svg class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $template['key'] }}</p>
                                    <p class="text-xs text-gray-500">{{ $template['name'] }}</p>
                                </div>
                            </div>
                        </td>

                        {{-- Channel badge — static classes so Tailwind includes them --}}
                        <td class="whitespace-nowrap px-6 py-4">
                            @if ($template['channel'] === 'email')
                                <span class="inline-flex items-center gap-x-1 rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700 ring-1 ring-blue-600/20">
                                    <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>Email
                                </span>
                            @elseif ($template['channel'] === 'whatsapp')
                                <span class="inline-flex items-center gap-x-1 rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-700 ring-1 ring-green-600/20">
                                    <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>WhatsApp
                                </span>
                            @else
                                <span class="inline-flex items-center gap-x-1 rounded-full bg-orange-50 px-2.5 py-0.5 text-xs font-medium text-orange-700 ring-1 ring-orange-600/20">
                                    <span class="h-1.5 w-1.5 rounded-full bg-orange-500"></span>Push
                                </span>
                            @endif
                        </td>

                        {{-- Active badge --}}
                        <td class="whitespace-nowrap px-6 py-4">
                            @if ($template['is_active'])
                                <span class="inline-flex items-center gap-x-1 rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-700 ring-1 ring-green-600/20">
                                    <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-x-1 rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-medium text-red-700 ring-1 ring-red-600/20">
                                    <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>Inactive
                                </span>
                            @endif
                        </td>

                        {{-- Version --}}
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                            {{ $template['version'] ?? '—' }}
                        </td>

                        {{-- Actions --}}
                        <td class="whitespace-nowrap px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-x-1">
                                <a href="{{ route('templates.show', $template['key']) }}" class="inline-flex items-center gap-x-1 rounded-md px-2.5 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100 transition-colors">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                    View
                                </a>
                                <a href="{{ route('templates.edit', $template['key']) }}" class="inline-flex items-center gap-x-1 rounded-md px-2.5 py-1.5 text-xs font-medium text-indigo-600 hover:bg-indigo-50 transition-colors">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                    Edit
                                </a>
                                <a href="{{ route('templates.render-preview', $template['key']) }}" class="inline-flex items-center gap-x-1 rounded-md px-2.5 py-1.5 text-xs font-medium text-emerald-600 hover:bg-emerald-50 transition-colors">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" /></svg>
                                    Preview
                                </a>
                                <button
                                    type="button"
                                    :disabled="deleteLoading"
                                    @click="
                                        Swal.fire({
                                            title: 'Delete template?',
                                            text: 'This action cannot be undone.',
                                            icon: 'warning',
                                            showCancelButton: true,
                                            confirmButtonColor: '#dc2626',
                                            cancelButtonColor: '#6b7280',
                                            confirmButtonText: 'Yes, delete',
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                deleteLoading = true;
                                                axios.delete('{{ route('templates.destroy', $template['key']) }}', {
                                                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
                                                })
                                                .then((res) => {
                                                    Toast.fire({ icon: 'success', title: res.data.message ?? 'Template deleted.' });
                                                    document.getElementById('template-row-{{ $template['key'] }}').remove();
                                                })
                                                .catch(() => {
                                                    Toast.fire({ icon: 'error', title: 'Failed to delete template.' });
                                                })
                                                .finally(() => { deleteLoading = false; });
                                            }
                                        });
                                    "
                                    class="inline-flex items-center gap-x-1 rounded-md px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 transition-colors disabled:opacity-50"
                                >
                                    <template x-if="deleteLoading">
                                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                    </template>
                                    <template x-if="!deleteLoading">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                    </template>
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-16 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                            <h3 class="mt-2 text-sm font-semibold text-gray-900">No templates found</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                @if (array_filter($filters ?? []))
                                    No templates match your filters. <a href="{{ route('templates.index') }}" class="text-indigo-600 hover:text-indigo-500">Clear filters</a>
                                @else
                                    Get started by creating your first template.
                                @endif
                            </p>
                            @unless (array_filter($filters ?? []))
                            <div class="mt-4">
                                <a href="{{ route('templates.create') }}" class="inline-flex items-center gap-x-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                    New Template
                                </a>
                            </div>
                            @endunless
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
            Showing <span class="font-medium">{{ $pagination['from'] ?? 0 }}</span>–<span class="font-medium">{{ $pagination['to'] ?? 0 }}</span> of <span class="font-medium">{{ $pagination['total'] ?? 0 }}</span> templates
        </p>
        <div class="flex gap-x-1">
            @if (($pagination['current_page'] ?? 1) > 1)
                <a href="{{ route('templates.index', array_merge(request()->query(), ['page' => $pagination['current_page'] - 1])) }}" class="inline-flex items-center gap-x-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
                    Previous
                </a>
            @endif
            @if (($pagination['current_page'] ?? 1) < ($pagination['last_page'] ?? 1))
                <a href="{{ route('templates.index', array_merge(request()->query(), ['page' => $pagination['current_page'] + 1])) }}" class="inline-flex items-center gap-x-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                    Next
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                </a>
            @endif
        </div>
    </nav>
    @endif

</div>
@endsection

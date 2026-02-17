@extends('layouts.app')
@section('title', 'Users')
@section('content')
<div class="space-y-4">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-gray-500">Manage notification recipient users.</p>
        <a href="{{ route('users.create') }}" class="inline-flex items-center gap-x-1.5 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Add User
        </a>
    </div>

    <form method="GET" action="{{ route('users.index') }}" class="flex items-center gap-x-3">
        <div class="relative flex-1 max-w-sm">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
            <input type="text" name="email" value="{{ $email }}" placeholder="Search by email..." class="block w-full rounded-lg border border-gray-300 py-2 pl-9 pr-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none">
        </div>
        @if ($email)<a href="{{ route('users.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Clear</a>@endif
    </form>

    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Locale / TZ</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($users as $user)
                    <tr class="hover:bg-gray-50" id="user-row-{{ $user['uuid'] }}" x-data="{ deleteLoading: false }">
                        <td class="whitespace-nowrap px-6 py-4">
                            <div class="flex items-center gap-x-3">
                                <div class="h-9 w-9 rounded-full bg-emerald-100 flex items-center justify-center">
                                    <span class="text-sm font-medium text-emerald-600">{{ strtoupper(substr($user['name'] ?? '', 0, 1)) }}</span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $user['name'] ?? '' }}</p>
                                    <p class="text-sm text-gray-500">{{ $user['email'] ?? '' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                            {{ $user['phone_e164'] ?? '—' }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                            {{ $user['locale'] ?? 'en' }} / {{ $user['timezone'] ?? 'UTC' }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4">
                            @if ($user['is_active'] ?? false)
                                <span class="inline-flex items-center gap-x-1 rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 bg-green-50 text-green-700 ring-green-600/20">
                                    <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-x-1 rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 bg-red-50 text-red-700 ring-red-600/20">
                                    <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>Inactive
                                </span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-x-1">
                                <a href="{{ route('users.show', $user['uuid']) }}" class="inline-flex items-center gap-x-1 rounded-md px-2.5 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100 transition-colors">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                    View
                                </a>
                                <a href="{{ route('users.edit', $user['uuid']) }}" class="inline-flex items-center gap-x-1 rounded-md px-2.5 py-1.5 text-xs font-medium text-indigo-600 hover:bg-indigo-50 transition-colors">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                    Edit
                                </a>
                                <button
                                    type="button"
                                    :disabled="deleteLoading"
                                    @click="
                                        Swal.fire({
                                            title: 'Delete User?',
                                            text: 'This action cannot be undone.',
                                            icon: 'warning',
                                            showCancelButton: true,
                                            confirmButtonColor: '#dc2626',
                                            cancelButtonColor: '#6b7280',
                                            confirmButtonText: 'Yes, delete',
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                deleteLoading = true;
                                                axios.delete('{{ route('users.destroy', $user['uuid']) }}')
                                                    .then((res) => {
                                                        Toast.fire({ icon: 'success', title: res.data.message });
                                                        document.getElementById('user-row-{{ $user['uuid'] }}').remove();
                                                    })
                                                    .catch(() => {
                                                        Toast.fire({ icon: 'error', title: 'Failed to delete user.' });
                                                    })
                                                    .finally(() => { deleteLoading = false; });
                                            }
                                        });
                                    "
                                    class="inline-flex items-center gap-x-1 rounded-md px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 transition-colors"
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
                        <td colspan="5" class="px-6 py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" /></svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No users found</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                @if ($email)
                                    No users match your search. <a href="{{ route('users.index') }}" class="text-indigo-600 hover:text-indigo-500">Clear search</a>
                                @else
                                    Get started by adding a new user.
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
            Showing <span class="font-medium">{{ $pagination['from'] ?? 0 }}</span> to <span class="font-medium">{{ $pagination['to'] ?? 0 }}</span> of <span class="font-medium">{{ $pagination['total'] ?? 0 }}</span> users
        </p>
        <div class="flex gap-x-1">
            @if (($pagination['current_page'] ?? 1) > 1)
                <a href="{{ route('users.index', array_merge(request()->query(), ['page' => $pagination['current_page'] - 1])) }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Previous</a>
            @endif
            @if (($pagination['current_page'] ?? 1) < ($pagination['last_page'] ?? 1))
                <a href="{{ route('users.index', array_merge(request()->query(), ['page' => $pagination['current_page'] + 1])) }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Next</a>
            @endif
        </div>
    </nav>
    @endif
</div>
@endsection

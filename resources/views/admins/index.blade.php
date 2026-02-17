@extends('layouts.app')
@section('title', 'Admin Management')
@section('content')
<div class="space-y-4">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-gray-500">Manage administrator accounts for the platform.</p>
        <a href="{{ route('admins.create') }}" class="inline-flex items-center gap-x-1.5 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Add Admin
        </a>
    </div>

    <form method="GET" action="{{ route('admins.index') }}" class="flex items-center gap-x-3">
        <div class="relative flex-1 max-w-sm">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
            <input type="text" name="search" value="{{ $search }}" placeholder="Search by name or email..." class="block w-full rounded-lg border border-gray-300 py-2 pl-9 pr-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none">
        </div>
        @if ($search)<a href="{{ route('admins.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Clear</a>@endif
    </form>

    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Admin</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Last Login</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($admins as $admin)
                    <tr class="hover:bg-gray-50" id="admin-row-{{ $admin['uuid'] }}"
                        x-data="{
                            isActive: {{ ($admin['is_active'] ?? false) ? 'true' : 'false' }},
                            toggleLoading: false,
                            deleteLoading: false,
                        }">
                        <td class="whitespace-nowrap px-6 py-4">
                            <div class="flex items-center gap-x-3">
                                <div class="h-9 w-9 rounded-full bg-indigo-100 flex items-center justify-center">
                                    <span class="text-sm font-medium text-indigo-600">{{ strtoupper(substr($admin['name'] ?? '', 0, 1)) }}</span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $admin['name'] ?? '' }}
                                        @if ($admin['uuid'] === $currentAdmin['uuid'])
                                            <span class="ml-1 inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500">You</span>
                                        @endif
                                    </p>
                                    <p class="text-sm text-gray-500">{{ $admin['email'] ?? '' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 {{ ($admin['role'] ?? '') === 'super_admin' ? 'bg-purple-50 text-purple-700 ring-purple-600/20' : 'bg-blue-50 text-blue-700 ring-blue-600/20' }}">
                                {{ $admin['role'] === 'super_admin' ? 'Super Admin' : 'Admin' }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4">
                            <span
                                x-show="isActive"
                                class="inline-flex items-center gap-x-1 rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 bg-green-50 text-green-700 ring-green-600/20">
                                <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                                Active
                            </span>
                            <span
                                x-show="!isActive"
                                x-cloak
                                class="inline-flex items-center gap-x-1 rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 bg-red-50 text-red-700 ring-red-600/20">
                                <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                Inactive
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                            {{ !empty($admin['last_login_at']) ? \Carbon\Carbon::parse($admin['last_login_at'])->diffForHumans() : 'Never' }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-right">
                            @if ($admin['uuid'] === $currentAdmin['uuid'])
                                <span class="text-xs text-gray-400 italic">Current account</span>
                            @else
                            <div class="flex items-center justify-end gap-x-1">
                                <a href="{{ route('admins.edit', $admin['uuid']) }}" class="inline-flex items-center gap-x-1 rounded-md px-2.5 py-1.5 text-xs font-medium text-indigo-600 hover:bg-indigo-50 transition-colors">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                    Edit
                                </a>
                                <button
                                    type="button"
                                    :disabled="toggleLoading"
                                    @click="
                                        let action = isActive ? 'deactivate' : 'activate';
                                        Swal.fire({
                                            title: 'Are you sure?',
                                            text: 'Do you want to ' + action + ' this admin?',
                                            icon: 'warning',
                                            showCancelButton: true,
                                            confirmButtonColor: '#4f46e5',
                                            cancelButtonColor: '#6b7280',
                                            confirmButtonText: 'Yes, ' + action,
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                toggleLoading = true;
                                                axios.patch('{{ route('admins.toggle-active', $admin['uuid']) }}')
                                                    .then((res) => {
                                                        isActive = res.data.data.is_active;
                                                        Toast.fire({ icon: 'success', title: res.data.message });
                                                    })
                                                    .catch(() => {
                                                        Toast.fire({ icon: 'error', title: 'Failed to toggle admin status.' });
                                                    })
                                                    .finally(() => { toggleLoading = false; });
                                            }
                                        });
                                    "
                                    class="inline-flex items-center gap-x-1 rounded-md px-2.5 py-1.5 text-xs font-medium transition-colors"
                                    :class="isActive ? 'text-amber-600 hover:bg-amber-50' : 'text-green-600 hover:bg-green-50'"
                                >
                                    <template x-if="toggleLoading">
                                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                    </template>
                                    <template x-if="!toggleLoading && isActive">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                    </template>
                                    <template x-if="!toggleLoading && !isActive">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                    </template>
                                    <span x-text="isActive ? 'Deactivate' : 'Activate'"></span>
                                </button>
                                <button
                                    type="button"
                                    :disabled="deleteLoading"
                                    @click="
                                        Swal.fire({
                                            title: 'Delete Admin?',
                                            text: 'This action cannot be undone.',
                                            icon: 'warning',
                                            showCancelButton: true,
                                            confirmButtonColor: '#dc2626',
                                            cancelButtonColor: '#6b7280',
                                            confirmButtonText: 'Yes, delete',
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                deleteLoading = true;
                                                axios.delete('{{ route('admins.destroy', $admin['uuid']) }}')
                                                    .then((res) => {
                                                        Toast.fire({ icon: 'success', title: res.data.message });
                                                        document.getElementById('admin-row-{{ $admin['uuid'] }}').remove();
                                                    })
                                                    .catch(() => {
                                                        Toast.fire({ icon: 'error', title: 'Failed to delete admin.' });
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
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No admins found</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                @if ($search)
                                    No admins match your search. <a href="{{ route('admins.index') }}" class="text-indigo-600 hover:text-indigo-500">Clear search</a>
                                @else
                                    Get started by creating a new admin.
                                @endif
                            </p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

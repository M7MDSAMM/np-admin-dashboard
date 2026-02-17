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
                    <tr class="hover:bg-gray-50">
                        <td class="whitespace-nowrap px-6 py-4">
                            <div class="flex items-center gap-x-3">
                                <div class="h-9 w-9 rounded-full bg-indigo-100 flex items-center justify-center">
                                    <span class="text-sm font-medium text-indigo-600">{{ strtoupper(substr($admin['name'] ?? '', 0, 1)) }}</span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $admin['name'] ?? '' }}</p>
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
                            <span class="inline-flex items-center gap-x-1 rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 {{ ($admin['is_active'] ?? false) ? 'bg-green-50 text-green-700 ring-green-600/20' : 'bg-red-50 text-red-700 ring-red-600/20' }}">
                                <span class="h-1.5 w-1.5 rounded-full {{ ($admin['is_active'] ?? false) ? 'bg-green-500' : 'bg-red-500' }}"></span>
                                {{ ($admin['is_active'] ?? false) ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                            {{ !empty($admin['last_login_at']) ? \Carbon\Carbon::parse($admin['last_login_at'])->diffForHumans() : 'Never' }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-right" x-data="{ open: false }">
                            <div class="relative inline-block text-left">
                                <button @click="open = !open" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 12.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 18.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5Z" /></svg>
                                </button>
                                <div x-show="open" @click.outside="open = false" x-transition class="absolute right-0 z-10 mt-1 w-44 rounded-lg bg-white py-1 shadow-lg ring-1 ring-gray-900/5" x-cloak>
                                    <a href="{{ route('admins.edit', $admin['uuid']) }}" class="flex items-center gap-x-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Edit</a>
                                    <form method="POST" action="{{ route('admins.toggle-active', $admin['uuid']) }}">@csrf @method('PATCH')
                                        <button type="submit" class="flex w-full items-center gap-x-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                            {{ ($admin['is_active'] ?? false) ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                    <div class="border-t border-gray-100 my-1"></div>
                                    <form method="POST" action="{{ route('admins.destroy', $admin['uuid']) }}" x-data @submit.prevent="if (confirm('Are you sure you want to delete this admin?')) $el.submit()">@csrf @method('DELETE')
                                        <button type="submit" class="flex w-full items-center gap-x-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50">Delete</button>
                                    </form>
                                </div>
                            </div>
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

@extends('layouts.app')
@section('title', 'Edit Admin')
@section('content')
<div class="mx-auto max-w-2xl">
    <div class="mb-6">
        <a href="{{ route('admins.index') }}" class="inline-flex items-center gap-x-1 text-sm text-gray-500 hover:text-gray-700">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            Back to Admins
        </a>
    </div>
    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5">
        <div class="px-6 py-5 border-b border-gray-100">
            <h3 class="text-base font-semibold text-gray-900">Edit Admin</h3>
            <p class="mt-1 text-sm text-gray-500">Leave password blank to keep current password.</p>
        </div>
        <form method="POST" action="{{ route('admins.update', $admin['uuid']) }}" class="px-6 py-6 space-y-5">
            @csrf @method('PUT')
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                <input type="text" name="name" id="name" value="{{ old('name', $admin['name']) }}" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none sm:text-sm">
                @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                <input type="email" name="email" id="email" value="{{ old('email', $admin['email']) }}" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none sm:text-sm">
                @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                <select name="role" id="role" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none sm:text-sm">
                    <option value="admin" {{ old('role', $admin['role']) === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="super_admin" {{ old('role', $admin['role']) === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                </select>
                @error('role')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                <input type="password" name="password" id="password" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none sm:text-sm" placeholder="Leave blank to keep current">
                @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none sm:text-sm">
            </div>
            <div class="rounded-lg bg-gray-50 px-4 py-3 border border-gray-200">
                <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                    <dt class="font-medium text-gray-500">UUID</dt>
                    <dd class="text-gray-900 font-mono text-xs">{{ $admin['uuid'] }}</dd>
                    <dt class="font-medium text-gray-500">Status</dt>
                    <dd>
                        <span class="inline-flex items-center gap-x-1 {{ ($admin['is_active'] ?? false) ? 'text-green-700' : 'text-red-700' }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ ($admin['is_active'] ?? false) ? 'bg-green-500' : 'bg-red-500' }}"></span>
                            {{ ($admin['is_active'] ?? false) ? 'Active' : 'Inactive' }}
                        </span>
                    </dd>
                    <dt class="font-medium text-gray-500">Created</dt>
                    <dd class="text-gray-900">{{ $admin['created_at'] ?? 'N/A' }}</dd>
                </dl>
            </div>
            <div class="flex items-center justify-end gap-x-3 pt-4 border-t border-gray-100">
                <a href="{{ route('admins.index') }}" class="rounded-lg px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">Update Admin</button>
            </div>
        </form>
    </div>
</div>
@endsection

@extends('layouts.app')
@section('title', 'Edit Admin')
@section('content')
<div class="mx-auto max-w-2xl" x-data="adminEditForm()">
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
        <form @submit.prevent="submit" class="px-6 py-6 space-y-5">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                <input type="text" id="name" x-model="form.name" class="mt-1 block w-full rounded-lg border px-3 py-2 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none sm:text-sm" :class="errors.name ? 'border-red-300' : 'border-gray-300'">
                <template x-if="errors.name">
                    <p class="mt-1 text-sm text-red-600" x-text="errors.name[0]"></p>
                </template>
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                <input type="email" id="email" x-model="form.email" class="mt-1 block w-full rounded-lg border px-3 py-2 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none sm:text-sm" :class="errors.email ? 'border-red-300' : 'border-gray-300'">
                <template x-if="errors.email">
                    <p class="mt-1 text-sm text-red-600" x-text="errors.email[0]"></p>
                </template>
            </div>
            <div>
                <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                <select id="role" x-model="form.role" class="mt-1 block w-full rounded-lg border px-3 py-2 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none sm:text-sm" :class="errors.role ? 'border-red-300' : 'border-gray-300'">
                    <option value="admin">Admin</option>
                    <option value="super_admin">Super Admin</option>
                </select>
                <template x-if="errors.role">
                    <p class="mt-1 text-sm text-red-600" x-text="errors.role[0]"></p>
                </template>
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                <input type="password" id="password" x-model="form.password" placeholder="Leave blank to keep current" class="mt-1 block w-full rounded-lg border px-3 py-2 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none sm:text-sm" :class="errors.password ? 'border-red-300' : 'border-gray-300'">
                <template x-if="errors.password">
                    <p class="mt-1 text-sm text-red-600" x-text="errors.password[0]"></p>
                </template>
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                <input type="password" id="password_confirmation" x-model="form.password_confirmation" class="mt-1 block w-full rounded-lg border px-3 py-2 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none sm:text-sm" :class="errors.password_confirmation ? 'border-red-300' : 'border-gray-300'">
                <template x-if="errors.password_confirmation">
                    <p class="mt-1 text-sm text-red-600" x-text="errors.password_confirmation[0]"></p>
                </template>
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
                <button type="submit" :disabled="loading" class="inline-flex items-center gap-x-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg x-show="loading" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    <span x-text="loading ? 'Updating...' : 'Update Admin'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function adminEditForm() {
    return {
        loading: false,
        form: {
            name: @js($admin['name'] ?? ''),
            email: @js($admin['email'] ?? ''),
            role: @js($admin['role'] ?? 'admin'),
            password: '',
            password_confirmation: '',
        },
        errors: {},

        validate() {
            this.errors = {};
            if (!this.form.name.trim()) this.errors.name = ['The name field is required.'];
            else if (this.form.name.length > 150) this.errors.name = ['The name must not exceed 150 characters.'];

            if (!this.form.email.trim()) this.errors.email = ['The email field is required.'];
            else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.form.email)) this.errors.email = ['Please enter a valid email address.'];

            if (!this.form.role) this.errors.role = ['The role field is required.'];

            if (this.form.password) {
                if (this.form.password.length < 8) this.errors.password = ['The password must be at least 8 characters.'];
                if (this.form.password !== this.form.password_confirmation) {
                    this.errors.password_confirmation = ['The password confirmation does not match.'];
                }
            }

            return Object.keys(this.errors).length === 0;
        },

        submit() {
            if (!this.validate()) return;

            this.loading = true;
            const data = { ...this.form, _method: 'PUT' };

            axios.post('{{ route("admins.update", $admin["uuid"]) }}', data)
                .then((res) => {
                    Toast.fire({ icon: 'success', title: res.data.message });
                    setTimeout(() => { window.location.href = '{{ route("admins.index") }}'; }, 1000);
                })
                .catch((err) => {
                    if (err.response && err.response.status === 422) {
                        this.errors = err.response.data.errors || {};
                        if (err.response.data.message) {
                            Toast.fire({ icon: 'error', title: err.response.data.message });
                        }
                    } else {
                        Toast.fire({ icon: 'error', title: 'An unexpected error occurred.' });
                    }
                })
                .finally(() => { this.loading = false; });
        },
    };
}
</script>
@endsection

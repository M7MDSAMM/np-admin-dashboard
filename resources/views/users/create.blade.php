@extends('layouts.app')
@section('title', 'Create User')
@section('content')
<div class="mx-auto max-w-2xl" x-data="userCreateForm()">
    <div class="mb-6">
        <a href="{{ route('users.index') }}" class="inline-flex items-center gap-x-1 text-sm text-gray-500 hover:text-gray-700">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            Back to Users
        </a>
    </div>
    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5">
        <div class="px-6 py-5 border-b border-gray-100">
            <h3 class="text-base font-semibold text-gray-900">Create New User</h3>
            <p class="mt-1 text-sm text-gray-500">Add a new notification recipient user.</p>
        </div>
        <form @submit.prevent="submit" class="px-6 py-6 space-y-5">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                <input type="text" id="name" x-model="form.name" class="mt-1 block w-full rounded-lg border px-3 py-2 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none sm:text-sm" :class="errors.name ? 'border-red-300' : 'border-gray-300'">
                <template x-if="errors.name">
                    <p class="mt-1 text-sm text-red-600" x-text="errors.name[0]"></p>
                </template>
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                <input type="email" id="email" x-model="form.email" class="mt-1 block w-full rounded-lg border px-3 py-2 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none sm:text-sm" :class="errors.email ? 'border-red-300' : 'border-gray-300'">
                <template x-if="errors.email">
                    <p class="mt-1 text-sm text-red-600" x-text="errors.email[0]"></p>
                </template>
            </div>
            <div>
                <label for="phone_e164" class="block text-sm font-medium text-gray-700">Phone (E.164)</label>
                <input type="text" id="phone_e164" x-model="form.phone_e164" placeholder="+1234567890" class="mt-1 block w-full rounded-lg border px-3 py-2 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none sm:text-sm" :class="errors.phone_e164 ? 'border-red-300' : 'border-gray-300'">
                <template x-if="errors.phone_e164">
                    <p class="mt-1 text-sm text-red-600" x-text="errors.phone_e164[0]"></p>
                </template>
            </div>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label for="locale" class="block text-sm font-medium text-gray-700">Locale</label>
                    <input type="text" id="locale" x-model="form.locale" placeholder="en" class="mt-1 block w-full rounded-lg border px-3 py-2 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none sm:text-sm" :class="errors.locale ? 'border-red-300' : 'border-gray-300'">
                    <template x-if="errors.locale">
                        <p class="mt-1 text-sm text-red-600" x-text="errors.locale[0]"></p>
                    </template>
                </div>
                <div>
                    <label for="timezone" class="block text-sm font-medium text-gray-700">Timezone</label>
                    <input type="text" id="timezone" x-model="form.timezone" placeholder="UTC" class="mt-1 block w-full rounded-lg border px-3 py-2 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none sm:text-sm" :class="errors.timezone ? 'border-red-300' : 'border-gray-300'">
                    <template x-if="errors.timezone">
                        <p class="mt-1 text-sm text-red-600" x-text="errors.timezone[0]"></p>
                    </template>
                </div>
            </div>
            <div class="flex items-center gap-x-3">
                <button type="button" @click="form.is_active = !form.is_active" :class="form.is_active ? 'bg-indigo-600' : 'bg-gray-200'" class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2">
                    <span :class="form.is_active ? 'translate-x-5' : 'translate-x-0'" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                </button>
                <label class="text-sm font-medium text-gray-700">Active</label>
            </div>
            <div class="flex items-center justify-end gap-x-3 pt-4 border-t border-gray-100">
                <a href="{{ route('users.index') }}" class="rounded-lg px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</a>
                <button type="submit" :disabled="loading" class="inline-flex items-center gap-x-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg x-show="loading" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    <span x-text="loading ? 'Creating...' : 'Create User'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function userCreateForm() {
    return {
        loading: false,
        form: { name: '', email: '', phone_e164: '', locale: 'en', timezone: 'UTC', is_active: true },
        errors: {},

        validate() {
            this.errors = {};
            if (!this.form.name.trim()) this.errors.name = ['The name field is required.'];
            else if (this.form.name.length > 150) this.errors.name = ['The name must not exceed 150 characters.'];

            if (!this.form.email.trim()) this.errors.email = ['The email field is required.'];
            else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.form.email)) this.errors.email = ['Please enter a valid email address.'];

            if (this.form.phone_e164 && !/^\+[1-9]\d{1,14}$/.test(this.form.phone_e164)) {
                this.errors.phone_e164 = ['Phone must be in E.164 format (e.g. +1234567890).'];
            }

            return Object.keys(this.errors).length === 0;
        },

        submit() {
            if (!this.validate()) return;

            this.loading = true;
            axios.post('{{ route("users.store") }}', this.form)
                .then((res) => {
                    Toast.fire({ icon: 'success', title: res.data.message });
                    setTimeout(() => { window.location.href = '{{ route("users.index") }}'; }, 1000);
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

@extends('layouts.app')
@section('title', 'User Preferences')
@section('content')
<div class="mx-auto max-w-2xl" x-data="preferencesForm()">
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('users.show', $user['uuid']) }}" class="inline-flex items-center gap-x-1 text-sm text-gray-500 hover:text-gray-700">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            Back to {{ $user['name'] ?? 'User' }}
        </a>
        <a href="{{ route('users.devices', $user['uuid']) }}" class="inline-flex items-center gap-x-1.5 text-sm text-indigo-600 hover:text-indigo-500">
            Devices
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
        </a>
    </div>

    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5">
        <div class="px-6 py-5 border-b border-gray-100">
            <div class="flex items-center gap-x-3">
                <div class="h-10 w-10 rounded-full bg-emerald-100 flex items-center justify-center">
                    <span class="text-sm font-medium text-emerald-600">{{ strtoupper(substr($user['name'] ?? '', 0, 1)) }}</span>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Notification Preferences</h3>
                    <p class="text-sm text-gray-500">{{ $user['name'] ?? '' }} &mdash; {{ $user['email'] ?? '' }}</p>
                </div>
            </div>
        </div>

        <form @submit.prevent="submit" class="divide-y divide-gray-100">
            {{-- Channel toggles --}}
            <div class="px-6 py-5 space-y-4">
                <h4 class="text-sm font-semibold text-gray-900">Notification Channels</h4>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-700">Email</p>
                            <p class="text-xs text-gray-500">Receive notifications via email</p>
                        </div>
                        <button type="button" @click="form.channel_email = !form.channel_email" :class="form.channel_email ? 'bg-indigo-600' : 'bg-gray-200'" class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2">
                            <span :class="form.channel_email ? 'translate-x-5' : 'translate-x-0'" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                        </button>
                    </div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-700">WhatsApp</p>
                            <p class="text-xs text-gray-500">Receive notifications via WhatsApp</p>
                        </div>
                        <button type="button" @click="form.channel_whatsapp = !form.channel_whatsapp" :class="form.channel_whatsapp ? 'bg-indigo-600' : 'bg-gray-200'" class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2">
                            <span :class="form.channel_whatsapp ? 'translate-x-5' : 'translate-x-0'" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                        </button>
                    </div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-700">Push Notifications</p>
                            <p class="text-xs text-gray-500">Receive push notifications on devices</p>
                        </div>
                        <button type="button" @click="form.channel_push = !form.channel_push" :class="form.channel_push ? 'bg-indigo-600' : 'bg-gray-200'" class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2">
                            <span :class="form.channel_push ? 'translate-x-5' : 'translate-x-0'" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Rate limit --}}
            <div class="px-6 py-5">
                <h4 class="text-sm font-semibold text-gray-900 mb-3">Rate Limiting</h4>
                <div>
                    <label for="rate_limit" class="block text-sm font-medium text-gray-700">Max notifications per minute</label>
                    <input type="number" id="rate_limit" x-model.number="form.rate_limit_per_minute" min="1" max="60" class="mt-1 block w-32 rounded-lg border px-3 py-2 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none sm:text-sm" :class="errors.rate_limit_per_minute ? 'border-red-300' : 'border-gray-300'">
                    <template x-if="errors.rate_limit_per_minute">
                        <p class="mt-1 text-sm text-red-600" x-text="errors.rate_limit_per_minute[0]"></p>
                    </template>
                </div>
            </div>

            {{-- Quiet hours --}}
            <div class="px-6 py-5">
                <h4 class="text-sm font-semibold text-gray-900 mb-3">Quiet Hours</h4>
                <p class="text-xs text-gray-500 mb-3">Suppress notifications during these hours. Leave empty to disable.</p>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="quiet_start" class="block text-sm font-medium text-gray-700">Start</label>
                        <input type="time" id="quiet_start" x-model="form.quiet_hours_start" class="mt-1 block w-full rounded-lg border px-3 py-2 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none sm:text-sm" :class="errors.quiet_hours_start ? 'border-red-300' : 'border-gray-300'">
                        <template x-if="errors.quiet_hours_start">
                            <p class="mt-1 text-sm text-red-600" x-text="errors.quiet_hours_start[0]"></p>
                        </template>
                    </div>
                    <div>
                        <label for="quiet_end" class="block text-sm font-medium text-gray-700">End</label>
                        <input type="time" id="quiet_end" x-model="form.quiet_hours_end" class="mt-1 block w-full rounded-lg border px-3 py-2 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none sm:text-sm" :class="errors.quiet_hours_end ? 'border-red-300' : 'border-gray-300'">
                        <template x-if="errors.quiet_hours_end">
                            <p class="mt-1 text-sm text-red-600" x-text="errors.quiet_hours_end[0]"></p>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="px-6 py-4 flex items-center justify-end">
                <button type="submit" :disabled="loading" class="inline-flex items-center gap-x-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg x-show="loading" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    <span x-text="loading ? 'Saving...' : 'Save Preferences'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function preferencesForm() {
    return {
        loading: false,
        form: {
            channel_email: @js((bool) ($prefs['channel_email'] ?? true)),
            channel_whatsapp: @js((bool) ($prefs['channel_whatsapp'] ?? false)),
            channel_push: @js((bool) ($prefs['channel_push'] ?? false)),
            rate_limit_per_minute: @js((int) ($prefs['rate_limit_per_minute'] ?? 5)),
            quiet_hours_start: @js($prefs['quiet_hours_start'] ?? ''),
            quiet_hours_end: @js($prefs['quiet_hours_end'] ?? ''),
        },
        errors: {},

        validate() {
            this.errors = {};
            const rl = this.form.rate_limit_per_minute;
            if (!rl || rl < 1 || rl > 60) {
                this.errors.rate_limit_per_minute = ['Rate limit must be between 1 and 60.'];
            }
            return Object.keys(this.errors).length === 0;
        },

        submit() {
            if (!this.validate()) return;

            this.loading = true;
            const data = { ...this.form, _method: 'PUT' };

            axios.post('{{ route("users.preferences.update", $user["uuid"]) }}', data)
                .then((res) => {
                    Toast.fire({ icon: 'success', title: res.data.message });
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

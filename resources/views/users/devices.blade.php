@extends('layouts.app')
@section('title', 'User Devices')
@section('content')
<div class="mx-auto max-w-3xl space-y-4" x-data="devicesPage()">
    <div class="flex items-center justify-between">
        <a href="{{ route('users.show', $user['uuid']) }}" class="inline-flex items-center gap-x-1 text-sm text-gray-500 hover:text-gray-700">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            Back to {{ $user['name'] ?? 'User' }}
        </a>
        <div class="flex items-center gap-x-2">
            <a href="{{ route('users.preferences', $user['uuid']) }}" class="inline-flex items-center gap-x-1.5 text-sm text-indigo-600 hover:text-indigo-500">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
                Preferences
            </a>
            <button @click="showModal = true" class="inline-flex items-center gap-x-1.5 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                Add Device
            </button>
        </div>
    </div>

    {{-- User header --}}
    <div class="rounded-xl bg-white px-6 py-4 shadow-sm ring-1 ring-gray-900/5">
        <div class="flex items-center gap-x-3">
            <div class="h-10 w-10 rounded-full bg-emerald-100 flex items-center justify-center">
                <span class="text-sm font-medium text-emerald-600">{{ strtoupper(substr($user['name'] ?? '', 0, 1)) }}</span>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-900">{{ $user['name'] ?? '' }}</h3>
                <p class="text-sm text-gray-500">{{ $user['email'] ?? '' }}</p>
            </div>
            <span class="ml-auto inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600" x-text="devices.length + ' device(s)'"></span>
        </div>
    </div>

    {{-- Devices table --}}
    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Token</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Platform</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Added</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <template x-for="device in devices" :key="device.uuid">
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <code class="text-xs text-gray-700 bg-gray-100 px-2 py-1 rounded font-mono break-all" x-text="device.token.length > 40 ? device.token.substring(0, 40) + '...' : device.token"></code>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1"
                                    :class="{
                                        'bg-green-50 text-green-700 ring-green-600/20': device.platform === 'android',
                                        'bg-blue-50 text-blue-700 ring-blue-600/20': device.platform === 'ios',
                                        'bg-purple-50 text-purple-700 ring-purple-600/20': device.platform === 'web',
                                        'bg-gray-50 text-gray-700 ring-gray-600/20': !device.platform,
                                    }"
                                    x-text="device.platform ? device.platform.charAt(0).toUpperCase() + device.platform.slice(1) : 'Unknown'"></span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500" x-text="device.created_at ? new Date(device.created_at).toLocaleDateString() : '—'"></td>
                            <td class="whitespace-nowrap px-6 py-4 text-right">
                                <button
                                    type="button"
                                    @click="deleteDevice(device.uuid)"
                                    class="inline-flex items-center gap-x-1 rounded-md px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 transition-colors"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                    Remove
                                </button>
                            </td>
                        </tr>
                    </template>
                    <template x-if="devices.length === 0">
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" /></svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No devices registered</h3>
                                <p class="mt-1 text-sm text-gray-500">Add a device token to enable push notifications.</p>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Add Device Modal --}}
    <div x-show="showModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-600/75" x-cloak>
        <div @click.outside="showModal = false; resetForm()" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="w-full max-w-md rounded-xl bg-white shadow-xl">
            <div class="px-6 py-5 border-b border-gray-100">
                <h3 class="text-base font-semibold text-gray-900">Add Device</h3>
                <p class="mt-1 text-sm text-gray-500">Register a new push notification token.</p>
            </div>
            <form @submit.prevent="addDevice" class="px-6 py-5 space-y-4">
                <div>
                    <label for="device_token" class="block text-sm font-medium text-gray-700">Device Token</label>
                    <input type="text" id="device_token" x-model="newDevice.token" placeholder="FCM device token" class="mt-1 block w-full rounded-lg border px-3 py-2 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none sm:text-sm" :class="modalErrors.token ? 'border-red-300' : 'border-gray-300'">
                    <template x-if="modalErrors.token">
                        <p class="mt-1 text-sm text-red-600" x-text="modalErrors.token[0]"></p>
                    </template>
                </div>
                <div>
                    <label for="device_platform" class="block text-sm font-medium text-gray-700">Platform</label>
                    <select id="device_platform" x-model="newDevice.platform" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none sm:text-sm">
                        <option value="android">Android</option>
                        <option value="ios">iOS</option>
                        <option value="web">Web</option>
                    </select>
                </div>
                <div class="flex items-center justify-end gap-x-3 pt-3">
                    <button type="button" @click="showModal = false; resetForm()" class="rounded-lg px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" :disabled="addLoading" class="inline-flex items-center gap-x-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg x-show="addLoading" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        <span x-text="addLoading ? 'Adding...' : 'Add Device'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function devicesPage() {
    return {
        devices: @js($devices ?? []),
        showModal: false,
        addLoading: false,
        newDevice: { token: '', platform: 'android' },
        modalErrors: {},

        resetForm() {
            this.newDevice = { token: '', platform: 'android' };
            this.modalErrors = {};
        },

        addDevice() {
            this.modalErrors = {};
            if (!this.newDevice.token.trim()) {
                this.modalErrors.token = ['The token field is required.'];
                return;
            }

            this.addLoading = true;
            axios.post('{{ route("users.devices.store", $user["uuid"]) }}', this.newDevice)
                .then((res) => {
                    Toast.fire({ icon: 'success', title: res.data.message });
                    // Reload page to get fresh device list
                    window.location.reload();
                })
                .catch((err) => {
                    if (err.response && err.response.status === 422) {
                        this.modalErrors = err.response.data.errors || {};
                        if (err.response.data.message) {
                            Toast.fire({ icon: 'error', title: err.response.data.message });
                        }
                    } else {
                        Toast.fire({ icon: 'error', title: 'Failed to add device.' });
                    }
                })
                .finally(() => { this.addLoading = false; });
        },

        deleteDevice(deviceUuid) {
            Swal.fire({
                title: 'Remove Device?',
                text: 'This device will no longer receive push notifications.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, remove',
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.delete('{{ url("users/" . $user["uuid"] . "/devices") }}/' + deviceUuid)
                        .then((res) => {
                            Toast.fire({ icon: 'success', title: res.data.message });
                            this.devices = this.devices.filter(d => d.uuid !== deviceUuid);
                        })
                        .catch(() => {
                            Toast.fire({ icon: 'error', title: 'Failed to remove device.' });
                        });
                }
            });
        },
    };
}
</script>
@endsection

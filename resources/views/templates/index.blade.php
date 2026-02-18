@extends('layouts.app')

@section('title', 'Templates')

@section('content')
<div class="flex flex-col gap-4">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Templates</h2>
            <p class="text-sm text-gray-500">Manage reusable content across channels.</p>
        </div>
        <a href="{{ route('templates.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500">
            <span>＋</span> New Template
        </a>
    </div>

    <form method="GET" class="grid gap-3 rounded-lg bg-white p-4 shadow-sm md:grid-cols-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Key / Name</label>
            <input type="text" name="key" value="{{ $filters['key'] ?? '' }}" placeholder="welcome_email" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Channel</label>
            <select name="channel" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Any</option>
                <option value="email" @selected(($filters['channel'] ?? '') === 'email')>Email</option>
                <option value="whatsapp" @selected(($filters['channel'] ?? '') === 'whatsapp')>WhatsApp</option>
                <option value="push" @selected(($filters['channel'] ?? '') === 'push')>Push</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Active</label>
            <select name="is_active" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Any</option>
                <option value="1" @selected(($filters['is_active'] ?? '') === '1')>Active</option>
                <option value="0" @selected(($filters['is_active'] ?? '') === '0')>Inactive</option>
            </select>
        </div>
        <div class="flex items-end">
            <button class="w-full rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-gray-800">Filter</button>
        </div>
    </form>

    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Key</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Name</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Channel</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Active</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Version</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($templates as $template)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $template['key'] }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $template['name'] }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-semibold text-indigo-600">{{ ucfirst($template['channel']) }}</span>
                    </td>
                    <td class="px-4 py-3">
                        @if ($template['is_active'])
                            <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-600">Active</span>
                        @else
                            <span class="inline-flex rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-600">Inactive</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $template['version'] ?? '—' }}</td>
                    <td class="px-4 py-3 text-right text-sm">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('templates.show', $template['key']) }}" class="text-indigo-600 hover:text-indigo-500">View</a>
                            <a href="{{ route('templates.edit', $template['key']) }}" class="text-gray-700 hover:text-gray-900">Edit</a>
                            <a href="{{ route('templates.render-preview', $template['key']) }}" class="text-emerald-600 hover:text-emerald-500">Preview</a>
                            <form method="POST" action="{{ route('templates.destroy', $template['key']) }}" onsubmit="return confirm('Delete template?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:text-red-500">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">No templates found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full" x-data="{ sidebarOpen: false }">
<div class="min-h-full">
    {{-- Mobile overlay --}}
    <div x-show="sidebarOpen" x-transition:enter="transition-opacity duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-40 bg-gray-600/75 lg:hidden" @click="sidebarOpen = false" x-cloak></div>

    {{-- Sidebar --}}
    <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-gray-900 transform transition-transform duration-300 lg:translate-x-0" :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" @click.outside="sidebarOpen = false">
        <div class="flex h-16 items-center gap-x-3 px-6 border-b border-gray-800">
            <div class="h-8 w-8 rounded-lg bg-indigo-500 flex items-center justify-center">
                <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" /></svg>
            </div>
            <span class="text-white font-semibold text-sm">Notification Platform</span>
        </div>
        <nav class="mt-4 px-3 space-y-1">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                Dashboard
            </a>
            <a href="{{ route('users.index') }}" class="flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('users.*') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" /></svg>
                Users
            </a>
            @if (($currentAdmin['role'] ?? '') === 'super_admin')
            <a href="{{ route('admins.index') }}" class="flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admins.*') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
                Admin Management
            </a>
            @endif
        </nav>
    </aside>

    {{-- Main content --}}
    <div class="lg:pl-64">
        {{-- Top bar --}}
        <header class="sticky top-0 z-30 flex h-16 items-center gap-x-4 border-b border-gray-200 bg-white px-4 shadow-sm sm:px-6 lg:px-8">
            <button type="button" class="lg:hidden -m-2.5 p-2.5 text-gray-700" @click="sidebarOpen = true">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
            </button>
            <div class="h-6 w-px bg-gray-200 lg:hidden"></div>
            <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
                <div class="flex items-center"><h1 class="text-lg font-semibold text-gray-900">@yield('title', 'Dashboard')</h1></div>
                <div class="ml-auto flex items-center gap-x-4">
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center gap-x-2 rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center">
                                <span class="text-sm font-medium text-indigo-600">{{ strtoupper(substr($currentAdmin['name'] ?? 'A', 0, 1)) }}</span>
                            </div>
                            <div class="hidden sm:block text-left">
                                <p class="text-sm font-medium text-gray-700">{{ $currentAdmin['name'] ?? 'Admin' }}</p>
                                <p class="text-xs text-gray-500">{{ ucfirst(str_replace('_', ' ', $currentAdmin['role'] ?? '')) }}</p>
                            </div>
                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                        </button>
                        <div x-show="open" @click.outside="open = false" x-transition class="absolute right-0 mt-2 w-48 rounded-lg bg-white py-1 shadow-lg ring-1 ring-gray-900/5" x-cloak>
                            <div class="px-4 py-2 border-b border-gray-100"><p class="text-sm text-gray-500">{{ $currentAdmin['email'] ?? '' }}</p></div>
                            <form method="POST" action="{{ route('logout') }}">@csrf
                                <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-50">Sign out</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        {{-- Flash messages → SweetAlert2 toasts --}}
        @if (session('success') || session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                @if (session('success'))
                Toast.fire({ icon: 'success', title: @js(session('success')) });
                @endif
                @if (session('error'))
                Toast.fire({ icon: 'error', title: @js(session('error')) });
                @endif
            });
        </script>
        @endif

        <main class="py-6 px-4 sm:px-6 lg:px-8">@yield('content')</main>
    </div>
</div>
</body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))" x-bind:class="{ 'dark': darkMode }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Manajemen Keuangan') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans antialiased text-gray-900 bg-gray-50 dark:bg-brand-dark dark:text-gray-100 flex h-screen overflow-hidden transition-colors duration-200">
        
        <!-- Sidebar -->
        <aside class="w-64 bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800 flex flex-col justify-between h-full flex-shrink-0 transition-colors duration-200">
            <div>
                <!-- Logo -->
                <div class="h-16 flex items-center px-6 border-b border-gray-100 dark:border-gray-800">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-brand-500 rounded-lg flex items-center justify-center text-white font-bold shadow-md shadow-brand-500/20">
                            MK
                        </div>
                        <span class="text-lg font-bold text-gray-900 dark:text-white">Manajemen Keuangan</span>
                    </div>
                </div>

                <!-- New Transaction Button -->
                <div class="px-6 py-4">
                    <a href="{{ route('transactions.index', ['new_transaction' => 1]) }}#transaction-form" class="w-full bg-brand-500 hover:bg-brand-600 text-white font-medium py-2.5 px-4 rounded-xl flex items-center justify-center gap-2 transition-all shadow-md shadow-brand-500/20">
                        <x-heroicon-o-plus class="w-5 h-5" />
                        <span>Transaksi Baru</span>
                    </a>
                </div>

                <!-- Navigation -->
                <nav class="px-4 py-2 space-y-1">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('dashboard') ? 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 font-medium' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-100' }}">
                        <x-heroicon-o-squares-2x2 class="w-5 h-5" />
                        Dashboard
                    </a>
                    <a href="{{ route('transactions.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('transactions.*') ? 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 font-medium' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-100' }}">
                        <x-heroicon-o-arrows-right-left class="w-5 h-5" />
                        Transaksi
                    </a>
                    <a href="{{ route('savings-goals.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('savings-goals.*') ? 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 font-medium' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-100' }}">
                        <x-heroicon-o-chart-pie class="w-5 h-5" />
                        Anggaran
                    </a>
                    <a href="{{ route('reports.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('reports.*') ? 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 font-medium' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-100' }}">
                        <x-heroicon-o-presentation-chart-line class="w-5 h-5" />
                        Laporan Keuangan
                    </a>
                    <a href="{{ route('profile') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('profile') ? 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 font-medium' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-100' }}">
                        <x-heroicon-o-user class="w-5 h-5" />
                        Profil Pengguna
                    </a>
                </nav>
            </div>

            <!-- Bottom Nav -->
            <div class="p-4 border-t border-gray-200 dark:border-gray-800">
                <a href="{{ route('settings.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('settings.*') ? 'text-brand-600 dark:text-brand-400 font-medium' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-100' }}">
                        <x-heroicon-o-cog-8-tooth class="w-5 h-5" />
                    Pengaturan
                </a>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col h-full overflow-hidden">
            <!-- Topbar -->
            <header class="h-16 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between px-8 flex-shrink-0 transition-colors duration-200">
                <div class="flex-1 max-w-xl">
                    <div class="relative">
                        <x-heroicon-o-magnifying-glass class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2" />
                        <input type="text" placeholder="Cari transaksi, anggaran..." class="w-full pl-10 pr-4 py-2 bg-gray-50 dark:bg-gray-800 border-transparent dark:border-gray-700 rounded-xl focus:bg-white dark:focus:bg-gray-900 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 sm:text-sm text-gray-900 dark:text-gray-100 transition-colors placeholder-gray-400 dark:placeholder-gray-500">
                    </div>
                </div>
                <div class="flex items-center gap-4 ml-4">
                    <!-- Dark Mode Toggle -->
                    <button @click="darkMode = !darkMode" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                        <x-heroicon-o-sun x-show="darkMode" class="w-6 h-6" style="display: none;" />
                        <x-heroicon-o-moon x-show="!darkMode" class="w-6 h-6" />
                    </button>
                    
                    <!-- Notifications Dropdown -->
                    @php
                        $unreadNotifications = auth()->user()->unreadNotifications()->latest()->take(8)->get();
                        $hasUnreadNotifications = $unreadNotifications->isNotEmpty();
                    @endphp
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" @click.away="open = false" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors relative">
                            <x-heroicon-o-bell class="w-6 h-6" />
                            @if($hasUnreadNotifications)
                                <span class="absolute top-1 right-1 w-2.5 h-2.5 bg-red-500 border-2 border-white dark:border-gray-900 rounded-full"></span>
                            @endif
                        </button>
                        
                        <div x-show="open" x-transition.opacity class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-900 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-800 overflow-hidden z-50" style="display: none;">
                            <div class="p-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                                <h3 class="font-semibold text-gray-900 dark:text-white">Notifikasi</h3>
                                @if($hasUnreadNotifications)
                                    <form method="POST" action="{{ route('notifications.read-all') }}">
                                        @csrf
                                        <button type="submit" class="text-xs text-brand-600 dark:text-brand-400 hover:underline">Tandai semua dibaca</button>
                                    </form>
                                @endif
                            </div>
                            <div class="max-h-96 overflow-y-auto">
                                @forelse($unreadNotifications as $notification)
                                    @php
                                        $data = $notification->data;
                                        $type = $data['type'] ?? 'info';
                                    @endphp
                                    <form method="POST" action="{{ route('notifications.read', $notification) }}">
                                        @csrf
                                        <button type="submit" class="w-full text-left p-4 border-b border-gray-50 dark:border-gray-800/50 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors flex gap-3">
                                            <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0
                                                {{ $type === 'error' ? 'bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400' : ($type === 'success' ? 'bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400' : 'bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400') }}">
                                                @if($type === 'error')
                                                    <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
                                                @elseif($type === 'success')
                                                    <x-heroicon-o-check-circle class="w-5 h-5" />
                                                @else
                                                    <x-heroicon-o-bell-alert class="w-5 h-5" />
                                                @endif
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $data['title'] ?? 'Notifikasi Baru' }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $data['message'] ?? 'Ada pembaruan baru.' }}</p>
                                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ $notification->created_at?->diffForHumans() }}</p>
                                            </div>
                                        </button>
                                    </form>
                                @empty
                                    <div class="p-6 text-center text-sm text-gray-500 dark:text-gray-400">
                                        Belum ada notifikasi baru.
                                    </div>
                                @endforelse
                            </div>
                            <div class="p-3 text-center border-t border-gray-100 dark:border-gray-800">
                                <a href="{{ route('reports.index') }}" class="text-sm font-medium text-brand-600 dark:text-brand-400 hover:text-brand-700 dark:hover:text-brand-300">Lihat Laporan Keuangan</a>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-colors" title="Keluar dari akun">
                            <x-heroicon-o-arrow-right-on-rectangle class="w-5 h-5 flex-shrink-0" />
                            <span>Keluar</span>
                        </button>
                    </form>

                    <a href="{{ route('profile') }}" class="flex items-center gap-3 border-l border-gray-200 dark:border-gray-700 pl-4 rounded-lg px-2 py-1.5 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                        <div class="w-9 h-9 rounded-full overflow-hidden border border-gray-200 dark:border-gray-700">
                            @if(auth()->user()->avatar)
                                <img src="{{ Storage::url(auth()->user()->avatar) }}" alt="Avatar" class="w-full h-full object-cover">
                            @else
                                <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=14b8a6&color=fff" alt="Avatar" class="w-full h-full object-cover">
                            @endif
                        </div>
                        <div class="hidden md:block">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Pengguna</p>
                        </div>
                    </a>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-4 md:p-8 bg-gray-50 dark:bg-brand-dark transition-colors duration-200">
                {{ $slot }}
            </main>
        </div>
        
        @livewireScripts
        <!-- Toast Notification Container -->
        <div x-data="{ toasts: [] }" 
             @notify.window="toasts.push({ id: Date.now(), type: $event.detail.type, message: $event.detail.message }); setTimeout(() => toasts.shift(), 3000)"
             class="fixed bottom-4 right-4 z-50 flex flex-col gap-2 pointer-events-none">
            <template x-for="toast in toasts" :key="toast.id">
                <div x-show="true" x-transition.opacity.duration.300ms 
                     class="pointer-events-auto px-4 py-3 rounded-xl shadow-lg border flex items-center gap-3 min-w-[300px]"
                     :class="{
                        'bg-white dark:bg-gray-800 border-green-100 dark:border-green-900/30 text-gray-900 dark:text-white': toast.type === 'success',
                        'bg-white dark:bg-gray-800 border-red-100 dark:border-red-900/30 text-gray-900 dark:text-white': toast.type === 'error'
                     }">
                    <div :class="{'text-green-500': toast.type === 'success', 'text-red-500': toast.type === 'error'}">
                        <x-heroicon-o-check-circle x-show="toast.type === 'success'" class="w-6 h-6" />
                        <x-heroicon-o-x-circle x-show="toast.type === 'error'" class="w-6 h-6" />
                    </div>
                    <p class="text-sm font-medium" x-text="toast.message"></p>
                </div>
            </template>
        </div>
    </body>
</html>

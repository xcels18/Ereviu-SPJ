<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SPJ OPD') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="app-shell font-sans antialiased text-slate-800">
        <div x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false" class="min-h-screen flex">

            <!-- Sidebar Overlay (mobile) -->
            <div
                x-show="sidebarOpen"
                x-transition:enter="transition-opacity ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="sidebarOpen = false"
                class="fixed inset-0 z-20 bg-slate-900/50 lg:hidden"
                style="display:none"
            ></div>

            <a href="#main-content" class="skip-link">Lewati navigasi</a>
            <!-- Sidebar -->
            <aside
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
                id="main-navigation" aria-label="Navigasi utama" class="app-sidebar fixed inset-y-0 left-0 z-30 w-64 flex flex-col -translate-x-full lg:translate-x-0 transition-transform duration-200 ease-in-out"
            >
                <!-- Logo -->
                <div class="flex items-center gap-3 px-5 h-16 border-b border-white/10 shrink-0">
                    <div class="w-9 h-9 bg-gradient-to-br from-brand-500 to-brand-700 rounded-xl flex items-center justify-center shrink-0 shadow-card-md">
                        <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <span class="font-bold text-sm text-white tracking-tight">KawalSPJ</span>
                        <p class="text-[11px] text-slate-400 leading-none mt-0.5 tracking-wide">Inspektorat Daerah</p>
                    </div>
                </div>

                <!-- Nav -->
                <nav class="flex-1 px-3 py-4 overflow-y-auto space-y-0.5">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-3 mb-2">Menu Utama</p>

                    <a href="{{ route('dashboard') }}"
                        class="nav-link {{ request()->routeIs('dashboard') ? 'nav-active' : '' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Dashboard
                    </a>

                    @can('manage_opd')
                    <a href="{{ route('opd.index') }}"
                        class="nav-link {{ request()->routeIs('opd.*') ? 'nav-active' : '' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        OPD
                    </a>
                    @endcan

                    @can('manage_sp2d')
                    <a href="{{ route('sp2d.index') }}"
                        class="nav-link {{ request()->routeIs('sp2d.*') ? 'nav-active' : '' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        SP2D
                    </a>
                    @endcan

                    @can('upload_spj')
                    <a href="{{ route('spj.upload') }}"
                        class="nav-link {{ request()->routeIs('spj.upload') ? 'nav-active' : '' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        Upload SPJ
                    </a>
                    @endcan

                    @can('manage_review_guide')
                    <a href="{{ route('review-guides.index') }}"
                        class="nav-link {{ request()->routeIs('review-guides.*') ? 'nav-active' : '' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        Panduan Reviu
                    </a>
                    @endcan

                    @can('manage_users')
                    <a href="{{ route('users.index') }}"
                        class="nav-link {{ request()->routeIs('users.*') ? 'nav-active' : '' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Kelola User
                    </a>
                    @endcan

                    @can('view_reports')
                    <div class="pt-4 pb-1">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-3">Laporan</p>
                    </div>
                    <a href="{{ route('reports.index') }}"
                        class="nav-link {{ request()->routeIs('reports.*') && !request()->routeIs('reports.cetak') ? 'nav-active' : '' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        Laporan
                    </a>
                    @can('export_reports')
                    <a href="{{ route('reports.cetak') }}"
                        class="nav-link {{ request()->routeIs('reports.cetak') ? 'nav-active' : '' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a1 1 0 001-1v-4a1 1 0 00-1-1H9a1 1 0 00-1 1v4a1 1 0 001 1zm8-14V3H7v4h10z"/>
                        </svg>
                        Cetak Laporan
                    </a>
                    @endcan
                    @endcan

                    @can('compile_spj')
                    <div class="pt-4 pb-1">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-3">Admin</p>
                    </div>
                    <a href="{{ route('spj-compile.index') }}"
                        class="nav-link {{ request()->routeIs('spj-compile.*') ? 'nav-active' : '' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 12v7a2 2 0 01-2 2H6a2 2 0 01-2-2v-7m16-5l-8-4-8 4m16 0l-8 4m8-4v5l-8 4m0-9L4 7m8 4v9m0-9L4 7m0 0v5l8 4"/>
                        </svg>
                        Compile SPJ
                    </a>
                    @endcan

                    @can('view_audit_trail')
                    @cannot('compile_spj')
                    <div class="pt-4 pb-1">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-3">Admin</p>
                    </div>
                    @endcannot
                    <a href="{{ route('audit-trail.index') }}"
                        class="nav-link {{ request()->routeIs('audit-trail.*') ? 'nav-active' : '' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        Audit Trail
                    </a>
                    @endcan
                </nav>

                <!-- User profile at bottom -->
                <div class="px-3 py-4 border-t border-white/10 shrink-0">
                    <a href="{{ route('profile.edit') }}" class="nav-link group">
                        <div class="w-8 h-8 bg-brand-600 rounded-full flex items-center justify-center shrink-0">
                            <span class="text-xs font-bold text-white">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-white truncate">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-slate-500 capitalize truncate">{{ Auth::user()->primary_role }}</p>
                        </div>
                    </a>
                </div>
            </aside>

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col min-w-0 overflow-hidden lg:ml-64">

                <!-- Topbar -->
                <header class="app-topbar min-h-20 bg-white/95 backdrop-blur border-b border-slate-100 flex items-center justify-between px-4 lg:px-6 shrink-0">
                    <!-- Mobile hamburger -->
                    <button aria-label="Buka atau tutup navigasi" aria-controls="main-navigation" :aria-expanded="sidebarOpen" @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 transition-colors focus:outline-none">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    <!-- Page header slot -->
                    <div class="flex-1 ml-4 lg:ml-0">
                        @isset($header)
                            {{ $header }}
                        @endisset
                    </div>

                    <!-- Right actions -->
                    <div class="flex items-center gap-2 shrink-0">
                        <x-dropdown align="right" width="56">
                            <x-slot name="trigger">
                                <button class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-sm text-slate-600 hover:bg-slate-100 transition-colors focus:outline-none">
                                    <div class="w-7 h-7 bg-brand-100 rounded-full flex items-center justify-center">
                                        <span class="text-xs font-bold text-brand-700">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                                    </div>
                                    <span class="hidden sm:inline text-sm font-medium text-slate-700">{{ Auth::user()->name }}</span>
                                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 20 20" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 8l4 4 4-4"/>
                                    </svg>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <div class="px-4 py-3 border-b border-slate-100">
                                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Akun</p>
                                    <p class="text-sm font-semibold text-slate-900 mt-0.5">{{ Auth::user()->name }}</p>
                                    <p class="text-xs text-slate-500">{{ Auth::user()->email }}</p>
                                </div>
                                <div class="py-1">
                                    <x-dropdown-link :href="route('profile.edit')">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        Profil Saya
                                    </x-dropdown-link>
                                </div>
                                <div class="border-t border-slate-100 py-1">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <x-dropdown-link :href="route('logout')"
                                            onclick="event.preventDefault(); this.closest('form').submit();">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                            Keluar
                                        </x-dropdown-link>
                                    </form>
                                </div>
                            </x-slot>
                        </x-dropdown>
                    </div>
                </header>

                <!-- Page Content -->
                <main id="main-content" tabindex="-1" class="flex-1 overflow-y-auto">
                    {{ $slot }}
                </main>
            </div>

        </div>

        @stack('scripts')
    </body>
</html>

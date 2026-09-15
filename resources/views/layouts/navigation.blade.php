<nav x-data="{ open: false }" class="bg-white border-b border-slate-100 sticky top-0 z-40 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <!-- Logo -->
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 shrink-0 mr-8">
                    <div class="w-8 h-8 bg-brand-700 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <span class="font-bold text-base text-slate-900 tracking-tight">SPJ <span class="text-brand-700">OPD</span></span>
                </a>

                <!-- Navigation Links -->
                <div class="hidden sm:flex items-center gap-1">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        Dashboard
                    </x-nav-link>

                    @can('manage_opd')
                    <x-nav-link :href="route('opd.index')" :active="request()->routeIs('opd.*')">
                        OPD
                    </x-nav-link>
                    @endcan

                    @can('manage_sp2d')
                    <x-nav-link :href="route('sp2d.index')" :active="request()->routeIs('sp2d.*')">
                        SP2D
                    </x-nav-link>
                    @endcan

                    @can('upload_spj')
                    <x-nav-link :href="route('spj.upload')" :active="request()->routeIs('spj.upload')">
                        Upload SPJ
                    </x-nav-link>
                    @endcan

                    @can('manage_review_guide')
                    <x-nav-link :href="route('review-guides.index')" :active="request()->routeIs('review-guides.*')">
                        Panduan Reviu
                    </x-nav-link>
                    @endcan

                    @can('manage_users')
                    <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                        Kelola User
                    </x-nav-link>
                    @endcan

                    @can('view_reports')
                    <x-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')">
                        Laporan
                    </x-nav-link>
                    @endcan

                    @can('compile_spj')
                    <x-nav-link :href="route('spj-compile.index')" :active="request()->routeIs('spj-compile.*')">
                        Compile SPJ
                    </x-nav-link>
                    @endcan
                </div>
            </div>

            <!-- User Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:gap-3">
                <x-dropdown align="right" width="56">
                    <x-slot name="trigger">
                        <button class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <div class="w-7 h-7 bg-brand-100 rounded-full flex items-center justify-center">
                                <span class="text-xs font-bold text-brand-700">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                            </div>
                            <div class="text-left hidden lg:block">
                                <div class="text-sm font-semibold text-slate-800 leading-tight">{{ Auth::user()->name }}</div>
                                <div class="text-xs text-slate-500 capitalize">{{ Auth::user()->primary_role }}</div>
                            </div>
                            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 20 20" stroke="currentColor" stroke-width="2">
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
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Profil Saya
                            </x-dropdown-link>
                        </div>
                        <div class="border-t border-slate-100 py-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();"
                                    class="text-red-600 hover:text-red-700 hover:bg-red-50">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    Keluar
                                </x-dropdown-link>
                            </form>
                        </div>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors focus:outline-none">
                    <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t border-slate-100">
        <div class="px-3 py-3 space-y-0.5">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">Dashboard</x-responsive-nav-link>
            @can('manage_opd')
            <x-responsive-nav-link :href="route('opd.index')">OPD</x-responsive-nav-link>
            @endcan
            @can('manage_sp2d')
            <x-responsive-nav-link :href="route('sp2d.index')">SP2D</x-responsive-nav-link>
            @endcan
            @can('upload_spj')
            <x-responsive-nav-link :href="route('spj.upload')">Upload SPJ</x-responsive-nav-link>
            @endcan
            @can('manage_review_guide')
            <x-responsive-nav-link :href="route('review-guides.index')">Panduan Reviu</x-responsive-nav-link>
            @endcan
            @can('view_reports')
            <x-responsive-nav-link :href="route('reports.index')">Laporan</x-responsive-nav-link>
            @endcan
            @can('compile_spj')
            <x-responsive-nav-link :href="route('spj-compile.index')">Compile SPJ</x-responsive-nav-link>
            @endcan
        </div>

        <div class="border-t border-slate-100 px-4 py-4">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 bg-brand-100 rounded-full flex items-center justify-center">
                    <span class="text-sm font-bold text-brand-700">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                </div>
                <div>
                    <div class="font-semibold text-slate-900">{{ Auth::user()->name }}</div>
                    <div class="text-xs text-slate-500">{{ Auth::user()->email }}</div>
                </div>
            </div>
            <div class="space-y-0.5">
                <x-responsive-nav-link :href="route('profile.edit')">Profil</x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                        onclick="event.preventDefault(); this.closest('form').submit();">
                        Keluar
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>

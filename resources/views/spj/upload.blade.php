<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div>
                <h1 class="page-title">Upload &amp; Reviu SPJ</h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    @if (($mode ?? 'sp2d_list') === 'opd_list')
                        Pilih OPD untuk melihat detail SP2D
                    @else
                        Upload Surat Pertanggungjawaban &amp; catatan reviu per SP2D
                    @endif
                </p>
            </div>
        </div>
    </x-slot>

    <div class="records-page spj-records py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="alert-success">
                    <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="alert-error">
                    <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ session('error') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="alert-error">
                    <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (($mode ?? 'sp2d_list') === 'opd_list')
                <!-- Executive Modern Hero Header for OPD Directory -->
                <div class="mb-8">
                    <div class="ek-spj-heading relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 via-brand-950 to-brand-900 text-white p-7 sm:p-8 shadow-xl shadow-slate-900/10 border border-slate-800">
                        <div class="absolute -right-16 -top-16 w-80 h-80 bg-brand-500/10 rounded-full blur-3xl pointer-events-none"></div>
                        <div class="absolute right-32 -bottom-20 w-64 h-64 bg-gold-400/10 rounded-full blur-2xl pointer-events-none"></div>

                        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                            <div class="space-y-3 max-w-2xl">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold tracking-wide uppercase bg-white/10 text-brand-200 border border-white/15 backdrop-blur-md">
                                        <span class="w-2 h-2 rounded-full bg-gold-400"></span>
                                        Direktori Perangkat Daerah
                                    </span>
                                </div>
                                <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight leading-tight">
                                    Pilih Perangkat Daerah untuk Reviu &amp; Bukti SPJ
                                </h2>
                                <p class="text-sm text-slate-300 leading-relaxed font-normal">
                                    Akses berkas SP2D, pantau persentase kelengkapan bukti belanja, dan lakukan reviu kepatuhan anggaran per dinas/badan secara sistematis.
                                </p>
                            </div>

                            <!-- Integrated Steps Pill -->
                            <div class="p-4 sm:p-5 bg-white/10 backdrop-blur-md rounded-2xl border border-white/15 shrink-0 self-start lg:self-center">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-brand-200 block mb-2">Alur Kerja Pengelolaan:</span>
                                <div class="flex items-center gap-2 text-xs text-slate-200">
                                    <span class="px-2 py-0.5 rounded-md bg-white/20 font-bold text-white">01</span>
                                    <span>Pilih OPD</span>
                                    <span class="text-brand-300">&rarr;</span>
                                    <span class="px-2 py-0.5 rounded-md bg-white/20 font-bold text-white">02</span>
                                    <span>Pilih SP2D</span>
                                    <span class="text-brand-300">&rarr;</span>
                                    <span class="px-2 py-0.5 rounded-md bg-white/20 font-bold text-white">03</span>
                                    <span>Upload &amp; Reviu</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modern Summary Metrics -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
                    <!-- Total OPD -->
                    <div class="group relative overflow-hidden bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md hover:border-slate-300 transition-all duration-300">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Perangkat Daerah</span>
                            <div class="w-9 h-9 rounded-xl bg-brand-50 group-hover:bg-brand-100 text-brand-700 flex items-center justify-center transition-colors">
                                <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            </div>
                        </div>
                        <div class="flex items-baseline justify-between">
                            <p class="text-3xl font-extrabold text-slate-900 tracking-tight">{{ $opdSummaries->count() }} <span class="text-sm font-semibold text-slate-500">OPD</span></p>
                            <span class="text-xs font-semibold text-brand-700 bg-brand-50 px-2.5 py-1 rounded-lg border border-brand-100">
                                {{ number_format($totalSp2dSemua, 0, ',', '.') }} SP2D
                            </span>
                        </div>
                        <div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                            <span>Cakupan Pengawasan</span>
                            <span class="font-medium text-slate-600">Aktif Terdata</span>
                        </div>
                    </div>

                    <!-- Total Nilai SP2D -->
                    <div class="group relative overflow-hidden bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md hover:border-slate-300 transition-all duration-300">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Nilai SP2D</span>
                            <div class="w-9 h-9 rounded-xl bg-emerald-50 group-hover:bg-emerald-100 text-emerald-700 flex items-center justify-center transition-colors">
                                <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                        </div>
                        <div class="flex items-baseline justify-between">
                            <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight font-mono">
                                Rp {{ number_format($totalAnggaranSemua, 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                            <span>Akumulasi Seluruh OPD</span>
                            <span class="font-medium text-emerald-600">Sesuai Filter</span>
                        </div>
                    </div>

                    <!-- Rata-rata Kelengkapan -->
                    <div class="group relative overflow-hidden bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md hover:border-slate-300 transition-all duration-300">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Rata-rata Kelengkapan</span>
                            <div class="w-9 h-9 rounded-xl bg-amber-50 group-hover:bg-amber-100 text-amber-700 flex items-center justify-center transition-colors">
                                <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                        </div>
                        <div class="flex items-baseline justify-between">
                            <p class="text-3xl font-extrabold text-slate-900 tracking-tight">{{ $rataPersentase }}<span class="text-base font-semibold text-slate-500">%</span></p>
                            <span class="text-xs font-semibold text-amber-700 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-100">
                                Rata-rata Bukti
                            </span>
                        </div>
                        <div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                            <span>Progres Pertanggungjawaban</span>
                            <span class="font-medium text-amber-600">Semua Rekening</span>
                        </div>
                    </div>
                </div>

                <!-- Modern Directory Section & Toolbar -->
                <section aria-labelledby="opd-directory-title" class="space-y-6 mb-12">
                    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm p-5 sm:p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h3 id="opd-directory-title" class="text-base sm:text-lg font-bold text-slate-900">
                                Daftar Perangkat Daerah
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Menampilkan {{ $opdSummaries->count() }} perangkat daerah dalam jangkauan tugas Anda
                            </p>
                        </div>

                        <!-- Modern Search Form -->
                        <form method="GET" action="{{ route('spj.upload') }}" class="flex items-center gap-2 w-full md:w-auto">
                            <div class="relative flex-1 md:w-80">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/></svg>
                                </div>
                                <input id="directory-search" name="search" type="search" value="{{ request('search') }}"
                                    placeholder="Cari nama atau singkatan dinas…"
                                    class="w-full pl-10 pr-4 py-2.5 text-xs sm:text-sm bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-2xl placeholder-slate-400 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-600 focus:border-brand-600 transition-all shadow-xs">
                            </div>
                            <button type="submit" class="px-5 py-2.5 bg-slate-900 hover:bg-brand-900 text-white rounded-2xl text-xs font-bold tracking-wide transition-all shadow-xs hover:shadow shrink-0">
                                Cari
                            </button>
                            @if(request()->filled('search'))
                                <a href="{{ route('spj.upload') }}" class="px-3.5 py-2.5 text-xs font-semibold text-rose-600 hover:text-rose-700 hover:bg-rose-50 rounded-2xl transition-colors shrink-0">
                                    Reset
                                </a>
                            @endif
                        </form>
                    </div>

                    <!-- Clean & Elegant OPD Cards Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                        @forelse ($opdSummaries as $item)
                            <div class="group relative bg-white rounded-3xl border border-slate-200/90 shadow-xs hover:shadow-xl hover:border-brand-300 hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between p-6">
                                <div>
                                    <!-- Top Card Meta -->
                                    <div class="flex items-center justify-between gap-2 mb-4">
                                        <span class="w-10 h-10 rounded-2xl bg-brand-50 text-brand-700 group-hover:bg-brand-900 group-hover:text-gold-400 flex items-center justify-center transition-colors shadow-xs">
                                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 20h18M5 20V6l7-3 7 3v14M9 8h1m4 0h1M9 12h1m4 0h1m-6 8v-4h4v4"/></svg>
                                        </span>
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 group-hover:bg-brand-50 group-hover:text-brand-800 transition-colors">
                                            <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                            {{ $item->total_sp2d }} SP2D
                                        </span>
                                    </div>

                                    <!-- OPD Title -->
                                    <div class="mb-4">
                                        <span class="text-[11px] font-bold tracking-wider uppercase text-brand-700">
                                            {{ $item->opd->singkatan ?: 'OPD' }}
                                        </span>
                                        <h4 class="text-base font-bold text-slate-900 group-hover:text-brand-800 transition-colors leading-snug line-clamp-2 mt-0.5">
                                            <a href="{{ route('spj.upload', ['opd_id' => $item->opd->id]) }}" class="hover:underline focus:outline-none">
                                                {{ $item->opd->nama_opd }}
                                            </a>
                                        </h4>
                                    </div>

                                    <!-- Nilai Total Anggaran -->
                                    <div class="p-3.5 rounded-2xl bg-slate-50/70 group-hover:bg-slate-50 border border-slate-100 transition-colors mb-4">
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Total Nilai SP2D Terbit</span>
                                        <span class="text-base sm:text-lg font-black text-slate-900 font-mono tracking-tight">
                                            Rp {{ number_format($item->total_anggaran, 0, ',', '.') }}
                                        </span>
                                    </div>

                                    <!-- Progress Kelengkapan Bukti -->
                                    <div class="space-y-1.5 mb-5">
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="font-medium text-slate-500">Kelengkapan Bukti</span>
                                            <span class="font-black text-slate-800">{{ $item->persentase }}%</span>
                                        </div>
                                        <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden shadow-inner">
                                            <div class="h-full rounded-full transition-all duration-500 {{ $item->persentase >= 100 ? 'bg-emerald-500' : ($item->persentase > 50 ? 'bg-brand-600' : 'bg-amber-500') }}"
                                                style="width: {{ min(100, $item->persentase) }}%"></div>
                                        </div>
                                        <p class="text-[11px] text-slate-400 flex items-center justify-between pt-0.5">
                                            <span>{{ $item->akun_dengan_bukti }} dari {{ $item->total_akun }} akun belanja</span>
                                            @if($item->persentase >= 100)
                                                <span class="text-emerald-600 font-bold">Lengkap</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                <!-- Action Link Button -->
                                <a href="{{ route('spj.upload', ['opd_id' => $item->opd->id]) }}"
                                    class="inline-flex items-center justify-between w-full px-4 py-3 bg-slate-100 hover:bg-brand-900 text-slate-700 hover:text-white rounded-2xl text-xs font-bold transition-all group-hover:bg-brand-900 group-hover:text-white shadow-xs">
                                    <span>Buka Dokumen SP2D</span>
                                    <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                                </a>
                            </div>
                        @empty
                            <div class="col-span-full py-16 text-center bg-white rounded-3xl border border-slate-200/80 shadow-xs">
                                <div class="w-16 h-16 rounded-3xl bg-slate-50 border border-slate-200/70 flex items-center justify-center mx-auto mb-4 text-slate-400 shadow-xs">
                                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                </div>
                                <h4 class="font-bold text-slate-800 text-base">Perangkat daerah tidak ditemukan</h4>
                                <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                                    {{ request()->filled('search') ? 'Tidak ada OPD yang sesuai dengan kata kunci pencarian Anda.' : 'Belum ada data perangkat daerah dalam cakupan akses akun Anda.' }}
                                </p>
                                @if(request()->filled('search'))
                                    <a href="{{ route('spj.upload') }}" class="inline-flex items-center gap-1.5 mt-4 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                        Tampilkan Semua OPD
                                    </a>
                                @endif
                            </div>
                        @endforelse
                    </div>
                </section>
            @else
                <!-- Executive Modern Workspace Header -->
                <div class="mb-8">
                    @if ($showOpdFilter)
                        <div class="mb-3.5">
                            <a href="{{ route('spj.upload') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-brand-700 transition-colors py-1.5 px-3 rounded-xl hover:bg-slate-100/80 -ml-2">
                                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                                <span>Kembali ke Direktori OPD</span>
                            </a>
                        </div>
                    @endif

                    <!-- Hero Banner OPD -->
                    <div class="ek-spj-heading relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 via-brand-950 to-brand-900 text-white p-7 sm:p-8 shadow-xl shadow-slate-900/10 border border-slate-800">
                        <div class="absolute -right-16 -top-16 w-80 h-80 bg-brand-500/10 rounded-full blur-3xl pointer-events-none"></div>
                        <div class="absolute right-32 -bottom-20 w-64 h-64 bg-gold-400/10 rounded-full blur-2xl pointer-events-none"></div>

                        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                            <div class="space-y-3 max-w-3xl">
                                <div class="flex items-center flex-wrap gap-2.5">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold tracking-wide uppercase bg-white/10 text-brand-200 border border-white/15 backdrop-blur-md">
                                        <span class="w-2 h-2 rounded-full bg-gold-400"></span>
                                        Ruang Kerja Pertanggungjawaban
                                    </span>
                                    @if ($selectedOpd?->singkatan)
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-white/15 text-white border border-white/20">
                                            {{ $selectedOpd->singkatan }}
                                        </span>
                                    @endif
                                </div>
                                <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight leading-tight">
                                    {{ $selectedOpd?->nama_opd ?? 'Dokumen SPJ Perangkat Daerah' }}
                                </h2>
                                <p class="text-sm text-slate-300 leading-relaxed max-w-2xl font-normal">
                                    Kelola berkas bukti belanja, verifikasi kepatuhan anggaran, serta pantau status dan catatan reviu dari tim Inspektorat secara terintegrasi.
                                </p>
                            </div>

                            <!-- Right KPI Summary Banner Card -->
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 p-4 sm:p-5 bg-white/10 backdrop-blur-md rounded-2xl border border-white/15 shrink-0">
                                <div class="space-y-1">
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-brand-200 flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-gold-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Total Nilai SP2D Terbit
                                    </span>
                                    <p class="text-xl sm:text-2xl font-black text-white font-mono tracking-tight">
                                        Rp {{ number_format($totalAnggaran ?? 0, 0, ',', '.') }}
                                    </p>
                                    <div class="flex items-center gap-2 text-xs text-slate-300 pt-0.5">
                                        <span class="inline-flex items-center gap-1 font-semibold text-white">
                                            <svg class="w-3.5 h-3.5 text-gold-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                            {{ $totalSp2dCount }} Berkas
                                        </span>
                                        <span>&bull;</span>
                                        <span>Tahun Anggaran {{ request('tahun') ?: date('Y') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if (!$uploadSchedule && !auth()->user()->can('manage_sp2d') && !$showOpdFilter)
                <div class="mb-6 p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 flex items-start gap-3 shadow-xs">
                    <div class="p-2 rounded-xl bg-amber-100 text-amber-700 shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-amber-800">Periode Unggahan Tidak Aktif</h4>
                        <p class="text-xs text-amber-700 mt-0.5 leading-relaxed">Periode upload SPJ saat ini sedang ditutup. Mohon menunggu instruksi atau jadwal resmi yang ditentukan oleh Inspektorat Daerah.</p>
                    </div>
                </div>
                @else
                    @if ($uploadSchedule)
                    <div class="mb-6 p-4 rounded-2xl bg-gradient-to-r from-emerald-50 via-emerald-50/70 to-teal-50 border border-emerald-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs shadow-xs">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span class="font-bold text-emerald-950">Periode Unggahan Terbuka</span>
                                </div>
                                <p class="text-emerald-700 text-xs mt-0.5">
                                    Aktif mulai <span class="font-semibold font-mono">{{ $uploadSchedule->start_date->format('d M Y') }}</span> s/d <span class="font-semibold font-mono">{{ $uploadSchedule->end_date->format('d M Y') }}</span>
                                </p>
                            </div>
                        </div>
                        <span class="self-start sm:self-auto inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-600 text-white font-semibold text-[11px] shadow-xs">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            Portal Siap Menerima Bukti Belanja
                        </span>
                    </div>
                    @endif

                    <!-- Executive Modern Metric Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-7">
                        <!-- Total SP2D -->
                        <div class="group relative overflow-hidden bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md hover:border-slate-300 transition-all duration-300">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Berkas SP2D</span>
                                <div class="w-9 h-9 rounded-xl bg-slate-100 group-hover:bg-brand-50 text-slate-600 group-hover:text-brand-700 flex items-center justify-center transition-colors">
                                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                            </div>
                            <div class="flex items-baseline justify-between">
                                <p class="text-3xl font-extrabold text-slate-900 tracking-tight">{{ $totalSp2dCount }}</p>
                                <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-lg">100% Terdata</span>
                            </div>
                            <div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                                <span>Dokumen Terbit</span>
                                <span class="font-medium text-slate-600">Terdaftar Sistem</span>
                            </div>
                        </div>

                        <!-- Belum Ada SPJ -->
                        <div class="group relative overflow-hidden bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md hover:border-slate-300 transition-all duration-300">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Belum Ada SPJ</span>
                                <div class="w-9 h-9 rounded-xl bg-rose-50 group-hover:bg-rose-100 text-rose-600 flex items-center justify-center transition-colors">
                                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                                </div>
                            </div>
                            <div class="flex items-baseline justify-between">
                                <p class="text-3xl font-extrabold text-rose-700 tracking-tight">{{ $belumCount }}</p>
                                <span class="text-xs font-semibold text-rose-700 bg-rose-50 px-2.5 py-1 rounded-lg border border-rose-100">
                                    {{ $totalSp2dCount > 0 ? round(($belumCount / $totalSp2dCount) * 100) : 0 }}% Belum
                                </span>
                            </div>
                            <div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                                <span>Perlu Unggahan</span>
                                <span class="font-medium text-rose-600">Segera Unggah</span>
                            </div>
                        </div>

                        <!-- Dalam Reviu -->
                        <div class="group relative overflow-hidden bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md hover:border-slate-300 transition-all duration-300">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Dalam Reviu</span>
                                <div class="w-9 h-9 rounded-xl bg-amber-50 group-hover:bg-amber-100 text-amber-600 flex items-center justify-center transition-colors">
                                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                                </div>
                            </div>
                            <div class="flex items-baseline justify-between">
                                <p class="text-3xl font-extrabold text-amber-700 tracking-tight">{{ $prosesCount }}</p>
                                <span class="text-xs font-semibold text-amber-700 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-100">
                                    {{ $totalSp2dCount > 0 ? round(($prosesCount / $totalSp2dCount) * 100) : 0 }}% Proses
                                </span>
                            </div>
                            <div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                                <span>Sedang Diperiksa</span>
                                <span class="font-medium text-amber-600">Inspektorat</span>
                            </div>
                        </div>

                        <!-- Selesai Direviu -->
                        <div class="group relative overflow-hidden bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md hover:border-slate-300 transition-all duration-300">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Selesai Direviu</span>
                                <div class="w-9 h-9 rounded-xl bg-emerald-50 group-hover:bg-emerald-100 text-emerald-600 flex items-center justify-center transition-colors">
                                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                            </div>
                            <div class="flex items-baseline justify-between">
                                <p class="text-3xl font-extrabold text-emerald-700 tracking-tight">{{ $reviewedCount }}</p>
                                <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-100">
                                    {{ $totalSp2dCount > 0 ? round(($reviewedCount / $totalSp2dCount) * 100) : 0 }}% Valid
                                </span>
                            </div>
                            <div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                                <span>Lengkap & Terverifikasi</span>
                                <span class="font-medium text-emerald-600">Tuntas</span>
                            </div>
                        </div>
                    </div>

                    <!-- Clean & Professional Table Card Container -->
                    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm overflow-hidden mb-8">
                        <!-- Modern Filter Toolbar -->
                        <div class="p-5 sm:p-6 border-b border-slate-100 bg-gradient-to-b from-slate-50/60 to-white">
                            <form method="GET" action="{{ route('spj.upload') }}" class="space-y-4">
                                @if ($selectedOpd)
                                    <input type="hidden" name="opd_id" value="{{ $selectedOpd->id }}">
                                @endif

                                <!-- Row 1: Search & Dropdowns -->
                                <div class="flex flex-col lg:flex-row lg:items-center gap-3">
                                    <!-- Search Input -->
                                    <div class="relative flex-1 min-w-[280px]">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/></svg>
                                        </div>
                                        <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari nomor SP2D atau kata kunci..."
                                            class="w-full pl-10 pr-4 py-2.5 text-xs sm:text-sm bg-white border border-slate-200 rounded-2xl placeholder-slate-400 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-600 focus:border-brand-600 transition-all shadow-xs">
                                    </div>

                                    <!-- Dropdown Filters -->
                                    <div class="flex flex-wrap sm:flex-nowrap items-center gap-2">
                                        <!-- Bulan -->
                                        <div class="relative">
                                            <select name="bulan" class="appearance-none py-2.5 pl-3.5 pr-8 text-xs font-semibold bg-white border border-slate-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-brand-600 text-slate-700 shadow-xs cursor-pointer">
                                                <option value="">Semua Bulan</option>
                                                @php
                                                    $bulanList = [
                                                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                                                    ];
                                                @endphp
                                                @foreach ($bulanList as $num => $label)
                                                    <option value="{{ $num }}" {{ (string) request('bulan') === (string) $num ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                            </div>
                                        </div>

                                        <!-- Tahun -->
                                        <div class="relative">
                                            <select name="tahun" class="appearance-none py-2.5 pl-3.5 pr-8 text-xs font-semibold bg-white border border-slate-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-brand-600 text-slate-700 shadow-xs cursor-pointer">
                                                <option value="">Semua Tahun</option>
                                                @foreach ($availableYears as $year)
                                                    <option value="{{ $year }}" {{ (string) request('tahun') === (string) $year ? 'selected' : '' }}>{{ $year }}</option>
                                                @endforeach
                                            </select>
                                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                            </div>
                                        </div>

                                        <!-- Limit -->
                                        <div class="relative">
                                            <select name="per_page" class="appearance-none py-2.5 pl-3.5 pr-8 text-xs font-semibold bg-white border border-slate-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-brand-600 text-slate-700 shadow-xs cursor-pointer">
                                                @foreach ([10, 20, 50, 100] as $opt)
                                                    <option value="{{ $opt }}" {{ (string) $perPage === (string) $opt ? 'selected' : '' }}>{{ $opt }} / hal</option>
                                                @endforeach
                                                <option value="all" {{ $perPage === 'all' ? 'selected' : '' }}>Semua</option>
                                            </select>
                                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                            </div>
                                        </div>

                                        <button type="submit" class="px-5 py-2.5 bg-slate-900 hover:bg-brand-900 text-white rounded-2xl text-xs font-bold tracking-wide transition-all shadow-xs hover:shadow flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4.5h18m-18 5h18m-18 5h18m-18 5h18"/></svg>
                                            Terapkan
                                        </button>

                                        @if (request()->hasAny(['search', 'tahun', 'bulan', 'status', 'per_page']))
                                            <a href="{{ route('spj.upload', $selectedOpd ? ['opd_id' => $selectedOpd->id] : []) }}"
                                                class="px-3 py-2.5 text-xs font-semibold text-rose-600 hover:text-rose-700 hover:bg-rose-50 rounded-2xl transition-colors inline-flex items-center gap-1"
                                                title="Bersihkan filter">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                Reset
                                            </a>
                                        @endif
                                    </div>
                                </div>

                                <!-- Row 2: Quick Status Filter Pills -->
                                <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mr-1 shrink-0">Filter Status:</span>
                                    @php
                                        $currentStatus = (string) request('status');
                                        $quickFilters = [
                                            '' => ['label' => 'Semua', 'count' => $totalSp2dCount],
                                            'belum' => ['label' => 'Belum Ada SPJ', 'count' => $belumCount],
                                            'submitted' => ['label' => 'Menunggu Reviu', 'count' => null],
                                            'in_review' => ['label' => 'Dalam Reviu', 'count' => $prosesCount],
                                            'reviewed' => ['label' => 'Selesai Direviu', 'count' => $reviewedCount],
                                        ];
                                    @endphp
                                    @foreach ($quickFilters as $stKey => $stData)
                                        @php
                                            $isActive = $currentStatus === $stKey;
                                            $filterParams = array_merge(request()->except(['status', 'page']), $stKey ? ['status' => $stKey] : []);
                                            if ($selectedOpd) {
                                                $filterParams['opd_id'] = $selectedOpd->id;
                                            }
                                        @endphp
                                        <a href="{{ route('spj.upload', $filterParams) }}"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl font-semibold transition-all shrink-0 {{ $isActive ? 'bg-brand-900 text-white shadow-xs' : 'bg-white hover:bg-slate-100 text-slate-600 border border-slate-200' }}">
                                            <span>{{ $stData['label'] }}</span>
                                            @if ($stData['count'] !== null)
                                                <span class="text-[10px] px-1.5 py-0.5 rounded-md {{ $isActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">
                                                    {{ $stData['count'] }}
                                                </span>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            </form>
                        </div>

                        <!-- Legend Status Bukti Belanja (Sleek Clean Style) -->
                        <div class="px-6 py-3 bg-slate-50/50 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-500">
                            <div class="flex items-center gap-2 font-medium text-slate-600">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
                                <span>Indikator Kelengkapan Akun:</span>
                            </div>
                            <div class="flex flex-wrap items-center gap-x-5 gap-y-1.5 text-[11px]">
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-md bg-emerald-500"></span>
                                    <span class="font-medium text-slate-600">Lengkap</span>
                                </span>
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-md bg-rose-500"></span>
                                    <span class="font-medium text-slate-600">Perlu Perbaikan</span>
                                </span>
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-md bg-amber-400"></span>
                                    <span class="font-medium text-slate-600">Menunggu Reviu</span>
                                </span>
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-md bg-indigo-400"></span>
                                    <span class="font-medium text-slate-600">Belum Dibelanjakan</span>
                                </span>
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-md bg-slate-300"></span>
                                    <span class="font-medium text-slate-600">Belum Ada Bukti</span>
                                </span>
                            </div>
                        </div>

                        <!-- Main Table Content -->
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs sm:text-sm">
                                <thead>
                                    <tr class="bg-slate-50/90 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                        <th scope="col" class="py-3.5 px-5 sm:px-6">Dokumen SP2D</th>
                                        <th scope="col" class="py-3.5 px-4 text-right">Nilai Total</th>
                                        <th scope="col" class="py-3.5 px-4 text-center">Status SPJ</th>
                                        <th scope="col" class="py-3.5 px-4 text-left">Kelengkapan Akun Belanja</th>
                                        <th scope="col" class="py-3.5 px-5 sm:px-6 text-center w-36">Tindakan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @php
                                        $currentUser = auth()->user();
                                        $currentUserIsAdmin = $currentUser->hasRole('admin') || $currentUser->hasRole('inspektur');
                                        $currentUserIsIrban = $currentUser->hasRole('irban');
                                        $currentUserAssignedOpdIds = $currentUserIsIrban
                                            ? \App\Models\AuditorOpdAssignment::where('user_id', $currentUser->id)->pluck('opd_id')->all()
                                             : [];
                                    @endphp
                                    @forelse ($sp2ds as $sp2d)
                                        @php
                                            $existingSpj = $sp2d->spj->first();
                                            $sp2dJson = [
                                                'id' => $sp2d->id,
                                                'nomor_sp2d' => $sp2d->nomor_sp2d,
                                                'tanggal' => $sp2d->tanggal->format('d/m/Y'),
                                                'jumlah_total' => $sp2d->jumlah_total,
                                                'budget_codes' => $sp2d->budgetCodes->map(function ($c) {
                                                    return [
                                                        'id' => $c->id,
                                                        'kode_belanja' => $c->kode_belanja,
                                                        'uraian' => $c->uraian,
                                                        'jumlah' => $c->jumlah,
                                                    ];
                                                }),
                                            ];

                                            $reviewJson = null;
                                            if ($existingSpj) {
                                                $documents = $existingSpj->documents;
                                                $findings = $existingSpj->findings;

                                                $allowManage = $existingSpj->status !== 'reviewed' && (
                                                    $currentUserIsAdmin
                                                    || ($currentUserIsIrban && in_array($sp2d->opd_id, $currentUserAssignedOpdIds, true))
                                                    || $currentUser->opd_id === $sp2d->opd_id
                                                );

                                                $reviewJson = [
                                                    'id' => $existingSpj->id,
                                                    'review_version' => $existingSpj->review_version,
                                                    'review_mode' => $existingSpj->review_mode,
                                                    'nomor_sp2d' => $sp2d->nomor_sp2d,
                                                    'tanggal' => $sp2d->tanggal->format('d/m/Y'),
                                                    'jumlah_total' => $sp2d->jumlah_total,
                                                    'opd_nama' => optional($sp2d->opd)->nama ?? '-',
                                                    'status' => $existingSpj->status,
                                                    'allow_manage' => $allowManage,
                                                    'sp2d_scan' => $documents->where('document_type', 'sp2d_scan')->values()->map(fn ($d) => [
                                                        'id' => $d->id,
                                                        'name' => $d->file_name,
                                                        'url' => route('spj.documents.download', $d->id),
                                                    ]),
                                                    'bukti_gabungan' => $documents->where('document_type', 'bukti_belanja')->whereNull('sp2d_budget_code_id')->values()->map(fn ($d) => [
                                                        'id' => $d->id,
                                                        'name' => $d->file_name,
                                                        'url' => route('spj.documents.download', $d->id),
                                                    ]),
                                                    'catatan_gabungan' => optional($findings->firstWhere('sp2d_budget_code_id', null))->description ?? '',
                                                    'status_bukti_gabungan' => optional($findings->firstWhere('sp2d_budget_code_id', null))->status_bukti,
                                                    'budget_codes' => $sp2d->budgetCodes->map(function ($c) use ($documents, $findings) {
                                                        return [
                                                            'id' => $c->id,
                                                            'kode_belanja' => $c->kode_belanja,
                                                            'uraian' => $c->uraian,
                                                            'jumlah' => $c->jumlah,
                                                            'bukti' => $documents->where('document_type', 'bukti_belanja')->where('sp2d_budget_code_id', $c->id)->values()->map(fn ($d) => [
                                                                'id' => $d->id,
                                                                'name' => $d->file_name,
                                                                'url' => route('spj.documents.download', $d->id),
                                                            ]),
                                                            'catatan' => optional($findings->firstWhere('sp2d_budget_code_id', $c->id))->description ?? '',
                                                            'status_bukti' => optional($findings->firstWhere('sp2d_budget_code_id', $c->id))->status_bukti,
                                                        ];
                                                    }),
                                                ];
                                            }

                                            // Hitung kelengkapan bukti
                                            $documentsForCount = $existingSpj ? $existingSpj->documents : collect();
                                            $findingsForCount = $existingSpj ? $existingSpj->findings : collect();
                                            $hasGabunganBukti = $documentsForCount->where('document_type', 'bukti_belanja')->whereNull('sp2d_budget_code_id')->isNotEmpty();
                                            $findingGabungan = $findingsForCount->firstWhere('sp2d_budget_code_id', null);

                                            $totalAkun = $sp2d->budgetCodes->count();
                                            $countLengkap = 0;
                                            $countTidakLengkap = 0;
                                            $countBelumAdaBukti = 0;
                                            $countBelumDinilai = 0;
                                            $countBelumDibelanjakan = 0;

                                            foreach ($sp2d->budgetCodes as $code) {
                                                $finding = $findingsForCount->firstWhere('sp2d_budget_code_id', $code->id) ?? $findingGabungan;
                                                $statusBuktiCode = $finding->status_bukti ?? null;

                                                if ($statusBuktiCode === 'belum_dibelanjakan') {
                                                    $countBelumDibelanjakan++;
                                                    continue;
                                                }

                                                $hasBuktiPerKode = $documentsForCount->where('document_type', 'bukti_belanja')->where('sp2d_budget_code_id', $code->id)->isNotEmpty();
                                                $hasBukti = $hasBuktiPerKode || $hasGabunganBukti;

                                                if (!$hasBukti) {
                                                    $countBelumAdaBukti++;
                                                    continue;
                                                }

                                                if ($statusBuktiCode === 'lengkap') {
                                                    $countLengkap++;
                                                } elseif ($statusBuktiCode === 'tidak_lengkap') {
                                                    $countTidakLengkap++;
                                                } else {
                                                    $countBelumDinilai++;
                                                }
                                            }

                                            // Ekstraksi tag jenis SP2D
                                            $tipeSp2d = 'SP2D';
                                            if (preg_match('/SP2D\/([A-Z0-9\-]+)/', $sp2d->nomor_sp2d, $matches)) {
                                                $tipeSp2d = $matches[1];
                                            }
                                        @endphp
                                        <tr class="hover:bg-slate-50/80 transition-colors group">
                                            <!-- Kolom Dokumen SP2D -->
                                            <td class="py-4 px-5 sm:px-6 align-top">
                                                <div class="flex items-center gap-2 mb-1.5">
                                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold tracking-wider uppercase bg-brand-50 text-brand-800 border border-brand-200/60">
                                                        {{ $tipeSp2d }}
                                                    </span>
                                                    <span class="text-xs font-semibold text-slate-500 flex items-center gap-1">
                                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                                                        {{ $sp2d->tanggal->format('d/m/Y') }}
                                                    </span>
                                                </div>
                                                <div class="font-mono text-xs sm:text-[13px] font-bold text-slate-900 group-hover:text-brand-700 transition-colors">
                                                    {{ $sp2d->nomor_sp2d }}
                                                </div>
                                                <div class="flex items-center gap-1.5 text-[11px] text-slate-400 mt-1">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"/></svg>
                                                    <span>{{ $totalAkun }} kode rekening anggaran</span>
                                                </div>
                                            </td>

                                            <!-- Kolom Nilai Total -->
                                            <td class="py-4 px-4 align-top text-right whitespace-nowrap">
                                                <span class="font-mono text-sm sm:text-[15px] font-black text-slate-900">
                                                    Rp {{ number_format($sp2d->jumlah_total, 0, ',', '.') }}
                                                </span>
                                            </td>

                                            <!-- Kolom Status SPJ -->
                                            <td class="py-4 px-4 align-top text-center whitespace-nowrap">
                                                @if ($existingSpj)
                                                    @if ($existingSpj->status === 'reviewed')
                                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                                            Selesai Direviu
                                                        </span>
                                                    @elseif ($existingSpj->status === 'in_review')
                                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 shadow-xs">
                                                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                                                            Dalam Reviu
                                                        </span>
                                                    @elseif ($existingSpj->status === 'submitted')
                                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                                            Menunggu Reviu
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                                            <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                                                            Draft
                                                        </span>
                                                    @endif
                                                @else
                                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-rose-50/80 text-rose-600 border border-rose-200/60">
                                                        <span class="w-2 h-2 rounded-full bg-rose-400"></span>
                                                        Belum Ada SPJ
                                                    </span>
                                                @endif
                                            </td>

                                            <!-- Kolom Kelengkapan Akun Belanja -->
                                            <td class="py-4 px-4 align-top">
                                                <div class="max-w-xs space-y-2">
                                                    <!-- Progress Bar Stack -->
                                                    <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden flex shadow-inner">
                                                        @if ($totalAkun > 0)
                                                            @if ($countLengkap > 0)
                                                                <span class="h-full bg-emerald-500 transition-all" style="width: {{ ($countLengkap / $totalAkun) * 100 }}%" title="Lengkap: {{ $countLengkap }}"></span>
                                                            @endif
                                                            @if ($countTidakLengkap > 0)
                                                                <span class="h-full bg-rose-500 transition-all" style="width: {{ ($countTidakLengkap / $totalAkun) * 100 }}%" title="Perlu Perbaikan: {{ $countTidakLengkap }}"></span>
                                                            @endif
                                                            @if ($countBelumDinilai > 0)
                                                                <span class="h-full bg-amber-400 transition-all" style="width: {{ ($countBelumDinilai / $totalAkun) * 100 }}%" title="Menunggu Reviu: {{ $countBelumDinilai }}"></span>
                                                            @endif
                                                            @if ($countBelumDibelanjakan > 0)
                                                                <span class="h-full bg-indigo-400 transition-all" style="width: {{ ($countBelumDibelanjakan / $totalAkun) * 100 }}%" title="Belum Dibelanjakan: {{ $countBelumDibelanjakan }}"></span>
                                                            @endif
                                                            @if ($countBelumAdaBukti > 0)
                                                                <span class="h-full bg-slate-200 transition-all" style="width: {{ ($countBelumAdaBukti / $totalAkun) * 100 }}%" title="Belum Ada Bukti: {{ $countBelumAdaBukti }}"></span>
                                                            @endif
                                                        @endif
                                                    </div>

                                                    <!-- Breakdown Badges -->
                                                    <div class="flex flex-wrap items-center gap-1 text-[11px]">
                                                        @if ($countLengkap > 0)
                                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 font-bold border border-emerald-200/60" title="Bukti Lengkap">
                                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>{{ $countLengkap }} Lengkap
                                                            </span>
                                                        @endif
                                                        @if ($countTidakLengkap > 0)
                                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-rose-50 text-rose-700 font-bold border border-rose-200/60" title="Bukti Tidak Lengkap / Perlu Revisi">
                                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>{{ $countTidakLengkap }} Perlu Perbaikan
                                                            </span>
                                                        @endif
                                                        @if ($countBelumDinilai > 0)
                                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 font-bold border border-amber-200/60" title="Menunggu Dinilai Auditor">
                                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>{{ $countBelumDinilai }} Menunggu
                                                            </span>
                                                        @endif
                                                        @if ($countBelumDibelanjakan > 0)
                                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 font-semibold border border-indigo-200/60" title="Dana Belum Dibelanjakan">
                                                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-400"></span>{{ $countBelumDibelanjakan }} Simpan Kas
                                                            </span>
                                                        @endif
                                                        @if ($countBelumAdaBukti > 0)
                                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-100 text-slate-500 font-medium" title="Belum Ada Bukti Diunggah">
                                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>{{ $countBelumAdaBukti }} Belum Ada Bukti
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- Kolom Tindakan -->
                                            <td class="py-4 px-5 sm:px-6 align-top text-center">
                                                @if (!$existingSpj)
                                                    <button type="button"
                                                        onclick='showUploadForm(@json($sp2dJson))'
                                                        class="inline-flex items-center justify-center gap-1.5 w-full px-3.5 py-2.5 bg-brand-800 hover:bg-brand-900 text-white rounded-xl text-xs font-bold shadow-xs hover:shadow-md hover:-translate-y-0.5 transition-all">
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                                        <span>Upload SPJ</span>
                                                    </button>
                                                @else
                                                    @can('review_spj')
                                                        <button type="button"
                                                            onclick='showReviewForm(@json($reviewJson), false)'
                                                            class="inline-flex items-center justify-center gap-1.5 w-full px-3.5 py-2.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white rounded-xl text-xs font-bold shadow-xs hover:shadow-md hover:-translate-y-0.5 transition-all">
                                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                            <span>Reviu</span>
                                                        </button>
                                                    @else
                                                        <button type="button"
                                                            onclick='showReviewForm(@json($reviewJson), true)'
                                                            class="inline-flex items-center justify-center gap-1.5 w-full px-3.5 py-2.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 hover:border-slate-300 rounded-xl text-xs font-bold shadow-xs hover:shadow-sm transition-all">
                                                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                            <span>Lihat Detail</span>
                                                        </button>
                                                    @endcan
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="py-16 text-center text-slate-400">
                                                <div class="w-16 h-16 rounded-3xl bg-slate-50 border border-slate-200/70 flex items-center justify-center mx-auto mb-4 text-slate-400 shadow-xs">
                                                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                                </div>
                                                <h4 class="font-bold text-slate-800 text-base">Tidak ada dokumen SP2D ditemukan</h4>
                                                <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Silakan sesuaikan kata kunci pencarian atau bersihkan filter status dan periode bulan/tahun.</p>
                                                @if (request()->hasAny(['search', 'tahun', 'bulan', 'status']))
                                                    <a href="{{ route('spj.upload', $selectedOpd ? ['opd_id' => $selectedOpd->id] : []) }}" class="inline-flex items-center gap-1.5 mt-4 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition-colors">
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                        Bersihkan Semua Filter
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Modern Pagination Footer -->
                        @if ($sp2dsPaginator && $sp2dsPaginator->hasPages())
                            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
                                <p class="font-medium">
                                    Menampilkan <span class="font-bold text-slate-800">{{ $sp2dsPaginator->firstItem() }}&ndash;{{ $sp2dsPaginator->lastItem() }}</span> dari <span class="font-bold text-slate-800">{{ $sp2dsPaginator->total() }}</span> total SP2D
                                </p>
                                <div>{{ $sp2dsPaginator->links() }}</div>
                            </div>
                        @elseif ($sp2dsPaginator)
                            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 text-xs text-slate-500 font-medium">
                                Menampilkan seluruh <span class="font-bold text-slate-800">{{ $sp2dsPaginator->total() }}</span> dokumen SP2D
                            </div>
                        @else
                            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 text-xs text-slate-500 font-medium">
                                Menampilkan seluruh <span class="font-bold text-slate-800">{{ $sp2ds->count() }}</span> dokumen SP2D
                            </div>
                        @endif
                    </div>
                @endif
            @endif

            <!-- Upload Modal (Redesigned Clean & Executive) -->
            <div id="uploadModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-md hidden z-50 flex items-start justify-center p-3 sm:p-4 overflow-y-auto">
                <div class="bg-white rounded-3xl shadow-2xl ring-1 ring-black/5 w-full max-w-5xl my-4 sm:my-6 flex flex-col overflow-hidden max-h-[92vh]">
                    <!-- Executive Header -->
                    <div class="flex items-center justify-between px-6 py-5 bg-gradient-to-r from-slate-950 via-brand-950 to-brand-900 shrink-0 text-white border-b border-brand-800/70">
                        <div class="flex items-center gap-3.5">
                            <div class="w-11 h-11 bg-white/10 rounded-2xl flex items-center justify-center border border-white/15 text-gold-400 shrink-0 shadow-xs">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-base sm:text-lg font-bold tracking-tight text-white">Unggah Pertanggungjawaban SPJ</h3>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-gold-400/20 text-gold-300 border border-gold-400/30">Formulir Resmi</span>
                                </div>
                                <p id="modal_subtitle" class="text-xs text-slate-300 font-mono mt-0.5"></p>
                            </div>
                        </div>
                        <button type="button" onclick="hideUploadForm()" class="w-9 h-9 flex items-center justify-center rounded-xl text-slate-300 hover:text-white hover:bg-white/10 transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form action="{{ route('spj.store') }}" method="POST" enctype="multipart/form-data" class="flex flex-col min-h-0 flex-1">
                        @csrf
                        <input type="hidden" name="sp2d_id" id="modal_sp2d_id">

                        <div class="p-6 sm:p-7 space-y-7 overflow-y-auto flex-1 min-h-0 bg-slate-50/40">
                            <!-- Bagian 1: Dossier SP2D & File Utama -->
                            <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 shadow-xs space-y-5">
                                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-7 h-7 rounded-xl bg-brand-900 text-white text-xs font-bold flex items-center justify-center shrink-0 shadow-xs">1</span>
                                        <div>
                                            <h4 class="text-sm font-bold text-slate-900">Identitas SP2D &amp; Berkas Utama</h4>
                                            <p class="text-xs text-slate-400">Verifikasi kelengkapan dokumen scan SP2D dan berkas pendukung</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Executive Mini SP2D Dossier -->
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 p-4 rounded-2xl bg-gradient-to-br from-slate-900 via-brand-950 to-brand-900 text-white shadow-xs">
                                    <div class="space-y-1">
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Nomor SP2D</span>
                                        <p id="modal_nomor_sp2d" class="text-xs sm:text-sm font-mono font-bold text-white break-all"></p>
                                    </div>
                                    <div class="space-y-1 sm:border-l sm:border-white/10 sm:pl-4">
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tanggal Terbit</span>
                                        <p id="modal_tanggal_sp2d" class="text-xs sm:text-sm font-semibold text-white"></p>
                                    </div>
                                    <div class="space-y-1 sm:border-l sm:border-white/10 sm:pl-4">
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-gold-300">Nilai Total SP2D</span>
                                        <p id="modal_nilai_total" class="text-sm sm:text-base font-black font-mono text-gold-400"></p>
                                    </div>
                                </div>

                                <!-- Modern Dropzones: Scan SP2D & Dokumen Lainnya -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Scan SP2D (Wajib) -->
                                    <div class="flex flex-col justify-between p-4 rounded-2xl border-2 border-dashed border-brand-200 hover:border-brand-500 bg-brand-50/20 hover:bg-brand-50/40 transition-all">
                                        <div>
                                            <div class="flex items-center justify-between mb-2">
                                                <label class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                                    <span>Scan Dokumen SP2D</span>
                                                    <span class="text-rose-600 font-bold">*</span>
                                                </label>
                                                <span class="text-[10px] font-bold uppercase tracking-wider text-brand-700 bg-brand-50 px-2 py-0.5 rounded-md border border-brand-200/60">Wajib</span>
                                            </div>
                                            <p class="text-xs text-slate-500 mb-3">Format PDF/JPG/PNG, maks 10MB per berkas.</p>
                                        </div>
                                        <div>
                                            <label for="sp2d_scan_input" class="flex items-center justify-center gap-2.5 w-full py-3 px-4 rounded-xl bg-white border border-brand-200 text-brand-800 text-xs font-bold hover:bg-brand-50 hover:border-brand-300 cursor-pointer transition-all shadow-xs">
                                                <svg class="w-4 h-4 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                                <span>Pilih Berkas Scan SP2D</span>
                                            </label>
                                            <input type="file" name="sp2d_scan[]" id="sp2d_scan_input" multiple required accept=".pdf,.jpg,.jpeg,.png" class="hidden">
                                            <div id="sp2d_scan_filenames" class="flex flex-wrap gap-2 mt-2"></div>
                                        </div>
                                    </div>

                                    <!-- Dokumen Lainnya (Opsional) -->
                                    <div class="flex flex-col justify-between p-4 rounded-2xl border-2 border-dashed border-slate-200 hover:border-slate-400 bg-slate-50/50 hover:bg-slate-50 transition-all">
                                        <div>
                                            <div class="flex items-center justify-between mb-2">
                                                <label class="text-xs font-bold text-slate-800">Dokumen Pendukung Lainnya</label>
                                                <span class="text-[10px] font-medium text-slate-400 bg-slate-100 px-2 py-0.5 rounded-md">Opsional</span>
                                            </div>
                                            <p class="text-xs text-slate-500 mb-3">SPTJM, BAP, Nota Dinas, dsb (PDF/JPG/XLS/DOC).</p>
                                        </div>
                                        <div>
                                            <label for="dokumen_lainnya_input" class="flex items-center justify-center gap-2.5 w-full py-3 px-4 rounded-xl bg-white border border-slate-200 text-slate-700 text-xs font-bold hover:bg-slate-50 hover:border-slate-300 cursor-pointer transition-all shadow-xs">
                                                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                                <span>Pilih Berkas Pendukung</span>
                                            </label>
                                            <input type="file" name="dokumen_lainnya[]" id="dokumen_lainnya_input" multiple accept=".pdf,.jpg,.jpeg,.png,.xls,.xlsx,.doc,.docx" class="hidden">
                                            <div id="dokumen_lainnya_filenames" class="flex flex-wrap gap-2 mt-2"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Bagian 2: Akun Belanja & Bukti Belanja -->
                            <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 shadow-xs space-y-5">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-7 h-7 rounded-xl bg-brand-900 text-white text-xs font-bold flex items-center justify-center shrink-0 shadow-xs">2</span>
                                        <div>
                                            <h4 class="text-sm font-bold text-slate-900">Unggah Bukti Transaksi Belanja</h4>
                                            <p class="text-xs text-slate-400">Pilih skema pengunggahan bukti gabungan atau per rincian akun</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Elegant Mode Switch Toggle Card -->
                                <div class="p-4 rounded-2xl bg-gradient-to-r from-amber-50/60 via-amber-50/40 to-slate-50 border border-amber-200/80 flex items-start sm:items-center justify-between gap-4">
                                    <div class="flex items-start gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 mt-0.5 sm:mt-0">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                        </div>
                                        <div>
                                            <label for="modal_gabungan_toggle" class="text-xs font-bold text-slate-900 cursor-pointer block">
                                                Gunakan 1 Dokumen Bukti untuk Seluruh Akun Belanja (Gabungan)
                                            </label>
                                            <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                                                Aktifkan jika SPJ berupa satu berkas bundel gabungan (seperti SP2D Gaji, UP, atau TU).
                                            </p>
                                        </div>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                        <input type="checkbox" id="modal_gabungan_toggle" class="sr-only peer">
                                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-800"></div>
                                    </label>
                                </div>

                                <!-- Dropzone Bukti Gabungan (Tampil Saat Toggle Aktif) -->
                                <div id="gabunganSection" class="hidden">
                                    <div id="buktiGabunganCol" class="p-5 rounded-2xl border-2 border-dashed border-amber-300 bg-amber-50/40 hover:bg-amber-50/60 transition-all flex flex-col items-center justify-center text-center">
                                        <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-800 flex items-center justify-center mb-3 shadow-xs">
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                        </div>
                                        <h5 class="text-xs font-bold text-slate-900">Upload Berkas Bukti Belanja Gabungan</h5>
                                        <p class="text-[11px] text-slate-500 mt-0.5 mb-3 max-w-sm">Berkas ini akan dijadikan bukti pertanggungjawaban bersama untuk seluruh akun di bawah ini.</p>
                                        <label for="bukti_belanja_gabungan_input" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-900 hover:bg-brand-950 text-white text-xs font-bold cursor-pointer transition-all shadow-xs">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                            <span>Pilih File Bukti Gabungan</span>
                                        </label>
                                        <input type="file" name="bukti_belanja_gabungan[]" id="bukti_belanja_gabungan_input" multiple accept=".pdf,.jpg,.jpeg,.png,.xls,.xlsx" class="hidden">
                                        <div id="bukti_belanja_gabungan_filenames" class="flex flex-wrap justify-center gap-2 mt-3"></div>
                                    </div>
                                </div>

                                <!-- Tabel Daftar Akun Belanja Modern -->
                                <div class="border border-slate-200/90 rounded-2xl overflow-hidden shadow-xs">
                                    <div class="px-5 py-3 bg-slate-50/80 border-b border-slate-200/80 flex items-center justify-between text-xs text-slate-600 font-bold uppercase tracking-wider">
                                        <span>Rincian Akun Belanja &amp; Berkas Bukti</span>
                                        <span id="buktiBelanjaColHeader">Unggahan Per Rekening</span>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-left text-xs sm:text-sm">
                                            <thead class="sr-only">
                                                <tr>
                                                    <th>Kode &amp; Uraian</th>
                                                    <th>Nilai</th>
                                                    <th>File Bukti</th>
                                                </tr>
                                            </thead>
                                            <tbody id="budgetCodesContainer" class="divide-y divide-slate-100 bg-white"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Action Footer -->
                        <div class="flex items-center justify-between gap-3 px-6 sm:px-7 py-4 bg-white border-t border-slate-200/80 shrink-0">
                            <button type="button" onclick="hideUploadForm()" class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors">
                                Batalkan
                            </button>
                            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-brand-900 hover:bg-brand-950 text-white rounded-xl text-xs font-bold shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all">
                                <svg class="w-4 h-4 text-gold-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                <span>Simpan &amp; Unggah SPJ</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Review Modal (3-Step Guided Workflow Wizard) -->
            <div id="reviewModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden z-50 flex items-start justify-center p-3 sm:p-4 overflow-y-auto">
                <div class="bg-white rounded-3xl shadow-card-lg ring-1 ring-black/5 w-full max-w-5xl my-4 sm:my-6 flex flex-col overflow-hidden max-h-[92vh]">
                    <!-- Header with Gradient & Stepper -->
                    <div class="px-6 py-4 sm:py-5 bg-gradient-to-r from-brand-950 via-brand-900 to-brand-800 shrink-0 text-white border-b border-brand-800">
                        <div class="flex items-center justify-between gap-4 mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center border border-white/15 text-gold-400 shrink-0">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 id="review_modal_title" class="text-base sm:text-lg font-bold tracking-tight text-white">Reviu SPJ</h3>
                                        <span id="review_readonly_badge" class="hidden px-2 py-0.5 rounded-full text-[11px] font-semibold bg-white/15 text-gold-300 border border-white/20">Mode Lihat (Read-only)</span>
                                    </div>
                                    <p id="review_modal_subtitle" class="text-xs text-brand-200 font-mono mt-0.5"></p>
                                </div>
                            </div>
                            <button type="button" onclick="hideReviewForm()" class="w-8 h-8 flex items-center justify-center rounded-lg text-brand-200 hover:text-white hover:bg-white/10 transition-colors">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <!-- 3-Step Wizard Navigation Indicator -->
                        <div class="grid grid-cols-3 gap-2 bg-black/20 p-1.5 rounded-2xl border border-white/10">
                            <!-- Step Tab 1 -->
                            <button type="button" onclick="goToReviewStep(1)" id="review_stepper_tab_1" class="flex items-center justify-center gap-2 py-2 px-3 rounded-xl text-xs font-semibold transition-all bg-white text-brand-950 shadow-sm">
                                <span class="w-5 h-5 rounded-full bg-brand-900 text-white text-[11px] flex items-center justify-center font-bold">1</span>
                                <span class="hidden sm:inline">Info &amp; Metode Reviu</span>
                                <span class="sm:hidden">Info SP2D</span>
                            </button>
                            <!-- Step Tab 2 -->
                            <button type="button" onclick="goToReviewStep(2)" id="review_stepper_tab_2" class="flex items-center justify-center gap-2 py-2 px-3 rounded-xl text-xs font-medium text-brand-200 hover:text-white hover:bg-white/5 transition-all">
                                <span class="w-5 h-5 rounded-full bg-white/15 text-brand-100 text-[11px] flex items-center justify-center font-bold">2</span>
                                <span class="hidden sm:inline">Pemeriksaan Bukti</span>
                                <span class="sm:hidden">Bukti</span>
                            </button>
                            <!-- Step Tab 3 -->
                            <button type="button" onclick="goToReviewStep(3)" id="review_stepper_tab_3" class="flex items-center justify-center gap-2 py-2 px-3 rounded-xl text-xs font-medium text-brand-200 hover:text-white hover:bg-white/5 transition-all">
                                <span class="w-5 h-5 rounded-full bg-white/15 text-brand-100 text-[11px] flex items-center justify-center font-bold">3</span>
                                <span class="hidden sm:inline">Keputusan &amp; Simpan</span>
                                <span class="sm:hidden">Keputusan</span>
                            </button>
                        </div>
                    </div>

                    <form id="reviewForm" method="POST" class="flex flex-col min-h-0 flex-1">
                        <input type="hidden" name="review_version" id="review-version" value="0">
                        <div class="px-6 py-3"><label class="form-label" for="review-reason">Alasan perubahan (wajib untuk reviu yang sudah selesai)</label><input id="review-reason" name="review_reason" maxlength="2000" class="form-input"></div>
                        @csrf
                        <input type="hidden" name="mode_gabungan" id="review_mode_gabungan_input" value="0">

                        <!-- Content Scrollable Body -->
                        <div class="p-6 overflow-y-auto flex-1 min-h-0 space-y-6">

                            <!-- ========================================== -->
                            <!-- STEP 1: INFORMASI SP2D & PILIHAN METODE (REDESIGN TOTAL) -->
                            <!-- ========================================== -->
                            <div id="reviewStepPanel1" class="space-y-6">
                                <!-- Executive SP2D Dossier Hero Card -->
                                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-brand-950 to-brand-900 p-6 text-white shadow-card-md border border-brand-800/60">
                                    <!-- Background Pattern Accent -->
                                    <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-gold-400/5 rounded-full blur-3xl pointer-events-none"></div>
                                    <div class="absolute right-12 top-6 text-white/5 pointer-events-none hidden md:block">
                                        <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
                                    </div>

                                    <div class="relative z-10 space-y-5">
                                        <!-- Top Row: OPD & Badge Jenis -->
                                        <div class="flex flex-wrap items-center justify-between gap-3">
                                            <div class="flex items-center gap-2">
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-gold-400/15 text-gold-300 border border-gold-400/30">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-gold-400"></span>
                                                    <span id="review_opd_nama">Dinas / Instansi OPD</span>
                                                </span>
                                                <span id="review_badge_total_akun" class="px-3 py-1 rounded-full text-xs font-semibold bg-white/10 text-brand-100 border border-white/15">
                                                    0 Akun Belanja
                                                </span>
                                            </div>
                                            <span class="text-[11px] font-mono text-brand-200 uppercase tracking-wider">
                                                Dokumen Kas Daerah &bull; Anggaran 2026
                                            </span>
                                        </div>

                                        <!-- Middle: Big Nomor SP2D & Total Nilai -->
                                        <div class="grid grid-cols-1 md:grid-cols-12 gap-5 items-end pt-1">
                                            <div class="md:col-span-7 space-y-1">
                                                <p class="text-[11px] font-bold text-brand-300 uppercase tracking-widest">Nomor Surat Perintah Pencairan Dana (SP2D)</p>
                                                <h3 id="review_nomor_sp2d" class="text-base sm:text-xl font-mono font-bold text-white tracking-tight break-all"></h3>
                                                <div class="flex items-center gap-2 text-xs text-brand-200 pt-1">
                                                    <svg class="w-4 h-4 text-brand-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                                                    <span>Tanggal Diterbitkan: <strong id="review_tanggal_sp2d" class="text-white font-semibold"></strong></span>
                                                </div>
                                            </div>

                                            <div class="md:col-span-5 bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/15 text-left md:text-right">
                                                <p class="text-[11px] font-bold text-gold-300 uppercase tracking-wider mb-1">Total Nilai Pencairan</p>
                                                <p id="review_nilai_total" class="text-xl sm:text-2xl font-mono font-extrabold text-white tracking-tight"></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Grid 2 Kolom: Berkas Scan SP2D & Pengarah Metode Pemeriksaan -->
                                <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
                                    <!-- Kolom Kiri: Berkas Scan SP2D Asli (lg:col-span-5) -->
                                    <div class="lg:col-span-5 bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-3 flex flex-col h-full">
                                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-8 h-8 rounded-xl bg-brand-50 text-brand-700 flex items-center justify-center border border-brand-100">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                                </div>
                                                <div>
                                                    <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Berkas Fisik Scan SP2D</h4>
                                                    <p class="text-[11px] text-slate-500">Lembar asli yang disahkan BPKAD</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex-1">
                                            <p class="text-xs text-slate-500 mb-3 leading-relaxed">
                                                Periksa keaslian dokumen tanda tangan dan cap basah pada berkas scan sebelum mengevaluasi bukti belanja di langkah berikutnya.
                                            </p>
                                            <div id="review_scan_files" class="flex flex-wrap items-center gap-2"></div>
                                        </div>

                                        <div class="pt-3 border-t border-slate-100 text-[11px] text-slate-400 flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span>Format yang didukung: PDF, JPG, PNG (Maks 10MB)</span>
                                        </div>
                                    </div>

                                    <!-- Kolom Kanan: Metode Pemeriksaan Bukti (lg:col-span-7) -->
                                    <div class="lg:col-span-7 bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4">
                                        <div>
                                            <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                                                <svg class="w-4 h-4 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75"/></svg>
                                                Pilih Strategi Pemeriksaan Bukti Belanja
                                            </h4>
                                            <p class="text-xs text-slate-500 mt-1">
                                                Sesuaikan cara reviu dengan tipe dokumen yang diunggah bendahara OPD:
                                            </p>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                            <!-- Pilihan 1: Mode Rinci (Default) -->
                                            <div id="modeCardRinci" onclick="selectReviewMode(false)" class="group relative rounded-2xl p-4 cursor-pointer transition-all duration-200 border-2 border-brand-600 bg-brand-50/20 ring-4 ring-brand-500/10 flex flex-col justify-between">
                                                <div>
                                                    <div class="flex items-center justify-between mb-2.5">
                                                        <span class="w-8 h-8 rounded-xl bg-brand-100 text-brand-700 flex items-center justify-center font-bold">
                                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"/></svg>
                                                        </span>
                                                        <span id="radioIndicatorRinci" class="w-5 h-5 rounded-full border-2 border-brand-600 bg-brand-600 flex items-center justify-center">
                                                            <span class="w-2 h-2 rounded-full bg-white"></span>
                                                        </span>
                                                    </div>
                                                    <h5 class="text-xs font-bold text-slate-900 group-hover:text-brand-700 transition-colors">Pemeriksaan Per Kode Akun</h5>
                                                    <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                                                        Menilai bukti belanja terpisah untuk setiap mata anggaran. Ideal untuk kegiatan operasional, belanja modal, atau pengadaan.
                                                    </p>
                                                </div>
                                                <div class="mt-3 pt-2.5 border-t border-brand-100/60 flex items-center justify-between text-[11px]">
                                                    <span class="font-semibold text-brand-700">Penilaian Mandiri</span>
                                                    <span class="text-slate-400">Rekomendasi</span>
                                                </div>
                                            </div>

                                            <!-- Pilihan 2: Mode Gabungan -->
                                            <div id="modeCardGabungan" onclick="selectReviewMode(true)" class="group relative rounded-2xl p-4 cursor-pointer transition-all duration-200 border-2 border-slate-200 bg-white hover:border-slate-300 flex flex-col justify-between">
                                                <div>
                                                    <div class="flex items-center justify-between mb-2.5">
                                                        <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center font-bold border border-amber-200/60">
                                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z"/></svg>
                                                        </span>
                                                        <span id="radioIndicatorGabungan" class="w-5 h-5 rounded-full border-2 border-slate-300 bg-white flex items-center justify-center">
                                                            <span class="w-2 h-2 rounded-full bg-transparent"></span>
                                                        </span>
                                                    </div>
                                                    <h5 class="text-xs font-bold text-slate-900 group-hover:text-amber-700 transition-colors">1 Dokumen Bukti Gabungan</h5>
                                                    <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                                                        Cocok bila seluruh akun diunggah dalam 1 berkas bundel gabungan utuh (misalnya dokumen daftar gaji kolektif LS-GAJI / UP).
                                                    </p>
                                                </div>
                                                <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px]">
                                                    <span class="font-semibold text-amber-700">1 Kali Penilaian</span>
                                                    <span class="text-slate-400">Bundel Tunggal</span>
                                                </div>
                                            </div>
                                        </div>

                                        <input type="checkbox" id="review_gabungan_toggle" class="hidden">
                                    </div>
                                </div>
                            </div>

                            <!-- ========================================== -->
                            <!-- STEP 2: PEMERIKSAAN BUKTI BELANJA          -->
                            <!-- ========================================== -->
                            <div id="reviewStepPanel2" class="hidden space-y-6">
                                <!-- Mode Gabungan Container -->
                                <div id="reviewGabunganSection" class="hidden bg-slate-50/80 border border-slate-200 rounded-2xl p-5 space-y-5">
                                    <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                                        <div>
                                            <h4 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                                                Bukti Belanja Gabungan
                                            </h4>
                                            <p class="text-xs text-slate-500 mt-0.5">Satu dokumen bukti berlaku sekaligus untuk seluruh akun belanja pada SP2D ini.</p>
                                        </div>
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">Mode Gabungan Aktif</span>
                                    </div>

                                    <!-- Files & Picker -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                        <div class="bg-white p-4 rounded-xl border border-slate-200">
                                            <label class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2 block">Berkas Bukti Gabungan</label>
                                            <div id="review_bukti_gabungan_files" class="flex flex-wrap gap-2"></div>
                                        </div>
                                        <div class="bg-white p-4 rounded-xl border border-slate-200 space-y-3">
                                            <div>
                                                <label class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5 block">Penilaian Kelengkapan</label>
                                                <div id="review_status_bukti_gabungan_wrapper"></div>
                                            </div>
                                            <div>
                                                <label for="review_catatan_gabungan" class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-1 block">Catatan Pemeriksa (Opsional)</label>
                                                <textarea name="catatan_reviu_gabungan" id="review_catatan_gabungan" rows="3" placeholder="Tuliskan catatan pemeriksaan untuk bukti gabungan ini..."
                                                    class="form-input text-xs w-full resize-none"></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Daftar Akun yang Tercakup -->
                                    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                                        <div class="px-4 py-2.5 bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600">
                                            Daftar Akun Belanja yang Dicakup Bukti Gabungan Ini:
                                        </div>
                                        <div id="reviewGabunganSummaryCodes" class="p-3 divide-y divide-slate-100 text-xs"></div>
                                    </div>
                                </div>

                                <!-- Mode Rinci: Daftar Kartu Akun Belanja (Bukan Tabel Sempit) -->
                                <div id="reviewRinciSection" class="space-y-4">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-200">
                                        <div>
                                            <h4 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                                <span class="w-2.5 h-2.5 rounded-full bg-brand-600"></span>
                                                Daftar Bukti Per Akun Belanja
                                            </h4>
                                            <p class="text-xs text-slate-500 mt-0.5">Tentukan status kelengkapan dan catatan untuk masing-masing kode akun belanja di bawah ini.</p>
                                        </div>
                                        <div id="review_dinilai_counter" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-100 border border-slate-200 text-xs font-semibold text-slate-700">
                                            <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                                            <span>Progres Penilaian: <strong id="review_dinilai_count">0/0</strong> Akun</span>
                                        </div>
                                    </div>

                                    <!-- Container Kartu-Kartu Akun Belanja -->
                                    <div id="reviewBudgetCodesContainer" class="space-y-3.5"></div>
                                </div>
                            </div>

                            <!-- ========================================== -->
                            <!-- STEP 3: REKAPITULASI & STATUS AKHIR        -->
                            <!-- ========================================== -->
                            <div id="reviewStepPanel3" class="hidden space-y-6">
                                <!-- Ringkasan Eksekutif Hasil Reviu -->
                                <div class="bg-gradient-to-br from-slate-50 via-brand-50/20 to-white border border-brand-100 rounded-2xl p-5 shadow-xs">
                                    <h4 class="text-xs font-bold text-brand-900 uppercase tracking-wider mb-4 flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-brand-600"></span>
                                        Ringkasan Hasil Pemeriksaan
                                    </h4>

                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
                                        <div class="bg-white p-3.5 rounded-xl border border-slate-200 text-center">
                                            <p class="text-[11px] font-semibold text-slate-400 uppercase">Akun Lengkap</p>
                                            <p id="summary_count_lengkap" class="text-xl font-bold text-emerald-600 mt-1">0</p>
                                        </div>
                                        <div class="bg-white p-3.5 rounded-xl border border-slate-200 text-center">
                                            <p class="text-[11px] font-semibold text-slate-400 uppercase">Perlu Perbaikan</p>
                                            <p id="summary_count_tidak_lengkap" class="text-xl font-bold text-red-600 mt-1">0</p>
                                        </div>
                                        <div class="bg-white p-3.5 rounded-xl border border-slate-200 text-center">
                                            <p class="text-[11px] font-semibold text-slate-400 uppercase">Belum Dibelanjakan</p>
                                            <p id="summary_count_belum_belanja" class="text-xl font-bold text-indigo-600 mt-1">0</p>
                                        </div>
                                    </div>

                                    <!-- Catatan Temuan/Perbaikan yang Terkumpul -->
                                    <div class="bg-white p-4 rounded-xl border border-slate-200">
                                        <h5 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Daftar Catatan &amp; Temuan Pemeriksa:</h5>
                                        <div id="summary_catatan_list" class="space-y-2 text-xs text-slate-600"></div>
                                    </div>
                                </div>

                                <!-- Box Penetapan Status Akhir Reviu -->
                                <div class="bg-white border-2 border-brand-200 rounded-2xl p-5 shadow-card-sm space-y-4">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <h4 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                                <svg class="w-4 h-4 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                Keputusan Status Reviu Dokumen
                                            </h4>
                                            <p class="text-xs text-slate-500 mt-0.5">Tentukan apakah proses reviu dokumen SPJ ini masih berlangsung atau sudah selesai seluruhnya.</p>
                                        </div>
                                        <span id="review_status_badge_display" class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600">
                                            Status Belum Dipilih
                                        </span>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <!-- Card Status: in_review -->
                                        <div id="statusCardInReview" onclick="setFinalReviewStatus('in_review')" class="border-2 rounded-xl p-4 cursor-pointer transition-all border-slate-200 hover:border-amber-400 bg-white">
                                            <div class="flex items-center justify-between mb-1.5">
                                                <span class="text-xs font-bold text-amber-700 flex items-center gap-1.5">
                                                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                                    Dalam Proses Reviu
                                                </span>
                                                <span id="radioStatusInReview" class="w-4 h-4 rounded-full border-2 border-slate-300"></span>
                                            </div>
                                            <p class="text-xs text-slate-500">Pemeriksaan masih berjalan, atau menunggu kelengkapan bukti tambahan dari OPD.</p>
                                        </div>

                                        <!-- Card Status: reviewed -->
                                        <div id="statusCardReviewed" onclick="setFinalReviewStatus('reviewed')" class="border-2 rounded-xl p-4 cursor-pointer transition-all border-slate-200 hover:border-emerald-400 bg-white">
                                            <div class="flex items-center justify-between mb-1.5">
                                                <span class="text-xs font-bold text-emerald-700 flex items-center gap-1.5">
                                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                                    Selesai Direviu
                                                </span>
                                                <span id="radioStatusReviewed" class="w-4 h-4 rounded-full border-2 border-slate-300"></span>
                                            </div>
                                            <p class="text-xs text-slate-500">Seluruh akun bukti telah dinilai dan reviu dokumen SP2D ini dinyatakan selesai.</p>
                                        </div>
                                    </div>

                                    <!-- Dropdown tersembunyi sebagai backing form standard -->
                                    <select name="status" id="review_status" class="hidden">
                                        <option value="" disabled selected>-- Pilih Status Reviu --</option>
                                        <option value="in_review">Dalam Proses Reviu</option>
                                        <option value="reviewed">Selesai Direviu</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- ========================================== -->
                        <!-- FOOTER NAVIGASI CERDAS (BACK, NEXT, SUBMIT) -->
                        <!-- ========================================== -->
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 px-6 py-4 bg-slate-50 border-t border-slate-200/80 shrink-0">
                            <!-- Left / Tutup -->
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="hideReviewForm()" class="btn-secondary text-xs px-4 py-2">
                                    Batal / Tutup
                                </button>
                                <button type="button" id="review_prev_btn" onclick="goToReviewStep(currentWizardStep - 1)" class="hidden inline-flex items-center gap-1.5 text-xs font-semibold px-4 py-2 rounded-xl border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                                    Sebelumnya
                                </button>
                            </div>

                            <!-- Right Nav Buttons -->
                            <div class="flex items-center justify-end gap-2.5">
                                <!-- Tombol Selanjutnya (muncul di Step 1 & Step 2) -->
                                <button type="button" id="review_next_btn" onclick="goToReviewStep(currentWizardStep + 1)" class="inline-flex items-center gap-1.5 text-xs font-semibold px-4 py-2 rounded-xl bg-brand-700 hover:bg-brand-800 text-white shadow-card-sm transition-all">
                                    <span>Lanjut ke Penilaian Bukti</span>
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                                </button>

                                <!-- Tombol Simpan Reviu (Aktif setelah status ditentukan di Step 3) -->
                                <button type="submit" id="review_submit_btn" class="hidden btn-primary text-xs px-5 py-2 shadow-card-md disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none transition-all">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Simpan Hasil Reviu
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    function statusPickerHtml(inputName, hasBukti, currentStatus, readonly) {
        const isLengkap = currentStatus === 'lengkap';
        const isTidak = currentStatus === 'tidak_lengkap';
        const isBelumBelanja = currentStatus === 'belum_dibelanjakan';
        // "Lengkap"/"Tidak Lengkap" wajib ada bukti terupload. "Belum Dibelanjakan"
        // selalu bisa dipilih karena memang tidak ada bukti yang perlu diunggah
        // jika dana akun tsb belum dibelanjakan sama sekali.
        const buktiDisabled = readonly || !hasBukti;
        const buktiExtraClass = !hasBukti ? 'opacity-40 cursor-not-allowed' : '';
        const buktiTitle = !hasBukti ? 'title="Perlu bukti terupload untuk memilih status ini"' : '';
        return `<div class="flex flex-wrap items-center gap-1.5 status-picker">
            <button type="button" ${buktiDisabled ? 'disabled' : ''} data-value="lengkap" ${buktiTitle} class="status-btn px-2.5 py-1 rounded-lg text-[11px] font-semibold border transition-colors ${buktiExtraClass} ${isLengkap ? 'bg-emerald-50 text-emerald-700 border-emerald-300' : 'bg-white text-slate-500 border-slate-200 hover:bg-slate-50'}">Lengkap</button>
            <button type="button" ${buktiDisabled ? 'disabled' : ''} data-value="tidak_lengkap" ${buktiTitle} class="status-btn px-2.5 py-1 rounded-lg text-[11px] font-semibold border transition-colors ${buktiExtraClass} ${isTidak ? 'bg-red-50 text-red-700 border-red-300' : 'bg-white text-slate-500 border-slate-200 hover:bg-slate-50'}">Tidak Lengkap</button>
            <button type="button" ${readonly ? 'disabled' : ''} data-value="belum_dibelanjakan" title="Dana akun ini belum dibelanjakan, tidak perlu bukti" class="status-btn px-2.5 py-1 rounded-lg text-[11px] font-semibold border transition-colors ${isBelumBelanja ? 'bg-indigo-50 text-indigo-700 border-indigo-300' : 'bg-white text-slate-500 border-slate-200 hover:bg-slate-50'}">Belum Dibelanjakan</button>
            <input type="hidden" name="${inputName}" value="${currentStatus || ''}">
        </div>`;
    }

    function bindStatusPicker(container) {
        const picker = container.querySelector('.status-picker');
        if (!picker) return;
        const hiddenInput = picker.querySelector('input[type=hidden]');
        const buttons = picker.querySelectorAll('.status-btn');
        buttons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (btn.disabled) return;
                hiddenInput.value = btn.dataset.value;
                buttons.forEach(function (b) {
                    b.classList.remove('bg-emerald-50', 'text-emerald-700', 'border-emerald-300', 'bg-red-50', 'text-red-700', 'border-red-300', 'bg-indigo-50', 'text-indigo-700', 'border-indigo-300', 'bg-white', 'text-slate-500', 'border-slate-200', 'hover:bg-slate-50');
                    if (b === btn && b.dataset.value === 'lengkap') {
                        b.classList.add('bg-emerald-50', 'text-emerald-700', 'border-emerald-300');
                    } else if (b === btn && b.dataset.value === 'tidak_lengkap') {
                        b.classList.add('bg-red-50', 'text-red-700', 'border-red-300');
                    } else if (b === btn && b.dataset.value === 'belum_dibelanjakan') {
                        b.classList.add('bg-indigo-50', 'text-indigo-700', 'border-indigo-300');
                    } else {
                        b.classList.add('bg-white', 'text-slate-500', 'border-slate-200', 'hover:bg-slate-50');
                    }
                });
                if (typeof updateDinilaiCounter === 'function') {
                    updateDinilaiCounter();
                }
            });
        });
    }

    // Render daftar nama file terpilih sebagai chip dengan tanda silang di pojok
    // kanan atas untuk menghapus file tersebut sebelum diupload (antisipasi
    // salah pilih file atau perubahan dokumen sebelum submit).
    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, char => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        }[char]));
    }

    function renderFileChips(inputEl, containerEl) {
        const files = Array.from(inputEl.files || []);
        if (!files.length) {
            containerEl.innerHTML = '';
            return;
        }
        containerEl.innerHTML = files.map(function (file, idx) {
            return `<span class="relative inline-flex items-center bg-white border border-slate-200 rounded-lg pl-2.5 pr-2 py-1.5 mt-2 mr-1.5 shadow-sm max-w-[12rem]">
                <span class="text-xs text-slate-700 truncate">${escapeHtml(file.name)}</span>
                <button type="button" data-idx="${idx}" title="Hapus file" class="remove-file-btn absolute -top-2 -right-2 w-5 h-5 flex items-center justify-center rounded-full bg-red-500 border-2 border-white text-white shadow hover:bg-red-600 transition-colors z-10">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </span>`;
        }).join('');

        containerEl.querySelectorAll('.remove-file-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const idx = parseInt(btn.dataset.idx, 10);
                const dt = new DataTransfer();
                Array.from(inputEl.files).forEach(function (file, i) {
                    if (i !== idx) dt.items.add(file);
                });
                inputEl.files = dt.files;
                renderFileChips(inputEl, containerEl);
            });
        });
    }

    let currentUploadSp2d = null;

    function showUploadForm(sp2d) {
        currentUploadSp2d = sp2d;

        document.getElementById('modal_sp2d_id').value = sp2d.id;
        document.getElementById('modal_nomor_sp2d').textContent = sp2d.nomor_sp2d;
        document.getElementById('modal_tanggal_sp2d').textContent = sp2d.tanggal;
        document.getElementById('modal_nilai_total').textContent = 'Rp ' + Number(sp2d.jumlah_total).toLocaleString('id-ID');
        document.getElementById('modal_subtitle').textContent = sp2d.nomor_sp2d;

        const scanInput = document.getElementById('sp2d_scan_input');
        scanInput.value = '';
        const scanFilenamesEl = document.getElementById('sp2d_scan_filenames');
        scanFilenamesEl.innerHTML = '';
        scanInput.onchange = function () {
            renderFileChips(scanInput, scanFilenamesEl);
        };

        const lainnyaInput = document.getElementById('dokumen_lainnya_input');
        lainnyaInput.value = '';
        const lainnyaFilenamesEl = document.getElementById('dokumen_lainnya_filenames');
        lainnyaFilenamesEl.innerHTML = '';
        lainnyaInput.onchange = function () {
            renderFileChips(lainnyaInput, lainnyaFilenamesEl);
        };

        // SP2D dengan LS-GAJI/UP secara default diupload sebagai satu bukti belanja
        // gabungan, namun petugas OPD tetap dapat mengubah pilihan ini secara manual
        // melalui toggle "Gunakan 1 bukti belanja untuk seluruh akun belanja" — ada
        // kasus lain (mis. TU) yang buktinya juga hanya 1 dokumen untuk semua akun.
        const nomorSp2d = sp2d.nomor_sp2d || '';
        const isBuktiGabunganDefault = nomorSp2d.includes('SP2D/LS-GAJI') || nomorSp2d.includes('SP2D/UP');

        const gabunganToggle = document.getElementById('modal_gabungan_toggle');
        gabunganToggle.checked = isBuktiGabunganDefault;
        gabunganToggle.onchange = function () {
            renderBuktiBelanjaSection(currentUploadSp2d, gabunganToggle.checked);
        };

        renderBuktiBelanjaSection(sp2d, isBuktiGabunganDefault);

        document.getElementById('uploadModal').classList.remove('hidden');
    }

    // Menampilkan/menyusun ulang bagian upload bukti belanja sesuai mode yang
    // dipilih: 1 bukti gabungan untuk seluruh akun belanja, atau bukti per akun
    // belanja. Dipanggil saat modal dibuka maupun saat toggle diubah manual.
    function renderBuktiBelanjaSection(sp2d, isBuktiGabungan) {
        const gabunganSection = document.getElementById('gabunganSection');
        const buktiGabunganCol = document.getElementById('buktiGabunganCol');
        const gabunganInput = document.getElementById('bukti_belanja_gabungan_input');
        const gabunganFilenamesEl = document.getElementById('bukti_belanja_gabungan_filenames');

        // Kosongkan pilihan file sebelumnya setiap kali mode berganti agar tidak
        // ada file "tersembunyi" yang ikut terkirim dari mode yang tidak aktif.
        gabunganInput.value = '';
        gabunganFilenamesEl.innerHTML = '';
        gabunganInput.required = false;

        gabunganSection.classList.toggle('hidden', !isBuktiGabungan);
        buktiGabunganCol.classList.toggle('hidden', !isBuktiGabungan);

        if (isBuktiGabungan) {
            gabunganInput.onchange = function () {
                renderFileChips(gabunganInput, gabunganFilenamesEl);
            };
        }

        const container = document.getElementById('budgetCodesContainer');
        container.innerHTML = '';

        (sp2d.budget_codes || []).forEach(function (code, idx) {
            const row = document.createElement('tr');
            row.className = 'hover:bg-slate-50/70 transition-colors group';
            const buktiCell = isBuktiGabungan
                ? `<td class="px-5 py-4 align-top text-xs text-amber-700 bg-amber-50/30">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-100/70 text-amber-800 font-semibold text-[11px]">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        Tercakup Dokumen Gabungan
                    </span>
                </td>`
                : `<td class="px-5 py-4 align-top">
                    <label class="inline-flex items-center gap-1.5 px-3 py-2 bg-white hover:bg-brand-50 text-brand-800 border border-brand-200 hover:border-brand-300 rounded-xl text-xs font-bold cursor-pointer transition-all shadow-xs">
                        <svg class="w-3.5 h-3.5 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        <span>Pilih Berkas</span>
                        <input type="file" name="bukti_belanja[${code.id}][]" multiple accept=".pdf,.jpg,.jpeg,.png,.xls,.xlsx" class="hidden">
                    </label>
                    <div class="flex flex-wrap gap-2 mt-2.5 file-names"></div>
                </td>`;
            row.innerHTML = `
                <td class="px-5 py-4 align-top">
                    <div class="flex items-start gap-3">
                        <span class="w-6 h-6 rounded-lg bg-slate-100 text-slate-600 text-[11px] font-bold flex items-center justify-center shrink-0 mt-0.5">${idx + 1}</span>
                        <div>
                            <p class="font-bold text-slate-900 font-mono text-xs">${escapeHtml(code.kode_belanja)}</p>
                            <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">${escapeHtml(code.uraian)}</p>
                        </div>
                    </div>
                </td>
                <td class="px-5 py-4 align-top text-right whitespace-nowrap">
                    <span class="font-mono text-xs sm:text-sm font-bold text-slate-900">
                        Rp ${Number(code.jumlah).toLocaleString('id-ID')}
                    </span>
                </td>
                ${buktiCell}
            `;
            if (!isBuktiGabungan) {
                const fileInput = row.querySelector('input[type=file]');
                const nameLabel = row.querySelector('.file-names');
                fileInput.addEventListener('change', function () {
                    renderFileChips(fileInput, nameLabel);
                });
            }
            container.appendChild(row);
        });
    }

    function hideUploadForm() {
        document.getElementById('uploadModal').classList.add('hidden');
    }

    function fileChips(files, allowManage) {
        if (!files || !files.length) {
            return '<span class="text-xs text-slate-400 italic">Belum ada file</span>';
        }
        return files.map(function (f) {
            const deleteBtn = allowManage
                ? `<button type="button" onclick="manageDocDelete(${f.id})" title="Hapus file ini"
                    class="w-4 h-4 flex items-center justify-center rounded-full bg-red-500 text-white hover:bg-red-600 transition-colors shrink-0">
                    <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>`
                : '';
            return `<span class="inline-flex items-center gap-1.5 text-xs font-medium text-brand-700 bg-brand-50 border border-brand-100 rounded-lg pl-2.5 pr-1.5 py-1.5">
                <a href="${escapeHtml(f.url)}" target="_blank" class="inline-flex items-center gap-1.5 hover:text-brand-900">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    ${escapeHtml(f.name)}
                </a>
                ${deleteBtn}
            </span>`;
        }).join('');
    }

    function addFileButton(spjId, fieldName) {
        return `<label class="inline-flex items-center gap-1 text-xs font-semibold text-brand-600 border border-dashed border-brand-300 rounded-lg px-2.5 py-1.5 cursor-pointer hover:bg-brand-50 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tambah
            <input type="file" class="hidden" accept=".pdf,.jpg,.jpeg,.png" onchange="manageDocUpload(${spjId}, '${fieldName}', this)">
        </label>`;
    }

    function manageDocDelete(documentId) {
        if (!confirm('Hapus file ini? Gunakan tombol Tambah untuk mengunggah file penggantinya.')) {
            return;
        }
        fetch('/spj/documents/' + documentId, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        }).then(function (res) {
            if (!res.ok) {
                return res.json().then(function (data) { throw new Error(data.message || 'Gagal menghapus file.'); });
            }
            location.reload();
        }).catch(function (err) {
            alert(err.message || 'Gagal menghapus file.');
        });
    }

    function manageDocUpload(spjId, fieldName, inputEl) {
        if (!inputEl.files || !inputEl.files.length) {
            return;
        }
        const formData = new FormData();
        formData.append(fieldName, inputEl.files[0]);
        fetch('/spj/' + spjId + '/documents', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: formData,
        }).then(function (res) {
            if (!res.ok) {
                return res.json().then(function (data) { throw new Error(data.message || 'Gagal mengupload file.'); });
            }
            location.reload();
        }).catch(function (err) {
            alert(err.message || 'Gagal mengupload file.');
            inputEl.value = '';
        });
    }

    let currentReviewSpj = null;
    let currentReviewReadonly = false;
    let currentWizardStep = 1;

    // Navigasi langkah (Step 1, 2, 3)
    function goToReviewStep(step) {
        if (step < 1 || step > 3) return;
        currentWizardStep = step;

        // Toggle Panels
        document.getElementById('reviewStepPanel1').classList.toggle('hidden', step !== 1);
        document.getElementById('reviewStepPanel2').classList.toggle('hidden', step !== 2);
        document.getElementById('reviewStepPanel3').classList.toggle('hidden', step !== 3);

        // Update Stepper Tabs Styling
        for (let i = 1; i <= 3; i++) {
            const tab = document.getElementById('review_stepper_tab_' + i);
            const numBadge = tab.querySelector('span:first-child');
            if (i === step) {
                tab.className = 'flex items-center justify-center gap-2 py-2 px-3 rounded-xl text-xs font-semibold transition-all bg-white text-brand-950 shadow-sm';
                numBadge.className = 'w-5 h-5 rounded-full bg-brand-900 text-white text-[11px] flex items-center justify-center font-bold';
            } else if (i < step) {
                tab.className = 'flex items-center justify-center gap-2 py-2 px-3 rounded-xl text-xs font-medium text-emerald-300 hover:text-white hover:bg-white/5 transition-all';
                numBadge.className = 'w-5 h-5 rounded-full bg-emerald-500/30 text-emerald-300 text-[11px] flex items-center justify-center font-bold';
            } else {
                tab.className = 'flex items-center justify-center gap-2 py-2 px-3 rounded-xl text-xs font-medium text-brand-200 hover:text-white hover:bg-white/5 transition-all';
                numBadge.className = 'w-5 h-5 rounded-full bg-white/15 text-brand-100 text-[11px] flex items-center justify-center font-bold';
            }
        }

        // Nav Buttons State
        const prevBtn = document.getElementById('review_prev_btn');
        const nextBtn = document.getElementById('review_next_btn');
        const submitBtn = document.getElementById('review_submit_btn');

        prevBtn.classList.toggle('hidden', step === 1);

        if (step === 1) {
            nextBtn.classList.remove('hidden');
            nextBtn.querySelector('span').textContent = 'Lanjut ke Penilaian Bukti';
            submitBtn.classList.add('hidden');
        } else if (step === 2) {
            nextBtn.classList.remove('hidden');
            nextBtn.querySelector('span').textContent = 'Lanjut ke Keputusan & Simpan';
            submitBtn.classList.add('hidden');
        } else if (step === 3) {
            nextBtn.classList.add('hidden');
            submitBtn.classList.toggle('hidden', currentReviewReadonly);
            renderReviewSummary();
        }

        // Scroll top modal content smoothly
        const scrollableBody = document.querySelector('#reviewForm .overflow-y-auto');
        if (scrollableBody) scrollableBody.scrollTop = 0;
    }

    // Pemilihan Mode Reviu (Rinci vs Gabungan)
    function selectReviewMode(isGabungan) {
        if (currentReviewReadonly) return;
        const toggle = document.getElementById('review_gabungan_toggle');
        const modeInput = document.getElementById('review_mode_gabungan_input');
        toggle.checked = isGabungan;
        modeInput.value = isGabungan ? '1' : '0';

        // Update card styles
        const cardRinci = document.getElementById('modeCardRinci');
        const cardGabungan = document.getElementById('modeCardGabungan');
        const indRinci = document.getElementById('radioIndicatorRinci');
        const indGabungan = document.getElementById('radioIndicatorGabungan');

        if (isGabungan) {
            cardGabungan.className = 'border-2 rounded-2xl p-4 cursor-pointer transition-all flex flex-col justify-between hover:shadow-md border-amber-500 bg-amber-50/30 ring-2 ring-amber-400/20';
            indGabungan.className = 'w-5 h-5 rounded-full border-2 border-amber-600 bg-amber-600 flex items-center justify-center';
            indGabungan.innerHTML = '<span class="w-2 h-2 rounded-full bg-white"></span>';

            cardRinci.className = 'border-2 rounded-2xl p-4 cursor-pointer transition-all flex flex-col justify-between hover:shadow-md border-slate-200 bg-white';
            indRinci.className = 'w-5 h-5 rounded-full border-2 border-slate-300 bg-white flex items-center justify-center';
            indRinci.innerHTML = '<span class="w-2 h-2 rounded-full bg-transparent"></span>';
        } else {
            cardRinci.className = 'border-2 rounded-2xl p-4 cursor-pointer transition-all flex flex-col justify-between hover:shadow-md border-brand-600 bg-brand-50/20 ring-2 ring-brand-500/20';
            indRinci.className = 'w-5 h-5 rounded-full border-2 border-brand-600 bg-brand-600 flex items-center justify-center';
            indRinci.innerHTML = '<span class="w-2 h-2 rounded-full bg-white"></span>';

            cardGabungan.className = 'border-2 rounded-2xl p-4 cursor-pointer transition-all flex flex-col justify-between hover:shadow-md border-slate-200 bg-white';
            indGabungan.className = 'w-5 h-5 rounded-full border-2 border-slate-300 bg-white flex items-center justify-center';
            indGabungan.innerHTML = '<span class="w-2 h-2 rounded-full bg-transparent"></span>';
        }

        renderReviewGabunganSection(currentReviewSpj, isGabungan, currentReviewReadonly);
    }

    // Set Final Review Status (Step 3)
    function setFinalReviewStatus(statusVal) {
        if (currentReviewReadonly) return;
        const select = document.getElementById('review_status');
        select.value = statusVal;

        const cardInReview = document.getElementById('statusCardInReview');
        const cardReviewed = document.getElementById('statusCardReviewed');
        const badgeDisplay = document.getElementById('review_status_badge_display');
        const radioInReview = document.getElementById('radioStatusInReview');
        const radioReviewed = document.getElementById('radioStatusReviewed');

        if (statusVal === 'in_review') {
            cardInReview.className = 'border-2 rounded-xl p-4 cursor-pointer transition-all border-amber-500 bg-amber-50/30 ring-2 ring-amber-400/20';
            radioInReview.className = 'w-4 h-4 rounded-full border-2 border-amber-600 bg-amber-600 flex items-center justify-center';
            radioInReview.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-white"></span>';

            cardReviewed.className = 'border-2 rounded-xl p-4 cursor-pointer transition-all border-slate-200 bg-white';
            radioReviewed.className = 'w-4 h-4 rounded-full border-2 border-slate-300';
            radioReviewed.innerHTML = '';

            badgeDisplay.className = 'px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300';
            badgeDisplay.textContent = 'Dalam Proses Reviu';
        } else if (statusVal === 'reviewed') {
            cardReviewed.className = 'border-2 rounded-xl p-4 cursor-pointer transition-all border-emerald-500 bg-emerald-50/30 ring-2 ring-emerald-400/20';
            radioReviewed.className = 'w-4 h-4 rounded-full border-2 border-emerald-600 bg-emerald-600 flex items-center justify-center';
            radioReviewed.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-white"></span>';

            cardInReview.className = 'border-2 rounded-xl p-4 cursor-pointer transition-all border-slate-200 bg-white';
            radioInReview.className = 'w-4 h-4 rounded-full border-2 border-slate-300';
            radioInReview.innerHTML = '';

            badgeDisplay.className = 'px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300';
            badgeDisplay.textContent = 'Selesai Direviu';
        }

        updateReviewSubmitState();
    }

    function updateReviewSubmitState() {
        const select = document.getElementById('review_status');
        const submitBtn = document.getElementById('review_submit_btn');
        if (currentReviewReadonly) {
            submitBtn.disabled = true;
            return;
        }
        const isChosen = select.value === 'in_review' || select.value === 'reviewed';
        submitBtn.disabled = !isChosen;
    }

    // Render Ringkasan Hasil Pemeriksaan di Step 3
    function renderReviewSummary() {
        if (!currentReviewSpj) return;
        const isGabungan = document.getElementById('review_gabungan_toggle').checked;
        const codes = currentReviewSpj.budget_codes || [];

        let countLengkap = 0;
        let countTidakLengkap = 0;
        let countBelumBelanja = 0;
        const listCatatan = [];

        if (isGabungan) {
            const inputGabungan = document.querySelector('input[name="status_bukti_gabungan"]');
            const statusGabungan = inputGabungan ? inputGabungan.value : '';
            const catatanGabungan = document.getElementById('review_catatan_gabungan').value.trim();

            if (statusGabungan === 'lengkap') countLengkap = codes.length;
            else if (statusGabungan === 'tidak_lengkap') countTidakLengkap = codes.length;
            else if (statusGabungan === 'belum_dibelanjakan') countBelumBelanja = codes.length;

            if (catatanGabungan) {
                listCatatan.push(`<strong>Catatan Gabungan:</strong> ${escapeHtml(catatanGabungan)}`);
            }
        } else {
            codes.forEach(function (c) {
                const input = document.querySelector(`input[name="status_bukti[${c.id}]"]`);
                const val = input ? input.value : '';
                if (val === 'lengkap') countLengkap++;
                else if (val === 'tidak_lengkap') countTidakLengkap++;
                else if (val === 'belum_dibelanjakan') countBelumBelanja++;

                const txt = document.querySelector(`textarea[name="catatan_reviu[${c.id}]"]`);
                const noteVal = txt ? txt.value.trim() : '';
                if (noteVal) {
                    listCatatan.push(`<strong>${escapeHtml(c.kode_belanja)}:</strong> ${escapeHtml(noteVal)}`);
                }
            });
        }

        document.getElementById('summary_count_lengkap').textContent = countLengkap;
        document.getElementById('summary_count_tidak_lengkap').textContent = countTidakLengkap;
        document.getElementById('summary_count_belum_belanja').textContent = countBelumBelanja;

        const catatanContainer = document.getElementById('summary_catatan_list');
        if (listCatatan.length === 0) {
            catatanContainer.innerHTML = '<p class="text-slate-400 italic">Tidak ada catatan temuan khusus yang dituliskan.</p>';
        } else {
            catatanContainer.innerHTML = listCatatan.map(item => `
                <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-100 flex items-start gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-brand-500 mt-1.5 shrink-0"></span>
                    <div class="leading-relaxed">${item}</div>
                </div>
            `).join('');
        }
    }

    // Update Counter Akun yang Sudah Dinilai di Step 2
    function updateDinilaiCounter() {
        const codes = (currentReviewSpj && currentReviewSpj.budget_codes) || [];
        let dinilaiCount = 0;
        codes.forEach(function (c) {
            const input = document.querySelector(`input[name="status_bukti[${c.id}]"]`);
            if (input && (input.value === 'lengkap' || input.value === 'tidak_lengkap' || input.value === 'belum_dibelanjakan')) {
                dinilaiCount++;
            }
        });
        const countEl = document.getElementById('review_dinilai_count');
        if (countEl) countEl.textContent = `${dinilaiCount}/${codes.length}`;
    }

    function showReviewForm(spj, readonly) {
        currentReviewSpj = spj;
        currentReviewReadonly = readonly;

        document.getElementById('review-version').value = spj.review_version ?? 0;
        const reviewReason = document.getElementById('review-reason');
        reviewReason.value = '';
        reviewReason.required = !readonly && spj.status === 'reviewed';
        reviewReason.disabled = readonly;
        reviewReason.placeholder = spj.status === 'reviewed'
            ? 'Contoh: Bukti tambahan sudah diperiksa dan dinyatakan lengkap.'
            : 'Alasan perubahan, jika ada';
        document.getElementById('reviewForm').action = '{{ url("/spj") }}/' + spj.id + '/review';
        document.getElementById('review_modal_title').textContent = readonly ? 'Lihat Dokumen SPJ' : 'Reviu Dokumen SPJ';
        document.getElementById('review_readonly_badge').classList.toggle('hidden', !readonly);
        document.getElementById('review_modal_subtitle').textContent = spj.nomor_sp2d;
        document.getElementById('review_nomor_sp2d').textContent = spj.nomor_sp2d;
        document.getElementById('review_tanggal_sp2d').textContent = spj.tanggal;
        document.getElementById('review_nilai_total').textContent = 'Rp ' + Number(spj.jumlah_total).toLocaleString('id-ID');
        document.getElementById('review_badge_total_akun').textContent = (spj.budget_codes || []).length + ' Akun Belanja';
        const opdEl = document.getElementById('review_opd_nama');
        if (opdEl) opdEl.textContent = spj.opd_nama || 'Instansi OPD';

        document.getElementById('review_scan_files').innerHTML = fileChips(spj.sp2d_scan, spj.allow_manage)
            + (spj.allow_manage ? addFileButton(spj.id, 'sp2d_scan[]') : '');

        // Mode Gabungan Default
        const spjNomorSp2d = spj.nomor_sp2d || '';
        const hasGabunganDocs = spj.bukti_gabungan && spj.bukti_gabungan.length > 0;
        const isBuktiGabunganDefault = hasGabunganDocs
            || spjNomorSp2d.includes('SP2D/LS-GAJI')
            || spjNomorSp2d.includes('SP2D/UP');

        selectReviewMode(spj.review_mode ? spj.review_mode === 'combined' : isBuktiGabunganDefault);

        const catatanGabungan = document.getElementById('review_catatan_gabungan');
        catatanGabungan.value = spj.catatan_gabungan || '';
        catatanGabungan.readOnly = readonly;

        // Status awal
        if (spj.status === 'reviewed' || spj.status === 'in_review') {
            setFinalReviewStatus(spj.status);
        } else {
            setFinalReviewStatus('');
        }

        // Mulai dari Step 1
        goToReviewStep(1);

        document.getElementById('reviewModal').classList.remove('hidden');
    }

    function renderReviewGabunganSection(spj, isBuktiGabungan, readonly) {
        if (!spj) return;
        const gabunganSection = document.getElementById('reviewGabunganSection');
        const rinciSection = document.getElementById('reviewRinciSection');
        const hasGabunganDocs = spj.bukti_gabungan && spj.bukti_gabungan.length > 0;

        gabunganSection.classList.toggle('hidden', !isBuktiGabungan);
        rinciSection.classList.toggle('hidden', isBuktiGabungan);

        if (isBuktiGabungan) {
            document.getElementById('review_bukti_gabungan_files').innerHTML = fileChips(spj.bukti_gabungan, spj.allow_manage)
                + (spj.allow_manage ? addFileButton(spj.id, 'bukti_belanja_gabungan[]') : '');

            const statusGabunganWrapperEl = document.getElementById('review_status_bukti_gabungan_wrapper');
            statusGabunganWrapperEl.innerHTML = statusPickerHtml('status_bukti_gabungan', hasGabunganDocs, spj.status_bukti_gabungan, readonly);
            bindStatusPicker(statusGabunganWrapperEl);

            // Ringkasan akun di mode gabungan
            const summaryCodesEl = document.getElementById('reviewGabunganSummaryCodes');
            summaryCodesEl.innerHTML = (spj.budget_codes || []).map(function (c) {
                return `<div class="py-2 flex items-center justify-between">
                    <div>
                        <span class="font-mono font-bold text-slate-800">${escapeHtml(c.kode_belanja)}</span>
                        <span class="text-slate-500 ml-2">${escapeHtml(c.uraian)}</span>
                    </div>
                    <span class="font-mono font-semibold text-slate-700">Rp ${Number(c.jumlah).toLocaleString('id-ID')}</span>
                </div>`;
            }).join('');
        } else {
            // Mode Rinci: Tampilkan kartu per akun belanja (desain modern & lega)
            const container = document.getElementById('reviewBudgetCodesContainer');
            container.innerHTML = '';

            (spj.budget_codes || []).forEach(function (code, idx) {
                const card = document.createElement('div');
                card.className = 'bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 shadow-xs hover:border-brand-200 transition-all space-y-4';

                const rowHasOwnBukti = code.bukti && code.bukti.length > 0;
                const hasBukti = rowHasOwnBukti;

                card.innerHTML = `
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 pb-3 border-b border-slate-100">
                        <div class="flex items-start gap-3">
                            <span class="w-6 h-6 rounded-lg bg-brand-50 border border-brand-200 text-brand-700 text-xs font-bold flex items-center justify-center shrink-0 mt-0.5">${idx + 1}</span>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-bold text-slate-900 text-xs sm:text-sm">${escapeHtml(code.kode_belanja)}</span>
                                </div>
                                <p class="text-xs text-slate-500 mt-0.5 font-medium">${escapeHtml(code.uraian)}</p>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-[11px] font-semibold text-slate-400 uppercase">Nilai Akun</p>
                            <p class="font-mono text-sm font-bold text-slate-900">Rp ${Number(code.jumlah).toLocaleString('id-ID')}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-start">
                        <!-- Kolom Bukti Terupload (lg:col-span-5) -->
                        <div class="lg:col-span-5 bg-slate-50/70 p-3.5 rounded-xl border border-slate-200/80">
                            <label class="text-[11px] font-bold text-slate-600 uppercase tracking-wider block mb-2 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13"/></svg>
                                Berkas Bukti Belanja
                            </label>
                            <div class="flex flex-wrap items-center gap-1.5">
                                ${fileChips(code.bukti, spj.allow_manage)}
                                ${spj.allow_manage ? addFileButton(spj.id, 'bukti_belanja[' + code.id + '][]') : ''}
                            </div>
                        </div>

                        <!-- Kolom Status Kelengkapan (lg:col-span-4) -->
                        <div class="lg:col-span-3">
                            <label class="text-[11px] font-bold text-slate-600 uppercase tracking-wider block mb-2">Penilaian Bukti</label>
                            ${statusPickerHtml('status_bukti[' + code.id + ']', hasBukti, code.status_bukti, readonly)}
                        </div>

                        <!-- Kolom Catatan Reviu (lg:col-span-3) -->
                        <div class="lg:col-span-4">
                            <label class="text-[11px] font-bold text-slate-600 uppercase tracking-wider block mb-2">Catatan Akun Ini (Opsional)</label>
                            <textarea name="catatan_reviu[${code.id}]" rows="2" placeholder="Catatan khusus akun ini..."
                                class="form-input text-xs w-full resize-none rounded-xl" ${readonly ? 'readonly' : ''}>${escapeHtml(code.catatan || '')}</textarea>
                        </div>
                    </div>
                `;

                container.appendChild(card);
                bindStatusPicker(card);
            });

            updateDinilaiCounter();
        }
    }

    function hideReviewForm() {
        document.getElementById('reviewModal').classList.add('hidden');
    }
    </script>
    @endpush
</x-app-layout>

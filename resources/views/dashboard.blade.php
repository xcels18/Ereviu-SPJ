<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="page-title">Dashboard</h1>
            <p class="text-xs text-slate-500 mt-0.5">Selamat datang, {{ Auth::user()->name }}</p>
        </div>
    </x-slot>

    <div class="dashboard-content p-4 sm:p-6 lg:p-8">
        <section class="dashboard-welcome mb-8">
            <div>
                <p class="eyebrow">INSPEKTORAT PUNCAK JAYA</p>
                <h2>Pengawasan yang baik,<br><span>dimulai dari data yang tertib.</span></h2>
                <p class="welcome-description">Pantau kelengkapan SPJ dan tindak lanjuti reviu dalam satu ruang kerja.</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    @can('upload_spj')<a href="{{ route('spj.upload') }}" class="btn-primary">Kelola dokumen SPJ <span aria-hidden="true">↗</span></a>@endcan
                    @can('view_reports')<a href="{{ route('reports.index') }}" class="btn-secondary">Lihat laporan <span aria-hidden="true">→</span></a>@endcan
                </div>
            </div>
            @can('view_reports')
            <div class="completion-panel">
                <span class="text-sm font-medium text-brand-800">Kelengkapan bukti belanja</span>
                <strong>{{ $persentaseBukti }}<span>%</span></strong>
                <progress max="100" value="{{ $persentaseBukti }}" aria-label="Kelengkapan bukti belanja">{{ $persentaseBukti }}%</progress>
                <p>{{ number_format($akunBelanjaDenganBukti, 0, ',', '.') }} dari {{ number_format($totalAkunBelanja, 0, ',', '.') }} akun belanja sudah memiliki bukti atau dinilai belum dibelanjakan.</p>
            </div>
            @endcan
        </section>


        @can('view_reports')
        @if (auth()->user()->hasRole('irban'))
        <p class="text-xs text-slate-500 -mt-2 mb-3">Data berikut dibatasi pada wilayah kerja Anda.</p>
        @endif
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 mb-6">
            <div class="stat-card">
                <div class="flex items-center justify-between mb-3">
                    <span class="stat-label">Total OPD</span>
                    <div class="w-9 h-9 bg-brand-50 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-brand-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                </div>
                <span class="stat-value text-brand-700">{{ $totalOpd }}</span>
                <span class="text-xs text-slate-400 mt-1">Organisasi Perangkat Daerah</span>
            </div>
            <div class="stat-card">
                <div class="flex items-center justify-between mb-3">
                    <span class="stat-label">Total SP2D</span>
                    <div class="w-9 h-9 bg-blue-50 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
                <span class="stat-value text-blue-700">{{ $totalSp2d }}</span>
                <span class="text-xs text-slate-400 mt-1">Surat Perintah Pencairan Dana</span>
            </div>
            <div class="stat-card">
                <div class="flex items-center justify-between mb-3">
                    <span class="stat-label">Belum Upload SPJ</span>
                    <div class="w-9 h-9 bg-amber-50 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <span class="stat-value text-amber-700">{{ $sp2dBelumUpload }}</span>
                <span class="text-xs text-slate-400 mt-1">SP2D yang belum diupload SPJ-nya</span>
            </div>
            <div class="stat-card">
                <div class="flex items-center justify-between mb-3">
                    <span class="stat-label">SPJ Sudah Diupload</span>
                    <div class="w-9 h-9 bg-emerald-50 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <span class="stat-value text-emerald-700">{{ $totalSpjUploaded }}</span>
                <span class="text-xs text-slate-400 mt-1">{{ $spjWithFindings }} di antaranya ada catatan reviu</span>
            </div>
            <div class="stat-card">
                <div class="flex items-center justify-between mb-3">
                    <span class="stat-label">Akun Belanja</span>
                    <div class="w-9 h-9 bg-purple-50 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
                <span class="stat-value text-purple-700">{{ $totalAkunBelanja }}</span>
                <span class="text-xs text-slate-400 mt-1">Kode belanja pada SP2D</span>
            </div>
            <div class="stat-card">
                <div class="flex items-center justify-between mb-3">
                    <span class="stat-label">Akun Belanja Sudah Ada Bukti</span>
                    <div class="w-9 h-9 bg-teal-50 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <span class="stat-value text-teal-700">{{ $akunBelanjaDenganBukti }} <span class="text-sm font-medium text-slate-400">/ {{ $totalAkunBelanja }}</span></span>
                <span class="text-xs text-slate-400 mt-1">{{ $persentaseBukti }}% kelengkapan bukti belanja</span>
            </div>
        </div>
        @endcan

        <div class="mb-5">
            <h2 class="text-sm font-semibold text-slate-700 mb-3">Ruang kerja Anda</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                @can('manage_opd')
                <a href="{{ route('opd.index') }}" class="card p-4 flex items-center gap-4 group hover:shadow-card-md transition-shadow">
                    <div class="w-10 h-10 bg-brand-50 rounded-xl flex items-center justify-center group-hover:bg-brand-100 transition-colors shrink-0">
                        <svg class="w-5 h-5 text-brand-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-slate-900 group-hover:text-brand-700 transition-colors text-sm">Kelola OPD</p>
                        <p class="text-xs text-slate-500 mt-0.5">Manajemen data OPD</p>
                    </div>
                    <svg class="w-4 h-4 text-slate-300 group-hover:text-brand-400 transition-colors ml-auto shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                @endcan

                @can('manage_sp2d')
                <a href="{{ route('sp2d.index') }}" class="card p-4 flex items-center gap-4 group hover:shadow-card-md transition-shadow">
                    <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center group-hover:bg-blue-100 transition-colors shrink-0">
                        <svg class="w-5 h-5 text-blue-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-slate-900 group-hover:text-blue-700 transition-colors text-sm">Kelola SP2D</p>
                        <p class="text-xs text-slate-500 mt-0.5">Data Surat Pencairan Dana</p>
                    </div>
                    <svg class="w-4 h-4 text-slate-300 group-hover:text-blue-400 transition-colors ml-auto shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                @endcan

                @can('upload_spj')
                <a href="{{ route('spj.upload') }}" class="card p-4 flex items-center gap-4 group hover:shadow-card-md transition-shadow">
                    <div class="w-10 h-10 bg-teal-50 rounded-xl flex items-center justify-center group-hover:bg-teal-100 transition-colors shrink-0">
                        <svg class="w-5 h-5 text-teal-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-slate-900 group-hover:text-teal-700 transition-colors text-sm">Upload SPJ</p>
                        <p class="text-xs text-slate-500 mt-0.5">Upload Pertanggungjawaban</p>
                    </div>
                    <svg class="w-4 h-4 text-slate-300 group-hover:text-teal-400 transition-colors ml-auto shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                @endcan

                @can('view_reports')
                <a href="{{ route('reports.index') }}" class="card p-4 flex items-center gap-4 group hover:shadow-card-md transition-shadow">
                    <div class="w-10 h-10 bg-red-50 rounded-xl flex items-center justify-center group-hover:bg-red-100 transition-colors shrink-0">
                        <svg class="w-5 h-5 text-red-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-slate-900 group-hover:text-red-700 transition-colors text-sm">Laporan</p>
                        <p class="text-xs text-slate-500 mt-0.5">Ekspor laporan SPJ & temuan</p>
                    </div>
                    <svg class="w-4 h-4 text-slate-300 group-hover:text-red-400 transition-colors ml-auto shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                @endcan
            </div>
        </div>

    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <div><h1 class="page-title">Perangkat Daerah</h1><p class="text-xs text-slate-500 mt-1">Data master / Organisasi Perangkat Daerah</p></div>
    </x-slot>
    <div class="records-page py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <section class="records-heading">
                <div><p class="records-kicker">DIREKTORI ORGANISASI</p><h2>Perangkat daerah,<br><span>dalam satu direktori.</span></h2><p>Kelola identitas OPD dan telusuri dokumen pencairan dananya.</p></div>
                @can('manage_opd')<a href="{{ route('opd.create') }}" class="btn-primary"><span aria-hidden="true">+</span> Tambah OPD</a>@endcan
            </section>
            @if(session('success'))<div class="alert-success mb-5" role="status">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="alert-error mb-5" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
            <div class="records-summary">
                <div><span>Total perangkat daerah</span><strong>{{ number_format($totalOpd, 0, ',', '.') }}</strong><p>OPD terdaftar dalam aplikasi</p></div>
                <div><span>Memiliki SP2D</span><strong>{{ number_format($withSp2d, 0, ',', '.') }}</strong><p>OPD dengan dokumen pencairan</p></div>
                <div><span>Belum memiliki SP2D</span><strong>{{ number_format($totalOpd - $withSp2d, 0, ',', '.') }}</strong><p>OPD tanpa dokumen pencairan</p></div>
            </div>
            <section class="records-register" aria-labelledby="opd-list-title">
                <div class="records-toolbar"><div><h3 id="opd-list-title">Daftar perangkat daerah</h3><p>Identitas organisasi dan jumlah dokumen yang terhubung.</p></div><span class="badge-slate">{{ number_format($opds->total(), 0, ',', '.') }} OPD</span></div>
                <form action="{{ route('opd.index') }}" method="GET" class="flex flex-col sm:flex-row sm:items-end gap-3 px-5 py-5 border-b border-slate-100">
                    <div class="flex-1"><label for="opd-search" class="form-label">Cari perangkat daerah</label><input id="opd-search" type="search" name="search" maxlength="200" value="{{ request('search') }}" placeholder="Nama OPD, singkatan, atau kode unit…" class="form-input"></div>
                    <div class="flex gap-2"><button type="submit" class="btn-primary">Cari OPD</button>@if(request()->filled('search'))<a href="{{ route('opd.index') }}" class="btn-secondary">Reset</a>@endif</div>
                </form>
                <div class="table-container">
                    <table class="table records-table">
                        <thead><tr><th>Perangkat daerah</th><th>Kode unit</th><th class="text-right">Dokumen SP2D</th><th class="text-right">Tindakan</th></tr></thead>
                        <tbody>
                            @forelse($opds as $opd)
                                <tr>
                                    <td><a href="{{ route('opd.show', $opd) }}" class="records-document-number">{{ $opd->nama_opd }}</a><p class="records-document-opd">{{ $opd->singkatan ?: 'Singkatan belum diisi' }}</p></td>
                                    <td class="font-mono text-xs text-slate-500">{{ $opd->kode_unit ?: '—' }}</td>
                                    <td class="text-right tabular-nums"><span class="font-semibold text-slate-800">{{ number_format($opd->sp2d_count, 0, ',', '.') }}</span><span class="text-xs text-slate-400 ml-1">dokumen</span></td>
                                    <td><div class="flex justify-end items-center gap-2">
                                        <a href="{{ route('opd.show', $opd) }}" class="btn-secondary btn-sm" aria-label="Detail {{ $opd->nama_opd }}">Detail ↗</a>
                                        @can('manage_opd')
                                            <a href="{{ route('opd.edit', $opd) }}" class="btn-secondary btn-sm" aria-label="Ubah {{ $opd->nama_opd }}">Ubah</a>
                                            <form action="{{ route('opd.destroy', $opd) }}" method="POST" onsubmit="return confirm('Hapus OPD ini? Data terkait dapat ikut terhapus. Tindakan ini tidak dapat dibatalkan.')">@csrf @method('DELETE')<button type="submit" class="p-2 rounded-lg text-slate-400 hover:text-red-700 hover:bg-red-50 focus:ring-2 focus:ring-red-300" aria-label="Hapus {{ $opd->nama_opd }}" title="Hapus OPD"><svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M9 6V4h6v2M5 6l1 14h12l1-14M10 10v6m4-6v6"/></svg></button></form>
                                        @endcan
                                    </div></td>
                                </tr>
                            @empty
                                <tr><td colspan="4"><div class="records-empty"><h4>{{ request()->filled('search') ? 'Perangkat daerah tidak ditemukan' : 'Direktori OPD masih kosong' }}</h4><p>{{ request()->filled('search') ? 'Coba kata kunci lain atau reset pencarian untuk melihat seluruh OPD.' : 'Tambahkan perangkat daerah untuk mulai mengelola dokumen.' }}</p><a href="{{ request()->filled('search') ? route('opd.index') : route('opd.create') }}" class="btn-secondary">{{ request()->filled('search') ? 'Reset pencarian' : 'Tambah OPD' }}</a></div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="records-register-footer">@if($opds->hasPages()){{ $opds->links() }}@else<span>{{ number_format($opds->total(), 0, ',', '.') }} OPD ditampilkan</span>@endif</div>
            </section>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('opd.index') }}" class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="page-title">{{ $opd->nama_opd }}</h1>
                @if ($opd->singkatan)
                    <p class="text-sm text-slate-500 mt-0.5">{{ $opd->singkatan }}</p>
                @endif
                <div class="p-4">{{ $sp2ds->links() }}</div>
            </div>
        </div>
    </x-slot>

    <div class="p-6">
        <div class="max-w-4xl space-y-5">
            <div class="card p-5">
                <div class="flex items-center justify-between">
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Nama OPD</p>
                            <p class="font-semibold text-slate-900 mt-1">{{ $opd->nama_opd }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Singkatan</p>
                            <p class="font-semibold text-slate-900 mt-1">{{ $opd->singkatan ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Jumlah SP2D</p>
                            <p class="font-semibold text-slate-900 mt-1">{{ $sp2ds->total() }} SP2D</p>
                        </div>
                    </div>
                    @can('manage_opd')
                    <a href="{{ route('opd.edit', $opd) }}" class="btn btn-sm bg-amber-50 text-amber-700 border border-amber-100 hover:bg-amber-100">Edit</a>
                    @endcan
                </div>
            </div>

            <div class="card overflow-hidden">
                <div class="card-header">
                    <h2 class="text-base font-semibold text-slate-900">Daftar SP2D</h2>
                </div>
                @if ($sp2ds->total() > 0)
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>No SP2D</th>
                                <th>Tanggal</th>
                                <th class="text-center">TW</th>
                                <th class="text-right">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sp2ds as $sp2d)
                            <tr>
                                <td class="font-mono text-xs font-semibold text-slate-900">{{ $sp2d->nomor_sp2d }}</td>
                                <td class="text-slate-600">{{ $sp2d->tanggal->format('d/m/Y') }}</td>
                                <td class="text-center"><span class="badge-blue">TW {{ $sp2d->triwulan }}</span></td>
                                <td class="text-right font-medium text-slate-900">Rp {{ number_format($sp2d->jumlah_total, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="p-10 text-center text-slate-400">
                    <svg class="w-10 h-10 mx-auto mb-3 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Belum ada SP2D untuk OPD ini
                </div>
                @endif
                <div class="p-4">{{ $sp2ds->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>

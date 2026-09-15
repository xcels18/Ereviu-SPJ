<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div>
                <h1 class="page-title">Cetak Laporan</h1>
                <p class="text-sm text-slate-500 mt-0.5">Cetak laporan hasil reviu SPJ per OPD dalam format PDF atau Excel</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Filter -->
            <div class="card p-4 mb-6">
                <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
                    <div>
                        <label class="form-label">OPD <span class="text-red-500">*</span></label>
                        <select name="opd_id" class="form-select w-full" required>
                            <option value="">Pilih OPD...</option>
                            @foreach ($opds as $opd)
                                <option value="{{ $opd->id }}" {{ (string) request('opd_id') === (string) $opd->id ? 'selected' : '' }}>{{ $opd->nama_opd }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Tahun</label>
                        <select name="tahun" class="form-select w-full">
                            <option value="">Semua Tahun</option>
                            @foreach ($availableYears as $year)
                                <option value="{{ $year }}" {{ (string) request('tahun') === (string) $year ? 'selected' : '' }}>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Triwulan</label>
                        <select name="triwulan" class="form-select w-full">
                            <option value="all" {{ request('triwulan', 'all') === 'all' ? 'selected' : '' }}>Seluruh Triwulan</option>
                            <option value="1" {{ request('triwulan') === '1' ? 'selected' : '' }}>Triwulan 1</option>
                            <option value="2" {{ request('triwulan') === '2' ? 'selected' : '' }}>Triwulan 2</option>
                            <option value="3" {{ request('triwulan') === '3' ? 'selected' : '' }}>Triwulan 3</option>
                            <option value="4" {{ request('triwulan') === '4' ? 'selected' : '' }}>Triwulan 4</option>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="btn-primary w-full justify-center">Tampilkan</button>
                    </div>
                </form>
            </div>

            @if ($selectedOpd)
                <div class="card overflow-hidden mb-6">
                    <div class="p-4 border-b border-slate-100 flex items-center justify-between flex-wrap gap-3">
                        <div>
                            <h2 class="text-base font-semibold text-slate-900">{{ $selectedOpd->nama_opd }}</h2>
                            <p class="text-xs text-slate-500 mt-0.5">
                                {{ $meta['triwulan_label'] }}{{ $meta['tahun'] ? ' Tahun ' . $meta['tahun'] : ' (Semua Tahun)' }}
                                &middot; {{ $meta['total_akun'] }} akun belanja
                            </p>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" target="_blank" rel="noopener"
                                class="btn bg-red-600 text-white hover:bg-red-700 focus:ring-red-500 shadow-sm px-4 py-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                Cetak PDF
                            </a>
                            <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}"
                                class="btn bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500 shadow-sm px-4 py-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                Cetak Excel
                            </a>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table w-full">
                            <thead>
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>No SP2D</th>
                                    <th>Tanggal SP2D</th>
                                    <th class="text-right">Nilai SP2D</th>
                                    <th>Akun Belanja</th>
                                    <th class="text-right">Nilai Akun Belanja</th>
                                    <th class="text-center">Status</th>
                                    <th>Catatan Reviu</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($groups as $group)
                                    @foreach ($group->items as $i => $item)
                                        <tr class="hover:bg-slate-50">
                                            @if ($i === 0)
                                                <td class="text-center align-top" rowspan="{{ count($group->items) }}">{{ $group->no }}</td>
                                                <td class="font-medium text-slate-800 align-top" rowspan="{{ count($group->items) }}">{{ $group->nomor_sp2d }}</td>
                                                <td class="align-top" rowspan="{{ count($group->items) }}">{{ $group->tanggal_sp2d->format('d/m/Y') }}</td>
                                                <td class="text-right align-top" rowspan="{{ count($group->items) }}">Rp {{ number_format($group->nilai_sp2d, 0, ',', '.') }}</td>
                                            @endif
                                            <td>{{ $item->akun_belanja }}</td>
                                            <td class="text-right">Rp {{ number_format($item->nilai_akun, 0, ',', '.') }}</td>
                                            <td class="text-center">
                                                @php
                                                    $statusClass = match ($item->status) {
                                                        'Lengkap' => 'bg-emerald-50 text-emerald-700',
                                                        'Tidak Lengkap' => 'bg-red-50 text-red-700',
                                                        'Belum Dinilai' => 'bg-amber-50 text-amber-700',
                                                        'Belum Dibelanjakan' => 'bg-indigo-50 text-indigo-700',
                                                        default => 'bg-slate-100 text-slate-500',
                                                    };
                                                @endphp
                                                <span class="badge {{ $statusClass }}">{{ $item->status }}</span>
                                            </td>
                                            <td class="text-sm text-slate-600">{{ $item->catatan_reviu ?: '-' }}</td>
                                        </tr>
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="8" class="py-10 text-center text-slate-400">Tidak ada data SP2D untuk periode yang dipilih.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="card p-10 text-center text-slate-400">
                    Pilih OPD, tahun, dan triwulan terlebih dahulu untuk menampilkan laporan.
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

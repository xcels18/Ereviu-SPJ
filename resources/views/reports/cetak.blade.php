<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div>
                <h1 class="page-title">Cetak Laporan</h1>
                <p class="text-sm text-slate-500 mt-0.5">Pratinjau hasil reviu SPJ dengan susunan data yang mengikuti laporan Excel.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Filter -->
            <div class="card p-4 mb-6">
                <form action="{{ route('reports.cetak') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
                    <div>
                        <label for="report-irban" class="form-label">Irban</label>
                        <select id="report-irban" name="irban_id" class="form-select w-full" onchange="this.form.elements.opd_id.value = ''; this.form.submit()">
                            <option value="">Semua Irban</option>
                            @foreach ($irbans as $irban)
                                <option value="{{ $irban->id }}" @selected($selectedIrban?->id === $irban->id)>{{ $irban->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="report-opd_id" class="form-label">OPD <span class="text-red-500">*</span></label>
                        <select id="report-opd_id" name="opd_id" class="form-select w-full" required>
                            <option value="">Pilih OPD...</option>
                            @foreach ($opds as $opd)
                                <option value="{{ $opd->id }}" {{ (string) request('opd_id') === (string) $opd->id ? 'selected' : '' }}>{{ $opd->nama_opd }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="report-tahun" class="form-label">Tahun</label>
                        <select id="report-tahun" name="tahun" class="form-select w-full">
                            <option value="">Semua Tahun</option>
                            @foreach ($availableYears as $year)
                                <option value="{{ $year }}" {{ (string) request('tahun') === (string) $year ? 'selected' : '' }}>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="report-triwulan" class="form-label">Triwulan</label>
                        <select id="report-triwulan" name="triwulan" class="form-select w-full">
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
                @if ($selectedIrban)
                    <p class="text-xs text-slate-500 mt-3">OPD ditampilkan sesuai wilayah kerja {{ $selectedIrban->name }} dan hak akses Anda. {{ $opds->count() }} OPD tersedia.</p>
                @endif
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
                        <div class="flex flex-wrap gap-2">
                            @if (count($groups) > 0)
                            <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" target="_blank" rel="noopener"
                                class="btn-secondary">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                Cetak PDF
                            </a>
                            <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}"
                                class="btn-primary">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                Cetak Excel
                            </a>
                            @endif
                        </div>
                    </div>
                    <div class="report-sheet-heading">
                        <p>PEMERINTAH KABUPATEN PUNCAK JAYA</p>
                        <p>INSPEKTORAT DAERAH KABUPATEN PUNCAK JAYA</p>
                        <h2 id="report-sheet-title">Laporan Hasil Reviu Surat Pertanggungjawaban (SPJ)</h2>
                    </div>
                    <dl class="report-sheet-meta">
                        <div><dt>Nama OPD</dt><dd>{{ $selectedOpd->nama_opd }}</dd></div>
                        <div><dt>Periode</dt><dd>{{ $meta['triwulan_label'] }}{{ $meta['tahun'] ? ' Tahun ' . $meta['tahun'] : ' (Semua Tahun)' }}</dd></div>
                        <div><dt>Jumlah data</dt><dd>{{ number_format(count($groups), 0, ',', '.') }} SP2D · {{ number_format($meta['total_akun'], 0, ',', '.') }} akun belanja</dd></div>
                    </dl>
                    <p class="px-5 pb-3 text-xs text-slate-500" id="report-scroll-hint">Geser tabel ke samping untuk melihat seluruh kolom. Nomor, tanggal, dan nilai SP2D digabung untuk akun dalam SP2D yang sama.</p>
                    <div class="report-sheet-scroll" role="region" aria-labelledby="report-sheet-title" aria-describedby="report-scroll-hint" tabindex="0">
                        <table class="report-sheet-table">
                            <caption class="sr-only">Rincian hasil reviu SPJ per akun belanja, dikelompokkan berdasarkan SP2D.</caption>
                            <thead>
                                <tr>
                                    <th scope="col" class="text-center">No</th>
                                    <th scope="col">No SP2D</th>
                                    <th scope="col">Tanggal SP2D</th>
                                    <th scope="col" class="text-right">Nilai SP2D</th>
                                    <th scope="col">Akun Belanja</th>
                                    <th scope="col" class="text-right">Nilai Akun Belanja</th>
                                    <th scope="col" class="text-center">Status</th>
                                    <th scope="col">Catatan Reviu</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($groups as $groupIndex => $group)
                                    @foreach ($group->items as $i => $item)
                                        <tr class="{{ $groupIndex % 2 === 0 ? 'report-group-white' : 'report-group-tinted' }} {{ $i === 0 ? 'report-group-start' : '' }}">
                                            @if ($i === 0)
                                                <td class="text-center align-middle" rowspan="{{ count($group->items) }}">{{ $group->no }}</td>
                                                <td class="font-medium text-slate-800 align-middle" rowspan="{{ count($group->items) }}">{{ $group->nomor_sp2d }}</td>
                                                <td class="align-middle" rowspan="{{ count($group->items) }}">{{ $group->tanggal_sp2d->format('d/m/Y') }}</td>
                                                <td class="text-right align-middle" rowspan="{{ count($group->items) }}">Rp {{ number_format($group->nilai_sp2d, 0, ',', '.') }}</td>
                                            @endif
                                            <td class="report-account">{{ $item->akun_belanja }}</td>
                                            <td class="text-right">Rp {{ number_format($item->nilai_akun, 0, ',', '.') }}</td>
                                            <td class="text-center">
                                                @php
                                                    $statusColors = config('report-statuses')[$item->status] ?? config('report-statuses.Belum Ada Bukti');
                                                @endphp
                                                <span class="report-status-badge" style="background-color: {{ $statusColors['background'] }}; color: {{ $statusColors['text'] }};">{{ $item->status }}</span>
                                            </td>
                                            <td class="report-notes">{{ $item->catatan_reviu ?: '-' }}</td>
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

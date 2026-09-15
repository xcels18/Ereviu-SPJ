<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div>
                <h1 class="page-title">Pemantauan Reviu SPJ</h1>
                <p class="text-sm text-slate-500 mt-0.5">Daftar SP2D &mdash; {{ $opd->nama_opd }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ route('reports.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-brand-700 hover:text-brand-800 mb-4">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                Kembali ke Laporan
            </a>

            <!-- Ringkasan kelengkapan -->
            @php
                $summaryCards = [
                    [
                        'label' => 'Jumlah SP2D',
                        'count' => $summary->sp2d_count,
                        'jumlah' => $summary->sp2d_jumlah,
                        'color' => 'text-brand-700',
                        'dot' => 'bg-brand-600',
                    ],
                    [
                        'label' => 'Total Akun Belanja',
                        'count' => $summary->total,
                        'jumlah' => $summary->total_jumlah,
                        'color' => 'text-slate-900',
                        'dot' => 'bg-slate-400',
                    ],
                    [
                        'label' => 'Bukti Lengkap',
                        'count' => $summary->lengkap,
                        'jumlah' => $summary->lengkap_jumlah,
                        'color' => 'text-emerald-700',
                        'dot' => 'bg-emerald-500',
                    ],
                    [
                        'label' => 'Bukti Tidak Lengkap',
                        'count' => $summary->tidak_lengkap,
                        'jumlah' => $summary->tidak_lengkap_jumlah,
                        'color' => 'text-red-700',
                        'dot' => 'bg-red-500',
                    ],
                    [
                        'label' => 'Belum Ada Bukti',
                        'count' => $summary->belum_ada_bukti,
                        'jumlah' => $summary->belum_ada_bukti_jumlah,
                        'color' => 'text-slate-500',
                        'dot' => 'bg-slate-300',
                    ],
                    [
                        'label' => 'Belum Dibelanjakan',
                        'count' => $summary->belum_dibelanjakan,
                        'jumlah' => $summary->belum_dibelanjakan_jumlah,
                        'color' => 'text-indigo-700',
                        'dot' => 'bg-indigo-500',
                    ],
                ];
            @endphp
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
                @foreach ($summaryCards as $c)
                    <div class="card px-4 py-3.5">
                        <div class="flex items-center gap-1.5 mb-1.5">
                            <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $c['dot'] }}"></span>
                            <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 truncate">{{ $c['label'] }}</span>
                        </div>
                        <div class="flex items-baseline justify-between gap-2">
                            <span class="text-xl font-bold leading-none {{ $c['color'] }}">{{ $c['count'] }}</span>
                            <span class="text-xs font-semibold text-slate-500 whitespace-nowrap">Rp {{ number_format($c['jumlah'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Filter -->
            <div class="card p-4 mb-6">
                <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
                    <div class="lg:col-span-2">
                        <label class="form-label">Cari Nomor SP2D</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor SP2D..."
                            class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Tahun</label>
                        <select name="tahun" class="form-select w-full" onchange="this.form.submit()">
                            <option value="">Semua Tahun</option>
                            @foreach ($availableYears as $year)
                                <option value="{{ $year }}" {{ (string) request('tahun') === (string) $year ? 'selected' : '' }}>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Bulan</label>
                        <select name="bulan" class="form-select w-full" onchange="this.form.submit()">
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
                    </div>
                    <div class="lg:col-span-4 flex justify-end gap-2">
                        <button type="submit" class="btn-primary">Terapkan Filter</button>
                        @if (request()->hasAny(['search', 'tahun', 'bulan']))
                            <a href="{{ route('reports.sp2d', $opd->id) }}" class="btn bg-slate-100 text-slate-600 hover:bg-slate-200">Reset</a>
                        @endif
                    </div>
                </form>
            </div>

            <div class="card overflow-hidden">
                <div class="card-header">
                    <h2 class="text-base font-semibold text-slate-900">Daftar SP2D</h2>
                    <span class="text-xs text-slate-400">{{ $sp2dsPaginator->total() }} SP2D</span>
                </div>

                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>No SP2D</th>
                                <th>Tanggal</th>
                                <th class="text-right">Nilai Total</th>
                                <th class="text-center">Status SPJ</th>
                                <th class="text-center">Kelengkapan Bukti Akun Belanja</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $statusBadge = [
                                    'draft' => 'badge-slate',
                                    'submitted' => 'badge-blue',
                                    'in_review' => 'badge-yellow',
                                    'reviewed' => 'badge-green',
                                ];
                                $statusLabel = [
                                    'draft' => 'Draft',
                                    'submitted' => 'Menunggu Reviu',
                                    'in_review' => 'Dalam Proses Reviu',
                                    'reviewed' => 'Selesai Direviu',
                                ];
                            @endphp
                            @forelse ($rows as $row)
                                <tr>
                                    <td class="font-mono text-xs text-slate-600">{{ $row->sp2d->nomor_sp2d }}</td>
                                    <td class="text-slate-600">{{ $row->sp2d->tanggal->format('d/m/Y') }}</td>
                                    <td class="text-right font-medium text-slate-900">Rp {{ number_format($row->sp2d->jumlah_total, 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        @if ($row->spj)
                                            <span class="{{ $statusBadge[$row->spj->status] ?? 'badge-slate' }}">
                                                {{ $statusLabel[$row->spj->status] ?? ucfirst(str_replace('_', ' ', $row->spj->status)) }}
                                            </span>
                                        @else
                                            <span class="badge-slate">Belum ada SPJ</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="inline-flex flex-col items-center gap-1.5 w-36">
                                            <div class="w-full h-1.5 rounded-full bg-slate-100 overflow-hidden flex">
                                                @if ($row->total_akun > 0)
                                                    @if ($row->lengkap > 0)
                                                        <span class="h-full bg-emerald-500" style="width: {{ $row->lengkap / $row->total_akun * 100 }}%"></span>
                                                    @endif
                                                    @if ($row->tidak_lengkap > 0)
                                                        <span class="h-full bg-red-500" style="width: {{ $row->tidak_lengkap / $row->total_akun * 100 }}%"></span>
                                                    @endif
                                                    @if ($row->belum_dinilai > 0)
                                                        <span class="h-full bg-amber-400" style="width: {{ $row->belum_dinilai / $row->total_akun * 100 }}%"></span>
                                                    @endif
                                                    @if ($row->belum_ada_bukti > 0)
                                                        <span class="h-full bg-slate-300" style="width: {{ $row->belum_ada_bukti / $row->total_akun * 100 }}%"></span>
                                                    @endif
                                                    @if ($row->belum_dibelanjakan > 0)
                                                        <span class="h-full bg-indigo-400" style="width: {{ $row->belum_dibelanjakan / $row->total_akun * 100 }}%"></span>
                                                    @endif
                                                @endif
                                            </div>
                                            <div class="flex items-center justify-center gap-2 text-[11px] text-slate-500 leading-none">
                                                <span class="inline-flex items-center gap-1" title="Bukti lengkap">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>{{ $row->lengkap }}
                                                </span>
                                                <span class="inline-flex items-center gap-1" title="Bukti tidak lengkap">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>{{ $row->tidak_lengkap }}
                                                </span>
                                                <span class="inline-flex items-center gap-1" title="Belum ada bukti">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>{{ $row->belum_ada_bukti }}
                                                </span>
                                                @if ($row->belum_dinilai > 0)
                                                    <span class="inline-flex items-center gap-1" title="Sudah ada bukti, belum dinilai">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>{{ $row->belum_dinilai }}
                                                    </span>
                                                @endif
                                                @if ($row->belum_dibelanjakan > 0)
                                                    <span class="inline-flex items-center gap-1" title="Dana belum dibelanjakan">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-400"></span>{{ $row->belum_dibelanjakan }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('reports.sp2d.detail', $row->sp2d->id) }}" class="btn-secondary btn-sm">
                                            Detail
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-slate-400">
                                        <svg class="w-10 h-10 mx-auto mb-3 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        Tidak ada SP2D yang sesuai dengan filter
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($sp2dsPaginator->hasPages())
                    <div class="px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                        <p class="text-xs text-slate-500">
                            Menampilkan {{ $sp2dsPaginator->firstItem() }}&ndash;{{ $sp2dsPaginator->lastItem() }} dari {{ $sp2dsPaginator->total() }} data
                        </p>
                        <div>{{ $sp2dsPaginator->links() }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

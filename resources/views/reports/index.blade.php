<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div>
                <h1 class="page-title">Laporan</h1>
                <p class="text-sm text-slate-500 mt-0.5">Rekap kelengkapan bukti belanja per OPD</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Ringkasan -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="stat-card">
                    <span class="stat-label">OPD Terpantau</span>
                    <span class="stat-value text-brand-700 text-xl">{{ $summary->total_opd }}</span>
                </div>
                <div class="stat-card">
                    <span class="stat-label">Total SP2D</span>
                    <span class="stat-value text-blue-700 text-xl">{{ $summary->total_sp2d }}</span>
                </div>
                <div class="stat-card">
                    <span class="stat-label">Total Akun Belanja</span>
                    <span class="stat-value text-slate-900 text-xl">{{ $summary->total_akun }}</span>
                </div>
                <div class="stat-card">
                    <span class="stat-label">SPJ Selesai Direviu</span>
                    <span class="stat-value text-emerald-700 text-xl">{{ $summary->spj_reviewed }}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div class="stat-card">
                    <div class="flex items-center justify-between mb-1">
                        <span class="stat-label">Akun Lengkap</span>
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    </div>
                    <span class="stat-value text-emerald-700 text-xl">{{ $summary->akun_lengkap }}</span>
                </div>
                <div class="stat-card">
                    <div class="flex items-center justify-between mb-1">
                        <span class="stat-label">Akun Tidak Lengkap</span>
                        <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                    </div>
                    <span class="stat-value text-red-700 text-xl">{{ $summary->akun_tidak_lengkap }}</span>
                </div>
                <div class="stat-card">
                    <div class="flex items-center justify-between mb-1">
                        <span class="stat-label">Belum Ada Bukti</span>
                        <span class="w-2.5 h-2.5 rounded-full bg-slate-300"></span>
                    </div>
                    <span class="stat-value text-slate-500 text-xl">{{ $summary->akun_belum_ada_bukti }}</span>
                </div>
            </div>

            @if ($summary->akun_belum_dinilai > 0)
                <p class="text-xs text-amber-600 mb-6 -mt-2">
                    Catatan: {{ $summary->akun_belum_dinilai }} akun belanja sudah memiliki bukti namun status kelengkapannya belum ditentukan oleh pemeriksa.
                </p>
            @endif

            <!-- Filter -->
            <div class="card p-4 mb-6">
                <div class="flex items-center gap-2 mb-4">
                    <a href="{{ route('reports.index', array_merge(request()->only(['search', 'tahun']), ['view' => 'ringkasan'])) }}"
                        class="px-3 py-1.5 rounded-lg text-sm font-medium {{ $view === 'ringkasan' ? 'bg-brand-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        Ringkasan OPD
                    </a>
                    <a href="{{ route('reports.index', array_merge(request()->only(['search', 'tahun']), ['view' => 'bulanan'])) }}"
                        class="px-3 py-1.5 rounded-lg text-sm font-medium {{ $view === 'bulanan' ? 'bg-brand-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        Per Bulan
                    </a>
                </div>
                <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
                    <input type="hidden" name="view" value="{{ $view }}">
                    <div class="lg:col-span-2">
                        <label class="form-label">Cari Nama OPD</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama OPD..."
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
                    @if ($view === 'ringkasan')
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
                    @endif
                    <div class="lg:col-span-4 flex justify-end gap-2">
                        <button type="submit" class="btn-primary">Terapkan Filter</button>
                        @if (request()->hasAny(['search', 'tahun', 'bulan']))
                            <a href="{{ route('reports.index', ['view' => $view]) }}" class="btn bg-slate-100 text-slate-600 hover:bg-slate-200">Reset</a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Detail per OPD -->
            <div class="card overflow-hidden mb-6">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between flex-wrap gap-3">
                    <div class="flex items-center gap-3">
                        <h2 class="text-base font-semibold text-slate-900">Detail Kelengkapan Bukti per OPD</h2>
                        <span class="text-xs text-slate-400">{{ $opdReports->count() }} OPD</span>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" target="_blank" rel="noopener"
                            class="btn bg-red-600 text-white hover:bg-red-700 focus:ring-red-500 shadow-sm px-3 py-1.5 text-xs">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            Cetak PDF
                        </a>
                        <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}"
                            class="btn bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500 shadow-sm px-3 py-1.5 text-xs">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            Cetak Excel
                        </a>
                    </div>
                </div>
                @if ($view === 'bulanan')
                    @php
                        $bulanSingkat = [
                            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
                            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
                            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
                        ];
                        $statusBadge = [
                            'lengkap' => 'bg-emerald-50 text-emerald-700',
                            'tidak_lengkap' => 'bg-red-50 text-red-700',
                            'belum_upload' => 'bg-slate-100 text-slate-500',
                        ];
                        $statusLabel = [
                            'lengkap' => 'Lengkap',
                            'tidak_lengkap' => 'Tidak Lengkap',
                            'belum_upload' => 'Belum Upload Bukti SPJ',
                        ];
                    @endphp
                    <div class="px-4 pt-3 flex flex-wrap items-center gap-3 text-xs text-slate-500">
                        <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Lengkap</span>
                        <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-red-500"></span> Tidak Lengkap</span>
                        <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-slate-300"></span> Belum Upload Bukti SPJ</span>
                        <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full border border-slate-300"></span> Tidak Ada SP2D</span>
                        @if (!request()->filled('tahun'))
                            <span class="ml-auto text-amber-600">Menampilkan seluruh tahun (pilih Tahun untuk hasil per periode yang lebih spesifik).</span>
                        @endif
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table w-full">
                            <thead>
                                <tr>
                                    <th>Dinas</th>
                                    @foreach ($bulanSingkat as $label)
                                        <th class="text-center">{{ $label }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($opdReports as $row)
                                    <tr class="hover:bg-slate-50">
                                        <td>
                                            <p class="font-medium text-slate-800">{{ $row->opd->nama_opd }}</p>
                                            <p class="text-xs text-slate-400">{{ $row->opd->singkatan ?? '-' }}</p>
                                        </td>
                                        @foreach ($bulanSingkat as $num => $label)
                                            @php $cell = $monthlyMatrix[$row->opd->id][$num] ?? null; @endphp
                                            <td class="text-center">
                                                @if ($cell)
                                                    <span class="badge {{ $statusBadge[$cell->status] }}" title="{{ $statusLabel[$cell->status] }}">
                                                        {{ $statusLabel[$cell->status] }}
                                                    </span>
                                                @else
                                                    <span class="text-slate-300">&mdash;</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="13" class="py-10 text-center text-slate-400">Tidak ada data OPD yang sesuai.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @else
                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th>OPD</th>
                                <th class="text-center">SP2D</th>
                                <th class="text-center">SPJ Diupload</th>
                                <th class="text-center">Selesai Direviu</th>
                                <th class="text-center">Total Akun</th>
                                <th class="text-center">Lengkap</th>
                                <th class="text-center">Tidak Lengkap</th>
                                <th class="text-center">Belum Ada Bukti</th>
                                <th class="text-center">% Lengkap</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($opdReports as $row)
                                <tr class="hover:bg-slate-50">
                                    <td>
                                        <p class="font-medium text-slate-800">{{ $row->opd->nama_opd }}</p>
                                        <p class="text-xs text-slate-400">{{ $row->opd->singkatan ?? '-' }}</p>
                                    </td>
                                    <td class="text-center">{{ $row->total_sp2d }}</td>
                                    <td class="text-center">{{ $row->total_sp2d - $row->sp2d_belum_upload }}</td>
                                    <td class="text-center">{{ $row->spj_reviewed }}</td>
                                    <td class="text-center font-medium">{{ $row->total_akun }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-emerald-50 text-emerald-700">{{ $row->akun_lengkap }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-red-50 text-red-700">{{ $row->akun_tidak_lengkap }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-slate-100 text-slate-500">{{ $row->akun_belum_ada_bukti }}</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="flex items-center gap-2 justify-center">
                                            <div class="w-16 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                                <div class="h-full bg-emerald-500" style="width: {{ min($row->persentase_lengkap, 100) }}%"></div>
                                            </div>
                                            <span class="text-xs font-medium text-slate-600">{{ $row->persentase_lengkap }}%</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('reports.sp2d', $row->opd->id) }}"
                                            class="text-xs font-medium text-brand-700 hover:text-brand-800">Detail</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="py-10 text-center text-slate-400">Tidak ada data OPD yang sesuai.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($opdReports->count() > 0)
                            <tfoot>
                                <tr class="bg-slate-50 font-semibold">
                                    <td>Total</td>
                                    <td class="text-center">{{ $summary->total_sp2d }}</td>
                                    <td class="text-center">{{ $summary->total_sp2d - $opdReports->sum('sp2d_belum_upload') }}</td>
                                    <td class="text-center">{{ $summary->spj_reviewed }}</td>
                                    <td class="text-center">{{ $summary->total_akun }}</td>
                                    <td class="text-center text-emerald-700">{{ $summary->akun_lengkap }}</td>
                                    <td class="text-center text-red-700">{{ $summary->akun_tidak_lengkap }}</td>
                                    <td class="text-center text-slate-500">{{ $summary->akun_belum_ada_bukti }}</td>
                                    <td class="text-center">
                                        {{ $summary->total_akun > 0 ? round(($summary->akun_lengkap / $summary->total_akun) * 100, 1) : 0 }}%
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>

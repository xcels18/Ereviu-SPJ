<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3 w-full">
            <div><h1 class="page-title">Laporan SPJ</h1><p class="text-sm text-slate-500 mt-1">Pantau bukti belanja dan hasil reviu per OPD.</p></div>
            <a href="{{ route('reports.cetak') }}" class="btn-secondary">Laporan rinci & cetak</a>
        </div>
    </x-slot>
    @php
        $months = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
        $statusFields = ['Belum Ada Bukti'=>'akun_belum_ada_bukti','Belum Direviu'=>'akun_belum_dinilai','Tidak Lengkap'=>'akun_tidak_lengkap','Lengkap'=>'akun_lengkap','Belum Dibelanjakan'=>'akun_belum_dibelanjakan'];
        $period = ($view === 'ringkasan' && request('bulan') ? ($months[(int) request('bulan')] ?? '') . ' · ' : '') . (request('tahun') ?: 'Semua tahun');
    @endphp
    <div class="py-6 reports-workspace">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="card reports-controls">
                <nav class="reports-tabs" aria-label="Tampilan laporan">
                    @foreach (['ringkasan'=>'Ringkasan OPD','bulanan'=>'Pemantauan bulanan'] as $key=>$label)
                        <a href="{{ route('reports.index', array_merge(request()->only(['search','tahun']), ['view'=>$key])) }}" class="{{ $view === $key ? 'is-active' : '' }}" @if($view === $key) aria-current="page" @endif>{{ $label }}</a>
                    @endforeach
                </nav>
                <form action="{{ route('reports.index') }}" method="GET" class="reports-filter">
                    <input type="hidden" name="view" value="{{ $view }}">
                    <div class="reports-search"><label for="report-search" class="form-label">OPD</label><input id="report-search" type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama OPD…" class="form-input w-full"></div>
                    <div><label for="report-year" class="form-label">Tahun</label><select id="report-year" name="tahun" class="form-select w-full"><option value="">Semua tahun</option>@foreach($availableYears as $year)<option value="{{ $year }}" @selected((string)request('tahun') === (string)$year)>{{ $year }}</option>@endforeach</select></div>
                    @if($view === 'ringkasan')<div><label for="report-month" class="form-label">Bulan</label><select id="report-month" name="bulan" class="form-select w-full"><option value="">Semua bulan</option>@foreach($months as $num=>$label)<option value="{{ $num }}" @selected((string)request('bulan') === (string)$num)>{{ $label }}</option>@endforeach</select></div>@endif
                    <button type="submit" class="btn-primary">Tampilkan</button>
                    @if(request()->filled('search') || request()->filled('tahun') || request()->filled('bulan'))<a href="{{ route('reports.index', ['view'=>$view]) }}" class="btn-secondary">Reset</a>@endif
                </form>
            </div>
            <div class="reports-context"><span>{{ $period }} · {{ $summary->total_opd }} OPD sesuai filter</span><span>Seluruh angka mengikuti filter aktif</span></div>
            <div class="reports-totals">
                @foreach(['SP2D'=>$summary->total_sp2d,'Akun belanja'=>$summary->total_akun,'SPJ selesai direviu'=>$summary->spj_reviewed] as $label=>$value)
                    <div><span>{{ $label }}</span><strong>{{ number_format($value,0,',','.') }}</strong></div>
                @endforeach
                <div><span>Nilai SP2D</span><strong class="reports-money">Rp {{ number_format($summary->total_anggaran,0,',','.') }}</strong></div>
            </div>
            <section class="card reports-status-panel" aria-labelledby="status-title">
                <div class="reports-section-title"><h2 id="status-title">Status akun belanja</h2><span>Satuan: akun, bukan jumlah dokumen</span></div>
                <div class="reports-status-grid">
                    @foreach($statusFields as $label=>$field)
                        @php $colors=config('report-statuses')[$label]; $count=$summary->$field - ($label === 'Lengkap' ? $summary->akun_belum_dibelanjakan : 0); @endphp
                        <div><span><i style="background:{{ $colors['text'] }}"></i>{{ $label }}</span><strong>{{ number_format($count,0,',','.') }}</strong><small>{{ $summary->total_akun ? round($count/$summary->total_akun*100,1) : 0 }}% dari seluruh akun</small></div>
                    @endforeach
                </div>
            </section>
            <section class="card overflow-hidden reports-details">
                <div class="reports-section-title reports-table-title"><div><h2>{{ $view === 'bulanan' ? 'Status per bulan' : 'Rincian per OPD' }}</h2><p>{{ $view === 'bulanan' ? 'Tuntas berarti seluruh akun lengkap atau belum dibelanjakan. Status campuran ditandai perlu tindak lanjut.' : 'Baca jumlah akun per status, lalu buka rincian SP2D untuk menindaklanjuti.' }}</p></div><div class="flex gap-2"><a class="btn-secondary" href="{{ request()->fullUrlWithQuery(['export'=>'pdf']) }}" target="_blank" rel="noopener">PDF</a><a class="btn-secondary" href="{{ request()->fullUrlWithQuery(['export'=>'excel']) }}">Excel</a></div></div>
                @if ($view === 'bulanan')
                    @php
                        $bulanSingkat = [
                            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
                            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
                            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
                        ];
                        $statusBadge = [
                            'lengkap' => 'bg-emerald-50 text-emerald-700',
                            'tidak_lengkap' => 'bg-amber-50 text-amber-700',
                            'belum_upload' => 'bg-red-50 text-red-700',
                        ];
                        $statusLabel = [
                            'lengkap' => 'Tuntas',
                            'tidak_lengkap' => 'Perlu tindak lanjut',
                            'belum_upload' => 'Belum ada bukti',
                        ];
                    @endphp
                    <div class="px-4 pt-3 flex flex-wrap items-center gap-3 text-xs text-slate-500">
                        <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Tuntas</span>
                        <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Perlu tindak lanjut</span>
                        <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-red-500"></span> Belum ada bukti</span>
                        <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full border border-slate-300"></span> Tidak Ada SP2D</span>
                        @if (!request()->filled('tahun'))
                            <span class="ml-auto text-amber-600">Menampilkan seluruh tahun (pilih Tahun untuk hasil per periode yang lebih spesifik).</span>
                        @endif
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table w-full">
                            <thead>
                                <tr>
                                    <th>OPD</th>
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
                    <div class="reports-table-scroll" tabindex="0" role="region" aria-label="Rincian status per OPD">
                        <table class="table w-full reports-summary-table">
                            <thead><tr><th scope="col">OPD / SP2D</th><th scope="col">Progres reviu SPJ</th>@foreach($statusFields as $label=>$field)<th scope="col" class="text-center">{{ $label }}<small>akun</small></th>@endforeach<th scope="col">Rincian</th></tr></thead>
                            <tbody>
                                @forelse($opdReports as $row)
                                    <tr>
                                        <td><strong>{{ $row->opd->nama_opd }}</strong><small>{{ $row->total_sp2d }} SP2D · {{ $row->total_akun }} akun belanja</small></td>
                                        <td><span>{{ $row->spj_reviewed }} selesai / {{ $row->total_sp2d }} SP2D</span><small>{{ $row->spj_proses }} SPJ dalam proses · {{ $row->sp2d_belum_upload }} belum ada SPJ</small></td>
                                        @foreach($statusFields as $label=>$field)
                                            @php $colors=config('report-statuses')[$label]; $count=$row->$field - ($label === 'Lengkap' ? $row->akun_belum_dibelanjakan : 0); @endphp
                                            <td class="text-center"><span class="reports-count" style="background:{{ $colors['background'] }};color:{{ $colors['text'] }}">{{ number_format($count,0,',','.') }}</span></td>
                                        @endforeach
                                        <td><a href="{{ route('reports.sp2d', array_merge(['opd'=>$row->opd->id], request()->only(['tahun','bulan']))) }}" class="btn-secondary" aria-label="Lihat SP2D {{ $row->opd->nama_opd }}">Lihat</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-center py-12">Tidak ada OPD sesuai filter. Ubah pencarian atau reset filter.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
            <details class="reports-help"><summary>Cara membaca laporan</summary><p>Status akun berbeda dengan status proses reviu SPJ. Selesai direviu berarti pemeriksaan selesai; hasilnya masih dapat berupa Tidak Lengkap. Belum Direviu berarti bukti tersedia tetapi belum dinilai. Belum Dibelanjakan ditampilkan terpisah dari Lengkap.</p><p>Ringkasan cetak PDF/Excel masih menggunakan format sebelumnya: angka Lengkap mencakup Belum Dibelanjakan. Gunakan Laporan rinci & cetak untuk status setiap akun secara terpisah.</p></details>
        </div>
    </div>
</x-app-layout>

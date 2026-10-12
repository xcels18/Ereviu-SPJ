<x-app-layout>
<x-slot name="header"><div><h1 class="page-title">Analisis Belanja Daerah</h1><p class="text-sm text-slate-500 mt-1">Belanja apa, OPD mana, dan berapa nilainya.</p></div></x-slot>
@php
    $rupiah=fn($value)=>'Rp '.number_format($value,0,',','.');
    $labels=['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    $filters=array_filter(array_merge(request()->only(['bulan','tanggal_dari','tanggal_sampai','nomor_sp2d','nilai_min','nilai_max']), ['tahun'=>$year,'triwulan'=>$quarter,'opd_id'=>$selected,'belanja'=>$term,'kode'=>$code]),fn($v)=>$v!==null && $v!=='');
    $period=($quarter ? 'Triwulan '.$quarter : 'Seluruh tahun').' '.$year;
    $top=$accounts->first(); $leader=$ranking->first();
    $pieColors=['#1e3a5f','#587faf','#439575','#a17c2c','#8062aa','#b56971'];
    $slices=$accounts->take(5)->map(fn($a)=>['code'=>$a->kode_belanja,'name'=>$a->nama_belanja ?: 'Belanja tanpa uraian','amount'=>(float)$a->amount])->values();
    $other=(float)$totals->amount-$slices->sum('amount');
    if($other>0.01) $slices->push(['name'=>'Belanja lainnya','amount'=>$other]);
    $pieValid=$totals->amount>0 && $accounts->every(fn($a)=>$a->amount>=0) && $other>=-0.01;
    $offset=0; $stops=[];
    foreach($slices as $i=>$slice){$next=$offset+($totals->amount>0 ? $slice['amount']/$totals->amount*100 : 0);$stops[]=$pieColors[$i].' '.$offset.'% '.$next.'%';$offset=$next;}
    $lineMax=max(1,$months->max('amount')); $lineMin=min(0,$months->min('amount')); $lineRange=$lineMax-$lineMin;
    $linePoints=$months->values()->map(fn($m,$i)=>(65+$i*565/max(1,$months->count()-1)).','.round(180-($m['amount']-$lineMin)/$lineRange*145,2))->implode(' ');

@endphp
<div class="p-6 max-w-7xl mx-auto spending-analysis analysis-explorer" x-data="spendingDetailModal()" @keydown.escape.window="tip.visible=false;close()" @scroll.window="tip.visible=false">
    @if($errors->any())<div class="alert-error mb-4" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form method="GET" action="{{ route('spending-analysis.index') }}" class="card analysis-query analysis-query-expanded">
        <div><label for="analysis-year" class="form-label">Tahun</label><input id="analysis-year" name="tahun" type="number" min="2000" max="2100" value="{{ $year }}" class="form-input w-full" required></div>
        <div><label for="analysis-quarter" class="form-label">Periode</label><select id="analysis-quarter" name="triwulan" class="form-select w-full"><option value="">Setahun</option>@foreach(range(1,4) as $q)<option value="{{ $q }}" @selected((int)$quarter===$q)>Triwulan {{ $q }}</option>@endforeach</select></div>
        <div><label for="analysis-opd" class="form-label">OPD</label><select id="analysis-opd" name="opd_id" class="form-select w-full"><option value="">Seluruh OPD sesuai akses</option>@foreach($opds as $opd)<option value="{{ $opd->id }}" @selected((string)$selected===(string)$opd->id)>{{ $opd->nama_opd }}</option>@endforeach</select></div>
        <div><label for="analysis-term" class="form-label">Nama belanja</label><input id="analysis-term" name="belanja" list="analysis-spending-names" autocomplete="off" value="{{ $term }}" placeholder="Ketik atau pilih nama belanja" class="form-input w-full" maxlength="150"><datalist id="analysis-spending-names">@foreach($spendingNames as $spendingName)<option value="{{ $spendingName }}"></option>@endforeach</datalist><p class="text-xs text-slate-500 mt-1">Saran dari rincian SP2D sesuai periode dan OPD. Kata kunci juga dapat digunakan.</p></div>
        <details class="analysis-advanced" @if(request()->anyFilled(['bulan','tanggal_dari','tanggal_sampai','nomor_sp2d','nilai_min','nilai_max','kode'])) open @endif>
            <summary>Filter lanjutan <span>— bulan, tanggal, kode, nomor SP2D, dan nilai</span></summary>
            <div class="analysis-advanced-grid">
                <div><label for="filter-month" class="form-label">Bulan</label><select id="filter-month" name="bulan" class="form-select w-full"><option value="">Semua bulan</option>@foreach($labels as $i=>$label)<option value="{{ $i+1 }}" @selected((int)request('bulan')===$i+1)>{{ $label }}</option>@endforeach</select></div>
                <div><label for="filter-from" class="form-label">Tanggal SP2D mulai</label><input id="filter-from" type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}" class="form-input w-full"></div>
                <div><label for="filter-to" class="form-label">Tanggal SP2D sampai</label><input id="filter-to" type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}" class="form-input w-full"></div>
                <div><label for="filter-code" class="form-label">Kode belanja persis</label><input id="filter-code" name="kode" value="{{ $code }}" maxlength="50" placeholder="Semua kode" class="form-input w-full"></div>
                <div><label for="filter-number" class="form-label">Nomor / jenis SP2D</label><input id="filter-number" name="nomor_sp2d" value="{{ request('nomor_sp2d') }}" maxlength="100" placeholder="Contoh: LS-GAJI, TU, 01567" class="form-input w-full"></div>
                <div><label for="filter-min" class="form-label">Nilai akun minimum (Rp)</label><input id="filter-min" type="number" min="0" step="0.01" name="nilai_min" value="{{ request('nilai_min') }}" placeholder="Tanpa batas" class="form-input w-full"></div>
                <div><label for="filter-max" class="form-label">Nilai akun maksimum (Rp)</label><input id="filter-max" type="number" min="0" step="0.01" name="nilai_max" value="{{ request('nilai_max') }}" placeholder="Tanpa batas" class="form-input w-full"></div>
            </div>
            <p class="text-xs text-slate-500 mt-3">Semua filter diterapkan bersamaan. Rentang tanggal tetap berada dalam tahun dan triwulan pilihan. Batas nilai berlaku per baris akun, bukan total SP2D.</p>
        </details>
        <div class="analysis-filter-actions"><span>Sesuaikan pilihan lalu tampilkan hasil analisis.</span><a class="btn-secondary" href="{{ route('spending-analysis.index',['tahun'=>$year]) }}">Reset</a><button class="btn-primary">Tampilkan analisis</button></div>
    </form>
    <div class="analysis-filter-context"><span>{{ $period }} · {{ $selected ? $names[$selected] : 'Seluruh OPD sesuai akses' }}{{ $term ? ' · Nama memuat: '.$term : '' }}{{ $code ? ' · Kode: '.$code : '' }}</span><a href="{{ route('spending-analysis.index',['tahun'=>$year]) }}">Reset filter</a></div>
    <div class="analysis-active-filters">@foreach(['bulan'=>'Bulan','tanggal_dari'=>'Mulai','tanggal_sampai'=>'Sampai','nomor_sp2d'=>'Nomor SP2D','nilai_min'=>'Nilai minimum','nilai_max'=>'Nilai maksimum'] as $key=>$label)@if(request()->filled($key))<span>{{ $label }}: {{ request($key) }}</span>@endif
@endforeach</div>
    @if($code)<a class="text-xs text-brand-700" href="{{ route('spending-analysis.index',array_diff_key($filters,['kode'=>true])) }}">Hapus batasan kode {{ $code }}</a>@endif
    @if($totals->entries)
    <section class="analysis-result" aria-labelledby="analysis-result-title">
        <header class="analysis-result-header"><div><h2 id="analysis-result-title">Hasil analisis</h2><p>{{ $period }} · {{ $selected ? $names[$selected] : 'Seluruh OPD sesuai akses' }}</p></div><span>Seluruh angka mengikuti filter aktif</span></header>
        <div class="analysis-result-base"><span>Total nilai rincian belanja sesuai filter</span><strong>{{ $rupiah($totals->amount) }}</strong><p>Dasar pembanding untuk kedua kotak di bawah. Bukan total pagu anggaran atau nilai belanja yang telah diverifikasi SPJ.</p></div>
        <div class="analysis-result-grid">
            <article><span class="analysis-result-label">PERINGKAT JENIS BELANJA</span><h3>Belanja dengan nilai terbesar</h3><p class="analysis-result-name">{{ $top->nama_belanja ?: 'Belanja tanpa uraian' }}</p><p class="analysis-result-code">Kode {{ $top->kode_belanja }}</p><dl><div><dt>Nilai belanja ini</dt><dd>{{ $rupiah($top->amount) }}</dd></div><div><dt>Porsi dari total sesuai filter</dt><dd>{{ $totals->amount>0 ? number_format($top->amount/$totals->amount*100,1).'%' : 'Tidak dihitung' }}</dd></div><div><dt>Rincian akun yang dijumlahkan</dt><dd>{{ number_format($top->entries,0,',','.') }} baris</dd></div></dl><p class="analysis-result-note">Dijumlahkan berdasarkan kode belanja di {{ $selected ? 'OPD terpilih' : 'seluruh OPD sesuai filter' }}.</p></article>
            <article><span class="analysis-result-label">{{ $selected ? 'RINGKASAN OPD TERPILIH' : 'PERINGKAT OPD' }}</span><h3>{{ $selected ? 'Nilai belanja OPD terpilih' : 'OPD dengan nilai terbesar' }}</h3><p class="analysis-result-name">{{ $names[$leader->opd_id] }}</p><dl><div><dt>Nilai seluruh belanja yang cocok di OPD ini</dt><dd>{{ $rupiah($leader->amount) }}</dd></div><div><dt>Porsi dari total sesuai filter</dt><dd>{{ $totals->amount>0 ? number_format($leader->amount/$totals->amount*100,1).'%' : 'Tidak dihitung' }}</dd></div><div><dt>Jumlah dokumen SP2D sesuai filter di OPD ini</dt><dd>{{ number_format($leader->documents,0,',','.') }} dokumen</dd></div></dl><p class="analysis-result-note">Setiap nomor SP2D dihitung satu kali, meskipun memiliki beberapa baris akun yang cocok. Jumlah dokumen dan nilai di kotak ini mencakup seluruh belanja sesuai filter pada OPD ini, bukan hanya jenis belanja terbesar di kotak kiri.</p></article>
        </div>
        <p class="analysis-result-footer">Kedua kotak adalah dua sudut pandang dari data yang sama. Nilainya tidak dijumlahkan satu sama lain. Jumlah baris akun juga berbeda dari jumlah SP2D.</p>
    </section>
    @else
    <section class="card p-8 text-center mb-5"><h2 class="font-semibold">Tidak ada rincian belanja sesuai pilihan</h2><p class="text-sm text-slate-500 mt-2">Ubah kata kunci, OPD, atau periode. Pencarian mencocokkan seluruh kata pada uraian belanja.</p></section>
    @endif
    <div class="analysis-mini-totals"><div><span>Nilai belanja terpilih</span><strong>{{ $rupiah($totals->amount) }}</strong></div><div><span>OPD terkait</span><strong>{{ $totals->agencies }}</strong></div><div><span>SP2D terkait</span><strong>{{ $totals->documents }}</strong></div><div><span>Jenis belanja / kode</span><strong>{{ $allAccounts->total() }}</strong></div></div>
    <div class="analysis-visual-grid">
        <section class="card p-5"><h2 class="font-semibold">1. Komposisi belanja</h2><p class="text-xs text-slate-500 mt-1">Pie · porsi lima belanja terbesar dan sisanya</p>
            @if($pieValid)
            <div class="analysis-pie-layout">
            <svg viewBox="0 0 200 200" class="analysis-clickable-pie" aria-label="Komposisi belanja; klik potongan untuk rincian">
                @php $angle=-90; @endphp
                @foreach($slices as $i=>$slice)
                    @php
                        $portion=$slice['amount']/$totals->amount;
                        $endAngle=$angle+$portion*360;
                        $x1=100+90*cos(deg2rad($angle)); $y1=100+90*sin(deg2rad($angle));
                        $x2=100+90*cos(deg2rad($endAngle)); $y2=100+90*sin(deg2rad($endAngle));
                        $sliceFilters=isset($slice['code']) ? ['kode'=>$slice['code']] : ['exclude_codes'=>$accounts->take(5)->pluck('kode_belanja')->all()];
                        $sliceUrl=route('spending-analysis.index',array_merge($filters,$sliceFilters,['detail'=>1]));
                        $path=$portion>=.999999 ? 'M100 10 A90 90 0 1 1 99.999 10 Z' : "M100 100 L$x1 $y1 A90 90 0 ".($portion>.5 ? 1 : 0)." 1 $x2 $y2 Z";
                        $angle=$endAngle;
                    @endphp
                    @if($portion>0)<path d="{{ $path }}" fill="{{ $pieColors[$i] }}" stroke="white" stroke-width="2" tabindex="0" role="button" aria-label="{{ $slice['name'] }}: {{ $rupiah($slice['amount']) }}. Buka rincian." data-url="{{ $sliceUrl }}" data-title="{{ $slice['name'] }}" data-value="{{ $rupiah($slice['amount']) }}" @mouseenter="showTip($el,$event)" @mousemove="moveTip($event)" @mouseleave="tip.visible=false" @focus="showTip($el)" @blur="tip.visible=false" @click="open($el)" @keydown.enter.prevent="open($el)" @keydown.space.prevent="open($el)"><title>{{ $slice['name'] }} · {{ number_format($portion*100,1) }}% · {{ $rupiah($slice['amount']) }}</title></path>@endif
                @endforeach
            </svg>
            <ul class="analysis-pie-legend">@foreach($slices as $i=>$slice)<li><i style="background:{{ $pieColors[$i] }}"></i><button type="button" class="text-left hover:underline" data-url="{{ route('spending-analysis.index',array_merge($filters,isset($slice['code']) ? ['kode'=>$slice['code']] : ['exclude_codes'=>$accounts->take(5)->pluck('kode_belanja')->all()],['detail'=>1])) }}" data-title="{{ $slice['name'] }}" data-value="{{ $rupiah($slice['amount']) }}" @mouseenter="showTip($el,$event)" @mousemove="moveTip($event)" @mouseleave="tip.visible=false" @focus="showTip($el)" @blur="tip.visible=false" @click="open($el)"><span>{{ $slice['name'] }}</span><small>{{ number_format($slice['amount']/$totals->amount*100,1) }}% · {{ $rupiah($slice['amount']) }}</small></button></li>@endforeach</ul></div>
            @else<p class="text-sm text-slate-500 py-8">Komposisi pie tidak tersedia untuk total nol atau nilai negatif. Lihat tabel rincian.</p>@endif
        </section>
        <section class="card p-5"><h2 class="font-semibold">2. Tren pencairan</h2><p class="text-xs text-slate-500 mt-1">Garis · nilai per bulan, bukan akumulasi · juta rupiah</p>
            <svg class="analysis-line-chart" viewBox="0 0 660 225" role="group" aria-label="Grafik garis tren nilai pencairan per bulan. Nilai lengkap pada tabel bulanan.">
                @foreach([0,.5,1] as $step)<line x1="65" y1="{{ 180-$step*145 }}" x2="630" y2="{{ 180-$step*145 }}" stroke="#e2e8ef"/><text x="57" y="{{ 184-$step*145 }}" text-anchor="end" font-size="10" fill="#7b8796">{{ number_format(($lineMin+$step*$lineRange)/1000000,2) }}</text>@endforeach
                <polyline points="{{ $linePoints }}" fill="none" stroke="#587faf" stroke-width="3" stroke-linejoin="round"/>
                @foreach($months->values() as $i=>$month)<circle role="button" data-url="{{ route('spending-analysis.index',array_merge($filters,['bulan'=>$month['month'],'detail'=>1])) }}" data-title="Pencairan {{ $labels[$month['month']-1] }} {{ $year }}" data-value="{{ $rupiah($month['amount']) }}" @mouseenter="showTip($el,$event)" @mousemove="moveTip($event)" @mouseleave="tip.visible=false" @focus="showTip($el)" @blur="tip.visible=false" @click="open($el)" @keydown.enter.prevent="open($el)" @keydown.space.prevent="open($el)" tabindex="0" cx="{{ 65+$i*565/max(1,$months->count()-1) }}" cy="{{ 180-($month['amount']-$lineMin)/$lineRange*145 }}" r="4" fill="#1e3a5f" aria-label="{{ $labels[$month['month']-1] }}: {{ $rupiah($month['amount']) }}"><title>{{ $labels[$month['month']-1] }}: {{ $rupiah($month['amount']) }}</title></circle><text x="{{ 65+$i*565/max(1,$months->count()-1) }}" y="205" text-anchor="middle" font-size="10" fill="#7b8796">{{ $labels[$month['month']-1] }}</text>@endforeach
            </svg><p class="text-xs text-slate-500 mt-2">Bulan tanpa rincian SP2D ditampilkan nol; ini tidak memastikan data bulan tersebut sudah lengkap.</p>
        </section>
    </div>
    <div class="analysis-comparison">
        <section class="card p-5"><h2 class="font-semibold">3. Peringkat belanja</h2><p class="text-xs text-slate-500 mt-1 mb-5">10 teratas · persentase dari total sesuai filter · klik untuk rincian SP2D/SPJ</p>
            @forelse($accounts as $account)<a class="analysis-rank" href="{{ route('spending-analysis.index',array_merge($filters,['kode'=>$account->kode_belanja])) }}" data-url="{{ route('spending-analysis.index',array_merge($filters,['kode'=>$account->kode_belanja,'detail'=>1])) }}" data-title="{{ $account->nama_belanja }}" data-value="{{ $rupiah($account->amount) }}" @mouseenter="showTip($el,$event)" @mousemove="moveTip($event)" @mouseleave="tip.visible=false" @focus="showTip($el)" @blur="tip.visible=false" @click.prevent="open($el)"><div><span>{{ $loop->iteration }}.</span><strong>{{ $account->nama_belanja ?: 'Belanja tanpa uraian' }}</strong><b>{{ $rupiah($account->amount) }}</b><span class="analysis-rank-percent" title="Porsi dari total nilai belanja sesuai filter">{{ $totals->amount > 0 ? number_format($account->amount/$totals->amount*100,1).'%' : '—' }}</span></div><div class="analysis-bar"><span style="width:{{ max(0,$account->amount/max(1,$accounts->max('amount'))*100) }}%"></span></div></a>@empty<p class="text-sm text-slate-500">Belum ada data.</p>@endforelse
        </section>
        <section class="card p-5"><h2 class="font-semibold">4. Perbandingan OPD</h2><p class="text-xs text-slate-500 mt-1 mb-5">Persentase dari total sesuai filter · klik batang atau OPD untuk rincian SP2D/SPJ</p><div class="analysis-opd-ranks">
            @forelse($ranking as $row)<a class="analysis-rank" href="{{ route('spending-analysis.index',array_merge($filters,['opd_id'=>$row->opd_id])) }}" data-url="{{ route('spending-analysis.index',array_merge($filters,['opd_id'=>$row->opd_id,'detail'=>1])) }}" data-title="{{ $names[$row->opd_id] }}" data-value="{{ $rupiah($row->amount) }}" @mouseenter="showTip($el,$event)" @mousemove="moveTip($event)" @mouseleave="tip.visible=false" @focus="showTip($el)" @blur="tip.visible=false" @click.prevent="open($el)"><div><span>{{ $loop->iteration }}.</span><strong>{{ $names[$row->opd_id] }}</strong><b>{{ $rupiah($row->amount) }}</b><span class="analysis-rank-percent" title="Porsi dari total nilai belanja sesuai filter">{{ $totals->amount > 0 ? number_format($row->amount/$totals->amount*100,1).'%' : '—' }}</span></div><div class="analysis-bar"><span style="width:{{ max(0,$row->amount/max(1,$ranking->max('amount'))*100) }}%"></span></div></a>@empty<p class="text-sm text-slate-500">Belum ada data.</p>@endforelse
        </div></section>
    </div>
    <section class="card p-5 mb-5"><h2 class="font-semibold">5. Pencairan per bulan</h2><p class="text-xs text-slate-500 mt-1 mb-4">Rincian nilai SP2D menurut bulan penerbitan · {{ $period }}</p><p class="text-xs text-slate-500 mb-3">Satuan grafik: juta rupiah. Arahkan kursor atau fokuskan batang untuk melihat nilai lengkap.</p>
        <div class="analysis-disbursement-scroll" role="region" aria-label="Diagram batang pencairan bulanan" tabindex="0">
            <div class="analysis-disbursement-chart">
                @foreach($months as $month)
                    <div class="analysis-disbursement-column">
                        <span class="analysis-disbursement-value">{{ number_format($month['amount']/1000000,2,',','.') }}</span>
                        <div class="analysis-disbursement-track">
                            <div role="button" data-url="{{ route('spending-analysis.index',array_merge($filters,['bulan'=>$month['month'],'detail'=>1])) }}" data-title="Pencairan {{ $labels[$month['month']-1] }} {{ $year }}" data-value="{{ $rupiah($month['amount']) }}" @mouseenter="showTip($el,$event)" @mousemove="moveTip($event)" @mouseleave="tip.visible=false" @focus="showTip($el)" @blur="tip.visible=false" @click="open($el)" @keydown.enter.prevent="open($el)" @keydown.space.prevent="open($el)" class="analysis-disbursement-bar" tabindex="0" style="height:{{ max(0,$month['amount']/max(1,$months->max('amount'))*100) }}%" title="{{ $labels[$month['month']-1] }}: {{ $rupiah($month['amount']) }}" aria-label="{{ $labels[$month['month']-1] }}: {{ $rupiah($month['amount']) }}"></div>
                        </div>
                        <strong>{{ $labels[$month['month']-1] }}</strong>
                    </div>
                @endforeach
            </div>
        </div>
        <details class="reports-help"><summary>Lihat nilai rupiah lengkap</summary><div class="overflow-x-auto"><table class="table w-full"><thead><tr><th>Bulan</th><th>Nilai pencairan</th></tr></thead><tbody>@foreach($months as $month)<tr><td>{{ $labels[$month['month']-1] }} {{ $year }}</td><td>{{ $rupiah($month['amount']) }}</td></tr>@endforeach</tbody></table></div></details>
    </section>
    <section class="card overflow-hidden"><div class="p-5"><h2 class="font-semibold">6. Tabel rincian belanja</h2><p class="text-xs text-slate-500 mt-1">{{ $allAccounts->total() }} kode belanja · diurutkan dari nilai terbesar</p></div><div class="overflow-x-auto"><table class="table w-full"><thead><tr><th>Nama belanja</th><th>Kode</th><th>Baris akun</th><th>Nilai terpilih</th><th>Porsi</th></tr></thead><tbody>@forelse($allAccounts as $account)<tr><td><button type="button" class="text-brand-700 text-left hover:underline" data-url="{{ route('spending-analysis.index',array_merge($filters,['kode'=>$account->kode_belanja,'detail'=>1])) }}" data-title="{{ $account->nama_belanja ?: 'Belanja tanpa uraian' }}" @click="open($el)">{{ $account->nama_belanja ?: 'Belanja tanpa uraian' }}</button></td><td>{{ $account->kode_belanja }}</td><td>{{ $account->entries }}</td><td class="whitespace-nowrap">{{ $rupiah($account->amount) }}</td><td>{{ $totals->amount>0 ? number_format($account->amount/$totals->amount*100,1).'%' : '—' }}</td></tr>@empty<tr><td colspan="5">Tidak ada rincian yang cocok.</td></tr>@endforelse</tbody></table></div><div class="p-5">{{ $allAccounts->links() }}</div></section>
    <details class="reports-help"><summary>Dasar perhitungan dan batasan data</summary><p>Nilai dihitung dari jumlah rincian akun SP2D, bukan total induk SP2D. Saat mencari perjalanan dinas, hanya akun dengan uraian yang memuat seluruh kata pencarian yang dihitung. Kode yang berulang tetap dijumlahkan; bila uraian berbeda untuk kode yang sama, satu uraian ditampilkan sebagai label.</p><p>Periode mengikuti tanggal SP2D. Nilai menggambarkan penyaluran menurut SP2D yang tercatat, bukan pengeluaran yang telah diverifikasi melalui SPJ atau persentase penyerapan pagu. SP2D tanpa rincian akun tidak masuk analisis ini. Bulan kosong tidak membuktikan tidak ada belanja bila data belum lengkap.</p></details>
<div x-show="tip.visible" x-cloak role="tooltip" class="analysis-chart-tooltip" :style="{left:tip.x+'px',top:tip.y+'px'}"><span x-text="tip.label"></span><strong x-text="tip.value"></strong><small>Klik untuk melihat rincian SP2D/SPJ</small></div>
<div x-show="visible" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50" @click.self="close()">
    <section x-ref="dialog" role="dialog" aria-modal="true" aria-labelledby="spending-modal-title" tabindex="-1" @keydown.tab="trap($event)" class="bg-white rounded-xl shadow-xl w-full max-w-4xl max-h-[85vh] overflow-y-auto p-6">
        <div class="flex justify-between items-start gap-4 mb-4"><div><h2 id="spending-modal-title" class="font-semibold" x-text="title"></h2><p class="text-xs text-slate-500 mt-1">SP2D dan SPJ terkait sesuai filter analisis.</p></div><button type="button" class="btn-secondary" @click="close()">Tutup</button></div>
        <p x-show="loading" role="status" class="py-5 text-sm text-slate-500">Memuat SPJ…</p><p x-show="error" role="alert" class="text-sm text-red-700" x-text="error"></p>
        <div x-show="!loading && !error"><p class="text-xs text-slate-500 mb-3" x-text="total + ' SP2D terkait · Total ' + money(amount)"></p><div class="overflow-x-auto"><table class="table w-full"><thead><tr><th>SP2D / OPD</th><th>Nilai belanja terpilih</th><th>Status SPJ</th><th>Aksi</th></tr></thead><tbody><template x-for="row in rows" :key="row.id"><tr><td><strong x-text="row.number"></strong><p class="text-xs text-slate-500" x-text="row.opd"></p><small x-text="row.date"></small></td><td class="whitespace-nowrap" x-text="money(row.amount)"></td><td x-text="statuses[row.status] || (row.status ? row.status : 'Belum ada SPJ')"></td><td><a :href="row.url" class="btn-secondary whitespace-nowrap" x-text="row.action"></a></td></tr></template></tbody></table></div><p x-show="!rows.length" class="text-sm py-4">Tidak ada SP2D terkait pada filter ini.</p><div class="flex justify-between items-center mt-4"><button class="btn-secondary" :disabled="page <= 1" @click="load(page-1)">Sebelumnya</button><span class="text-xs text-slate-500" x-text="'Halaman ' + page + ' / ' + last"></span><button class="btn-secondary" :disabled="page >= last" @click="load(page+1)">Berikutnya</button></div></div>
    </section>
</div>
</div>
@push('scripts')
<script>
function spendingDetailModal() {
    return {
        tip:{visible:false,label:'',value:'',x:0,y:0},
        showTip(el,event){this.tip.label=el.dataset.title;this.tip.value=el.dataset.value;this.tip.visible=true;const rect=el.getBoundingClientRect();this.moveTip(event || {clientX:rect.left+rect.width/2,clientY:rect.top});},
        moveTip(event){this.tip.x=Math.max(8,Math.min(event.clientX+14,window.innerWidth-300));this.tip.y=Math.max(8,Math.min(event.clientY+16,window.innerHeight-150));},
        visible:false, loading:false, error:'', title:'', url:'', rows:[], page:1, last:1, total:0, amount:0, trigger:null, controller:null,
        statuses:{submitted:'Diajukan',in_review:'Dalam reviu',reviewed:'Selesai direviu',draft:'Draf'},
        money(value){return 'Rp ' + Number(value).toLocaleString('id-ID');},
        open(button){this.tip.visible=false;this.trigger=button;this.title=button.dataset.title;this.url=button.dataset.url;this.visible=true;this.$nextTick(()=>this.$refs.dialog.focus());this.load(1);},
        close(){if(!this.visible)return;this.controller?.abort();this.visible=false;this.trigger?.focus();},
        trap(event){const nodes=Array.from(this.$refs.dialog.querySelectorAll('button:not([disabled]),a[href]')).filter(n=>n.getClientRects().length);const first=nodes[0],last=nodes[nodes.length-1];if(event.shiftKey && (document.activeElement===first || document.activeElement===this.$refs.dialog)){event.preventDefault();last?.focus();}else if(!event.shiftKey && document.activeElement===last){event.preventDefault();first?.focus();}},
        async load(page){this.controller?.abort();const controller=new AbortController();this.controller=controller;this.loading=true;this.error='';try{const url=new URL(this.url,location.origin);url.searchParams.set('page',page);const response=await fetch(url,{headers:{Accept:'application/json'},signal:controller.signal});if(!response.ok)throw new Error('Rincian tidak dapat dimuat. Periksa sesi atau muat ulang halaman.');const data=await response.json();this.rows=data.rows;this.page=data.page;this.last=data.last_page;this.total=data.total;this.amount=data.amount;}catch(e){if(e.name!=='AbortError')this.error=e.message;}finally{if(this.controller===controller)this.loading=false;}}
    };
}
</script>
@endpush
</x-app-layout>

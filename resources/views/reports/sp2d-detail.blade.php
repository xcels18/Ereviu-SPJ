<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div>
                <h1 class="page-title">Detail Kelengkapan Bukti SP2D</h1>
                <p class="text-sm text-slate-500 mt-0.5 font-mono">{{ $sp2d->nomor_sp2d }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ route('reports.sp2d', $sp2d->opd_id) }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-brand-700 hover:text-brand-800 mb-4">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                Kembali ke Daftar SP2D
            </a>

            <!-- Info SP2D -->
            <div class="card p-5 mb-6">
                <div class="flex flex-wrap items-center gap-x-8 gap-y-3">
                    <div>
                        <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">OPD</p>
                        <p class="text-sm font-semibold text-slate-800">{{ $sp2d->opd->nama_opd }}</p>
                    </div>
                    <div class="w-px h-9 bg-slate-100 hidden sm:block"></div>
                    <div>
                        <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Nomor SP2D</p>
                        <p class="text-sm font-mono font-semibold text-slate-800">{{ $sp2d->nomor_sp2d }}</p>
                    </div>
                    <div class="w-px h-9 bg-slate-100 hidden sm:block"></div>
                    <div>
                        <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Tanggal</p>
                        <p class="text-sm font-semibold text-slate-800">{{ $sp2d->tanggal->format('d/m/Y') }}</p>
                    </div>
                    <div class="w-px h-9 bg-slate-100 hidden sm:block"></div>
                    <div>
                        <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Nilai Total</p>
                        <p class="text-sm font-bold text-brand-700">Rp {{ number_format($sp2d->jumlah_total, 0, ',', '.') }}</p>
                    </div>
                    <div class="w-px h-9 bg-slate-100 hidden sm:block"></div>
                    <div>
                        <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Status SPJ</p>
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
                        @if ($spj)
                            <span class="{{ $statusBadge[$spj->status] ?? 'badge-slate' }}">{{ $statusLabel[$spj->status] ?? ucfirst(str_replace('_', ' ', $spj->status)) }}</span>
                        @else
                            <span class="badge-slate">Belum ada SPJ</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Ringkasan kelengkapan -->
            @php
                $summaryCards = [
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
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-6">
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

            @if ($summary->belum_dinilai > 0)
                <p class="text-xs text-amber-600 mb-6 -mt-2">
                    Catatan: {{ $summary->belum_dinilai }} akun belanja sudah memiliki bukti namun status kelengkapannya belum ditentukan oleh pemeriksa.
                </p>
            @endif

            @if (!$spj)
                <div class="alert-info mb-6">
                    <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    SPJ untuk SP2D ini belum diupload, sehingga seluruh akun belanja berikut masih berstatus belum ada bukti.
                </div>
            @endif

            <!-- Rincian per akun belanja -->
            <div class="card overflow-hidden">
                <div class="card-header">
                    <h2 class="text-base font-semibold text-slate-900">Rincian Akun Belanja</h2>
                    <span class="text-xs text-slate-400">{{ $akunList->count() }} akun</span>
                </div>

                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Kode &amp; Nama Akun</th>
                                <th class="text-right">Nilai</th>
                                <th class="text-center">Status Bukti</th>
                                <th>Bukti</th>
                                <th>Catatan Reviu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $akunStatusBadge = [
                                    'lengkap' => 'badge-green',
                                    'tidak_lengkap' => 'badge-red',
                                    'belum_dinilai' => 'badge-yellow',
                                    'belum_ada_bukti' => 'badge-slate',
                                    'belum_dibelanjakan' => 'badge-indigo',
                                ];
                                $akunStatusLabel = [
                                    'lengkap' => 'Lengkap',
                                    'tidak_lengkap' => 'Tidak Lengkap',
                                    'belum_dinilai' => 'Belum Dinilai',
                                    'belum_ada_bukti' => 'Belum Ada Bukti',
                                    'belum_dibelanjakan' => 'Belum Dibelanjakan',
                                ];
                            @endphp
                            @forelse ($akunList as $akun)
                                <tr>
                                    <td>
                                        <p class="font-semibold text-slate-900 font-mono text-xs">{{ $akun->kode_belanja }}</p>
                                        <p class="text-xs text-slate-500 mt-0.5">{{ $akun->uraian }}</p>
                                    </td>
                                    <td class="text-right font-medium text-slate-900 whitespace-nowrap">Rp {{ number_format($akun->jumlah, 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        <span class="{{ $akunStatusBadge[$akun->status] }}">{{ $akunStatusLabel[$akun->status] }}</span>
                                    </td>
                                    <td>
                                        @if ($akun->bukti->isEmpty())
                                            <span class="text-xs text-slate-400 italic">Belum ada file</span>
                                        @else
                                            <div class="flex flex-wrap gap-1.5">
                                                @foreach ($akun->bukti as $file)
                                                    <a href="{{ route('spj.documents.download', $file->id) }}" target="_blank"
                                                        class="inline-flex items-center gap-1.5 text-xs font-medium text-brand-700 bg-brand-50 border border-brand-100 rounded-lg px-2.5 py-1.5 hover:bg-brand-100 transition-colors">
                                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                        {{ $file->file_name }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-xs text-slate-600 max-w-xs">{{ $akun->catatan ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-slate-400">Tidak ada akun belanja untuk SP2D ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="page-title">Audit Trail</h1>
            <p class="text-xs text-slate-500 mt-1">Administrasi / Jejak aktivitas</p>
        </div>
    </x-slot>

    @php
        $hasFilters = $search !== '' || $moduleFilter !== '' || $actionFilter !== '' || $userFilter !== '' || $dateFrom !== '' || $dateTo !== '';
        $tones = ['login' => 'teal', 'logout' => 'neutral', 'login_failed' => 'red', 'create' => 'teal', 'update' => 'amber', 'delete' => 'red', 'upload' => 'blue', 'review' => 'violet', 'import' => 'blue', 'compile' => 'violet'];
        $symbols = ['login' => '↗', 'logout' => '↙', 'login_failed' => '!', 'create' => '+', 'update' => '↻', 'delete' => '−', 'upload' => '↑', 'review' => '✓', 'import' => '↓', 'compile' => '≡'];
    @endphp

    <div class="audit-workspace">
        <section class="audit-heading" aria-labelledby="audit-title">
            <div>
                <p class="audit-kicker">TRANSPARANSI & AKUNTABILITAS</p>
                <h2 id="audit-title">Setiap aktivitas, tercatat.</h2>
                <p>Telusuri akses akun dan perubahan data dalam satu linimasa.</p>
            </div>
            <a href="{{ request()->fullUrl() }}" class="btn-secondary audit-refresh">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7v5h-5M4 17v-5h5M6.1 7a7 7 0 0111.6-1L20 9M4 15l2.3 3A7 7 0 0017.9 17"/></svg>
                Muat ulang
            </a>
        </section>

        <section class="audit-metrics" aria-label="Ringkasan aktivitas sesuai filter">
            @foreach ([['Total aktivitas', $summary->total, 'Seluruh hasil pencarian', 'neutral', '≡'], ['Pengguna tercatat', $summary->users, 'Akun unik, tidak termasuk tamu', 'teal', '◎'], ['Perubahan data', $summary->changes, 'Termasuk unggah dan reviu', 'blue', '↻'], ['Login gagal', $summary->failed, 'Percobaan akses tidak berhasil', 'red', '!']] as [$label, $value, $hint, $tone, $symbol])
                <div class="audit-metric">
                    <div class="audit-metric-top"><span>{{ $label }}</span><span class="audit-symbol audit-tone-{{ $tone }}" aria-hidden="true">{{ $symbol }}</span></div>
                    <strong>{{ number_format($value, 0, ',', '.') }}</strong>
                    <p>{{ $hint }}</p>
                </div>
            @endforeach
        </section>

        <section class="audit-filter-panel" aria-label="Filter aktivitas">
            <form method="GET" action="{{ route('audit-trail.index') }}">
                <div class="audit-filter-title"><h3>Temukan aktivitas</h3><span>Ringkasan mengikuti filter yang dipilih</span></div>
                @if ($errors->any())
                    <div class="alert-error" role="alert"><div><strong>Periksa filter Anda.</strong><ul class="mt-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>
                @endif
                <div class="audit-filter-grid">
                    <div class="audit-search-field">
                        <label for="audit-search">Pencarian</label>
                        <div class="audit-search-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path stroke-linecap="round" d="m16 16 4 4"/></svg>
                            <input id="audit-search" name="search" type="search" maxlength="200" value="{{ old('search', $search) }}" placeholder="Nama, email, aktivitas, atau IP…" class="form-input">
                        </div>
                    </div>
                    <div><label for="audit-module">Modul</label><select id="audit-module" name="module" class="form-input"><option value="">Semua modul</option>@foreach ($moduleLabels as $key => $label)<option value="{{ $key }}" @selected($moduleFilter === $key)>{{ $label }}</option>@endforeach</select></div>
                    <div><label for="audit-action">Jenis aktivitas</label><select id="audit-action" name="action" class="form-input"><option value="">Semua aktivitas</option>@foreach ($actionLabels as $key => $label)<option value="{{ $key }}" @selected($actionFilter === $key)>{{ $label }}</option>@endforeach</select></div>
                    <div class="audit-user-field"><label for="audit-user">Pengguna</label><select id="audit-user" name="user_id" class="form-input"><option value="">Semua pengguna</option>@foreach ($users as $user)<option value="{{ $user->id }}" @selected($userFilter === (string) $user->id)>{{ $user->name }}</option>@endforeach</select></div>
                    <div><label for="audit-from">Dari tanggal</label><input id="audit-from" type="date" name="date_from" value="{{ old('date_from', $dateFrom) }}" class="form-input"></div>
                    <div><label for="audit-to">Sampai tanggal</label><input id="audit-to" type="date" name="date_to" value="{{ old('date_to', $dateTo) }}" class="form-input"></div>
                    <div><label for="audit-sort">Urutan</label><select id="audit-sort" name="sort" class="form-input"><option value="newest" @selected($sort === 'newest')>Terbaru dahulu</option><option value="oldest" @selected($sort === 'oldest')>Terlama dahulu</option></select></div>
                    <div><label for="audit-limit">Per halaman</label><select id="audit-limit" name="per_page" class="form-input">@foreach ([15, 25, 50] as $limit)<option value="{{ $limit }}" @selected($perPage === $limit)>{{ $limit }} aktivitas</option>@endforeach</select></div>
                </div>
                <div class="audit-filter-footer">
                    <p><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>Zona waktu: {{ $timezone }}</p>
                    <div class="flex flex-wrap gap-2">@if ($hasFilters || $sort !== 'newest' || $perPage !== 15)<a href="{{ route('audit-trail.index') }}" class="btn-secondary">Reset filter</a>@endif<button type="submit" class="btn-primary">Terapkan filter <span aria-hidden="true">→</span></button></div>
                </div>
            </form>
        </section>

        <section class="audit-stream" aria-labelledby="audit-stream-title">
            <div class="audit-stream-heading">
                <div><h3 id="audit-stream-title">Linimasa aktivitas <span class="audit-count">{{ number_format($logs->total(), 0, ',', '.') }}</span></h3><p>{{ $hasFilters ? 'Hasil sesuai filter Anda.' : 'Seluruh aktivitas yang tersimpan.' }} Pilih aktivitas untuk melihat detail.</p></div>
                <span class="audit-order-label">{{ $sort === 'newest' ? 'Terbaru → Terlama' : 'Terlama → Terbaru' }}</span>
            </div>
            @forelse ($logs->getCollection()->groupBy(fn ($log) => $log->created_at->format('Y-m-d')) as $day => $dayLogs)
                <div class="audit-day"><span>{{ \Illuminate\Support\Carbon::parse($day)->locale('id')->translatedFormat('l, d F Y') }}</span><span>{{ $dayLogs->count() }} aktivitas di halaman ini</span></div>
                <div class="audit-day-events">
                    @foreach ($dayLogs as $log)
                        <details class="audit-event">
                            <summary>
                                <time class="audit-event-time" datetime="{{ $log->created_at->toIso8601String() }}">{{ $log->created_at->format('H:i') }}<span>{{ $log->created_at->format(':s') }}</span></time>
                                <span class="audit-event-icon audit-tone-{{ $tones[$log->action] ?? 'neutral' }}" aria-hidden="true">{{ $symbols[$log->action] ?? '•' }}</span>
                                <div class="audit-event-content">
                                    <div class="audit-event-labels"><strong>{{ $actionLabels[$log->action] ?? ucfirst($log->action) }}</strong><span class="audit-module">{{ $moduleLabels[$log->module] ?? ucfirst($log->module) }}</span>@if ($log->action === 'login_failed')<span class="audit-failed-label">Akses gagal</span>@endif</div>
                                    <p>{{ $log->description }}</p>
                                </div>
                                <div class="audit-actor"><span class="audit-avatar" aria-hidden="true">{{ $log->user_name ? mb_strtoupper(mb_substr($log->user_name, 0, 1)) : '?' }}</span><div><strong>{{ $log->user_name ?: 'Tamu / tidak dikenal' }}</strong><span>{{ $log->user_role ? ucwords(str_replace('_', ' ', $log->user_role)) : 'Tanpa peran tercatat' }}</span></div></div>
                                <span class="audit-expand" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"/></svg></span>
                            </summary>
                            <div class="audit-event-detail">
                                <div class="audit-detail-title"><h4>Detail aktivitas</h4><span>LOG #{{ $log->id }}</span></div>
                                <p class="audit-full-description">{{ $log->description }}</p>
                                <dl>
                                    <div><dt>Waktu pencatatan</dt><dd>{{ $log->created_at->locale('id')->translatedFormat('d M Y, H:i:s') }} ({{ $timezone }})</dd></div>
                                    <div><dt>Email pengguna</dt><dd>{{ $log->user_email ?: 'Tidak tercatat' }}</dd></div>
                                    <div><dt>Alamat IP</dt><dd class="audit-mono">{{ $log->ip_address ?: 'Tidak tercatat' }}</dd></div>
                                    <div><dt>Objek terkait</dt><dd>{{ $log->subject_type ? class_basename($log->subject_type).' #'.$log->subject_id : 'Tidak ada objek terkait' }}</dd></div>
                                    <div class="audit-device"><dt>Perangkat / browser</dt><dd>{{ $log->user_agent ?: 'Tidak tercatat' }}</dd></div>
                                </dl>
                            </div>
                        </details>
                    @endforeach
                </div>
            @empty
                <div class="audit-empty"><span aria-hidden="true">⌕</span><h4>{{ $hasFilters ? 'Aktivitas tidak ditemukan' : 'Linimasa masih kosong' }}</h4><p>{{ $hasFilters ? 'Coba kata kunci lain atau perluas rentang tanggal pencarian.' : 'Aktivitas akun dan pengelolaan data akan ditampilkan di sini setelah tercatat.' }}</p>@if ($hasFilters)<a href="{{ route('audit-trail.index') }}" class="btn-secondary">Tampilkan semua aktivitas</a>@endif</div>
            @endforelse
            <div class="audit-pagination">
                <p>@if ($logs->total())Menampilkan <strong>{{ $logs->firstItem() }}–{{ $logs->lastItem() }}</strong> dari <strong>{{ number_format($logs->total(), 0, ',', '.') }}</strong> aktivitas @else 0 aktivitas @endif</p>
                @if ($logs->hasPages())
                    <nav aria-label="Halaman aktivitas">
                        @if ($logs->onFirstPage())<span class="audit-page-disabled" aria-disabled="true">← Sebelumnya</span>@else<a href="{{ $logs->previousPageUrl() }}" rel="prev">← Sebelumnya</a>@endif
                        <span class="audit-page-current" aria-current="page">{{ $logs->currentPage() }} / {{ $logs->lastPage() }}</span>
                        @if ($logs->hasMorePages())<a href="{{ $logs->nextPageUrl() }}" rel="next">Berikutnya →</a>@else<span class="audit-page-disabled" aria-disabled="true">Berikutnya →</span>@endif
                    </nav>
                @endif
            </div>
        </section>
        <p class="audit-footnote">Riwayat menampilkan identitas pengguna pada saat aktivitas dicatat.</p>
    </div>
</x-app-layout>

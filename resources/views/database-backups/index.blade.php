<x-app-layout>
    <x-slot name="header"><h1 class="page-title">Backup Database</h1></x-slot>
    <div class="p-6 max-w-7xl mx-auto">
        @if(session('success'))<div class="alert-success mb-4">{{ session('success') }}</div>@endif
        <section class="card p-6 mb-5">
            <h2 class="font-semibold">Cadangan data aplikasi</h2>
            <p class="text-sm text-slate-500 mt-2">Backup SQL mencakup struktur tabel dan data. Dokumen unggahan tidak termasuk; gunakan Pusat Arsip SPJ untuk dokumen. Simpan salinan APP_KEY secara terpisah untuk pemulihan data terenkripsi.</p>
            <p class="text-xs text-slate-500 mt-2">Proses berjalan melalui worker antrean. Unduhan tersedia 24 jam dan hanya untuk admin pembuatnya. Simpan berkas di lokasi aman karena berisi data seluruh aplikasi. Hindari perubahan struktur database selama backup.</p>
            <form action="{{ route('database-backups.store') }}" method="POST" class="mt-4">@csrf<button class="btn-primary">Buat backup database</button> <a class="btn-secondary" href="{{ route('database-backups.index') }}">Perbarui status</a></form>
        </section>
        <section class="card p-6"><h2 class="font-semibold mb-4">Backup Anda</h2>
            @forelse($backups as $backup)
                <article class="py-4 border-b border-slate-100"><p class="text-sm font-medium">{{ \Carbon\Carbon::parse($backup['created_at'])->format('d/m/Y H:i') }} · {{ ['queued'=>'Dalam antrean','processing'=>'Sedang diproses','completed'=>'Siap diunduh','failed'=>'Gagal'][$backup['status']] }}</p>
                    @if($backup['status'] === 'completed')<p class="text-xs text-slate-500 my-2">{{ number_format($backup['bytes']/1048576, 2) }} MiB</p><a href="{{ route('database-backups.download', $backup['id']) }}" class="btn-secondary">Unduh SQL</a><p class="text-xs text-slate-500 mt-2 break-all">SHA-256: {{ $backup['sha256'] }}</p>@endif
                    @if(isset($backup['error']))<p class="text-sm text-red-700 mt-2">{{ $backup['error'] }}</p>@endif
                </article>
            @empty<p class="text-sm text-slate-500">Belum ada backup yang tersedia.</p>@endforelse
        </section>
    </div>
</x-app-layout>

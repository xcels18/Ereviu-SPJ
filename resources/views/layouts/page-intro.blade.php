@php
    $introductions = [
        'opd.index' => ['PERANGKAT DAERAH', 'Kenali unit, kelola dengan mudah.', 'Data organisasi perangkat daerah sebagai dasar pengelolaan dokumen dan penugasan.'],
        'users.index' => ['PENGGUNA & AKSES', 'Tim yang terhubung, kerja yang terarah.', 'Kelola akun, peran, dan cakupan wilayah kerja pengguna aplikasi.'],
        'review-guides.index' => ['PANDUAN REVIU', 'Acuan yang jelas untuk setiap pemeriksaan.', 'Telusuri kode rekening dan persyaratan dokumen sebelum melakukan reviu.'],
        'reports.index' => ['PUSAT LAPORAN', 'Lihat gambaran besar. Temukan prioritas.', 'Pantau kelengkapan pertanggungjawaban dan tindak lanjut setiap perangkat daerah.'],
        'reports.cetak' => ['EKSPOR LAPORAN', 'Dari data menjadi laporan.', 'Pilih perangkat daerah dan periode, lalu siapkan laporan untuk ditinjau atau diunduh.'],
        'reports.spj' => ['LAPORAN SPJ', 'Status dokumen dalam satu pandangan.', 'Telusuri progres pengumpulan dan pemeriksaan dokumen pertanggungjawaban.'],
        'reports.findings' => ['CATATAN REVIU', 'Setiap catatan, langkah perbaikan.', 'Tinjau temuan dan catatan pemeriksaan sesuai cakupan akses Anda.'],
        'spj-compile.index' => ['KOMPILASI DOKUMEN', 'Satukan dokumen, siapkan dengan mudah.', 'Pilih OPD, tentukan dokumen, lalu gabungkan bukti menjadi berkas yang siap diunduh.'],
        'profile.edit' => ['AKUN SAYA', 'Ruang pribadi, kendali di tangan Anda.', 'Perbarui informasi profil dan pengaturan keamanan akun.'],
    ];
    $introduction = $introductions[request()->route()?->getName()] ?? null;
@endphp
@if($introduction)
<section class="workspace-intro"><p>{{ $introduction[0] }}</p><h2>{{ $introduction[1] }}</h2><p>{{ $introduction[2] }}</p></section>
@endif

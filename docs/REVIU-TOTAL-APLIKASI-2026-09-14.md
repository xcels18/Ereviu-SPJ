# Reviu aplikasi SPJ — 14 September 2026

## Lingkup dan batas

Reviu kode lokal: autentikasi/MFA, izin pengguna, OPD, SP2D, upload, reviu, laporan, kompilasi, audit, migrasi dan pengujian. Tidak mengubah kode bisnis atau database. Tidak menguji server produksi, backup/restore aktual, penetrasi, konfigurasi TLS, kerentanan dependency terkini, atau tampilan setiap halaman pada perangkat nyata. Temuan struktural bukan klaim bahwa kejadian sudah terjadi pada produksi.

Verifikasi saat ini: `php artisan test --compact` menghasilkan 46 lulus, 1 gagal, 1 dilewati, 178 assertions. Uji gagal di DocumentWorkspaceTest:39 mengharapkan teks “Direktori perangkat daerah” yang tidak ada. HTTP merespons 200; assertion wilayah sesudah assertion teks belum dijalankan pada tes itu, sehingga kegagalan ini sendiri bukan bukti kebocoran akses. Uji terarah mengulangi kegagalan yang sama. Mayoritas tes menggunakan SQLite in-memory, bukan MySQL produksi.

## Temuan berurutan menurut dampak

### 1. P1 — UUID ekspor tidak cocok dengan tipe ID audit

Bukti: SpjCompileController::generate mengirim SpjExport ber-ID UUID ke ActivityLogger dalam transaksi. Logger menulis ID tersebut ke subject_id, sedangkan migrasi activity_logs mendefinisikannya unsignedBigInteger. Konfigurasi MySQL strict=true.

Dampak jika dibiarkan: penyimpanan audit UUID dapat ditolak MySQL, transaksi pembuatan paket batal, kompilasi tidak masuk antrean. Ini ketidakcocokan kode/skema yang kuat; belum direproduksi dengan transaksi ekspor pada MySQL produksi. Tes SQLite tidak menjamin kompatibilitas tipe ini.

Perbaikan: subject_id yang mendukung ID string/UUID dengan migrasi kompatibel data lama, periksa cast/pencarian/index, tambah tes integrasi MySQL untuk generate → audit → queue → download.

### 2. P1 — mode penilaian gabungan/per akun tidak mempunyai sumber kebenaran tersimpan

Bukti: SpjController::reviewStore menerima mode_gabungan dari request, menulis cabang terpilih tanpa menonaktifkan penilaian cabang lain. ReportController:281,410,847 memprioritaskan penilaian per akun lalu fallback gabungan.

Contoh: akun pernah dinilai tidak_lengkap, lalu pemeriksa memilih gabungan lengkap; penilaian akun lama tetap ada dan dapat mengalahkan hasil baru pada laporan.

Perbaikan: simpan mode aktif dan versi reviu, laporan membaca satu versi aktif; riwayat lama dipertahankan sebagai histori. Jika tidak: hasil laporan tidak selalu mencerminkan keputusan terbaru.

### 3. P1 — transaksi database belum mencakup keberhasilan upload secara menyeluruh

Bukti: SpjController::store meng-commit header SPJ sebelum loop penyimpanan berkas. addDocuments menyimpan file dan metadata bertahap. destroyDocument menghapus berkas sebelum metadata tanpa pemeriksaan keberhasilan delete.

Dampak: disk penuh/putus proses dapat meninggalkan SPJ parsial, metadata/file tidak sinkron, atau berkas yatim; unique SPJ lalu menolak percobaan membuat header ulang.

Perbaikan: staging upload, status pending/ready/failed, finalisasi metadata dalam transaksi, cleanup kompensasi, retry idempoten; jangan menganggap transaksi SQL bisa me-rollback filesystem.

### 4. P1 — keputusan reviu bisa tertimpa formulir lama

Bukti: lockForUpdate mengurutkan penulisan, tetapi request tidak memeriksa versi yang dibaca pemeriksa. Temuan di-update/delete langsung. reviewed dapat kembali in_review tanpa alasan pembukaan kembali.

Dampak: pemeriksa kedua bisa menimpa pekerjaan pertama, riwayat keputusan sulit direkonstruksi. Pemeriksaan status pada tambah/hapus dokumen juga berada di luar lock bersama dengan finalisasi reviu, sehingga perubahan berkas bersamaan perlu diuji.

Perbaikan: optimistic version check, riwayat revisi, alasan reopen, izin yang jelas, dan lock/status check konsisten untuk perubahan dokumen. Selesai reviu tetap boleh menghasilkan tidak_lengkap.

### 5. P1 — nilai header SP2D dapat berubah tanpa rekonsiliasi SPJ/detail

Bukti: Sp2dController::update membatasi perubahan kode/jumlah detail saat SPJ ada, tetapi jumlah_total/nomor_sp2d header masih dapat diubah sebelum reviewed. Upload menyalin jumlah_total dan nomor_sp2d ke SPJ.

Dampak: total, nomor referensi, dan laporan SPJ dapat berbeda dari SP2D tanpa penanda snapshot/revisi. Validasi panjang kode 100 dan uraian 2000 pada update juga melebihi kolom kode 50 dan uraian 255 pada migrasi, berpotensi gagal di MySQL.

Perbaikan: tentukan nilai resmi vs snapshot, rekonsiliasi nominal dengan decimal, validasi panjang sesuai kolom, alur koreksi header yang tercatat. Kode akun berulang sah; jangan menggabungkan detail untuk memaksakan keunikan.

### 6. P1 — deployment belum terbukti aman pada data produksi

Bukti: migrasi protect_archive_integrity membuat unique index lalu mengganti beberapa FK, bukan operasi idempoten yang memeriksa setiap langkah. Unique mensyaratkan satu SPJ per SP2D. composer setup menjalankan key:generate dan migrate --force.

Dampak: data produksi berbeda dapat menghentikan migrasi; perubahan DDL parsial membuat retry gagal. Menjalankan composer setup pada instalasi lama juga berisiko mengganti APP_KEY sehingga data MFA terenkripsi tidak bisa dibaca dengan kunci baru.

Perbaikan: preflight read-only, salinan produksi, backup database+files+APP_KEY dan uji restore, migrasi dapat dilanjutkan, runbook khusus update yang mempertahankan APP_KEY/.env/storage. Ini prioritas sebelum rilis, bukan alasan menganggap data produksi sudah rusak.

### 7. P2 — laporan/detail masih mengambil data besar ke memori

Bukti: ReportController::spjReport/findingsReport/cetakLaporan dan matriks memakai get(); SpjController menghitung ringkasan dari get(); OpdController::show memuat seluruh SP2D relasi.

Dampak: halaman lambat, memori habis, timeout saat data meningkat meskipun kompilasi sudah memakai queue.

Perbaikan: agregasi SQL, pagination, ekspor antrean, pengambilan bertahap dan uji query pada volume representatif. Tambahkan indeks berdasarkan EXPLAIN, bukan menambah indeks sembarang.

### 8. P2 — impor Excel sinkron dan nilai tidak numerik dijadikan nol

Bukti: Sp2dController::import memakai Excel::toArray; nilai nonnumeric diubah menjadi 0; existing nomor dilewati; parsing/grouping dilakukan sebelum transaksi.

Dampak: format angka lokal atau data salah dapat menjadi nol tanpa pesan per baris; impor besar membebani request web, koreksi sulit dilacak.

Perbaikan: staging/pratinjau, parser nominal eksplisit, tolak angka ambigu, laporan kesalahan per baris, chunk/queue, idempotensi batch. Pertahankan beberapa baris dengan kode sama sesuai aturan bisnis.

### 9. P2 — audit dapat mencatat penghapusan yang sebenarnya ditolak

Bukti: OpdController::destroy dan UserController::destroy mencatat delete sebelum model guard/database mengizinkan penghapusan. Tidak dibungkus transaksi yang sama.

Dampak: audit mengatakan data dihapus padahal masih ada; mengurangi keandalan bukti operasional.

Perbaikan: validasi + mutation + log keberhasilan dalam transaksi; percobaan gagal dicatat dengan action/outcome berbeda, snapshot identitas dipertahankan.

### 10. P2 — lifecycle akun admin/Irban belum lengkap

Bukti: manajemen pengguna membatasi hapus diri sendiri tetapi update mengizinkan perubahan peran sendiri; tidak ada perlindungan admin terakhir. parent_irban_id divalidasi saat menyimpan operator, tetapi perubahan peran induk tidak memeriksa operator turunannya.

Dampak: seluruh admin dapat hilang karena salah ubah peran; operator kehilangan konteks wilayah setelah induk berubah. Kolom users.role legacy masih ditulis, meski pembacaan sudah memakai Spatie.

Perbaikan: perlindungan admin terakhir, pemindahan operator sebelum perubahan induk, penonaktifan akun dan invalidasi sesi, pemeriksaan role saat migrasi. Akses lintas wilayah perlu matriks tes terpusat; ReportController menggunakan pola scope berbeda dari upload.

### 11. P2 — MFA perlu perlindungan sesi sensitif tambahan

Bukti: endpoint unduh seluruh recovery codes hanya auth pada routes/auth.php; tidak ada password.confirm. useRecoveryCode melakukan read-modify-write tanpa lock/conditional update. MFA masih opsional.

Dampak: sesi yang diambil alih berpotensi mengunduh recovery codes; dua request bersamaan berpotensi menggunakan kode yang sama. Ini analisis kode, bukan eksploitasi yang sudah dibuktikan.

Perbaikan: konfirmasi password baru untuk pengunduhan, konsumsi kode atomik, throttle endpoint sensitif, kebijakan MFA untuk peran berhak luas. Verifikasi konfigurasi produksi terpisah.

### 12. P2 — kapasitas dan pemulihan kompilasi masih terbatas

Kondisi: antrean khusus, ZIP bertahap, expiry dan batas memori sudah ada. Namun download masih melalui worker web; batas satu pekerjaan per pengguna bukan kuota global; pemeriksaan ruang bukan reservasi; job tidak melanjutkan dari bagian terakhir setelah crash. Konfigurasi Supervisor tersedia, tetapi keaktifan produksi belum diperiksa.

Dampak: banyak unduhan menghabiskan worker, disk penuh, atau paket harus diproses ulang setelah gangguan.

Perbaikan: monitoring worker/disk/failed jobs, kuota global dan per OPD, offload download ke web server, cleanup yang memeriksa hasil delete, checkpoint jika kebutuhan beban membenarkan. Tetapkan kapasitas dengan dokumen nyata, jangan mengandalkan benchmark sintetis saja.

### 13. P2/P3 — alur koreksi dan pengalaman pengguna

Kondisi: pembatasan akun setelah SPJ ada melindungi data, tetapi belum ada alur koreksi akun yang jelas; status utama hanya draft/submitted/in_review/reviewed. Upload per request dibatasi 10 MB/file, belum resumable. Banyak interaksi memakai modal/confirm browser.

Dampak: pengguna kesulitan memperbaiki kesalahan yang sah, mengulang upload gagal, dan mengetahui pekerjaan yang perlu didahulukan.

Perbaikan: alur permintaan koreksi dengan alasan/persetujuan, checklist yang mengacu ID rincian, progres/retry per file, antrean tugas sesuai peran dan tenggat. Audit keyboard, fokus modal, layar kecil, dan kontras perlu pengujian UI nyata; belum disimpulkan gagal dari reviu ini.

## Yang dipertahankan

39 kelompok kode berulang adalah rincian sah berdasarkan konfirmasi pemilik proses bisnis. ID masing-masing baris dan hubungan dokumennya harus tetap utuh. Tidak tambahkan unique(sp2d_id,kode_belanja). Snapshot audit tetap berguna. Kompilasi antrean dan ZIP bertahap adalah fondasi yang baik. Validasi wilayah dan constraint penghapusan yang sudah ada perlu diperluas, bukan dibuang.

## Urutan kerja

1. Sebelum rilis: tipe ID audit, integrasi MySQL, mode reviu, konsistensi upload, preflight/backup/restore.
2. Berikutnya: rekonsiliasi nominal, konflik reviu, lifecycle akun, audit outcome, hardening MFA.
3. Kapasitas: laporan/impor antrean, kuota/monitoring, offload download.
4. UX: koreksi resmi, pekerjaan/tenggat, unggah resumable, uji mobile/aksesibilitas.

Kriteria selesai bukan sekadar jumlah tes lulus: jalankan alur bisnis pada salinan MySQL produksi, kegagalan disk/proses, request bersamaan, dan pemulihan backup. Reviu ini tidak menyatakan aplikasi bebas celah atau siap deploy tanpa verifikasi tersebut.

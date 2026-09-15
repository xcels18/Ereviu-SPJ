# Arsitektur arsip SPJ volume besar

## Masalah sebelumnya

POST generate menjalankan FPDI untuk seluruh SP2D, menyimpan semua PDF, lalu membuat satu ZIP di request HTTP. Dokumen yang tidak dapat dibaca dapat terlewat tanpa laporan. Unduhan menghapus ZIP segera setelah stream selesai/terputus; pengguna harus mengompilasi ulang. UI memakai animasi tahap berulang dan cookie header, bukan status proses sebenarnya.

## Alur baru

1. POST generate memvalidasi OPD, SP2D, akun, dan pilihan mode. Transaksi mengunci baris pengguna untuk membatasi satu paket aktif per pengguna. Request mengembalikan HTTP 202 dengan UUID paket.
2. Tabel `spj_exports` menyimpan status, pilihan, jumlah berkas/byte, bagian hasil, pesan kegagalan, dan masa simpan. Antrean database khusus `spj_compile` mengerjakan paket di luar PHP web. Job hanya membawa UUID.
3. Worker membaca SP2D dengan lazyById(50). Dokumen terpilih ditulis sebagai manifest JSONL di direktori private per UUID. Path, ukuran aktual, dan mtime dicatat. Dokumen ditentukan saat worker mulai berjalan; sumber tidak disalin seluruhnya. Perubahan ukuran/mtime dan berkas hilang dideteksi sebelum/sesudah ZIP ditulis. Ini bukan snapshot kriptografis; jangan mengubah dokumen sumber selama proses.
4. Libzip menerima referensi file disk (`addFile`), bukan isi file dalam string. Paket dibagi saat mencapai sekitar 1 GiB atau 1.000 file, mana yang lebih dulu. Batas memakai ukuran input sehingga ukuran ZIP dapat sedikit lebih besar akibat metadata; satu file lebih besar dibuatkan ZIP sendiri. Setiap ZIP mandiri, bukan ZIP multipart yang harus digabungkan.
5. ZIP dibuat dengan ekstensi `.tmp`, diperiksa hasil close, kemudian di-rename sebelum dicatat sebagai bagian selesai. Nama arsip menyertakan ID SP2D dan ID dokumen agar nomor/nama duplikat tidak menimpa file. Progres bertambah setelah satu ZIP benar-benar selesai ditulis.
6. Browser mengambil status setiap 5 detik dan menawarkan link unduhan native. File tidak dimuat menjadi Blob JavaScript. BinaryFileResponse mendukung Range/Content-Length; file disimpan 24 jam agar permintaan ulang/range dapat dilayani. Unduhan hanya untuk pembuat paket dengan izin compile_spj.
7. Pembatalan dicek antar unit kerja dan melalui cancel callback libzip saat close. Pembatalan ketika FPDI bekerja baru diamati sesudah unit PDF selesai. Paket gagal/batal tidak dapat diunduh. Semua bagian harus berhasil sebelum hasil tersedia.
8. Scheduler membersihkan paket kedaluwarsa dan menandai worker yang terhenti. Hard timeout satu job adalah 3.600 detik; retry_after antrean 3.700 detik. Tidak ada retry kompilasi otomatis, untuk menghindari pengulangan I/O besar. Setelah gagal, buat paket baru yang lebih kecil. Unduhan dapat dilanjutkan; kompilasi setelah crash belum dilanjutkan dari checkpoint.

## Mode

- **Dokumen asli (default):** scan SP2D dan bukti belanja yang dipilih, mengikuti aturan bukti gabungan aplikasi lama. File lain di luar kategori ini tidak disertakan. Tidak melakukan render/decode gambar/PDF, sehingga cocok untuk gigabyte dan tidak mengubah kualitas, tanda tangan, atau isi dokumen.
- **PDF gabungan:** kompatibilitas alur sebelumnya; maksimal 64 MiB input dan 300 halaman per SP2D, gambar maksimal 25 megapiksel. Proses gagal secara jelas jika berkas tidak dapat dibaca. Bukan jalur untuk menggabungkan satu PDF bergigabyte. Dokumen asli dianjurkan untuk arsip resmi/tanda tangan digital.
- **Paling cepat:** CM_STORE. ZIP berfungsi sebagai kemasan tanpa penghematan ukuran yang dijanjikan. Cocok untuk PDF/JPEG/PNG yang sudah terkompresi.
- **Kompresi ringan:** DEFLATE level 1, lossless. CPU lebih ringan daripada level tinggi; rasio bergantung konten. Tidak melakukan recompress gambar atau menurunkan resolusi.

## Kapasitas dan penerapan

- Jalankan migrasi `php artisan migrate --path=database/migrations/2026_09_05_100000_create_spj_exports_table.php --force` dan `npm run build`.
- Jalankan `php artisan queue:work spj_compile --queue=spj-compile --sleep=3 --tries=1 --timeout=3600 --memory=512 --max-jobs=1` di bawah Supervisor; contoh ada di `deploy/spj-compile-worker.conf`. Mulai dengan **1 worker** untuk membatasi tekanan disk/CPU. Worker berhenti tiap paket untuk mengembalikan memori FPDI. Supervisor harus memulai ulang worker.
- Aktifkan Laravel scheduler setiap menit (`php artisan schedule:run`) agar `spj-exports:prune` berjalan setiap jam. Tanpa scheduler file tidak dibersihkan otomatis, walaupun endpoint sudah menolak unduhan setelah kedaluwarsa.
- PHP CLI harus 64-bit dan menyediakan zip/libzip, pcntl, serta driver database. Dibutuhkan tabel jobs/cache/failed_jobs standar Laravel. Antrean compile tidak menggunakan QUEUE_CONNECTION=sync milik lingkungan lain. Job yang berjalan dibatasi satu jam; workload yang memerlukan waktu lebih lama perlu dipecah melalui pilihan SP2D.
- Worker dan web harus berbagi filesystem private yang sama. Arsitektur ini untuk satu host/shared storage; belum memakai object storage atau distributed workers.
- Reserve default 1 GiB disk bebas di luar estimasi paket. Sebelum arsip, worker memeriksa ruang setidaknya 105% total input plus reserve. Mode merged memerlukan tambahan ruang PDF sementara. Pemeriksaan ruang bukan reservasi filesystem: gunakan volume khusus dengan quota, pantau disk dan jangan menjalankan banyak worker pada disk yang sama.
- `SPJ_COMPILE_PART_BYTES`, `SPJ_COMPILE_PART_FILES`, `SPJ_COMPILE_MIN_FREE_BYTES` mengatur pembagian dan reserve. Rebuild config cache lalu restart worker setelah perubahan.
- Untuk produksi, gunakan Nginx/PHP-FPM. Response Range diuji di aplikasi; offload Nginx X-Accel-Redirect/object storage belum diterapkan, sehingga unduhan besar masih memakai satu worker web selama transfer. Kapasitas unduhan serentak harus diperhitungkan.
- Preview lokal memakai migrasi tambahan dan worker sementara. Supervisor dan scheduler server produksi belum dipasang oleh perubahan kode ini.

## Verifikasi

Tes mencakup queue connection, ZIP per bagian dan kesamaan byte sumber, Range, akses lintas pengguna, pembatalan, request ganda, filter lintas OPD, sumber hilang, expiry, pruning, batas FPDI dan reserve disk. Uji skala opsional: `SPJ_EXPORT_SCALE=1 php artisan test --filter=test_thousand_file_gib_archive_with_bounded_php_memory`. Uji memakai 1.024 entri 1 MiB dari sumber sintetis berulang (1 GiB output), bukan benchmark PDF nyata atau banyak pengguna. Hasil lokal: 8,09 detik pada pemrosesan arsip, peak PHP 60,5 MiB, 16 ZIP @ sekitar 64 MiB; total pengujian termasuk setup 11,41 detik. Peak PHP tidak mengukur keseluruhan RSS/native libzip dan hasil waktu bergantung cache/disk.

## Referensi

- [Laravel queues](https://laravel.com/framework/docs/12.x/queues): timeout worker harus lebih pendek daripada retry_after; lihat implementasi lokal Laravel 13 untuk konfigurasi yang digunakan.
- [PHP ZipArchive::setCompressionName](https://www.php.net/manual/en/ziparchive.setcompressionname.php): CM_STORE dan DEFLATE serta tingkat kompresi.

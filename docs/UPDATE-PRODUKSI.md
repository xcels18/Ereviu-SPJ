# Update produksi dengan data yang sudah ada

1. Buat salinan database produksi ke database uji terpisah. Salin berkas unggahan dan gunakan APP_KEY yang sesuai secara privat. Jangan menjalankan migrate:fresh, db:wipe, seeder contoh, atau key:generate pada instalasi lama.
2. Cadangkan database, storage/app, .env dan APP_KEY di tempat terbatas; uji pemulihannya. Pastikan backup lengkap, bukan hanya struktur tabel.
3. Di salinan: composer install --no-dev --prefer-dist --optimize-autoloader; npm ci; npm run build. Jalankan php artisan app:preflight sebelum migrasi. Selesaikan setiap temuan; kode akun berulang bukan error.
4. Jalankan php artisan migrate --force di salinan. Uji akun tiap peran, unggah, revisi, pergantian mode reviu, laporan, audit UUID dan download. Jalankan suite integrasi pada database MySQL khusus tes, jangan gunakan database produksi untuk RefreshDatabase.
5. Saat rilis: aktifkan maintenance, hentikan intake pekerjaan dan tunggu pekerjaan aktif selesai, ambil backup akhir, pasang kode/build, jalankan preflight dan migrate --force. Pertahankan .env dan storage. Jalankan view:cache dan config:cache, restart worker lewat Supervisor dan aktifkan kembali aplikasi.
6. Scheduler perlu menjalankan schedule:run setiap menit; pending_file_deletions diproses setiap lima menit. Monitor kegagalan queue, usia antrean, disk dan pending cleanup. Supervisor kompilasi menggunakan deploy/spj-compile-worker.conf.
7. Jika gagal, pertahankan maintenance, periksa status migrasi dan jangan mengulang skrip secara buta. DDL MySQL dapat tersimpan sebagian. Restore database bersama berkas/kode yang sesuai bila diperlukan. Jangan rollback audit UUID ke kolom angka.

Batas yang masih ada: upload belum resumable lintas koneksi dan proses mati paksa dapat meninggalkan file yatim; ekspor laporan sinkron dibatasi 2.000 baris, bukan queue laporan; offload unduhan memerlukan konfigurasi web server produksi. Pengujian lokal bukan bukti kapasitas produksi.

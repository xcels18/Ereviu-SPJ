# Status perbaikan reviu total

## Diterapkan dan diuji

- Kolom ID objek audit mendukung UUID. Migrasi diterapkan ke MySQL pengembangan; round-trip UUID diuji dalam transaksi yang dibatalkan.
- Reviu menyimpan versi, mode aktif, dan snapshot penilaian sebelumnya. Pergantian scope tidak lagi membiarkan hasil akun lama mengalahkan hasil gabungan baru. Formulir dengan versi lama ditolak; perubahan hasil selesai memerlukan alasan.
- Upload header/metadata dibungkus transaksi; kegagalan yang tertangkap membersihkan berkas baru. Penambahan dokumen memeriksa status kembali di bawah lock induk yang sama dengan reviu.
- Penghapusan dokumen memakai antrean pembersihan setelah transaksi metadata berhasil; histori reviu mencegah SPJ terakhir ikut dihapus. Scheduler perlu berjalan.
- Perubahan nomor/total header SP2D yang sudah memiliki SPJ ditolak, panjang kode/uraian mengikuti kolom database. Ini perlindungan sementara sampai alur koreksi resmi tersedia.
- Audit penghapusan OPD/pengguna berada dalam transaksi perubahan.
- Admin terakhir dan Irban yang masih memiliki operator dilindungi dari perubahan peran/penghapusan. Role legacy belum dihapus.
- Recovery code dikonsumsi dengan lock; unduhan memerlukan konfirmasi password. Endpoint enable/disable memiliki throttle.
- Laporan SPJ/temuan dan detail OPD dipaginasi. Ekspor/cetak tertentu dibatasi 2.000 baris untuk menghindari permintaan tanpa batas, bukan ekspor background.
- Impor menolak nominal tidak valid, bukan menggantinya dengan nol. Format yang diterima: angka tanpa pemisah ribuan, maksimal dua desimal bertitik.
- Antrean kompilasi dibatasi global/per OPD dengan admission lock; pemeriksaan disk awal; kegagalan cleanup tidak ditandai berhasil.
- `app:preflight` dan `spj:operations-status` tersedia. `app:ensure-key` mempertahankan kunci yang sudah ada pada setup.
- Migrasi integritas lama dapat melewati unique index/FK restrict yang sudah diterapkan; kode belanja berulang tetap sah.

## Verifikasi

49 tes lulus, 194 assertions, satu uji beban opt-in dilewati. Build Vite dan Blade berhasil. Preflight lokal: tidak ada SPJ ganda, OPD SPJ berbeda, dokumen lintas SP2D, atau pengguna tanpa role. Pengujian tipe UUID dilakukan terpisah di MySQL lokal. Suite keseluruhan masih SQLite; tidak ada uji pada produksi.

## Belum selesai — jangan dianggap sudah diterapkan

- Upload resumable lintas koneksi serta cleanup berkas yatim akibat proses mati paksa. Transaksi SQL tidak dapat menjamin rollback filesystem saat proses dimatikan paksa.
- Impor chunk/queue beserta pratinjau dan laporan kesalahan per baris; ekspor laporan background dan agregasi SQL untuk seluruh dashboard/matriks.
- Alur permintaan/persetujuan koreksi SP2D, penonaktifan akun dan invalidasi sesi, halaman penelusuran histori reviu, tenggat pekerjaan.
- Offload download melalui web server, reservasi/kuota byte global, resume job kompilasi, monitoring/backup otomatis produksi.
- Pengujian UI mobile/keyboard, audit dependency, uji beban nyata, salinan data produksi, backup dan restore produksi.

Perubahan saat ini hanya diterapkan pada workspace dan database pengembangan. Panduan produksi ada di UPDATE-PRODUKSI.md. Dokumen ini sengaja membedakan perbaikan nyata dengan pengembangan lanjutan yang belum selesai.

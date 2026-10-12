# Backup database

Admin membuka `/database-backups` dari Pusat Arsip SPJ. Backup SQL MySQL/MariaDB dikerjakan oleh worker `spj_compile` pada antrean `spj-compile`. Worker dan scheduler harus aktif; restart worker setelah deploy. Tidak memerlukan migrasi.

Pasang mysqldump yang kompatibel dengan server. Untuk MariaDB set `DB_BACKUP_BINARY` ke path mariadb-dump. Backup mencakup tabel, data, dan trigger bawaan dump; tidak mencakup berkas unggahan, APP_KEY, akun/grant server, event atau stored routine. Jangan menjalankan perubahan skema selama snapshot. Snapshot konsisten ditujukan untuk tabel InnoDB.

Hasil disimpan di storage/app/private/database-backups dengan direktori privat. SQL hanya dapat diunduh admin pembuatnya; kedaluwarsa 24 jam. Scheduler menjalankan database-backups:prune setiap jam. Kredensial dump menggunakan file sementara privat, tidak masuk argumen proses; proses gagal membersihkan file sementara. Jika worker dihentikan paksa, pembersihan tetap melalui jadwal kedaluwarsa. Satu backup aktif diperbolehkan sekaligus.

Pemulihan harus diuji pada database terpisah terlebih dahulu oleh administrator database. Jangan mengimpor backup percobaan ke database produksi. Simpan APP_KEY dan dokumen unggahan secara terpisah. Fitur ini tidak menyediakan restore otomatis.

Verifikasi lokal: dump MySQL berhasil (1.69 MiB); pengujian akses admin, kepemilikan, kedaluwarsa dan pembersihan kegagalan lulus. Restore belum diuji.

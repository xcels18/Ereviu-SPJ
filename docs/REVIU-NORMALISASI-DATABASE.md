# Reviu normalisasi database

Tanggal: 7 September 2026. Lingkup: migrasi lengkap, model, controller pengelolaan pengguna dan SPJ, serta enam pemeriksaan agregat read-only terhadap MySQL lokal. Tidak ada skema atau data bisnis yang diubah. Constraint database aktual belum dibandingkan satu per satu terhadap migrasi; hasil struktur di bawah bersumber dari kode migrasi.

## Kesimpulan

Entitas utama sudah dipisahkan, tetapi dependensi data dan granularitas SPJ, akun, serta penilaian belum konsisten. Tidak tepat menyatakan seluruh database sudah 3NF hanya karena setiap tabel memiliki ID. Prioritasnya adalah satu sumber kebenaran, granularitas yang jelas, dan constraint relasi. Kebijakan penghapusan dan indeks merupakan masalah integritas/performa terkait, bukan pelanggaran normalisasi dengan sendirinya.

## Hasil pemeriksaan data lokal

| Pemeriksaan | Hasil |
|---|---:|
| SPJ dengan OPD berbeda dari SP2D induk | 0 |
| Kelompok SP2D dengan lebih dari satu SPJ | 0 |
| Kelompok (sp2d_id, kode_belanja) dengan lebih dari satu baris | 39 |
| Kelompok penilaian (spj_id, sp2d_budget_code_id) berulang | 0 |
| Dokumen dengan akun milik SP2D berbeda dari induk SPJ | 0 |
| Tahun/triwulan berbeda dari tanggal SP2D | 0 |

Angka 39 adalah jumlah kelompok, bukan jumlah baris duplikat. Belum diperiksa apakah jumlah dan uraian baris sama. Jangan menggabungkan atau menghapus baris otomatis. Pemeriksaan dilakukan sebagai query terpisah, bukan snapshot transaksi tunggal.

## Temuan dan rekomendasi

1. **Tinggi — OPD berulang di SPJ.** `spj.sp2d_id` menentukan `sp2d.opd_id`, sementara `spj.opd_id` menyimpan nilai yang sama. Untuk kepemilikan saat ini, ambil OPD melalui SP2D. Jika dimaksudkan sebagai snapshot historis, namai dan dokumentasikan secara eksplisit. Foreign key terpisah tidak menjamin kedua OPD sama.
2. **Tinggi — peran pengguna mempunyai dua sumber.** `users.role` digunakan untuk filter/manajemen, sedangkan `model_has_roles` digunakan oleh `hasRole`/permission. Pilih tabel role Spatie sebagai sumber otoritatif, pindahkan pembacaan legacy, audit ketidaksesuaian, baru hapus kolom lama. Jumlah mismatch role belum dihitung.
3. **Tinggi — granularitas SPJ tidak ditegakkan.** Controller membuat satu SPJ untuk keseluruhan SP2D, tetapi model menggunakan hasMany, tidak ada unique(sp2d_id) dalam migrasi, dan kolom akun legacy masih ada pada header SPJ. Jika benar satu SPJ per SP2D, audit data lama lalu gunakan constraint unik dan pindahkan cakupan akun seluruhnya ke detail. Jika beberapa pengajuan/revisi sah, modelkan nomor revisi dan status aktif secara eksplisit; jangan memaksakan unique tunggal.
4. **Tinggi — akun versus rincian transaksi.** `sp2d_budget_codes` menyimpan kode, uraian, dan jumlah tanpa identitas master akun. Data aktual memiliki 39 kelompok kode berulang. Usulan: master akun berversi, tabel akun per SP2D, dan tabel rincian apabila satu akun memang memiliki banyak transaksi. `uraian` transaksi boleh berbeda dari nama master akun; jangan menganggap keduanya salinan. Kode yang sama lintas tahun/versi nomenklatur tidak otomatis memiliki arti identik.
5. **Tinggi — temuan dan penilaian tercampur.** `spj_findings` memuat jenis temuan serta status kelengkapan; controller menggunakannya sebagai satu penilaian per akun/gabungan. Pisahkan `spj_reviews`, `spj_review_assessments`, dan temuan jika satu penilaian dapat memiliki beberapa temuan. Beri identitas scope eksplisit untuk gabungan. Unique dengan kolom nullable tidak cukup menjamin hanya satu scope gabungan pada MySQL; gunakan rancangan scope non-null atau mekanisme constraint yang sesuai.
6. **Tinggi — cakupan relasi tidak dijamin.** Dokumen/temuan menunjuk SPJ dan akun secara independen. Foreign key biasa tidak memastikan akun berasal dari SP2D yang sama. Rancang detail SPJ yang menjadi titik referensi dokumen dan penilaian, atau gunakan constraint komposit yang konsisten; tetap validasi pada layanan aplikasi. Jangan menambah kolom redundan tanpa aturan sinkronisasi hanya demi constraint.
7. **Menengah — nilai dan periode turunan.** `sp2d.tahun` dan `triwulan` dapat diturunkan dari tanggal jika definisinya tahun kalender. `sp2d.jumlah_total` dapat merupakan total detail atau nilai resmi dokumen; `spj.jumlah` serta `nomor_spj` saat ini disalin dari SP2D pada upload. Tetapkan makna: nilai resmi/snapshot dipertahankan dengan rekonsiliasi; nilai turunan dihitung atau dikontrol. Jangan menghapus tahun jika sebenarnya tahun anggaran berbeda dari tahun tanggal.
8. **Menengah — daftar persyaratan JSON.** `review_guides.required_documents` cocok sebagai konfigurasi sederhana. Jika dibutuhkan checklist per persyaratan, identitas dokumen wajib, versi pedoman, atau laporan kekurangan, pisahkan ke tabel persyaratan pedoman dan hubungkan ke hasil pemeriksaan. JSON sendiri bukan bukti kesalahan desain.
9. **Tinggi — cascade pada identitas pembuat.** Penghapusan users dapat menghapus SP2D, dokumen, dan temuan melalui created_by/uploaded_by. Untuk arsip pertanggungjawaban gunakan penonaktifan akun dan RESTRICT atau SET NULL plus snapshot identitas sesuai kebutuhan. Null-on-delete akun pada dokumen/temuan berisiko mengubah arti akun menjadi scope gabungan. Ini masalah integritas/retensi, bukan normalisasi murni.

## Denormalisasi yang layak dipertahankan

- Identitas pelaku dalam `activity_logs` adalah snapshot historis yang disengaja.
- `spj_exports.selection`, `parts`, nama OPD snapshot, serta counter progres adalah data pekerjaan sementara; tidak perlu dipecah hanya demi normalisasi. Pecah parts jika kelak bagian arsip memiliki lifecycle, retry, atau analitik independen.
- Payload queue, session, cache, dan MFA tidak perlu dinormalisasi menjadi tabel bisnis.
- Tabel penugasan auditor memiliki unique(user_id, opd_id), dan pivot izin memiliki key komposit; pemisahan relasinya sudah baik.

## Urutan migrasi yang disarankan

1. Tetapkan granularitas akun dan kardinalitas SPJ; audit 39 kelompok secara terkontrol, termasuk relasi dokumen/temuan dan rekonsiliasi jumlah.
2. Backup database dan berkas, lalu uji pemulihan. Buat tabel/kolom baru secara aditif tanpa menghapus legacy.
3. Backfill bertahap dengan pemetaan ID lama ke ID baru. Bandingkan jumlah record, total nominal, dan jumlah tautan dokumen sebelum/sesudah.
4. Pindahkan pembacaan/penulisan melalui satu layanan transaksi. Uji upload, revisi, laporan, kompilasi, izin pengguna, dan akses lintas OPD.
5. Tambahkan constraint setelah data lolos audit; rencanakan dampak lock untuk tabel besar. Hapus kolom legacy setelah cutover terverifikasi dan periode rollback selesai.

Tidak ada penggabungan akun, penghapusan kolom, atau migrasi normalisasi yang dijalankan dalam reviu ini.

## Perbaikan diterapkan — 7 September 2026

- Migrasi `2026_09_07_140000_protect_archive_integrity` sudah diterapkan di MySQL lokal: unique SPJ per SP2D serta RESTRICT pada tujuh relasi arsip. Cadangan struktur tujuh tabel disimpan privat di `storage/app/private/schema-before-integrity-20260907.sql` (bukan backup data lengkap).
- Upload SPJ baru memakai transaksi dan lock induk saat pembuatan header, dengan pesan untuk menggunakan Tambah Dokumen jika SPJ sudah ada. Constraint unik menjadi perlindungan akhir untuk penulisan bersamaan. Proses penyimpanan seluruh berkas belum menjadi transaksi atomik dengan filesystem.
- Pembacaan peran di pengelolaan pengguna, tampilan dan audit menggunakan relasi Spatie. Kolom `users.role` masih disimpan untuk kompatibilitas migrasi/seeder; tidak lagi menjadi sumber pembacaan peran aplikasi tersebut. Penghapusan fisik kolom belum dilakukan. Create/update pengguna beserta assignment memakai transaksi.
- Model SPJ memvalidasi kesamaan OPD dan kepemilikan akun. Model dokumen/temuan memvalidasi akun terhadap SP2D induknya. Validasi cakupan ini adalah perlindungan Eloquent, bukan constraint lintas tabel untuk SQL langsung.
- Akun pengguna dengan arsip serta OPD dengan SP2D ditolak penghapusannya dengan pesan validasi; database RESTRICT tetap melindungi referensi utama dari SQL langsung.
- Verifikasi: 47 tes lulus, 183 assertions; satu uji beban opsional dilewati. Kompilasi Blade berhasil.

Masih membutuhkan desain/migrasi lanjutan: pemisahan master akun dan rincian untuk 39 kelompok berulang, versi penilaian dan temuan, penghapusan kolom redundan, constraint cakupan lintas tabel, serta rekonsiliasi nilai resmi versus turunan. Tidak ada baris akun berulang yang digabung atau dihapus.

# Rencana perbaikan lanjutan aplikasi SPJ

Dicatat: 15 September 2026, atas permintaan pengguna.
Status: backlog untuk pekerjaan mendatang; bukan pernyataan bahwa fitur sudah diterapkan.

## Konteks dan aturan yang harus dipertahankan

- Menurut pengguna, data nyata saat ini adalah SP2D beserta rincian akun belanjanya. Dokumen yang mungkin sudah diunggah masih dummy.
- Kode belanja berulang dalam satu SP2D adalah rincian yang sah. Jangan menggabungkan, menghapus, atau memberi batasan unik berdasarkan pasangan SP2D dan kode belanja. Pertahankan ID rincian dan hubungan buktinya.
- Jangan menghapus dokumen dummy tanpa instruksi pengguna.
- Utamakan konsistensi data dan kelengkapan alur kerja sebelum penambahan fitur lain.
- Pengujian lokal tidak menggantikan pengujian MySQL dan salinan data produksi. Update produksi memerlukan backup lengkap serta uji pemulihan.

## Prioritas pekerjaan

| Urutan | Perbaikan | Hasil yang diharapkan |
|---|---|---|
| 1 | Pratinjau dan impor SP2D di background | Sebelum menyimpan, tampilkan SP2D baru, nomor yang sudah ada, dan kesalahan per baris. Sediakan progres, hasil impor, serta pencegahan pemrosesan ganda. Impor besar tidak menahan request web. |
| 2 | Alur koreksi SP2D | Koreksi nomor, nilai, atau rincian melalui alasan dan riwayat yang jelas; hubungan data dan bukti tetap terjaga. |
| 3 | Unggah dokumen bertahap dan bisa dilanjutkan | Progres per berkas, pengulangan hanya untuk bagian gagal, dan kelanjutan upload setelah koneksi putus. Selesaikan sebelum penggunaan dokumen nyata dalam jumlah besar. |
| 4 | Perhitungan dashboard dan laporan di database | Gunakan agregasi SQL dan pengambilan data terbatas; kurangi pemuatan seluruh data ke memori. Verifikasi performa dengan volume SP2D representatif. |
| 5 | Halaman riwayat reviu dan daftar pekerjaan | Pengguna dapat menelusuri versi penilaian yang sudah tersimpan dan melihat pekerjaan yang belum dinilai atau memerlukan perbaikan. |
| 6 | Backup otomatis dan pemantauan operasional | Pantau antrean gagal, usia antrean, kapasitas disk, dan hasil backup. Uji pemulihan database serta berkas, bukan hanya keberadaan file backup. |

## Verifikasi sebelum rilis

- Jalankan pengujian integrasi pada database MySQL khusus uji dan salinan data produksi, bukan database produksi langsung.
- Uji hak akses tiap peran, konsistensi nominal/rincian, upload, koreksi, reviu, laporan, dan kompilasi.
- Uji kegagalan koneksi, disk penuh, worker berhenti, dan permintaan bersamaan sesuai fitur yang diubah.
- Pertahankan APP_KEY, konfigurasi, database, dan berkas unggahan saat update; ikuti UPDATE-PRODUKSI.md.

## Titik mulai yang disarankan

Mulai dari prioritas 1: pratinjau dan impor SP2D di background, karena paling sesuai dengan penggunaan aplikasi saat catatan ini dibuat.

Referensi: STATUS-PERBAIKAN-2026-09-14.md, REVIU-TOTAL-APLIKASI-2026-09-14.md, dan UPDATE-PRODUKSI.md. Status implementasi terbaru harus diperiksa kembali sebelum mengerjakan backlog ini.

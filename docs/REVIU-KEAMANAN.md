# Reviu keamanan dan pembaruan desain KawalSPJ

Tanggal: 5 September 2026. Metode: tinjauan kode lokal dan pengujian otomatis dengan SQLite sementara. Bukan pengujian penetrasi server produksi.

## Temuan yang diperbaiki

| Prioritas | Temuan dan dampak | Perbaikan |
| --- | --- | --- |
| Tinggi | `SpjController::reviewStore` memeriksa izin `review_spj`, tetapi tidak memeriksa wilayah SPJ. Irban dapat mengubah status/catatan SPJ di luar penugasannya melalui ID SPJ. | Pemeriksaan wilayah dijalankan sebelum validasi dan perubahan data. Uji mencakup penolakan wilayah lain dan keberhasilan wilayah yang ditugaskan. |
| Tinggi | `SpjController::store` menerima status bukti dan catatan dari pengguna yang hanya memiliki izin unggah. Status `belum_dibelanjakan` dapat memengaruhi perhitungan kelengkapan tanpa bukti. | Request berisi kolom reviu dari pengguna tanpa izin reviu ditolak dengan 403 sebelum penyimpanan. |
| Tinggi | Modal SPJ memasukkan nama berkas, uraian akun, dan catatan ke `innerHTML` tanpa escaping. Catatan dengan penutup textarea dapat membentuk elemen HTML aktif ketika modal dibuka. | Escaping karakter HTML pada teks dan atribut dinamis tersebut. URL dokumen tetap berasal dari route aplikasi. |
| Sedang | POST `/mfa/challenge` tidak memiliki pembatasan percobaan, sehingga kode MFA dapat ditebak berulang kali. | Middleware membatasi 5 request per menit per IP. Ini belum membatasi serangan terdistribusi berdasarkan akun. |
| Sedang | Pemeriksaan akses dokumen dapat menggunakan kecocokan OPD sebagai fallback bagi pemeriksa yang tidak ditugaskan. | Pemeriksa harus lolos penugasan; fallback OPD hanya untuk pengguna non-pemeriksa dengan OPD yang tidak null. |

## Risiko lanjutan yang perlu ditangani

- **Dokumen lama pada disk public:** `downloadDocument` dan penghapusan masih mendukung fallback `public`. Jika berkas lama masih tersedia melalui `public/storage`, URL statis dapat melewati otorisasi controller. Keberadaan berkas dan konfigurasi server produksi belum diverifikasi. Inventarisasi dan migrasikan dokumen lama ke disk private, lalu blokir URL publik dokumen SPJ setelah memastikan akses unduh tetap berfungsi.
- **MFA:** kode pemulihan dikonsumsi dengan read-modify-write tanpa transaksi/penguncian sehingga request bersamaan berpotensi memakai kode yang sama. Challenge belum memiliki tenggat khusus; flag `mfa_verified` masih boolean, belum terikat ID pengguna. Unduhan kode pemulihan belum meminta konfirmasi password ulang. Perlu penguatan alur dan uji concurrency.
- **Periode upload:** `addDocuments` dan `destroyDocument` memeriksa status SPJ, tetapi tidak memeriksa jadwal sebagaimana upload awal. Perlu keputusan aturan bisnis yang konsisten untuk pelengkapan dokumen di luar periode.
- **Beban resource:** pengambilan seluruh data untuk statistik dan opsi `per_page=all`, serta upload banyak berkas tanpa batas jumlah di aplikasi, berpotensi membebani memori. Terapkan agregasi SQL, batas jumlah berkas, dan pembatasan proses kompilasi.
- **Deployment:** contoh Nginx masih HTTP pada port 80. Verifikasi TLS dan secure session cookie di server sebenarnya. Berkas dump SQL ada di root workspace dan belum tercakup `.gitignore`; pindahkan backup sensitif ke penyimpanan terkontrol sebelum distribusi repositori. Isi dump tidak dibuka dalam reviu ini.
- **Dependensi:** `npm audit` dan `composer audit --locked` tidak berhasil menghubungi registry karena DNS lingkungan. Status advisory dependensi belum dapat dipastikan; jangan mengartikan hasil ini sebagai bebas kerentanan.

## Desain

Identitas KawalSPJ diseragamkan pada login dan navigasi. Palet teal, permukaan netral, sidebar dengan indikator aktif terang, jarak komponen, dan tipografi diperbarui melalui komponen bersama. Dashboard mendapat pengantar, tindakan sesuai izin, serta indikator kelengkapan dari data aktual. Ditambahkan skip link, label tombol navigasi, dukungan Escape, fokus keyboard, dan reduced motion. Login diperiksa secara visual di browser lokal; keseluruhan alur tiap peran belum diuji manual.

## Validasi

26 pengujian PHPUnit berhasil (70 assertions), termasuk pengujian pembatasan wilayah reviu, penolakan kolom reviu dari akun unggah, dan rate limit MFA. Pemeriksaan regresi escaping HTML berhasil. Build Vite dan kompilasi seluruh Blade berhasil. Suite PHPUnit memakai SQLite in-memory, bukan database aplikasi. Migrasi enum lama disesuaikan memakai schema builder agar dapat diuji lintas driver; perubahan ini belum diuji pada MySQL produksi. Tidak menjalankan migrasi atau mengubah data produksi.

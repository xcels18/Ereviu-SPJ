# Tema SPJ mengikuti e-Konsul

Acuan: proyek lokal e-konsul/DESIGN_SYSTEM.md, versi 3 Oktober 2026.

Tema antarmuka web bersama mencakup layout aplikasi dan guest: login, reset password/MFA, dashboard, OPD, SP2D, upload/reviu, kompilasi, laporan, panduan reviu, pengguna, profil dan audit. Warna navy #1e3a5f, latar #f8fafc, kartu putih, border #e2e8ef, aksen positif #439575. Font Inter/Avenir Next. Sidebar 245px, topbar 78px, kartu 10px dan tombol/input 7px. Logo Inspektorat ditampilkan pada layout masuk.

Layout guest disusun ulang menjadi header institusi, pengantar dan kartu akses. Komponen global menyesuaikan seluruh halaman yang menggunakan x-app-layout. Header dekoratif SPJ dipadatkan menjadi panel putih. Alur/route/hak akses dan data bisnis tidak diubah. Template PDF/Excel tidak disusun ulang dalam perubahan antarmuka web ini.

Verifikasi: build dan kompilasi Blade berhasil, 54 tes lulus (satu uji beban opsional dilewati). Login diperiksa visual di browser. Kredensial admin bawaan tidak diterima; inspeksi visual interaktif seluruh halaman setelah login masih membutuhkan sesi pengguna yang sah.

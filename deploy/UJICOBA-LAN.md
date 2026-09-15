# Catatan: Ujicoba Aplikasi dari Laptop/HP Lain (Jaringan Lokal)

Untuk menguji aplikasi ini dari perangkat lain (laptop/HP) yang terhubung ke
Wi-Fi/LAN yang sama dengan komputer ini — **tanpa perlu deploy ke NUC dulu**.

## Cara pakai

1. Buka terminal di folder project ini.
2. Jalankan:
   ```
   ./deploy/serve-lan.sh
   ```
3. Script akan otomatis:
   - Mendeteksi IP Wi-Fi/LAN komputer ini
   - Memperbarui `APP_URL` di `.env` sesuai IP tersebut
   - Menjalankan server dan menampilkan alamat yang harus dibuka, contoh:
     ```
     http://192.168.1.134:8000
     ```
4. Buka alamat tersebut dari browser di laptop/HP lain (harus terhubung ke
   Wi-Fi/router yang sama).
5. Login: `admin@inspektorat.go.id` / `password`
6. Selesai ujicoba → tekan `Ctrl+C` di terminal untuk menghentikan server.

## Catatan penting

- Alamat IP bisa berubah setiap kali komputer ini konek ulang ke Wi-Fi
  (karena DHCP) — script selalu mendeteksi ulang otomatis, tidak perlu diatur
  manual setiap kali.
- Kedua perangkat **wajib** berada di jaringan Wi-Fi/router yang sama (bukan
  jaringan tamu/terisolasi), kalau tidak perangkat lain tidak akan bisa
  menjangkau alamat tersebut.
- Firewall macOS di komputer ini sudah mengizinkan koneksi masuk untuk PHP,
  jadi tidak perlu pengaturan tambahan.
- Ini hanya untuk ujicoba sementara (server development). Untuk pemakaian
  sehari-hari oleh semua user kantor, gunakan panduan deploy ke NUC di
  `deploy/DEPLOY_GUIDE.md`.

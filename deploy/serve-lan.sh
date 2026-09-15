#!/usr/bin/env bash
# Jalankan skrip ini setiap kali ingin ujicoba aplikasi dari laptop/HP lain
# di jaringan Wi-Fi/LAN yang sama.
#
# Cara pakai:
#   chmod +x deploy/serve-lan.sh   (cukup sekali)
#   ./deploy/serve-lan.sh
#
# Tekan Ctrl+C di terminal untuk menghentikan server saat selesai ujicoba.

set -e
cd "$(dirname "$0")/.."

# 1. Deteksi otomatis IP lokal mesin ini di jaringan Wi-Fi/LAN
IP=$(ipconfig getifaddr en0 2>/dev/null || ipconfig getifaddr en1 2>/dev/null)
if [ -z "$IP" ]; then
  echo "Tidak bisa mendeteksi IP lokal. Pastikan terhubung ke Wi-Fi/LAN."
  exit 1
fi

PORT=8000
NEW_URL="http://${IP}:${PORT}"

# 2. Update APP_URL di .env supaya link yang dihasilkan aplikasi konsisten
if grep -q "^APP_URL=" .env; then
  sed -i.bak "s|^APP_URL=.*|APP_URL=${NEW_URL}|" .env && rm -f .env.bak
else
  echo "APP_URL=${NEW_URL}" >> .env
fi

php artisan config:clear > /dev/null

echo "=========================================="
echo " Aplikasi siap diakses dari laptop lain di jaringan yang sama:"
echo ""
echo "   ${NEW_URL}"
echo ""
echo " Pastikan laptop lain terhubung ke Wi-Fi/router yang sama."
echo " Tekan Ctrl+C untuk menghentikan server."
echo "=========================================="

# 3. Jalankan server, menerima koneksi dari semua interface (0.0.0.0)
php artisan serve --host=0.0.0.0 --port="$PORT"

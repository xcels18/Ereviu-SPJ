#!/usr/bin/env bash
# Jalankan di NUC (Ubuntu Server) setelah kode aplikasi & database sudah dipindahkan.
# Script ini HANYA menyiapkan aplikasi (permission, cache, optimasi).
# Install PHP/Nginx/MySQL dilakukan manual sesuai DEPLOY_GUIDE.md.

set -e

APP_DIR="/var/www/spj-opd"
cd "$APP_DIR"

echo "== Install dependency PHP (tanpa paket development) =="
composer install --no-dev --optimize-autoloader

echo "== Generate APP_KEY jika belum ada =="
php artisan key:generate --force

echo "== Migrasi skema (aman dijalankan berulang, hanya menambah yang belum ada) =="
php artisan migrate --force

echo "== Buat symlink storage publik =="
php artisan storage:link

echo "== Set permission storage & cache =="
sudo chown -R www-data:www-data storage bootstrap/cache
sudo find storage bootstrap/cache -type d -exec chmod 775 {} \;
sudo find storage bootstrap/cache -type f -exec chmod 664 {} \;

echo "== Cache config/route/view untuk performa production =="
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "== Selesai. Restart php-fpm & nginx agar konfigurasi baru terpakai =="
sudo systemctl restart php8.3-fpm nginx

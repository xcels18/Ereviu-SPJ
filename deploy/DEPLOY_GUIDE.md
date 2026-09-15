# Panduan Deploy Aplikasi SPJ OPD ke Server Lokal (NUC)

Aplikasi: Laravel 13, PHP ^8.3, database MySQL.
Target: NUC mini PC sebagai server lokal, diakses user lain via jaringan LAN kantor.

Semua file di folder `deploy/` ini dibuat untuk mendukung proses ini:
- `nginx-spj-opd.conf` — konfigurasi web server
- `.env.production.example` — template environment production
- `spj_opd_seed.sql` — dump database saat ini (1 user admin + roles/permission, tanpa data uji coba)
- `setup-app.sh` — otomatisasi setup aplikasi setelah kode di-copy ke NUC
- `backup-db.sh` — script backup database harian

---

## 1. Instalasi dasar di NUC (Ubuntu Server 22.04/24.04)

```bash
sudo apt update && sudo apt upgrade -y

# PHP 8.3 + ekstensi yang dibutuhkan Laravel
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.3 php8.3-fpm php8.3-mysql php8.3-mbstring php8.3-xml \
  php8.3-curl php8.3-zip php8.3-bcmath php8.3-gd php8.3-intl

# Nginx
sudo apt install -y nginx

# MySQL Server
sudo apt install -y mysql-server
sudo mysql_secure_installation

# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Git (untuk clone/copy kode, opsional jika copy manual)
sudo apt install -y git unzip
```

## 2. Siapkan database MySQL

```bash
sudo mysql -u root <<'SQL'
CREATE DATABASE spj_opd CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'spj_opd_user'@'localhost' IDENTIFIED BY 'GANTI_PASSWORD_YANG_KUAT';
GRANT ALL PRIVILEGES ON spj_opd.* TO 'spj_opd_user'@'localhost';
FLUSH PRIVILEGES;
SQL

# Import data awal (1 user admin + roles/permission, database sudah bersih dari data uji coba)
mysql -u spj_opd_user -p spj_opd < deploy/spj_opd_seed.sql
```

Simpan password DB ke file yang dipakai `backup-db.sh`:
```bash
echo -n 'GANTI_PASSWORD_YANG_KUAT' | sudo tee /etc/spj-opd-db-password
sudo chmod 600 /etc/spj-opd-db-password
```

## 3. Pindahkan kode aplikasi

Dari komputer ini, kompres kode (tanpa `vendor/`, `node_modules/`, `storage/app/public/spj`, dan `.git` jika tidak diperlukan):

```bash
tar --exclude='vendor' --exclude='node_modules' --exclude='.git' \
    --exclude='storage/app/public/spj' \
    -czf spj-opd.tar.gz -C /Users/habelpggalo/project spj-opd
```

Salin `spj-opd.tar.gz` ke NUC (via USB, `scp`, atau file sharing lain), lalu di NUC:

```bash
sudo mkdir -p /var/www/spj-opd
sudo tar -xzf spj-opd.tar.gz -C /var/www --strip-components=1 -C /var/www/spj-opd
cd /var/www/spj-opd
```

## 4. Konfigurasi environment

```bash
cp deploy/.env.production.example .env
nano .env   # isi APP_URL (IP NUC), DB_PASSWORD sesuai yang dibuat di langkah 2
```

## 5. Jalankan setup otomatis

```bash
chmod +x deploy/setup-app.sh deploy/backup-db.sh
sudo ./deploy/setup-app.sh
```

Script ini melakukan: `composer install --no-dev`, `key:generate`, `migrate`, `storage:link`,
set permission `storage/` & `bootstrap/cache/`, cache config/route/view, lalu restart php-fpm & nginx.

## 6. Pasang konfigurasi Nginx

```bash
sudo cp deploy/nginx-spj-opd.conf /etc/nginx/sites-available/spj-opd
sudo ln -s /etc/nginx/sites-available/spj-opd /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default   # opsional, matikan default site
sudo nginx -t   # cek syntax valid
sudo systemctl restart nginx
```

## 7. Izinkan akses dari jaringan LAN (firewall)

```bash
sudo ufw allow 80/tcp
sudo ufw status
```

Cari tahu IP lokal NUC: `ip addr show | grep inet`. User lain di kantor mengakses lewat
`http://<IP_NUC>` dari browser.

## 8. Backup otomatis harian

```bash
sudo crontab -e
# tambahkan baris:
0 23 * * * /var/www/spj-opd/deploy/backup-db.sh >> /var/log/spj-opd-backup.log 2>&1
```

## 9. Verifikasi akhir

- Buka `http://<IP_NUC>` dari komputer lain di jaringan yang sama → halaman login harus tampil.
- Login pakai `admin@inspektorat.go.id` / `password`, **segera ganti password ini setelah login pertama**.
- Cek `php artisan test` di NUC (opsional, hanya scaffolding auth bawaan).
- Coba upload SPJ untuk memastikan permission storage benar (tidak ada error 500 saat upload).

## Catatan penting

- Login pertama pakai password default `password` — **wajib diganti** segera setelah verifikasi berhasil.
- `spj_opd_seed.sql` hanya berisi struktur roles/permission + 1 admin, tidak ada data OPD/SP2D contoh —
  data OPD dan SP2D asli perlu diinput/diimport ulang oleh admin setelah aplikasi berjalan di NUC.
- Jika nanti akses dari luar jaringan kantor diperlukan, pertimbangkan VPN daripada expose port ke internet.

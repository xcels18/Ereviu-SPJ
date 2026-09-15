#!/usr/bin/env bash
# Backup harian database MySQL spj_opd.
# Pasang via cron, contoh (jalan tiap hari jam 23:00):
#   0 23 * * * /var/www/spj-opd/deploy/backup-db.sh >> /var/log/spj-opd-backup.log 2>&1

set -e

BACKUP_DIR="/var/backups/spj-opd"
DB_NAME="spj_opd"
DB_USER="spj_opd_user"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

mkdir -p "$BACKUP_DIR"

mysqldump -u "$DB_USER" -p"$(cat /etc/spj-opd-db-password)" "$DB_NAME" \
  | gzip > "$BACKUP_DIR/spj_opd_${TIMESTAMP}.sql.gz"

# Simpan hanya 30 backup terakhir agar disk tidak penuh
ls -1t "$BACKUP_DIR"/spj_opd_*.sql.gz | tail -n +31 | xargs -r rm --

echo "Backup selesai: $BACKUP_DIR/spj_opd_${TIMESTAMP}.sql.gz"

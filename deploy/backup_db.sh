#!/usr/bin/env bash
# =====================================================================
# ViretaDev — Automated Encrypted Database Backup Script
#
# Takes a compressed PostgreSQL dump, encrypts it with AES-256,
# logs the result, and purges backups older than 7 days.
#
# Cron entry recommendation (runs daily at 3:30 AM):
# 30 3 * * * /path/to/deploy/backup_db.sh >> /path/to/application/logs/backup.log 2>&1
# =====================================================================

set -eo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"

# 1. Load Environment Variables
if [ -f "${APP_DIR}/.env" ]; then
    set -a
    # shellcheck disable=SC1091
    source "${APP_DIR}/.env"
    set +a
else
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] ERROR: .env file not found in ${APP_DIR}"
    exit 1
fi

DB_HOST="${DB_HOST:-localhost}"
DB_PORT="${DB_PORT:-5432}"
DB_USER="${DB_USER:-postgres}"
DB_NAME="${DB_NAME:-vireta_db}"
ENCRYPTION_PASS="${APP_ENCRYPTION_KEY:-vireta_secure_backup_key_2026}"

BACKUP_DIR="${HOME}/vireta-private/db_dumps/encrypted"
mkdir -p "${BACKUP_DIR}"

DATE_TAG="$(date '+%Y%m%d_%H%M%S')"
RAW_DUMP="${BACKUP_DIR}/${DB_NAME}_${DATE_TAG}.dump"
ENC_DUMP="${BACKUP_DIR}/${DB_NAME}_${DATE_TAG}.dump.enc"

echo "[$(date '+%Y-%m-%d %H:%M:%S')] Starting automated database backup for: ${DB_NAME}..."

# 2. Run pg_dump
PGPASSWORD="${DB_PASSWORD}" pg_dump -h "${DB_HOST}" -p "${DB_PORT}" -U "${DB_USER}" \
    -Fc -d "${DB_NAME}" -f "${RAW_DUMP}"

# 3. Encrypt with OpenSSL AES-256-CBC
openssl enc -aes-256-cbc -salt -pbkdf2 -iter 100000 \
    -in "${RAW_DUMP}" -out "${ENC_DUMP}" -pass pass:"${ENCRYPTION_PASS}"

# Remove raw unencrypted dump immediately
rm -f "${RAW_DUMP}"

ENC_SIZE="$(du -h "${ENC_DUMP}" | cut -f1)"
echo "[$(date '+%Y-%m-%d %H:%M:%S')] Backup successfully created & encrypted: ${ENC_DUMP} (Size: ${ENC_SIZE})"

# 4. Auto-purge backups older than 7 days
echo "[$(date '+%Y-%m-%d %H:%M:%S')] Purging backups older than 7 days..."
find "${BACKUP_DIR}" -name "${DB_NAME}_*.dump.enc" -type f -mtime +7 -delete

echo "[$(date '+%Y-%m-%d %H:%M:%S')] Backup job completed successfully."

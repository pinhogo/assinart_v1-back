#!/usr/bin/env bash
set -euo pipefail

APP_ROOT="/home/site/wwwroot"
DATA_STORAGE="/home/data/storage/app/public"
BACKUP_DIR="/home/data/backups"

# 1. Garante pastas persistentes de mídia e runtime do Laravel (idempotente)
mkdir -p "${DATA_STORAGE}"/{avatars,covers,cards,wallpapers}
mkdir -p "${BACKUP_DIR}"
mkdir -p "${APP_ROOT}/storage/app"
mkdir -p "${APP_ROOT}/storage/framework"/{sessions,views,cache/data}
mkdir -p "${APP_ROOT}/storage/logs"

# 2. Recria symlinks de storage público apontando para /home/data (sobrevive a redeploys ZIP)
rm -rf "${APP_ROOT}/storage/app/public" "${APP_ROOT}/public/storage"
ln -sfn "${DATA_STORAGE}" "${APP_ROOT}/storage/app/public"
ln -sfn "${DATA_STORAGE}" "${APP_ROOT}/public/storage"

# 3. Backup preventivo com timestamp do SQLite antes de rodar migrations
DB_PATH="${DB_DATABASE:-/home/data/database.sqlite}"
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ] && [ -n "${DB_PATH}" ]; then
  mkdir -p "$(dirname "${DB_PATH}")"
  if [ -f "${DB_PATH}" ] && [ -s "${DB_PATH}" ]; then
    TIMESTAMP="$(date -u +%Y%m%dT%H%M%SZ)"
    BACKUP_FILE="${BACKUP_DIR}/database_${TIMESTAMP}.sqlite"
    if command -v sqlite3 >/dev/null 2>&1; then
      sqlite3 "${DB_PATH}" ".backup '${BACKUP_FILE}'"
    else
      cp -p "${DB_PATH}" "${BACKUP_FILE}"
    fi
  elif [ ! -f "${DB_PATH}" ]; then
    touch "${DB_PATH}"
  fi
fi

# 4. Executa migrations e gera caches de runtime com as App Settings do ambiente
cd "${APP_ROOT}"
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Aplica configuração do Nginx e recarrega (validar comportamento de reload no App Service real)
if [ -f "${APP_ROOT}/azure/nginx.conf" ] && [ -d "/etc/nginx/sites-available" ]; then
  cp "${APP_ROOT}/azure/nginx.conf" /etc/nginx/sites-available/default
  nginx -t || true
  nginx -s reload || service nginx reload || true
fi

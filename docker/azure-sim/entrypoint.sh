#!/usr/bin/env bash
set -euo pipefail

APP_ROOT="/home/site/wwwroot"

mkdir -p /home/data/storage/app/public/{avatars,covers,cards,wallpapers}
mkdir -p /home/data/backups
mkdir -p "${APP_ROOT}/storage/logs"

# Garante APP_KEY valido se o container for iniciado sem a variavel definida
if [ -z "${APP_KEY:-}" ]; then
  export APP_KEY="base64:$(php -r 'echo base64_encode(random_bytes(32));')"
fi

# Aguarda PostgreSQL ficar acessivel quando DB_CONNECTION=pgsql
if [ "${DB_CONNECTION:-sqlite}" = "pgsql" ]; then
  PG_HOST="${DB_HOST:-postgres}"
  PG_PORT="${DB_PORT:-5432}"
  echo "[azure-sim] Aguardando PostgreSQL em ${PG_HOST}:${PG_PORT}..."
  for _ in $(seq 1 30); do
    if php -r 'exit(@fsockopen(getenv("DB_HOST") ?: "postgres", (int)(getenv("DB_PORT") ?: 5432), $e, $s, 2) ? 0 : 1);'; then
      break
    fi
    sleep 2
  done
fi

# Limpa caches antigos para garantir que alteracoes de env (sqlite <-> pgsql) reflitam no boot
rm -f "${APP_ROOT}/bootstrap/cache/config.php" "${APP_ROOT}/bootstrap/cache/routes-v7.php"

# Reproduz o boot idempotente do Azure App Service
bash "${APP_ROOT}/azure/startup.sh"

# Garante que workers www-data do PHP-FPM consigam ler/gravar em /home/data e storage/
chown -R www-data:www-data /home/data "${APP_ROOT}/storage" "${APP_ROOT}/bootstrap/cache"

# Inicia PHP-FPM (127.0.0.1:9000) em background e Nginx (8080) em foreground
php-fpm -D
exec nginx -g "daemon off;"

#!/usr/bin/env bash
set -euo pipefail

# Backup seguro com timestamp do SQLite de produção (/home/data/database.sqlite)
#
# Uso:
#   ./azure/backup.sh
#   DB_DATABASE=/caminho/banco.sqlite BACKUP_DIR=/caminho/backups ./azure/backup.sh
#
# Restauração em caso de falha/rollback:
#   1. Pare ou coloque o app em manutenção e identifique o arquivo em /home/data/backups/:
#      ls -lh /home/data/backups/database_*.sqlite
#   2. Faça uma cópia de segurança do estado atual:
#      cp -p /home/data/database.sqlite /home/data/database.sqlite.broken
#   3. Restaure de forma atômica (com WAL fechado):
#      rm -f /home/data/database.sqlite-wal /home/data/database.sqlite-shm
#      sqlite3 /home/data/database.sqlite ".restore '/home/data/backups/database_YYYYMMDDTHHMMSSZ.sqlite'"
#      (ou fallback sem sqlite3 CLI: cp -p /home/data/backups/database_YYYYMMDDTHHMMSSZ.sqlite /home/data/database.sqlite)
#   4. Reinicie o App Service:
#      az webapp restart --resource-group rg-assinart-prod --name assinart-api

DB_PATH="${DB_DATABASE:-/home/data/database.sqlite}"
BACKUP_DIR="${BACKUP_DIR:-/home/data/backups}"
RETENTION_COUNT="${RETENTION_COUNT:-14}"

if [ ! -f "${DB_PATH}" ]; then
  echo "Erro: arquivo SQLite não encontrado em ${DB_PATH}" >&2
  exit 1
fi

mkdir -p "${BACKUP_DIR}"
TIMESTAMP="$(date -u +%Y%m%dT%H%M%SZ)"
BACKUP_FILE="${BACKUP_DIR}/database_${TIMESTAMP}.sqlite"

if command -v sqlite3 >/dev/null 2>&1; then
  sqlite3 "${DB_PATH}" ".backup '${BACKUP_FILE}'"
else
  # Fallback atômico simples quando sqlite3 CLI não está instalado
  cp -p "${DB_PATH}" "${BACKUP_FILE}"
fi

echo "Backup criado em: ${BACKUP_FILE}"

# Mantém apenas os N backups mais recentes para evitar esgotamento de disco
if [ "${RETENTION_COUNT}" -gt 0 ] 2>/dev/null; then
  ls -1t "${BACKUP_DIR}"/database_*.sqlite 2>/dev/null | tail -n +"$((RETENTION_COUNT + 1))" | xargs -r rm -f
fi

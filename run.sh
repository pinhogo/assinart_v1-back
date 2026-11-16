#!/usr/bin/env bash

# ==============================================================================
# Assin.art v1 — Local Backend Runner (Linux)
# ==============================================================================

set -e

# Configurações padrão
DEFAULT_PORT=8000
PORT="${1:-${PORT:-$DEFAULT_PORT}}"
HOST="127.0.0.1"

# Cores Neo-Brutalistas para o terminal
BOLD="\033[1m"
GREEN="\033[1;32m"
YELLOW="\033[1;33m"
CYAN="\033[1;36m"
RED="\033[1;31m"
RESET="\033[0m"

echo -e "${CYAN}${BOLD}"
cat << 'EOF'
  ___               _                   _   
 / _ \   ___  ___ (_) _ __    __ _  _ _| |_ 
/ /_\ \ (_-< (_-< | || '_ \  / _` || '_|  _| 
/_/   \_\/__/ /__/ |_||_.__/ \__,_||_|   \__|
  Backend API (Laravel 11) — Local Dev Server
EOF
echo -e "${RESET}"

# 1. Verificar binário do PHP
if ! command -v php >/dev/null 2>&1; then
    echo -e "${RED}[ERRO] PHP não encontrado no PATH do sistema.${RESET}"
    echo "Instale o PHP (>= 8.2) antes de continuar."
    exit 1
fi

# 2. Verificar extensões essenciais do PHP
MISSING_EXTS=""
if ! php -r 'exit(extension_loaded("pdo_sqlite") ? 0 : 1);' >/dev/null 2>&1; then
    MISSING_EXTS="${MISSING_EXTS} pdo_sqlite"
fi
if ! php -r 'exit(extension_loaded("iconv") ? 0 : 1);' >/dev/null 2>&1; then
    MISSING_EXTS="${MISSING_EXTS} iconv"
fi

if [ -n "$MISSING_EXTS" ]; then
    echo -e "${RED}[ERRO] Faltam extensões do PHP obrigatórias:${MISSING_EXTS}${RESET}"
    echo -e "${YELLOW}Para resolver no Arch/EndeavourOS, execute:${RESET}"
    echo "  sudo pacman -S --needed php-sqlite"
    echo "  sudo sed -i -E 's/^;(extension=(iconv|pdo_sqlite|sqlite3|bcmath|exif|intl))/\1/' /etc/php/php.ini"
    exit 1
fi

# 3. Verificar arquivo .env
if [ ! -f ".env" ]; then
    echo -e "${YELLOW}[CONFIG] Arquivo .env não encontrado. Copiando de .env.example...${RESET}"
    cp .env.example .env
fi

# 4. Verificar dependências (vendor/)
if [ ! -d "vendor" ] || [ ! -f "vendor/autoload.php" ]; then
    echo -e "${YELLOW}[COMPOSER] Instalando dependências do projeto...${RESET}"
    if command -v composer >/dev/null 2>&1; then
        composer install
    else
        echo -e "${RED}[ERRO] Composer não encontrado. Instale o composer para baixar dependências.${RESET}"
        exit 1
    fi
fi

# 5. Verificar APP_KEY
if ! grep -q "^APP_KEY=base64:" .env 2>/dev/null; then
    echo -e "${YELLOW}[SECURITY] Gerando nova chave da aplicação (APP_KEY)...${RESET}"
    php artisan key:generate --ansi
fi

# 6. Verificar banco de dados SQLite
if [ ! -f "database/database.sqlite" ]; then
    echo -e "${YELLOW}[DATABASE] Criando arquivo database/database.sqlite...${RESET}"
    mkdir -p database
    touch database/database.sqlite
    php artisan migrate --force --ansi
fi

# 7. Verificar storage link
if [ ! -L "public/storage" ]; then
    echo -e "${YELLOW}[STORAGE] Criando link simbólico public/storage...${RESET}"
    php artisan storage:link --quiet || true
fi

# 8. Garantir permissões de escrita em storage e cache
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# 9. Verificar se a porta já está em uso
if command -v ss >/dev/null 2>&1; then
    if ss -tuln | grep -q ":${PORT} "; then
        echo -e "${YELLOW}[AVISO] A porta ${PORT} já está em uso! Tentando inicializar...${RESET}"
    fi
fi

echo -e "${GREEN}${BOLD}✔ Ambiente verificado e pronto!${RESET}"
echo -e "--------------------------------------------------------"
echo -e "🚀 ${BOLD}API Base:${RESET}        ${CYAN}http://${HOST}:${PORT}/api/v1${RESET}"
echo -e "📄 ${BOLD}Página Pública:${RESET}  ${CYAN}http://${HOST}:${PORT}/{username}${RESET}"
echo -e "❤️  ${BOLD}Healthcheck:${RESET}     ${CYAN}http://${HOST}:${PORT}/up${RESET}"
echo -e "💾 ${BOLD}Banco de Dados:${RESET}  ${YELLOW}database/database.sqlite (SQLite)${RESET}"
echo -e "--------------------------------------------------------"
echo -e "Pressione ${BOLD}Ctrl+C${RESET} para encerrar o servidor."
echo ""

# Executar o servidor Laravel
exec php artisan serve --host="${HOST}" --port="${PORT}"

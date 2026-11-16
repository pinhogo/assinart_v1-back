#!/usr/bin/env bash
set -euo pipefail

# Provisionamento Azure App Service (Linux / PHP 8.3) + PostgreSQL Flexible Server para Assin.art v1
# Parâmetros configuráveis via variáveis de ambiente antes da execução.
RG="${RG:-rg-assinart-prod}"
LOCATION="${LOCATION:-brazilsouth}"
FALLBACK_LOCATION="${FALLBACK_LOCATION:-eastus2}"
PLAN="${PLAN:-plan-assinart-prod}"
APP_NAME="${APP_NAME:-assinart-api}"
SKU="${SKU:-B1}"
DB_CONNECTION="${DB_CONNECTION:-pgsql}"
PG_SERVER_NAME="${PG_SERVER_NAME:-${APP_NAME}-pg}"
PG_DATABASE="${PG_DATABASE:-assinart}"
PG_ADMIN_USER="${PG_ADMIN_USER:-assinart_admin}"
PG_ADMIN_PASSWORD="${PG_ADMIN_PASSWORD:-}"
PG_SKU="${PG_SKU:-Standard_B1ms}"
PG_TIER="${PG_TIER:-Burstable}"
PG_STORAGE_SIZE="${PG_STORAGE_SIZE:-32}"
PG_VERSION="${PG_VERSION:-16}"
MAIL_MAILER="${MAIL_MAILER:-log}"
MAIL_HOST="${MAIL_HOST:-smtp.resend.com}"
MAIL_PORT="${MAIL_PORT:-587}"
MAIL_USERNAME="${MAIL_USERNAME:-resend}"
MAIL_PASSWORD="${MAIL_PASSWORD:-}"
MAIL_ENCRYPTION="${MAIL_ENCRYPTION:-tls}"
BUDGET_NAME="${BUDGET_NAME:-budget-assinart-prod}"
BUDGET_AMOUNT="${BUDGET_AMOUNT:-30}"
BUDGET_EMAIL="${BUDGET_EMAIL:-}"
APP_KEY_VALUE="${APP_KEY:-base64:$(openssl rand -base64 32)}"

echo "1. Validando sessão e assinatura ativa no Azure CLI..."
az account show --output table

echo "2. Verificando disponibilidade e política de região Linux SKU ${SKU} (${LOCATION} / fallback ${FALLBACK_LOCATION})..."
AVAILABLE_LOCATIONS="$(az appservice list-locations --sku "${SKU}" --linux-workers-enabled --query "[].name" -o tsv | tr '[:upper:]' '[:lower:]' | tr -d ' ')"
POLICY_LOCATIONS="$(az policy assignment list --query "[?name=='sys.regionrestriction'].parameters.listOfAllowedLocations.value[]" -o tsv 2>/dev/null | tr '[:upper:]' '[:lower:]' | tr -d ' ' || true)"

location_allowed() {
  local candidate="$1"
  if ! echo "${AVAILABLE_LOCATIONS}" | grep -qx "${candidate}"; then
    return 1
  fi
  if [ -n "${POLICY_LOCATIONS}" ] && ! echo "${POLICY_LOCATIONS}" | grep -qx "${candidate}"; then
    return 1
  fi
  return 0
}

if ! location_allowed "${LOCATION}"; then
  echo "Aviso: região ${LOCATION} indisponível ou bloqueada por política. Buscando fallback compatível..."
  SELECTED=""
  for candidate in "${FALLBACK_LOCATION}" chilecentral brazilsouth eastus2 northcentralus canadacentral francecentral spaincentral; do
    if location_allowed "${candidate}"; then
      SELECTED="${candidate}"
      break
    fi
  done
  if [ -z "${SELECTED}" ]; then
    echo "Erro: nenhuma região compatível encontrada para Linux ${SKU}." >&2
    exit 1
  fi
  LOCATION="${SELECTED}"
  echo "Região selecionada automaticamente: ${LOCATION}"
fi

echo "3. Verificando quota de App Service / vCPU e disponibilidade do nome ${APP_NAME}..."
az vm list-usage --location "${LOCATION}" --output table || true
if az rest --method post \
  --uri "https://management.azure.com/subscriptions/$(az account show --query id -o tsv)/providers/Microsoft.Web/checknameavailability?api-version=2022-09-01" \
  --body "{\"name\":\"${APP_NAME}\",\"type\":\"Microsoft.Web/sites\"}" \
  --query "nameAvailable" -o tsv | grep -qi "false"; then
  echo "Aviso: nome ${APP_NAME} já existe ou não está disponível globalmente. Confirme se pertence a este Resource Group."
fi

echo "4. Criando Resource Group (${RG}) e App Service Plan (${PLAN}, SKU ${SKU} Linux, instância única)..."
az group create --name "${RG}" --location "${LOCATION}" --output table

az appservice plan create \
  --name "${PLAN}" \
  --resource-group "${RG}" \
  --location "${LOCATION}" \
  --sku "${SKU}" \
  --is-linux \
  --number-of-workers 1 \
  --output table

echo "5. Criando Web App (${APP_NAME}) em PHP:8.3..."
if az webapp show --resource-group "${RG}" --name "${APP_NAME}" >/dev/null 2>&1; then
  echo "Web App ${APP_NAME} já existe em ${RG}."
else
  az webapp create \
    --resource-group "${RG}" \
    --plan "${PLAN}" \
    --name "${APP_NAME}" \
    --runtime "PHP:8.3" \
    --output none
fi

echo "6. Aplicando hardening HTTPS, Always On, TLS 1.2, FTPS desativado e instância única..."
az webapp update \
  --resource-group "${RG}" \
  --name "${APP_NAME}" \
  --https-only true \
  --output table

az webapp config set \
  --resource-group "${RG}" \
  --name "${APP_NAME}" \
  --always-on true \
  --ftps-state Disabled \
  --min-tls-version 1.2 \
  --http20-enabled true \
  --number-of-workers 1 \
  --startup-file "/home/site/wwwroot/azure/startup.sh" \
  --output table

# Libera autenticação básica no endpoint Kudu/SCM para deploy via Publish Profile no GitHub Actions
az resource update \
  --resource-group "${RG}" \
  --name scm \
  --namespace Microsoft.Web \
  --resource-type basicPublishingCredentialsPolicies \
  --parent "sites/${APP_NAME}" \
  --set properties.allow=true \
  --output none || true

if [ "${DB_CONNECTION}" = "pgsql" ]; then
  echo "6.5. Provisionando Azure Database for PostgreSQL Flexible Server (${PG_SERVER_NAME}, ${PG_SKU})..."
  if az postgres flexible-server show --resource-group "${RG}" --name "${PG_SERVER_NAME}" >/dev/null 2>&1; then
    echo "PostgreSQL Flexible Server ${PG_SERVER_NAME} já existe em ${RG}."
    if [ -z "${PG_ADMIN_PASSWORD}" ]; then
      PG_ADMIN_PASSWORD="$(az webapp config appsettings list --resource-group "${RG}" --name "${APP_NAME}" --query "[?name=='DB_PASSWORD'].value | [0]" -o tsv 2>/dev/null || true)"
    fi
    if [ -z "${PG_ADMIN_PASSWORD}" ]; then
      echo "Erro: ${PG_SERVER_NAME} já existe. Informe PG_ADMIN_PASSWORD para atualizar as App Settings." >&2
      exit 1
    fi
  else
    if [ -z "${PG_ADMIN_PASSWORD}" ]; then
      PG_ADMIN_PASSWORD="Pg!$(openssl rand -hex 12)Az9"
    fi
    az postgres flexible-server create \
      --resource-group "${RG}" \
      --name "${PG_SERVER_NAME}" \
      --location "${LOCATION}" \
      --admin-user "${PG_ADMIN_USER}" \
      --admin-password "${PG_ADMIN_PASSWORD}" \
      --sku-name "${PG_SKU}" \
      --tier "${PG_TIER}" \
      --version "${PG_VERSION}" \
      --storage-size "${PG_STORAGE_SIZE}" \
      --public-access 0.0.0.0 \
      --yes \
      --output none
  fi

  az postgres flexible-server db create \
    --resource-group "${RG}" \
    --server-name "${PG_SERVER_NAME}" \
    --name "${PG_DATABASE}" \
    --output none || true
fi

echo "7. Configurando App Settings de produção (${DB_CONNECTION}, sem SANCTUM_STATEFUL_DOMAINS)..."
DB_SETTINGS=()
if [ "${DB_CONNECTION}" = "pgsql" ]; then
  DB_SETTINGS=(
    DB_CONNECTION="pgsql"
    DB_HOST="${PG_SERVER_NAME}.postgres.database.azure.com"
    DB_PORT="5432"
    DB_DATABASE="${PG_DATABASE}"
    DB_USERNAME="${PG_ADMIN_USER}"
    DB_PASSWORD="${PG_ADMIN_PASSWORD}"
    DB_SSLMODE="require"
  )
else
  DB_SETTINGS=(
    DB_CONNECTION="sqlite"
    DB_DATABASE="/home/data/database.sqlite"
    DB_SQLITE_BUSY_TIMEOUT="5000"
    DB_SQLITE_JOURNAL_MODE="WAL"
  )
fi

MAIL_SETTINGS=(
  MAIL_MAILER="${MAIL_MAILER}"
  MAIL_FROM_ADDRESS="contato@assin.art"
  MAIL_FROM_NAME="Assinart"
  MAIL_REPLY_TO_ADDRESS="sac@assin.art"
  MAIL_REPLY_TO_NAME="Assinart"
)
if [ "${MAIL_MAILER}" = "smtp" ] && [ -n "${MAIL_PASSWORD}" ]; then
  MAIL_SETTINGS+=(
    MAIL_HOST="${MAIL_HOST}"
    MAIL_PORT="${MAIL_PORT}"
    MAIL_USERNAME="${MAIL_USERNAME}"
    MAIL_PASSWORD="${MAIL_PASSWORD}"
    MAIL_ENCRYPTION="${MAIL_ENCRYPTION}"
  )
fi

az webapp config appsettings set \
  --resource-group "${RG}" \
  --name "${APP_NAME}" \
  --settings \
    APP_NAME="Assinart" \
    APP_ENV="production" \
    APP_DEBUG="false" \
    APP_KEY="${APP_KEY_VALUE}" \
    APP_TIMEZONE="America/Sao_Paulo" \
    APP_LOCALE="pt_BR" \
    APP_URL="https://${APP_NAME}.azurewebsites.net" \
    FRONTEND_URL="https://assin.art" \
    CORS_ALLOWED_ORIGINS="https://assin.art,https://www.assin.art" \
    "${DB_SETTINGS[@]}" \
    FILESYSTEM_DISK="public" \
    MEDIA_DISK="public" \
    SESSION_DRIVER="file" \
    CACHE_STORE="file" \
    QUEUE_CONNECTION="sync" \
    LOG_CHANNEL="stderr" \
    LOG_LEVEL="warning" \
    "${MAIL_SETTINGS[@]}" \
    WEBSITES_ENABLE_APP_SERVICE_STORAGE="true" \
    WEBSITES_CONTAINER_START_TIME_LIMIT="230" \
  --output none

echo "8. Habilitando logs de aplicação e servidor web em filesystem..."
az webapp log config \
  --resource-group "${RG}" \
  --name "${APP_NAME}" \
  --application-logging filesystem \
  --level information \
  --web-server-logging filesystem \
  --output table

echo "9. Criando alerta de orçamento (${BUDGET_AMOUNT} USD/mês) para controle de crédito Azure for Students..."
START_DATE="$(date -u +%Y-%m-01)"
END_DATE="$(date -u -d '+1 year' +%Y-%m-01 2>/dev/null || date -u -v+1y +%Y-%m-01)"
if [ -n "${BUDGET_EMAIL}" ]; then
  az consumption budget create \
    --budget-name "${BUDGET_NAME}" \
    --resource-group "${RG}" \
    --amount "${BUDGET_AMOUNT}" \
    --category cost \
    --time-grain monthly \
    --start-date "${START_DATE}" \
    --end-date "${END_DATE}" \
    --threshold 80 \
    --contact-emails "${BUDGET_EMAIL}" || echo "Aviso: configure o alerta de orçamento manualmente no portal caso a assinatura Student restrinja az consumption budget."
else
  az consumption budget create \
    --budget-name "${BUDGET_NAME}" \
    --resource-group "${RG}" \
    --amount "${BUDGET_AMOUNT}" \
    --category cost \
    --time-grain monthly \
    --start-date "${START_DATE}" \
    --end-date "${END_DATE}" || echo "Aviso: defina BUDGET_EMAIL para notificações ou configure o alerta no portal Azure Cost Management."
fi

echo "Provisionamento concluído para https://${APP_NAME}.azurewebsites.net"

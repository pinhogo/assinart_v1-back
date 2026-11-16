#!/usr/bin/env bash
set -euo pipefail

# Provisionamento Azure App Service (Linux / PHP 8.3) para Assin.art v1
# Parâmetros configuráveis via variáveis de ambiente antes da execução.
RG="${RG:-rg-assinart-prod}"
LOCATION="${LOCATION:-brazilsouth}"
FALLBACK_LOCATION="${FALLBACK_LOCATION:-eastus2}"
PLAN="${PLAN:-plan-assinart-prod}"
APP_NAME="${APP_NAME:-assinart-api}"
SKU="${SKU:-B1}"
BUDGET_NAME="${BUDGET_NAME:-budget-assinart-prod}"
BUDGET_AMOUNT="${BUDGET_AMOUNT:-15}"
BUDGET_EMAIL="${BUDGET_EMAIL:-}"
APP_KEY_VALUE="${APP_KEY:-base64:$(openssl rand -base64 32)}"

echo "1. Validando sessão e assinatura ativa no Azure CLI..."
az account show --output table

echo "2. Verificando disponibilidade de região Linux SKU ${SKU} (${LOCATION} / fallback ${FALLBACK_LOCATION})..."
AVAILABLE_LOCATIONS="$(az appservice list-locations --sku "${SKU}" --linux-workers-enabled --query "[].name" -o tsv)"
if ! echo "${AVAILABLE_LOCATIONS}" | grep -qx "${LOCATION}"; then
  echo "Aviso: região ${LOCATION} indisponível para ${SKU} Linux. Tentando fallback ${FALLBACK_LOCATION}..."
  if echo "${AVAILABLE_LOCATIONS}" | grep -qx "${FALLBACK_LOCATION}"; then
    LOCATION="${FALLBACK_LOCATION}"
  else
    echo "Erro: nem ${LOCATION} nem ${FALLBACK_LOCATION} disponíveis para Linux ${SKU}." >&2
    exit 1
  fi
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
az webapp create \
  --resource-group "${RG}" \
  --plan "${PLAN}" \
  --name "${APP_NAME}" \
  --runtime "PHP:8.3" \
  --output table

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

echo "7. Configurando App Settings de produção (sem SANCTUM_STATEFUL_DOMAINS)..."
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
    DB_CONNECTION="sqlite" \
    DB_DATABASE="/home/data/database.sqlite" \
    DB_SQLITE_BUSY_TIMEOUT="5000" \
    DB_SQLITE_JOURNAL_MODE="WAL" \
    FILESYSTEM_DISK="public" \
    MEDIA_DISK="public" \
    SESSION_DRIVER="file" \
    CACHE_STORE="file" \
    QUEUE_CONNECTION="sync" \
    LOG_CHANNEL="stderr" \
    LOG_LEVEL="warning" \
    MAIL_MAILER="log" \
    WEBSITES_ENABLE_APP_SERVICE_STORAGE="true" \
    WEBSITES_CONTAINER_START_TIME_LIMIT="230" \
  --output table

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

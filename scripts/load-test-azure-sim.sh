#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"
COMPOSE_FILE="${REPO_ROOT}/docker/azure-sim/compose.yml"

DB_MODE="sqlite"
BASE_URL=""
VUS=20
DURATION=30
RUNNER="auto"
NO_UP=0
CONTAINER_NAME="assinart-azure-sim-app"

usage() {
  cat <<'EOF'
Uso: scripts/load-test-azure-sim.sh [opcoes]

Opcoes:
  --db sqlite|pgsql       Banco alvo da simulacao (padrao: sqlite)
  --url URL               URL base da API (padrao: http://127.0.0.1:8080)
  --vus N                 Nivel de concorrencia / virtual users (padrao: 20)
  --duration SEGUNDOS     Duracao do teste em segundos (padrao: 30)
  --runner auto|k6|php|curl
                          Executor de carga (padrao: auto)
  --container NOME        Nome do container app (padrao: assinart-azure-sim-app)
  --no-up                 Nao sobe/recria containers via compose (usa ambiente ja ativo)
  -h, --help              Exibe esta ajuda
EOF
}

while [ "$#" -gt 0 ]; do
  case "$1" in
    --db)
      DB_MODE="${2:-}"
      shift 2
      ;;
    --url)
      BASE_URL="${2:-}"
      shift 2
      ;;
    --vus)
      VUS="${2:-}"
      shift 2
      ;;
    --duration)
      DURATION="${2:-}"
      shift 2
      ;;
    --runner)
      RUNNER="${2:-}"
      shift 2
      ;;
    --container)
      CONTAINER_NAME="${2:-}"
      shift 2
      ;;
    --no-up)
      NO_UP=1
      shift
      ;;
    -h|--help)
      usage
      exit 0
      ;;
    *)
      echo "Erro: opcao desconhecida '$1'." >&2
      usage >&2
      exit 1
      ;;
  esac
done

if [ "${DB_MODE}" != "sqlite" ] && [ "${DB_MODE}" != "pgsql" ]; then
  echo "Erro: --db deve ser 'sqlite' ou 'pgsql' (recebido: '${DB_MODE}')." >&2
  exit 1
fi

if ! [[ "${VUS}" =~ ^[0-9]+$ ]] || [ "${VUS}" -lt 1 ]; then
  echo "Erro: --vus deve ser um inteiro >= 1." >&2
  exit 1
fi

if ! [[ "${DURATION}" =~ ^[0-9]+$ ]] || [ "${DURATION}" -lt 1 ]; then
  echo "Erro: --duration deve ser um inteiro >= 1 (em segundos)." >&2
  exit 1
fi

if [ -z "${BASE_URL}" ]; then
  BASE_URL="http://127.0.0.1:${AZURE_SIM_PORT:-8080}"
fi
BASE_URL="${BASE_URL%/}"

detect_compose() {
  if docker compose version >/dev/null 2>&1; then
    COMPOSE_MODE="compose-v2"
    COMPOSE_CMD=(docker compose)
  elif command -v docker-compose >/dev/null 2>&1; then
    COMPOSE_MODE="compose-v1"
    COMPOSE_CMD=(docker-compose)
  else
    COMPOSE_MODE="docker-cli"
  fi
}

check_docker_daemon() {
  if ! command -v docker >/dev/null 2>&1; then
    echo "Erro: binario 'docker' nao encontrado no PATH." >&2
    exit 1
  fi
  if ! docker info >/dev/null 2>&1; then
    echo "Erro: daemon Docker nao esta acessivel (verifique 'sudo systemctl start docker' ou Docker Desktop)." >&2
    exit 1
  fi
}

start_via_docker_cli() {
  local port="${AZURE_SIM_PORT:-8080}"
  local net_name="assinart-azure-sim-net"
  docker network inspect "${net_name}" >/dev/null 2>&1 || docker network create "${net_name}" >/dev/null
  docker volume create azure_sim_data >/dev/null

  echo "[azure-sim] Compose indisponivel; construindo imagem via 'docker build'..."
  docker build -f "${REPO_ROOT}/docker/azure-sim/Dockerfile" -t assinart-azure-sim:latest "${REPO_ROOT}"

  if [ "${DB_MODE}" = "pgsql" ]; then
    docker volume create azure_sim_pgdata >/dev/null
    docker rm -f assinart-azure-sim-postgres >/dev/null 2>&1 || true
    docker run -d --name assinart-azure-sim-postgres \
      --network "${net_name}" \
      --network-alias postgres \
      --restart unless-stopped \
      -e POSTGRES_DB="${PG_DATABASE:-assinart}" \
      -e POSTGRES_USER="${PG_USERNAME:-assinart}" \
      -e POSTGRES_PASSWORD="${PG_PASSWORD:-assinart_sim_secret}" \
      -v azure_sim_pgdata:/var/lib/postgresql/data \
      postgres:16-alpine >/dev/null

    echo "[azure-sim] Aguardando PostgreSQL iniciar..."
    for _ in $(seq 1 30); do
      if docker exec assinart-azure-sim-postgres pg_isready -U "${PG_USERNAME:-assinart}" -d "${PG_DATABASE:-assinart}" >/dev/null 2>&1; then
        break
      fi
      sleep 2
    done
  fi

  docker rm -f "${CONTAINER_NAME}" >/dev/null 2>&1 || true
  local db_args=()
  if [ "${DB_MODE}" = "pgsql" ]; then
    db_args=(
      -e DB_CONNECTION=pgsql
      -e DB_HOST=postgres
      -e DB_PORT=5432
      -e "DB_DATABASE=${PG_DATABASE:-assinart}"
      -e "DB_USERNAME=${PG_USERNAME:-assinart}"
      -e "DB_PASSWORD=${PG_PASSWORD:-assinart_sim_secret}"
      -e DB_SSLMODE=disable
    )
  else
    db_args=(
      -e DB_CONNECTION=sqlite
      -e DB_DATABASE=/home/data/database.sqlite
      -e DB_SQLITE_JOURNAL_MODE=WAL
      -e DB_SQLITE_BUSY_TIMEOUT=5000
    )
  fi

  docker run -d --name "${CONTAINER_NAME}" \
    --network "${net_name}" \
    --cpus=1.0 \
    --memory=1792m \
    --restart unless-stopped \
    -p "${port}:8080" \
    -v azure_sim_data:/home/data \
    -e APP_NAME=Assinart \
    -e APP_ENV=production \
    -e APP_DEBUG=false \
    -e "APP_KEY=${APP_KEY:-base64:QXNzaW5hcnRBenVyZVNpbUtleTIwMjZCMUxvYWRUc3Q=}" \
    -e "APP_URL=${APP_URL:-http://127.0.0.1:${port}}" \
    -e SESSION_DRIVER=file \
    -e CACHE_STORE=file \
    -e QUEUE_CONNECTION=sync \
    -e FILESYSTEM_DISK=public \
    -e MEDIA_DISK=public \
    -e LOG_CHANNEL=stack \
    -e LOG_STACK=single,stderr \
    -e LOG_LEVEL=warning \
    -e MAIL_MAILER=log \
    "${db_args[@]}" \
    assinart-azure-sim:latest >/dev/null
}

ensure_environment() {
  check_docker_daemon
  detect_compose

  if [ "${NO_UP}" -eq 0 ]; then
    echo "[azure-sim] Subindo ambiente Azure B1 (1.0 vCPU / 1792 MB) com --db ${DB_MODE}..."
    if [ "${COMPOSE_MODE}" = "docker-cli" ]; then
      start_via_docker_cli
    elif [ "${DB_MODE}" = "pgsql" ]; then
      export DB_CONNECTION="pgsql"
      export DB_HOST="postgres"
      export DB_PORT="5432"
      export DB_DATABASE="${PG_DATABASE:-assinart}"
      export DB_USERNAME="${PG_USERNAME:-assinart}"
      export DB_PASSWORD="${PG_PASSWORD:-assinart_sim_secret}"
      export DB_SSLMODE="disable"

      "${COMPOSE_CMD[@]}" -f "${COMPOSE_FILE}" --profile postgres up -d postgres
      echo "[azure-sim] Aguardando healthcheck do PostgreSQL..."
      for _ in $(seq 1 30); do
        if docker exec assinart-azure-sim-postgres pg_isready -U "${DB_USERNAME}" -d "${DB_DATABASE}" >/dev/null 2>&1; then
          break
        fi
        sleep 2
      done
      "${COMPOSE_CMD[@]}" -f "${COMPOSE_FILE}" --profile postgres up -d --build --force-recreate app
    else
      export DB_CONNECTION="sqlite"
      export DB_DATABASE="/home/data/database.sqlite"
      export DB_SQLITE_JOURNAL_MODE="WAL"
      export DB_SQLITE_BUSY_TIMEOUT="5000"

      "${COMPOSE_CMD[@]}" -f "${COMPOSE_FILE}" up -d --build --force-recreate app
    fi
  fi

  echo "[azure-sim] Aguardando ${BASE_URL}/up responder 200..."
  local ready=0
  for _ in $(seq 1 40); do
    if curl -fsS --max-time 3 "${BASE_URL}/up" >/dev/null 2>&1; then
      ready=1
      break
    fi
    sleep 2
  done
  if [ "${ready}" -ne 1 ]; then
    echo "Erro: endpoint ${BASE_URL}/up nao ficou pronto a tempo." >&2
    docker logs --tail 50 "${CONTAINER_NAME}" 2>&1 || true
    exit 1
  fi
}

seed_load_test_target() {
  echo "[azure-sim] Preparando artista publicado e botao ativo no banco (${DB_MODE})..."
  local seed_php
  seed_php='
require "/home/site/wwwroot/vendor/autoload.php";
$app = require "/home/site/wwwroot/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$driver = Illuminate\Support\Facades\DB::connection()->getDriverName();
$wal = "";
if ($driver === "sqlite") {
    $row = Illuminate\Support\Facades\DB::selectOne("PRAGMA journal_mode");
    $wal = $row->journal_mode ?? "";
}

$artist = App\Models\Artist::updateOrCreate(
    ["username" => "loadtest_artist"],
    [
        "name" => "Load Test Artist",
        "email" => "loadtest_artist@assin.art",
        "password" => Illuminate\Support\Facades\Hash::make("LoadTest123!"),
        "bio" => "Artista sintetico para benchmark Azure B1",
        "is_published" => true,
        "email_verified_at" => now(),
    ]
);

$button = App\Models\Button::updateOrCreate(
    ["artist_id" => $artist->id, "title" => "Load Test Portfolio"],
    [
        "url" => "https://assin.art",
        "platform" => "custom",
        "display_mode" => "button",
        "order_index" => 0,
        "is_active" => true,
    ]
);

echo "SEED_OK|" . $artist->username . "|" . $button->id . "|" . $driver . "|" . $wal . "\n";
'
  local seed_out
  seed_out="$(docker exec -i "${CONTAINER_NAME}" php -r "${seed_php}")"
  local seed_line
  seed_line="$(printf '%s\n' "${seed_out}" | grep '^SEED_OK|' | tail -n 1 || true)"
  if [ -z "${seed_line}" ]; then
    echo "Erro: falha ao preparar artista/botao de carga. Saida:" >&2
    printf '%s\n' "${seed_out}" >&2
    exit 1
  fi

  IFS='|' read -r _ TARGET_USERNAME TARGET_BUTTON_ID ACTIVE_DRIVER ACTIVE_WAL <<< "${seed_line}"
  echo "[azure-sim] Alvo pronto: username=${TARGET_USERNAME}, button_id=${TARGET_BUTTON_ID}, driver=${ACTIVE_DRIVER}${ACTIVE_WAL:+, journal_mode=${ACTIVE_WAL}}"
}

resolve_runner() {
  if [ "${RUNNER}" = "auto" ]; then
    if command -v k6 >/dev/null 2>&1; then
      EFFECTIVE_RUNNER="k6-local"
    elif docker image inspect grafana/k6:latest >/dev/null 2>&1; then
      EFFECTIVE_RUNNER="k6-docker"
    elif command -v php >/dev/null 2>&1 && php -m 2>/dev/null | grep -qi '^curl$'; then
      EFFECTIVE_RUNNER="php"
    elif command -v curl >/dev/null 2>&1; then
      EFFECTIVE_RUNNER="curl"
    else
      echo "Erro: nenhum executor disponivel (k6, docker grafana/k6, php+curl ou curl)." >&2
      exit 1
    fi
  elif [ "${RUNNER}" = "k6" ]; then
    if command -v k6 >/dev/null 2>&1; then
      EFFECTIVE_RUNNER="k6-local"
    else
      EFFECTIVE_RUNNER="k6-docker"
    fi
  elif [ "${RUNNER}" = "php" ] || [ "${RUNNER}" = "curl" ]; then
    EFFECTIVE_RUNNER="${RUNNER}"
  else
    echo "Erro: --runner invalido '${RUNNER}'." >&2
    exit 1
  fi
  echo "[azure-sim] Executor selecionado: ${EFFECTIVE_RUNNER} (vus=${VUS}, duration=${DURATION}s)"
}

build_k6_script() {
  cat <<'K6EOF'
import http from 'k6/http';
import { check } from 'k6';
import { Counter, Trend } from 'k6/metrics';

export const options = {
  vus: Number(__ENV.VUS || 20),
  duration: `${__ENV.DURATION || 30}s`,
  summaryTrendStats: ['avg', 'min', 'med', 'p(95)', 'max'],
};

const err5xxTimeout = new Counter('err_5xx_timeout');
const status429 = new Counter('status_429');
const status2xx3xx = new Counter('status_2xx_3xx');
const latTrack = new Trend('lat_track', true);
const latRedirect = new Trend('lat_redirect', true);
const latProfile = new Trend('lat_profile', true);
const latUp = new Trend('lat_up', true);

function visitorIp(vu, iter) {
  const a = ((vu * 37 + iter) % 250) + 1;
  const b = ((vu + iter * 13) % 250) + 1;
  return `203.0.113.${a}, 198.51.100.${b}`;
}

function recordOutcome(res, trend, expectedStatus) {
  trend.add(res.timings.duration);
  if (res.status === 0 || res.status >= 500) {
    err5xxTimeout.add(1);
  } else if (res.status === 429) {
    status429.add(1);
  } else if (res.status === expectedStatus || (res.status >= 200 && res.status < 400)) {
    status2xx3xx.add(1);
  }
}

export default function () {
  const baseUrl = __ENV.BASE_URL;
  const username = __ENV.TARGET_USERNAME;
  const buttonId = __ENV.TARGET_BUTTON_ID;
  const xff = visitorIp(__VU, __ITER);

  const trackRes = http.post(
    `${baseUrl}/api/v1/track`,
    JSON.stringify({
      type: 'pageview',
      username: username,
      visitor_id: `k6-${__VU}-${__ITER}`,
      referrer: 'https://instagram.com',
      user_agent: 'AzureSimLoadTest/1.0',
    }),
    {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Forwarded-For': xff,
      },
      timeout: '10s',
    }
  );
  recordOutcome(trackRes, latTrack, 204);
  check(trackRes, { 'track 204/429': (r) => r.status === 204 || r.status === 429 });

  const redirRes = http.get(`${baseUrl}/r/${buttonId}`, {
    redirects: 0,
    headers: { 'X-Forwarded-For': xff },
    timeout: '10s',
  });
  recordOutcome(redirRes, latRedirect, 302);
  check(redirRes, { 'redirect 302/429': (r) => r.status === 302 || r.status === 429 });

  const profRes = http.get(`${baseUrl}/${username}`, {
    headers: {
      'Accept': 'application/json',
      'X-Forwarded-For': xff,
    },
    timeout: '10s',
  });
  recordOutcome(profRes, latProfile, 200);
  check(profRes, { 'profile 200/429': (r) => r.status === 200 || r.status === 429 });

  const upRes = http.get(`${baseUrl}/up`, {
    headers: { 'X-Forwarded-For': xff },
    timeout: '10s',
  });
  recordOutcome(upRes, latUp, 200);
  check(upRes, { 'up 200': (r) => r.status === 200 });
}

export function handleSummary(data) {
  const m = data.metrics || {};
  const total = (m.http_reqs && m.http_reqs.values && m.http_reqs.values.count) || 0;
  const err5xx = (m.err_5xx_timeout && m.err_5xx_timeout.values && m.err_5xx_timeout.values.count) || 0;
  const c429 = (m.status_429 && m.status_429.values && m.status_429.values.count) || 0;
  const ok = (m.status_2xx_3xx && m.status_2xx_3xx.values && m.status_2xx_3xx.values.count) || 0;
  const dur = (m.http_req_duration && m.http_req_duration.values) || {};
  const errPct = total > 0 ? ((err5xx / total) * 100).toFixed(2) : '0.00';

  const fmtTrend = (name, key) => {
    const vals = m[name] && m[name].values;
    return vals ? Number(vals[key] || 0).toFixed(2) : '0.00';
  };

  const lines = [
    `TOTAL_REQUESTS=${total}`,
    `SUCCESS_2XX_3XX=${ok}`,
    `THROTTLED_429=${c429}`,
    `ERROR_5XX_TIMEOUT=${err5xx}`,
    `ERROR_RATE_PCT=${errPct}`,
    `P50_MS=${Number(dur.med || 0).toFixed(2)}`,
    `P95_MS=${Number(dur['p(95)'] || 0).toFixed(2)}`,
    `ENDPOINT_TRACK_P50=${fmtTrend('lat_track', 'med')} ENDPOINT_TRACK_P95=${fmtTrend('lat_track', 'p(95)')}`,
    `ENDPOINT_REDIRECT_P50=${fmtTrend('lat_redirect', 'med')} ENDPOINT_REDIRECT_P95=${fmtTrend('lat_redirect', 'p(95)')}`,
    `ENDPOINT_PROFILE_P50=${fmtTrend('lat_profile', 'med')} ENDPOINT_PROFILE_P95=${fmtTrend('lat_profile', 'p(95)')}`,
    `ENDPOINT_UP_P50=${fmtTrend('lat_up', 'med')} ENDPOINT_UP_P95=${fmtTrend('lat_up', 'p(95)')}`,
  ];

  return {
    stdout: lines.join('\n') + '\n',
  };
}
K6EOF
}

run_php_load_test() {
  php -r '
$baseUrl = rtrim($argv[1], "/");
$username = $argv[2];
$buttonId = $argv[3];
$vus = max(1, (int)$argv[4]);
$duration = max(1, (int)$argv[5]);

$endpoints = ["track", "redirect", "profile", "up"];
$mh = curl_multi_init();
$handles = [];
$latencies = [];
$endpointLatencies = ["track" => [], "redirect" => [], "profile" => [], "up" => []];
$total = 0;
$ok = 0;
$c429 = 0;
$err5xx = 0;
$seq = 0;

$makeHandle = function (int $slot, int $seqNum) use ($baseUrl, $username, $buttonId, $endpoints) {
    $kind = $endpoints[$seqNum % 4];
    $ipLast = (($seqNum + $slot * 17) % 250) + 1;
    $xff = "203.0.113.{$ipLast}";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

    if ($kind === "track") {
        $payload = json_encode([
            "type" => "pageview",
            "username" => $username,
            "visitor_id" => "php-{$slot}-{$seqNum}",
            "referrer" => "https://instagram.com",
            "user_agent" => "AzureSimLoadTest/1.0",
        ]);
        curl_setopt($ch, CURLOPT_URL, "{$baseUrl}/api/v1/track");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Accept: application/json",
            "X-Forwarded-For: {$xff}",
        ]);
    } elseif ($kind === "redirect") {
        curl_setopt($ch, CURLOPT_URL, "{$baseUrl}/r/{$buttonId}");
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["X-Forwarded-For: {$xff}"]);
    } elseif ($kind === "profile") {
        curl_setopt($ch, CURLOPT_URL, "{$baseUrl}/{$username}");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Accept: application/json",
            "X-Forwarded-For: {$xff}",
        ]);
    } else {
        curl_setopt($ch, CURLOPT_URL, "{$baseUrl}/up");
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["X-Forwarded-For: {$xff}"]);
    }

    return [$ch, $kind];
};

$deadline = microtime(true) + $duration;
for ($i = 0; $i < $vus; $i++) {
    $seq++;
    [$ch, $kind] = $makeHandle($i, $seq);
    curl_multi_add_handle($mh, $ch);
    $handles[(int)$ch] = ["ch" => $ch, "slot" => $i, "kind" => $kind];
}

do {
    do {
        $status = curl_multi_exec($mh, $active);
    } while ($status === CURLM_CALL_MULTI_PERFORM);

    while ($info = curl_multi_info_read($mh)) {
        $ch = $info["handle"];
        $id = (int)$ch;
        $meta = $handles[$id];
        $code = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $ms = (float)curl_getinfo($ch, CURLINFO_TOTAL_TIME) * 1000.0;

        $total++;
        $latencies[] = $ms;
        $endpointLatencies[$meta["kind"]][] = $ms;

        if ($info["result"] !== CURLE_OK || $code === 0 || $code >= 500) {
            $err5xx++;
        } elseif ($code === 429) {
            $c429++;
        } elseif ($code >= 200 && $code < 400) {
            $ok++;
        }

        curl_multi_remove_handle($mh, $ch);
        curl_close($ch);
        unset($handles[$id]);

        if (microtime(true) < $deadline) {
            $seq++;
            [$nextCh, $nextKind] = $makeHandle($meta["slot"], $seq);
            curl_multi_add_handle($mh, $nextCh);
            $handles[(int)$nextCh] = ["ch" => $nextCh, "slot" => $meta["slot"], "kind" => $nextKind];
            $active = 1;
        }
    }

    if ($active > 0) {
        curl_multi_select($mh, 0.05);
    }
} while ($active > 0 || count($handles) > 0);

curl_multi_close($mh);

$pct = function (array $vals, float $p): float {
    if (empty($vals)) {
        return 0.0;
    }
    sort($vals, SORT_NUMERIC);
    $idx = (int)ceil(($p / 100.0) * count($vals)) - 1;
    $idx = max(0, min($idx, count($vals) - 1));
    return $vals[$idx];
};

$errPct = $total > 0 ? ($err5xx / $total) * 100.0 : 0.0;
echo "TOTAL_REQUESTS={$total}\n";
echo "SUCCESS_2XX_3XX={$ok}\n";
echo "THROTTLED_429={$c429}\n";
echo "ERROR_5XX_TIMEOUT={$err5xx}\n";
echo sprintf("ERROR_RATE_PCT=%.2f\n", $errPct);
echo sprintf("P50_MS=%.2f\n", $pct($latencies, 50));
echo sprintf("P95_MS=%.2f\n", $pct($latencies, 95));
echo sprintf("ENDPOINT_TRACK_P50=%.2f ENDPOINT_TRACK_P95=%.2f\n", $pct($endpointLatencies["track"], 50), $pct($endpointLatencies["track"], 95));
echo sprintf("ENDPOINT_REDIRECT_P50=%.2f ENDPOINT_REDIRECT_P95=%.2f\n", $pct($endpointLatencies["redirect"], 50), $pct($endpointLatencies["redirect"], 95));
echo sprintf("ENDPOINT_PROFILE_P50=%.2f ENDPOINT_PROFILE_P95=%.2f\n", $pct($endpointLatencies["profile"], 50), $pct($endpointLatencies["profile"], 95));
echo sprintf("ENDPOINT_UP_P50=%.2f ENDPOINT_UP_P95=%.2f\n", $pct($endpointLatencies["up"], 50), $pct($endpointLatencies["up"], 95));
' "${BASE_URL}" "${TARGET_USERNAME}" "${TARGET_BUTTON_ID}" "${VUS}" "${DURATION}"
}

run_curl_xargs_load_test() {
  local tmp_results
  tmp_results="$(mktemp)"
  local end_ts=$(( $(date +%s) + DURATION ))
  export BASE_URL TARGET_USERNAME TARGET_BUTTON_ID end_ts

  seq 1 "${VUS}" | xargs -P "${VUS}" -I {} bash -c '
    vu="{}"
    iter=0
    while [ "$(date +%s)" -lt "${end_ts}" ]; do
      iter=$((iter + 1))
      ip_last=$(( ((vu * 31 + iter) % 250) + 1 ))
      xff="203.0.113.${ip_last}"
      curl -sS -o /dev/null --max-time 10 -w "track %{http_code} %{time_total}\n" \
        -X POST "${BASE_URL}/api/v1/track" \
        -H "Content-Type: application/json" -H "Accept: application/json" -H "X-Forwarded-For: ${xff}" \
        -d "{\"type\":\"pageview\",\"username\":\"${TARGET_USERNAME}\",\"visitor_id\":\"curl-${vu}-${iter}\"}" || echo "track 000 10.0"
      curl -sS -o /dev/null --max-time 10 -w "redirect %{http_code} %{time_total}\n" \
        -H "X-Forwarded-For: ${xff}" "${BASE_URL}/r/${TARGET_BUTTON_ID}" || echo "redirect 000 10.0"
      curl -sS -o /dev/null --max-time 10 -w "profile %{http_code} %{time_total}\n" \
        -H "Accept: application/json" -H "X-Forwarded-For: ${xff}" "${BASE_URL}/${TARGET_USERNAME}" || echo "profile 000 10.0"
      curl -sS -o /dev/null --max-time 10 -w "up %{http_code} %{time_total}\n" \
        -H "X-Forwarded-For: ${xff}" "${BASE_URL}/up" || echo "up 000 10.0"
    done
  ' > "${tmp_results}"

  awk '
    {
      code = $2 + 0;
      ms = ($3 + 0) * 1000.0;
      total++;
      lat[total] = ms;
      if (code == 0 || code >= 500) err++;
      else if (code == 429) c429++;
      else if (code >= 200 && code < 400) ok++;
    }
    END {
      n = asort(lat);
      p50 = (n > 0) ? lat[int((n * 0.50) + 0.9999)] : 0;
      p95 = (n > 0) ? lat[int((n * 0.95) + 0.9999)] : 0;
      err_pct = (total > 0) ? (err / total) * 100.0 : 0;
      printf "TOTAL_REQUESTS=%d\nSUCCESS_2XX_3XX=%d\nTHROTTLED_429=%d\nERROR_5XX_TIMEOUT=%d\nERROR_RATE_PCT=%.2f\nP50_MS=%.2f\nP95_MS=%.2f\n",
        total, ok, c429, err, err_pct, p50, p95;
    }
  ' "${tmp_results}"
  rm -f "${tmp_results}"
}

inspect_logs_for_locks() {
  local docker_locks=0
  local laravel_locks=0

  if docker ps -a --format '{{.Names}}' | grep -qx "${CONTAINER_NAME}"; then
    docker_locks="$(docker logs "${CONTAINER_NAME}" 2>&1 | grep -ic 'database is locked' || true)"
    laravel_locks="$(docker exec "${CONTAINER_NAME}" sh -c 'if [ -f /home/site/wwwroot/storage/logs/laravel.log ]; then grep -ic "database is locked" /home/site/wwwroot/storage/logs/laravel.log || true; else echo 0; fi' 2>/dev/null || echo 0)"
  fi

  TOTAL_LOCK_ERRORS=$((docker_locks + laravel_locks))
  echo "LOCK_LOG_CONTAINER=${docker_locks}"
  echo "LOCK_LOG_LARAVEL=${laravel_locks}"
  echo "LOCK_LOG_TOTAL=${TOTAL_LOCK_ERRORS}"
}

ensure_environment
seed_load_test_target
resolve_runner

echo "[azure-sim] Disparando carga concorrente contra POST /api/v1/track, GET /r/{id}, GET /{username} e GET /up..."

SUMMARY_OUTPUT=""
case "${EFFECTIVE_RUNNER}" in
  k6-local)
    SUMMARY_OUTPUT="$(
      BASE_URL="${BASE_URL}" \
      TARGET_USERNAME="${TARGET_USERNAME}" \
      TARGET_BUTTON_ID="${TARGET_BUTTON_ID}" \
      VUS="${VUS}" \
      DURATION="${DURATION}" \
      k6 run --quiet - < <(build_k6_script)
    )"
    ;;
  k6-docker)
    SUMMARY_OUTPUT="$(
      build_k6_script | docker run --rm -i --network host \
        -e BASE_URL="${BASE_URL}" \
        -e TARGET_USERNAME="${TARGET_USERNAME}" \
        -e TARGET_BUTTON_ID="${TARGET_BUTTON_ID}" \
        -e VUS="${VUS}" \
        -e DURATION="${DURATION}" \
        grafana/k6:latest run --quiet -
    )"
    ;;
  php)
    SUMMARY_OUTPUT="$(run_php_load_test)"
    ;;
  curl)
    SUMMARY_OUTPUT="$(run_curl_xargs_load_test)"
    ;;
esac

echo ""
echo "=== Relatorio Carga Azure B1 (${DB_MODE}) ==="
printf '%s\n' "${SUMMARY_OUTPUT}"
inspect_logs_for_locks

ERR_COUNT="$(printf '%s\n' "${SUMMARY_OUTPUT}" | awk -F= '/^ERROR_5XX_TIMEOUT=/{print $2; exit}')"
P95_VAL="$(printf '%s\n' "${SUMMARY_OUTPUT}" | awk -F= '/^P95_MS=/{print $2; exit}')"
ERR_COUNT="${ERR_COUNT:-0}"
P95_VAL="${P95_VAL:-0}"

CUTOVER_TRIGGERED=0
if [ "${TOTAL_LOCK_ERRORS}" -gt 0 ] || [ "${ERR_COUNT}" -gt 0 ]; then
  CUTOVER_TRIGGERED=1
fi
if awk -v p95="${P95_VAL}" 'BEGIN { exit !(p95 > 500.0) }'; then
  CUTOVER_TRIGGERED=1
fi

if [ "${CUTOVER_TRIGGERED}" -eq 1 ]; then
  echo "VEREDITO=GATILHO_CORTE_POSTGRESQL (API-12): database_is_locked=${TOTAL_LOCK_ERRORS}, err_5xx_timeout=${ERR_COUNT}, p95_ms=${P95_VAL}"
else
  echo "VEREDITO=APROVADO_NOS_LIMITES_B1 (database_is_locked=0, err_5xx_timeout=0, p95<=500ms)"
fi

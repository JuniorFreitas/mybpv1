#!/usr/bin/env bash
# Expõe o MyBP local via cloudflared (named tunnel "devmybp" ou quick).
#
#   Browser → https://devmybp.… → localhost:8000 (container mybpdp)
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
# shellcheck disable=SC1091
source "${ROOT}/scripts/tunnel-env.sh"
RUN_DIR="${ROOT}/.dev/run"
LOG_DIR="${ROOT}/.dev/logs"
STORAGE_APP="${ROOT}/storage/app"
APP_TARGET="${APP_TARGET:-http://127.0.0.1:8000}"
COMPOSE_SERVICE="${COMPOSE_SERVICE:-mybpdp}"
CLOUDFLARED_BIN="${CLOUDFLARED_BIN:-cloudflared}"
FORCE_QUICK=0
NAMED_MODE=0

usage() {
  cat <<EOF
Uso: $(basename "$0") [opções]

  --quick            Força trycloudflare (ignora .dev/tunnel.env)
  -h, --help         Ajuda

Named tunnel (CNAME fixo):
  ./scripts/tunnel-named-setup.sh --app devmybp.dynamusti.com.br
  ./scripts/tunnel-up.sh
EOF
}

while [[ $# -gt 0 ]]; do
  case "$1" in
    --quick) FORCE_QUICK=1; shift ;;
    -h|--help) usage; exit 0 ;;
    *) echo "Opção desconhecida: $1" >&2; usage; exit 1 ;;
  esac
done

mkdir -p "${RUN_DIR}" "${LOG_DIR}" "${STORAGE_APP}" "${ROOT}/.dev"

if ! command -v "${CLOUDFLARED_BIN}" >/dev/null 2>&1; then
  echo "cloudflared não encontrado no PATH." >&2
  echo "Instale: brew install cloudflare/cloudflare/cloudflared" >&2
  exit 1
fi

http_ok() {
  curl -sf --max-time 3 -o /dev/null "$1"
}

app_ready() {
  http_ok "${APP_TARGET}/health" || http_ok "${APP_TARGET}/g/login"
}

if ! app_ready; then
  echo "==> App local não respondeu — subindo Docker…"
  (cd "${ROOT}" && docker compose up -d)
  for _ in $(seq 1 90); do
    if app_ready; then
      break
    fi
    sleep 1
  done
fi

if ! app_ready; then
  echo "App não respondeu em ${APP_TARGET} (/health ou /g/login)" >&2
  exit 1
fi

echo "==> encerrando tunnels anteriores…"
"${ROOT}/scripts/tunnel-down.sh" --keep-env || true
pkill -f 'cloudflared tunnel --url http://127\.0\.0\.1:8000' 2>/dev/null || true
pkill -f "cloudflared tunnel --config ${ROOT}/.dev/cloudflared.yml" 2>/dev/null || true
sleep 0.5

load_tunnel_env() {
  local file="${ROOT}/.dev/tunnel.env"
  [[ -f "${file}" ]] || return 0
  while IFS= read -r line || [[ -n "${line}" ]]; do
    line="${line%$'\r'}"
    [[ "${line}" =~ ^[[:space:]]*# ]] && continue
    [[ "${line}" =~ ^[[:space:]]*$ ]] && continue
    if [[ "${line}" =~ ^([A-Z_][A-Z0-9_]*)=(.*)$ ]]; then
      local key="${BASH_REMATCH[1]}"
      local val="${BASH_REMATCH[2]}"
      val="${val#\"}"
      val="${val%\"}"
      val="${val#\'}"
      val="${val%\'}"
      if [[ -z "${!key:-}" ]]; then
        if [[ "${key}" == *_HOST ]]; then
          val="$(printf '%s' "${val}" | tr '[:upper:]' '[:lower:]')"
        fi
        export "${key}=${val}"
      fi
    fi
  done <"${file}"
}

is_hostname() {
  [[ "$1" =~ ^([a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$ ]]
}

resolve_tunnel_id() {
  if [[ -n "${TUNNEL_ID:-}" ]]; then
    printf '%s\n' "${TUNNEL_ID}"
    return 0
  fi
  if [[ -z "${TUNNEL_NAME:-}" ]]; then
    return 1
  fi
  local list_json
  list_json="$("${CLOUDFLARED_BIN}" tunnel list --output json 2>/dev/null || true)"
  TUNNEL_NAME="${TUNNEL_NAME}" LIST_JSON="${list_json}" python3 - <<'PY'
import json, os
name = os.environ["TUNNEL_NAME"]
raw = os.environ.get("LIST_JSON") or "[]"
try:
    data = json.loads(raw)
except json.JSONDecodeError:
    raise SystemExit(1)
if isinstance(data, dict):
    data = data.get("tunnels") or data.get("result") or []
if not isinstance(data, list):
    data = []
for item in data:
    if item.get("name") == name:
        ident = item.get("id") or item.get("uuid") or ""
        if ident:
            print(ident)
            raise SystemExit(0)
raise SystemExit(1)
PY
}

write_named_config() {
  local cred="${TUNNEL_CREDENTIALS_FILE:-${HOME}/.cloudflared/${TUNNEL_ID}.json}"
  if [[ ! -f "${cred}" ]]; then
    echo "Credencial do túnel não encontrada: ${cred}" >&2
    echo "Rode: ./scripts/tunnel-named-setup.sh --app HOST" >&2
    exit 1
  fi

  local yml="${ROOT}/.dev/cloudflared.yml"
  {
    printf 'tunnel: %s\n' "${TUNNEL_ID}"
    printf 'credentials-file: %s\n' "${cred}"
    printf 'ingress:\n'
    printf '  - hostname: %s\n' "${TUNNEL_APP_HOST}"
    printf '    service: %s\n' "${APP_TARGET}"
    printf '  - service: http_status:404\n'
  } >"${yml}"
}

daemonize_cmd() {
  local pid_file="$1"
  local log_file="$2"
  shift 2
  : >"${log_file}"
  python3 - "$pid_file" "${log_file}" "$@" <<'PY'
import os, sys, subprocess
pid_file, log_file, *cmd = sys.argv[1:]
log = open(log_file, "a", buffering=1)
proc = subprocess.Popen(
    cmd,
    stdin=subprocess.DEVNULL,
    stdout=log,
    stderr=subprocess.STDOUT,
    start_new_session=True,
    close_fds=True,
)
open(pid_file, "w", encoding="utf-8").write(str(proc.pid) + "\n")
PY
}

start_named_tunnel() {
  local yml="${ROOT}/.dev/cloudflared.yml"
  local log_file="${LOG_DIR}/cloudflared-named.log"
  local pid_file="${RUN_DIR}/cloudflared-named.pid"

  echo "==> cloudflared named (${TUNNEL_NAME} ${TUNNEL_ID})" >&2
  daemonize_cmd "${pid_file}" "${log_file}" \
    "${CLOUDFLARED_BIN}" tunnel --config "${yml}" --no-autoupdate run

  local registered=0
  for _ in $(seq 1 60); do
    if grep -q 'Registered tunnel connection' "${log_file}" 2>/dev/null; then
      registered=1
      break
    fi
    if [[ -f "${pid_file}" ]] && ! kill -0 "$(cat "${pid_file}")" 2>/dev/null; then
      echo "cloudflared (named) encerrou prematuramente. Veja ${log_file}" >&2
      return 1
    fi
    sleep 1
  done

  if [[ "${registered}" -ne 1 ]]; then
    echo "Named tunnel não registrou conexão. Veja ${log_file}" >&2
    return 1
  fi
}

start_quick_tunnel() {
  local log_file="${LOG_DIR}/cloudflared-app.log"
  local pid_file="${RUN_DIR}/cloudflared-app.pid"
  local url_file="${RUN_DIR}/cloudflared-app.url"

  rm -f "${url_file}"
  echo "==> cloudflared (quick) → ${APP_TARGET}" >&2
  daemonize_cmd "${pid_file}" "${log_file}" \
    "${CLOUDFLARED_BIN}" tunnel --url "${APP_TARGET}" --no-autoupdate

  local url=""
  local registered=0
  for _ in $(seq 1 60); do
    if [[ -z "${url}" ]]; then
      url="$(grep -Eo 'https://[a-z0-9-]+\.trycloudflare\.com' "${log_file}" | tail -1 || true)"
    fi
    if grep -q 'Registered tunnel connection' "${log_file}" 2>/dev/null; then
      registered=1
    fi
    if [[ -n "${url}" && "${registered}" -eq 1 ]]; then
      printf '%s\n' "${url}" >"${url_file}"
      printf '%s\n' "${url}"
      return 0
    fi
    if [[ -f "${pid_file}" ]] && ! kill -0 "$(cat "${pid_file}")" 2>/dev/null; then
      echo "cloudflared (quick) encerrou prematuramente. Veja ${log_file}" >&2
      return 1
    fi
    sleep 1
  done

  echo "Não foi possível obter URL do tunnel. Veja ${log_file}" >&2
  return 1
}

write_tunnel_files() {
  local url="$1"
  local host="${url#https://}"
  host="${host%%/*}"

  printf '%s\n' "${host}" >"${STORAGE_APP}/tunnel.host"
  printf '%s\n' "${url}" >"${STORAGE_APP}/tunnel.url"
  printf '%s\n' "${host}" >"${ROOT}/.dev/tunnel.host"
  printf '%s\n' "${url}" >"${ROOT}/.dev/tunnel.url"
}

load_tunnel_env

if [[ "${FORCE_QUICK}" -eq 0 && -n "${TUNNEL_NAME:-}" && -n "${TUNNEL_APP_HOST:-}" ]]; then
  NAMED_MODE=1
fi

APP_URL=""

if [[ "${NAMED_MODE}" -eq 1 ]]; then
  if ! is_hostname "${TUNNEL_APP_HOST}"; then
    echo "Host inválido (TUNNEL_APP_HOST): ${TUNNEL_APP_HOST}" >&2
    exit 1
  fi
  TUNNEL_ID="$(resolve_tunnel_id)" || {
    echo "Túnel '${TUNNEL_NAME}' não encontrado. Rode ./scripts/tunnel-named-setup.sh --app HOST" >&2
    exit 1
  }
  export TUNNEL_ID
  write_named_config
  APP_URL="https://${TUNNEL_APP_HOST}"
  write_tunnel_files "${APP_URL}"
  start_named_tunnel
else
  APP_URL="$(start_quick_tunnel)"
  write_tunnel_files "${APP_URL}"
fi

apply_tunnel_app_env "${APP_URL}"
reload_app_after_env

if [[ "${NAMED_MODE}" -eq 1 ]]; then
  cat <<EOF

Tunnel nomeado (CNAME fixo — ${TUNNEL_NAME})

  App         ${APP_URL}
              → localhost:8000 (mybpdp)
              → APP_URL / cookies seguros / TRUSTED_PROXIES=*

Hosts: ${STORAGE_APP}/tunnel.host
.env backup: ${ROOT}/.dev/env.pre-tunnel
Parar: ${ROOT}/scripts/tunnel-down.sh
EOF
else
  cat <<EOF

Tunnel quick ativo (URL muda a cada execução)

  App         ${APP_URL}
              → localhost:8000 (mybpdp)

Hosts: ${STORAGE_APP}/tunnel.host
.env backup: ${ROOT}/.dev/env.pre-tunnel
Parar: ${ROOT}/scripts/tunnel-down.sh
EOF
fi

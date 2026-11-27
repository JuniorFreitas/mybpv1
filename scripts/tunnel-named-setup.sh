#!/usr/bin/env bash
# Cria (ou reutiliza) o named tunnel Cloudflare "devmybp" e aponta o CNAME.
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
CLOUDFLARED_BIN="${CLOUDFLARED_BIN:-cloudflared}"
TUNNEL_NAME="${TUNNEL_NAME:-devmybp}"
APP_HOST=""
SKIP_DNS=0

usage() {
  cat <<EOF
Uso: $(basename "$0") --app devmybp.dynamusti.com.br

  --name NAME          Nome do túnel (default: devmybp)
  --app FQDN           Hostname público do MyBP (:8000) [obrigatório]
  --skip-dns           Não cria CNAME (você adiciona no painel)
  -h, --help

Ou, sem argumentos: lê os hosts de .dev/tunnel.env

Requer: cloudflared logado na mesma conta do domínio (cloudflared tunnel login).

CNAME manual (se --skip-dns):
  Tipo   CNAME
  Nome   subdomínio
  Alvo   <TUNNEL_ID>.cfargotunnel.com
  Proxy  laranja (proxied)
EOF
}

while [[ $# -gt 0 ]]; do
  case "$1" in
    --name) TUNNEL_NAME="$2"; shift 2 ;;
    --app) APP_HOST="$2"; shift 2 ;;
    --skip-dns) SKIP_DNS=1; shift ;;
    -h|--help) usage; exit 0 ;;
    *) echo "Opção desconhecida: $1" >&2; usage; exit 1 ;;
  esac
done

load_hosts_from_env_file() {
  local file="$1"
  [[ -f "${file}" ]] || return 1
  echo "==> lendo hosts de ${file}"
  while IFS= read -r line || [[ -n "${line}" ]]; do
    line="${line%$'\r'}"
    [[ "${line}" =~ ^[[:space:]]*# ]] && continue
    [[ "${line}" =~ ^[[:space:]]*$ ]] && continue
    if [[ "${line}" =~ ^([A-Z_][A-Z0-9_]*)=(.*)$ ]]; then
      local key="${BASH_REMATCH[1]}"
      local val="${BASH_REMATCH[2]}"
      val="${val#\"}"
      val="${val%\"}"
      case "${key}" in
        TUNNEL_NAME) [[ -z "${TUNNEL_NAME:-}" || "${TUNNEL_NAME}" == "devmybp" ]] && TUNNEL_NAME="${val}" ;;
        TUNNEL_ID) [[ -z "${TUNNEL_ID:-}" ]] && TUNNEL_ID="${val}" ;;
        TUNNEL_APP_HOST) [[ -z "${APP_HOST}" ]] && APP_HOST="${val}" ;;
      esac
    fi
  done <"${file}"
}

if [[ -z "${APP_HOST}" ]]; then
  load_hosts_from_env_file "${ROOT}/.dev/tunnel.env" || true
fi

if [[ -z "${APP_HOST}" ]]; then
  echo "Informe o domínio do app (--app)." >&2
  echo "Ex.: $0 --app devmybp.dynamusti.com.br" >&2
  usage
  exit 1
fi

is_hostname() {
  [[ "$1" =~ ^([a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$ ]]
}

APP_HOST="$(printf '%s' "${APP_HOST}" | tr '[:upper:]' '[:lower:]')"
if ! is_hostname "${APP_HOST}"; then
  echo "Host inválido: ${APP_HOST}" >&2
  echo "Use um FQDN (ex.: devmybp.dynamusti.com.br)." >&2
  exit 1
fi

if ! command -v "${CLOUDFLARED_BIN}" >/dev/null 2>&1; then
  echo "cloudflared não encontrado no PATH." >&2
  exit 1
fi

cert="${HOME}/.cloudflared/cert.pem"
if [[ ! -f "${cert}" ]]; then
  echo "==> cloudflared tunnel login (abre o browser na conta Cloudflare do domínio)"
  "${CLOUDFLARED_BIN}" tunnel login
fi

list_json="$("${CLOUDFLARED_BIN}" tunnel list --output json 2>/dev/null || true)"
TUNNEL_ID="$(
  TUNNEL_NAME="${TUNNEL_NAME}" LIST_JSON="${list_json}" python3 - <<'PY'
import json, os
name = os.environ["TUNNEL_NAME"]
raw = os.environ.get("LIST_JSON") or "[]"
try:
    data = json.loads(raw)
except json.JSONDecodeError:
    data = []
if isinstance(data, dict):
    data = data.get("tunnels") or data.get("result") or []
if not isinstance(data, list):
    data = []
for item in data:
    if item.get("name") == name:
        print(item.get("id") or item.get("uuid") or "")
        break
PY
)"

if [[ -z "${TUNNEL_ID}" ]]; then
  echo "==> criando túnel ${TUNNEL_NAME}…"
  create_out="$("${CLOUDFLARED_BIN}" tunnel create "${TUNNEL_NAME}")"
  echo "${create_out}"
  TUNNEL_ID="$(printf '%s\n' "${create_out}" | grep -Eo '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}' | tail -1 || true)"
fi

if [[ -z "${TUNNEL_ID}" ]]; then
  echo "Não foi possível obter o UUID do túnel ${TUNNEL_NAME}." >&2
  exit 1
fi

echo "==> túnel ${TUNNEL_NAME}  ${TUNNEL_ID}"

if [[ "${SKIP_DNS}" -eq 0 ]]; then
  echo "==> CNAME ${APP_HOST} → ${TUNNEL_ID}.cfargotunnel.com"
  if ! "${CLOUDFLARED_BIN}" tunnel route dns "${TUNNEL_NAME}" "${APP_HOST}"; then
    echo "Falha ao criar CNAME automático para ${APP_HOST}." >&2
    echo "No painel Cloudflare → DNS → Record:" >&2
    echo "  CNAME  ${APP_HOST}  ${TUNNEL_ID}.cfargotunnel.com  (proxied)" >&2
  fi
else
  echo "==> --skip-dns: crie o CNAME apontando para ${TUNNEL_ID}.cfargotunnel.com"
fi

mkdir -p "${ROOT}/.dev"
env_file="${ROOT}/.dev/tunnel.env"
{
  printf 'TUNNEL_NAME=%s\n' "${TUNNEL_NAME}"
  printf 'TUNNEL_ID=%s\n' "${TUNNEL_ID}"
  printf 'TUNNEL_APP_HOST=%s\n' "${APP_HOST}"
} >"${env_file}"

echo
echo "Gravado ${env_file}"
echo "Com o Docker no ar (docker compose up -d), rode: ./scripts/tunnel-up.sh"
echo "Quick tunnel (URL dinâmica): ./scripts/tunnel-up.sh --quick"

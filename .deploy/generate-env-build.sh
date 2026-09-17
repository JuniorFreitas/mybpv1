#!/usr/bin/env bash
# Gera .env.prod ou .env.homol para o build webpack (MIX_*), a partir do SSM.
# Os arquivos gerados são gitignored — não commitar.
#
# Uso:
#   .deploy/generate-env-build.sh <prod|homol>
#
# Override:
#   SSM_PREFIX=/mybp/prod AWS_REGION=us-east-1 .deploy/generate-env-build.sh prod

set -euo pipefail

AWS_REGION="${AWS_REGION:-us-east-1}"
ENV_NAME="${1:-}"
ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

usage() {
  echo "Uso: $0 <prod|homol>" >&2
  exit 1
}

if [[ "$ENV_NAME" != "prod" && "$ENV_NAME" != "homol" ]]; then
  usage
fi

case "$ENV_NAME" in
  prod)
    OUT_FILE=".env.prod"
    EXAMPLE_FILE=".env.prod.example"
    APP_NAME='MyBP - Seu negócio na sua mão'
    APP_URL="https://sistema.mybp.com.br"
    MIX_AMBIENTE="prod"
    SSM_PREFIX="${SSM_PREFIX:-/mybp/prod}"
    ;;
  homol)
    OUT_FILE=".env.homol"
    EXAMPLE_FILE=".env.homol.example"
    APP_NAME='MyBP - Homologação'
    APP_URL="https://qa.mybp.com.br"
    MIX_AMBIENTE="homol"
    # Keys de frontend compartilhadas com prod (mesmo Pusher/Maps), salvo override
    SSM_PREFIX="${SSM_PREFIX:-/mybp/prod}"
    ;;
esac

for bin in aws jq; do
  if ! command -v "$bin" >/dev/null 2>&1; then
    echo "Erro: ${bin} não instalado." >&2
    exit 1
  fi
done

if [[ ! -f "$EXAMPLE_FILE" ]]; then
  echo "Erro: template ${EXAMPLE_FILE} não encontrado." >&2
  exit 1
fi

echo "Gerando ${OUT_FILE} a partir de SSM ${SSM_PREFIX}..."

fetch_param() {
  local name="$1"
  aws ssm get-parameter \
    --name "${SSM_PREFIX}/${name}" \
    --with-decryption \
    --region "${AWS_REGION}" \
    --query 'Parameter.Value' \
    --output text 2>/dev/null || true
}

PUSHER_APP_ID="$(fetch_param PUSHER_APP_ID)"
PUSHER_APP_KEY="$(fetch_param PUSHER_APP_KEY)"
PUSHER_APP_SECRET="$(fetch_param PUSHER_APP_SECRET)"
MIX_GOOGLE_MAPS_KEY="$(fetch_param MIX_GOOGLE_MAPS_KEY)"
MIX_TYNEKEY="$(fetch_param MIX_TYNEKEY)"

missing=()
for pair in \
  "PUSHER_APP_ID=${PUSHER_APP_ID}" \
  "PUSHER_APP_KEY=${PUSHER_APP_KEY}" \
  "MIX_GOOGLE_MAPS_KEY=${MIX_GOOGLE_MAPS_KEY}"; do
  k="${pair%%=*}"
  v="${pair#*=}"
  if [[ -z "$v" || "$v" == "None" ]]; then
    missing+=("$k")
  fi
done

if [[ ${#missing[@]} -gt 0 ]]; then
  echo "Erro: parâmetros SSM ausentes em ${SSM_PREFIX}:" >&2
  printf '  - %s\n' "${missing[@]}" >&2
  exit 1
fi

# Escrita sem ecoar secrets
umask 077
cat > "${OUT_FILE}" <<EOF
# Gerado por .deploy/generate-env-build.sh — NÃO COMMITTAR
APP_NAME="${APP_NAME}"
APP_URL=${APP_URL}
PUSHER_APP_ID=${PUSHER_APP_ID}
PUSHER_APP_KEY=${PUSHER_APP_KEY}
PUSHER_APP_SECRET=${PUSHER_APP_SECRET}
PUSHER_APP_CLUSTER=mt1
PUSHER_APP_TLS=true
PUSHER_APP_ENCRYPTED=true
PUSHER_APP_HOST=
MIX_PUSHER_APP_KEY=\${PUSHER_APP_KEY}
MIX_PUSHER_APP_CLUSTER=\${PUSHER_APP_CLUSTER}
MIX_PUSHER_APP_TLS=true
MIX_AMBIENTE=${MIX_AMBIENTE}
MIX_URL_SITE=\${APP_URL}
MIX_URL_ADMIN=\${APP_URL}/g
MIX_URL_PUBLICO=\${APP_URL}/publico
MIX_GOOGLE_MAPS_KEY=${MIX_GOOGLE_MAPS_KEY}
MIX_TYNEKEY=${MIX_TYNEKEY}
NPS_HABILITADO=false
NPS_EMPRESAS_EXCLUIDAS=100
NPS_EMPRESA_GERENCIAMENTO=100
NPS_MIN_ACESSOS_90_DIAS=3
WHATSAPP_MOVIMENTACAO_NOTIFICACOES_HABILITADAS=false
EOF

echo "OK: ${OUT_FILE} gerado (gitignored)."

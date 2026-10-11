#!/usr/bin/env bash
# Gera relatório de usabilidade + admissão MyBP por cliente_id.
# Uso:
#   .cursor/skills/relatorio-cliente-usabilidade/scripts/run.sh 103967
#   .cursor/skills/relatorio-cliente-usabilidade/scripts/run.sh 103967 --dias=90
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../../../.." && pwd)"
cd "$ROOT"

if [[ $# -lt 1 ]]; then
  echo "Uso: $0 <cliente_id> [--dias=90] [--output=dir]" >&2
  exit 1
fi

CLIENTE_ID="$1"
shift

if ! [[ "$CLIENTE_ID" =~ ^[0-9]+$ ]]; then
  echo "cliente_id inválido: $CLIENTE_ID" >&2
  exit 1
fi

if docker compose ps --status running 2>/dev/null | grep -q mybpdp; then
  exec docker compose exec -T mybpdp php artisan mybp:relatorio-cliente-usabilidade "$CLIENTE_ID" "$@"
fi

exec php artisan mybp:relatorio-cliente-usabilidade "$CLIENTE_ID" "$@"

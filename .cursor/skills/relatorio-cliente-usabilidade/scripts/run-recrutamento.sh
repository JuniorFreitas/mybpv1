#!/usr/bin/env bash
# Relatório de recrutamento MyBP por cliente_id.
# Uso: .cursor/skills/relatorio-cliente-usabilidade/scripts/run-recrutamento.sh 63122 --dias=90
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../../../.." && pwd)"
cd "$ROOT"
if [[ $# -lt 1 ]]; then
  echo "Uso: $0 <cliente_id> [--dias=90]" >&2
  exit 1
fi
CLIENTE_ID="$1"; shift
if docker compose ps --status running 2>/dev/null | grep -q mybpdp; then
  exec docker compose exec -T mybpdp php artisan mybp:relatorio-cliente-recrutamento "$CLIENTE_ID" "$@"
fi
exec php artisan mybp:relatorio-cliente-recrutamento "$CLIENTE_ID" "$@"

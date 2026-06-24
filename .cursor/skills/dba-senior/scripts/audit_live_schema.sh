#!/usr/bin/env bash
# Coleta métricas operacionais do INFORMATION_SCHEMA (tamanho, PK ausente, índices).
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
ROOT="$(cd "$SCRIPT_DIR/../../../.." && pwd)"
OUTPUT=""

while [[ $# -gt 0 ]]; do
  case "$1" in
    --output) OUTPUT="$2"; shift 2 ;;
    --root) ROOT="$2"; shift 2 ;;
    *) shift ;;
  esac
done

if [[ -z "$OUTPUT" ]]; then
  OUTPUT="$ROOT/docs/database/audits/$(date +%Y-%m-%d)"
fi

LIVE_JSON="$OUTPUT/schema-live.json"
if [[ ! -f "$LIVE_JSON" ]]; then
  echo "[audit_live_schema] schema-live.json não encontrado; execute dump_live_ddl.sh primeiro" >&2
  exit 1
fi

python3 "$SCRIPT_DIR/build_live_stats.py" \
  --live "$LIVE_JSON" \
  --output "$OUTPUT/schema-live-raw.json"

echo "[audit_live_schema] Complete -> $OUTPUT/schema-live-raw.json"

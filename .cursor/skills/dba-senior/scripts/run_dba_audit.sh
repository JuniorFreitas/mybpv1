#!/usr/bin/env bash
# Orquestrador da auditoria DBA MyBP.
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
ROOT="$(cd "$SCRIPT_DIR/../../../.." && pwd)"
OUTPUT=""
SKIP_LIVE=false
RUN_EXPLAIN=false
APPLY_DDL=false

while [[ $# -gt 0 ]]; do
  case "$1" in
    --output) OUTPUT="$2"; shift 2 ;;
    --root) ROOT="$2"; shift 2 ;;
    --skip-live) SKIP_LIVE=true; shift ;;
    --explain) RUN_EXPLAIN=true; shift ;;
    --apply-ddl) APPLY_DDL=true; shift ;;
    *) shift ;;
  esac
done

if [[ -z "$OUTPUT" ]]; then
  OUTPUT="$ROOT/docs/database/audits/$(date +%Y-%m-%d)"
fi
mkdir -p "$OUTPUT"

LIVE_OK=false
LIVE_SKIP_REASON=""

echo "=== DBA Audit MyBP ==="
echo "Output: $OUTPUT"

echo "[1/7] Audit migrations..."
python3 "$SCRIPT_DIR/audit_migrations.py" \
  --root "$ROOT" \
  --output "$OUTPUT/migrations-raw.json"

echo "[2/7] Audit models..."
python3 "$SCRIPT_DIR/audit_models.py" \
  --root "$ROOT" \
  --migrations "$OUTPUT/migrations-raw.json" \
  --output "$OUTPUT/models-raw.json"

echo "[3/7] Scan SQL patterns..."
python3 "$SCRIPT_DIR/scan_sql_patterns.py" \
  --root "$ROOT" \
  --output "$OUTPUT/sql-patterns-raw.json"

if [[ "$SKIP_LIVE" == "false" ]]; then
  echo "[4/7] Live DDL dump..."
  if bash "$SCRIPT_DIR/dump_live_ddl.sh" --root "$ROOT" --output "$OUTPUT"; then
    LIVE_OK=true
    echo "[5/7] DDL diff..."
    python3 "$SCRIPT_DIR/ddl_diff.py" \
      --migrations "$OUTPUT/migrations-raw.json" \
      --live "$OUTPUT/schema-live.json" \
      --output "$OUTPUT/ddl-diff.json"
    echo "[6/7] Live schema stats..."
    bash "$SCRIPT_DIR/audit_live_schema.sh" --root "$ROOT" --output "$OUTPUT"
  else
    LIVE_SKIP_REASON="MySQL indisponível ou conexão recusada (host não local ou container down)"
    echo "[4/7] Live skipped: $LIVE_SKIP_REASON"
  fi
else
  LIVE_SKIP_REASON="--skip-live solicitado"
  echo "[4/7] Live skipped: $LIVE_SKIP_REASON"
fi

echo "[7/7] Merge findings..."
MERGE_ARGS=(
  --output "$OUTPUT"
  --root "$ROOT"
  --migrations "$OUTPUT/migrations-raw.json"
  --models "$OUTPUT/models-raw.json"
  --sql "$OUTPUT/sql-patterns-raw.json"
)
if [[ "$LIVE_OK" == "true" ]]; then
  MERGE_ARGS+=(--live)
  MERGE_ARGS+=(--ddl-diff "$OUTPUT/ddl-diff.json")
  MERGE_ARGS+=(--live-stats "$OUTPUT/schema-live-raw.json")
else
  MERGE_ARGS+=(--live-skipped-reason "$LIVE_SKIP_REASON")
fi

python3 "$SCRIPT_DIR/merge_dba_findings.py" "${MERGE_ARGS[@]}"

python3 "$SCRIPT_DIR/generate_ddl_suggestions.py" "$OUTPUT/findings.json"

python3 "$SCRIPT_DIR/generate_report.py" "$OUTPUT/findings.json"

if [[ "$APPLY_DDL" == "true" ]]; then
  if [[ "$LIVE_OK" != "true" ]]; then
    echo "ERROR: --apply-ddl requer ambiente live local" >&2
    exit 1
  fi
  echo "WARNING: Copiando stubs de migration para database/migrations/ (revisão humana assumida)"
  if compgen -G "$OUTPUT/ddl-suggestions/migrations/*.php" > /dev/null; then
    cp "$OUTPUT/ddl-suggestions/migrations/"*.php "$ROOT/database/migrations/"
    docker compose -f "$ROOT/docker-compose.dev.yml" exec -T app php artisan migrate --force
  fi
fi

echo "=== DBA Audit complete ==="
echo "Dashboard: $OUTPUT/dashboard.html"
echo "Findings:  $OUTPUT/findings.csv"

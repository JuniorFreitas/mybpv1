#!/usr/bin/env bash
# Extrai DDL live do MySQL (mysqldump --no-data) e schema-live.json via INFORMATION_SCHEMA.
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
mkdir -p "$OUTPUT"

# Carregar .env
ENV_FILE="$ROOT/.env"
if [[ -f "$ENV_FILE" ]]; then
  set -a
  # shellcheck disable=SC1090
  source <(grep -E '^DB_' "$ENV_FILE" | sed 's/\r$//')
  set +a
fi

DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"
DB_DATABASE="${DB_DATABASE:-mybp}"
DB_USERNAME="${DB_USERNAME:-root}"
DB_PASSWORD="${DB_PASSWORD:-}"

ALLOWED_HOSTS="^(localhost|127\.0\.0\.1|mysql)$"
if [[ ! "$DB_HOST" =~ $ALLOWED_HOSTS ]]; then
  echo "[dump_live_ddl] BLOCKED: host '$DB_HOST' não é local. Produção bloqueada." >&2
  exit 2
fi

MYSQL_MODE="none"

if docker compose -f "$ROOT/docker-compose.dev.yml" ps mysql 2>/dev/null | grep -q "Up"; then
  MYSQL_MODE="compose"
elif command -v mysql &>/dev/null; then
  MYSQL_MODE="host"
elif command -v docker &>/dev/null && [[ "$DB_HOST" =~ ^(127\.0\.0\.1|localhost)$ ]]; then
  MYSQL_MODE="docker_run"
  DB_HOST="host.docker.internal"
else
  echo "[dump_live_ddl] MySQL não disponível (docker compose, cliente local ou docker run)" >&2
  exit 1
fi

run_mysql() {
  local sql="$1"
  case "$MYSQL_MODE" in
    compose)
      docker compose -f "$ROOT/docker-compose.dev.yml" exec -T mysql \
        mysql -u"${DB_USERNAME:-root}" ${DB_PASSWORD:+-p"$DB_PASSWORD"} "$DB_DATABASE" -N -e "$sql"
      ;;
    host)
      mysql -h"${DB_HOST}" -P"$DB_PORT" -u"$DB_USERNAME" ${DB_PASSWORD:+-p"$DB_PASSWORD"} "$DB_DATABASE" -N -e "$sql"
      ;;
    docker_run)
      docker run --rm -i mysql:5.7 mysql \
        -h"host.docker.internal" -P"$DB_PORT" -u"$DB_USERNAME" ${DB_PASSWORD:+-p"$DB_PASSWORD"} "$DB_DATABASE" -N -e "$sql"
      ;;
  esac
}

echo "[dump_live_ddl] Testando conexão..."
if ! run_mysql "SELECT 1" &>/dev/null; then
  echo "[dump_live_ddl] Falha na conexão com $DB_DATABASE" >&2
  exit 1
fi

echo "[dump_live_ddl] mysqldump --no-data..."
case "$MYSQL_MODE" in
  compose)
    docker compose -f "$ROOT/docker-compose.dev.yml" exec -T mysql \
      mysqldump -u"${DB_USERNAME:-root}" ${DB_PASSWORD:+-p"$DB_PASSWORD"} \
      --no-data --routines --triggers --single-transaction "$DB_DATABASE" \
      > "$OUTPUT/schema-live-ddl.sql" 2>/dev/null || true
    ;;
  host)
    if command -v mysqldump &>/dev/null; then
      mysqldump -h"127.0.0.1" -P"$DB_PORT" -u"$DB_USERNAME" ${DB_PASSWORD:+-p"$DB_PASSWORD"} \
        --no-data --routines --triggers --single-transaction "$DB_DATABASE" \
        > "$OUTPUT/schema-live-ddl.sql" 2>/dev/null || true
    else
      echo "-- mysqldump não disponível no host" > "$OUTPUT/schema-live-ddl.sql"
    fi
    ;;
  docker_run)
    docker run --rm -i mysql:5.7 mysqldump \
      -h"host.docker.internal" -P"$DB_PORT" -u"$DB_USERNAME" ${DB_PASSWORD:+-p"$DB_PASSWORD"} \
      --no-data --routines --triggers --single-transaction "$DB_DATABASE" \
      > "$OUTPUT/schema-live-ddl.sql" 2>/dev/null || true
    ;;
esac

echo "[dump_live_ddl] Coletando INFORMATION_SCHEMA..."
python3 "$SCRIPT_DIR/collect_live_schema.py" \
  --mode "$MYSQL_MODE" \
  --root "$ROOT" \
  --database "$DB_DATABASE" \
  --output "$OUTPUT/schema-live.json"

echo "[dump_live_ddl] Complete -> $OUTPUT/schema-live-ddl.sql, schema-live.json"

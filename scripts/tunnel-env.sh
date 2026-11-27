#!/usr/bin/env bash
# Helpers: aplica/restaura APP_URL e chaves relacionadas no .env
# Usado por tunnel-up.sh / tunnel-down.sh.

ENV_FILE="${ROOT}/.env"
ENV_BACKUP="${ROOT}/.dev/env.pre-tunnel"
APP_TARGET="${APP_TARGET:-http://127.0.0.1:8000}"
COMPOSE_SERVICE="${COMPOSE_SERVICE:-mybpdp}"

TUNNEL_ENV_KEYS=(
  APP_URL
  ASSET_URL
  SESSION_SECURE_COOKIE
  SESSION_DOMAIN
  SANCTUM_STATEFUL_DOMAINS
  TRUSTED_PROXIES
)

env_get() {
  local key="$1"
  python3 - "$ENV_FILE" "$key" <<'PY'
import sys
path, key = sys.argv[1], sys.argv[2]
try:
    with open(path, encoding="utf-8") as f:
        for raw in f:
            line = raw.rstrip("\n")
            if line.startswith(key + "="):
                print(line[len(key) + 1 :])
                raise SystemExit(0)
except FileNotFoundError:
    pass
print("")
PY
}

env_set_many() {
  python3 - "$ENV_FILE" "$@" <<'PY'
import sys
from pathlib import Path

path = Path(sys.argv[1])
updates = {}
for item in sys.argv[2:]:
    if "=" not in item:
        continue
    k, v = item.split("=", 1)
    updates[k] = v

text = path.read_text(encoding="utf-8") if path.exists() else ""
lines = text.splitlines()
seen = set()
out = []
for line in lines:
    stripped = line.strip()
    if not stripped or stripped.startswith("#") or "=" not in line:
        out.append(line)
        continue
    key = line.split("=", 1)[0]
    if key in updates:
        if key in seen:
            continue
        out.append(f"{key}={updates[key]}")
        seen.add(key)
    else:
        out.append(line)

for key, val in updates.items():
    if key not in seen:
        out.append(f"{key}={val}")

path.write_text("\n".join(out) + ("\n" if out else ""), encoding="utf-8")
PY
}

backup_env_for_tunnel() {
  mkdir -p "${ROOT}/.dev"
  if [[ -f "${ENV_BACKUP}" ]]; then
    echo "==> backup .env já existe (${ENV_BACKUP})"
    return 0
  fi
  : >"${ENV_BACKUP}"
  local key val
  for key in "${TUNNEL_ENV_KEYS[@]}"; do
    val="$(env_get "${key}")"
    printf '%s=%s\n' "${key}" "${val}" >>"${ENV_BACKUP}"
  done
  echo "==> backup .env → ${ENV_BACKUP}"
}

apply_tunnel_app_env() {
  local app_url="$1"
  local host="${app_url#https://}"
  host="${host#http://}"
  host="${host%%/*}"

  if [[ -z "${app_url}" || -z "${host}" ]]; then
    echo "apply_tunnel_app_env: URL inválida" >&2
    return 1
  fi

  if [[ ! -f "${ENV_FILE}" ]]; then
    echo "apply_tunnel_app_env: falta ${ENV_FILE}" >&2
    return 1
  fi

  backup_env_for_tunnel

  local sanctum
  sanctum="$(env_get SANCTUM_STATEFUL_DOMAINS)"
  if [[ -z "${sanctum}" ]]; then
    sanctum="localhost,127.0.0.1,127.0.0.1:8000"
  fi
  if [[ ",${sanctum}," != *",${host},"* ]]; then
    sanctum="${sanctum},${host}"
  fi

  echo "==> .env APP_URL=${app_url} (+ session / sanctum / trusted proxies)"
  env_set_many \
    "APP_URL=${app_url}" \
    "ASSET_URL=${app_url}" \
    "SESSION_SECURE_COOKIE=true" \
    "SESSION_DOMAIN=${host}" \
    "SANCTUM_STATEFUL_DOMAINS=${sanctum}" \
    "TRUSTED_PROXIES=*"
}

restore_tunnel_app_env() {
  if [[ ! -f "${ENV_BACKUP}" ]]; then
    echo "==> sem backup .env para restaurar"
    return 0
  fi
  if [[ ! -f "${ENV_FILE}" ]]; then
    echo "restore_tunnel_app_env: falta ${ENV_FILE}" >&2
    return 1
  fi

  echo "==> restaurando .env a partir de ${ENV_BACKUP}"
  python3 - "$ENV_FILE" "$ENV_BACKUP" <<'PY'
import sys
from pathlib import Path

env_path = Path(sys.argv[1])
backup_path = Path(sys.argv[2])
updates = {}
for line in backup_path.read_text(encoding="utf-8").splitlines():
    line = line.strip()
    if not line or line.startswith("#") or "=" not in line:
        continue
    key, val = line.split("=", 1)
    updates[key] = val

text = env_path.read_text(encoding="utf-8") if env_path.exists() else ""
lines = text.splitlines()
seen = set()
out = []
for line in lines:
    stripped = line.strip()
    if not stripped or stripped.startswith("#") or "=" not in line:
        out.append(line)
        continue
    key = line.split("=", 1)[0]
    if key in updates:
        out.append(f"{key}={updates[key]}")
        seen.add(key)
    else:
        out.append(line)

for key, val in updates.items():
    if key not in seen:
        out.append(f"{key}={val}")

env_path.write_text("\n".join(out) + ("\n" if out else ""), encoding="utf-8")
PY
  rm -f "${ENV_BACKUP}"
}

reload_app_after_env() {
  if ! command -v docker >/dev/null 2>&1; then
    return 0
  fi
  if ! docker compose -f "${ROOT}/docker-compose.yml" ps --status running --services 2>/dev/null | grep -qx "${COMPOSE_SERVICE}"; then
    return 0
  fi

  echo "==> recriando container ${COMPOSE_SERVICE} (recarrega APP_URL do .env)…"
  (cd "${ROOT}" && docker compose up -d --force-recreate --no-deps "${COMPOSE_SERVICE}")

  local i
  for i in $(seq 1 90); do
    if curl -sf --max-time 3 -o /dev/null "${APP_TARGET}/health" \
      || curl -sf --max-time 3 -o /dev/null "${APP_TARGET}/g/login"; then
      break
    fi
    sleep 1
  done

  docker compose -f "${ROOT}/docker-compose.yml" exec -T "${COMPOSE_SERVICE}" \
    php artisan optimize:clear --ansi >/dev/null 2>&1 || true
}

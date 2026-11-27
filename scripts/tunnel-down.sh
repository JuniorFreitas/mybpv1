#!/usr/bin/env bash
# Encerra o cloudflared do MyBP e restaura o .env (salvo se --keep-env).
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
# shellcheck disable=SC1091
source "${ROOT}/scripts/tunnel-env.sh"
RUN_DIR="${ROOT}/.dev/run"
STORAGE_APP="${ROOT}/storage/app"
KEEP_ENV=0

while [[ $# -gt 0 ]]; do
  case "$1" in
    --keep-env) KEEP_ENV=1; shift ;;
    -h|--help)
      echo "Uso: $(basename "$0") [--keep-env]"
      exit 0
      ;;
    *) echo "Opção desconhecida: $1" >&2; exit 1 ;;
  esac
done

stop_pid_file() {
  local file="$1"
  local name
  name="$(basename "${file}" .pid)"
  if [[ ! -f "${file}" ]]; then
    return 0
  fi
  local pid
  pid="$(cat "${file}" 2>/dev/null || true)"
  if [[ -n "${pid}" ]] && kill -0 "${pid}" 2>/dev/null; then
    pkill -P "${pid}" 2>/dev/null || true
    kill "${pid}" 2>/dev/null || true
    sleep 0.3
    kill -9 "${pid}" 2>/dev/null || true
    echo "==> ${name} parado (pid ${pid})"
  else
    echo "==> ${name}: já parado"
  fi
  rm -f "${file}"
}

found=0
shopt -s nullglob
for f in "${RUN_DIR}"/cloudflared*.pid; do
  found=1
  stop_pid_file "${f}"
done
shopt -u nullglob

pkill -f 'cloudflared tunnel --url http://127\.0\.0\.1:8000' 2>/dev/null || true
pkill -f "cloudflared tunnel --config ${ROOT}/.dev/cloudflared.yml" 2>/dev/null || true

if [[ "${found}" -eq 0 ]]; then
  echo "==> nenhum PID de cloudflared em ${RUN_DIR}"
fi

rm -f \
  "${STORAGE_APP}/tunnel.host" \
  "${STORAGE_APP}/tunnel.url" \
  "${ROOT}/.dev/tunnel.host" \
  "${ROOT}/.dev/tunnel.url"

echo "==> hosts do tunnel removidos"

if [[ "${KEEP_ENV}" -eq 1 ]]; then
  echo "==> --keep-env: .env não restaurado"
  exit 0
fi

had_env_backup=0
[[ -f "${ROOT}/.dev/env.pre-tunnel" ]] && had_env_backup=1

restore_tunnel_app_env

if [[ "${had_env_backup}" -eq 1 ]]; then
  reload_app_after_env
fi

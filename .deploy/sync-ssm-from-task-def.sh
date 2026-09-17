#!/usr/bin/env bash
# Cria/atualiza parâmetros SSM SecureString a partir da task definition ECS atual
# e da lista de secrets em task-definition.prod.example.json
#
# Uso:
#   .deploy/sync-ssm-from-task-def.sh [task-definition] [ssm-prefix]
# Ex.:
#   .deploy/sync-ssm-from-task-def.sh mybp-prod /mybp/prod
#
# Não imprime valores. Requer AWS CLI + jq + python3.

set -euo pipefail

AWS_REGION="${AWS_REGION:-us-east-1}"
TASK_DEF="${1:-mybp-prod}"
SSM_PREFIX="${2:-/mybp/prod}"
EXAMPLE_FILE="${EXAMPLE_FILE:-.deploy/task-definition.prod.example.json}"
ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

if [[ ! -f "$EXAMPLE_FILE" ]]; then
  echo "Erro: não encontrado ${EXAMPLE_FILE}" >&2
  exit 1
fi

for bin in aws jq python3; do
  if ! command -v "$bin" >/dev/null 2>&1; then
    echo "Erro: ${bin} não instalado." >&2
    exit 1
  fi
done

echo "Task definition: ${TASK_DEF}"
echo "SSM prefix: ${SSM_PREFIX}"
echo "Região: ${AWS_REGION}"

TMP_DIR="$(mktemp -d)"
trap 'rm -rf "$TMP_DIR"' EXIT

aws ecs describe-task-definition \
  --task-definition "${TASK_DEF}" \
  --region "${AWS_REGION}" \
  --query 'taskDefinition.containerDefinitions[0].environment' \
  --output json > "${TMP_DIR}/env.json"

python3 - "$EXAMPLE_FILE" "${TMP_DIR}/env.json" "${SSM_PREFIX}" "${AWS_REGION}" "${TMP_DIR}" <<'PY'
import json
import subprocess
import sys
from pathlib import Path

example = json.loads(Path(sys.argv[1]).read_text())
env_list = json.loads(Path(sys.argv[2]).read_text())
prefix = sys.argv[3].rstrip("/")
region = sys.argv[4]
tmp = Path(sys.argv[5])

env = {e["name"]: e.get("value", "") for e in env_list}
names = [s["name"] for s in (example["containerDefinitions"][0].get("secrets") or [])]

missing = []
ok_create = 0
ok_update = 0
failed = 0

print(f"Secrets no example: {len(names)}")

for name in names:
    value = env.get(name)
    if value is None or value == "":
        missing.append(name)
        continue

    param_name = f"{prefix}/{name}"
    exists = False
    try:
        subprocess.run(
            [
                "aws", "ssm", "get-parameter",
                "--name", param_name,
                "--region", region,
                "--output", "text",
                "--query", "Parameter.Name",
            ],
            check=True,
            capture_output=True,
            text=True,
        )
        exists = True
    except subprocess.CalledProcessError:
        exists = False

    payload = {
        "Name": param_name,
        "Type": "SecureString",
        "Value": value,
        "Overwrite": True,
    }
    payload_file = tmp / f"{name}.json"
    payload_file.write_text(json.dumps(payload, ensure_ascii=False))

    try:
        subprocess.run(
            [
                "aws", "ssm", "put-parameter",
                "--cli-input-json", f"file://{payload_file}",
                "--region", region,
                "--output", "text",
            ],
            check=True,
            capture_output=True,
            text=True,
        )
        if exists:
            print(f"OK update {param_name}")
            ok_update += 1
        else:
            print(f"OK create {param_name}")
            ok_create += 1
    except subprocess.CalledProcessError as exc:
        print(f"FAIL {param_name}", file=sys.stderr)
        err = (exc.stderr or "").strip()
        if err:
            # não vazar valor; só mensagem AWS
            print(err.splitlines()[-1], file=sys.stderr)
        failed += 1

if missing:
    print("Sem valor na task atual (pulados):")
    for n in missing:
        print(f"  - {n}")

print("")
print(f"Resumo: created={ok_create} updated={ok_update} skipped={len(missing)} failed={failed}")
sys.exit(1 if failed else 0)
PY

#!/usr/bin/env bash
# Migra secrets da task definition ECS (plain env) para SSM Parameter Store.
# Mantém a imagem atual e demais configs; remove do environment os nomes listados
# em task-definition.prod.example.json → secrets.
#
# Uso:
#   .deploy/migrate-task-def-to-ssm.sh [task-family] [cluster] [service]
# Ex.:
#   .deploy/migrate-task-def-to-ssm.sh mybp-prod MyBPClusterProd mybp-prod-service

set -euo pipefail

AWS_REGION="${AWS_REGION:-us-east-1}"
TASK_FAMILY="${1:-mybp-prod}"
CLUSTER_NAME="${2:-MyBPClusterProd}"
SERVICE_NAME="${3:-mybp-prod-service}"
EXAMPLE_FILE="${EXAMPLE_FILE:-.deploy/task-definition.prod.example.json}"
ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

for bin in aws jq python3; do
  if ! command -v "$bin" >/dev/null 2>&1; then
    echo "Erro: ${bin} não instalado." >&2
    exit 1
  fi
done

if [[ ! -f "$EXAMPLE_FILE" ]]; then
  echo "Erro: não encontrado ${EXAMPLE_FILE}" >&2
  exit 1
fi

TMP_DIR="$(mktemp -d)"
trap 'rm -rf "$TMP_DIR"' EXIT

echo "Family: ${TASK_FAMILY}"
echo "Cluster: ${CLUSTER_NAME}"
echo "Service: ${SERVICE_NAME}"

CURRENT_TASK_DEF=$(aws ecs describe-services \
  --cluster "${CLUSTER_NAME}" \
  --services "${SERVICE_NAME}" \
  --region "${AWS_REGION}" \
  --query 'services[0].taskDefinition' \
  --output text)

if [[ -z "$CURRENT_TASK_DEF" || "$CURRENT_TASK_DEF" == "None" ]]; then
  echo "Erro: não foi possível obter task definition do serviço." >&2
  exit 1
fi

echo "Task atual: ${CURRENT_TASK_DEF}"

aws ecs describe-task-definition \
  --task-definition "${CURRENT_TASK_DEF}" \
  --region "${AWS_REGION}" \
  --query 'taskDefinition' \
  --output json > "${TMP_DIR}/current.json"

python3 - "$EXAMPLE_FILE" "${TMP_DIR}/current.json" "${TMP_DIR}/register.json" <<'PY'
import json
import sys
from pathlib import Path

example = json.loads(Path(sys.argv[1]).read_text())
current = json.loads(Path(sys.argv[2]).read_text())

secret_defs = example["containerDefinitions"][0].get("secrets") or []
secret_names = {s["name"] for s in secret_defs}
secrets_by_name = {s["name"]: s for s in secret_defs}

# Campos inválidos no register-task-definition
for k in [
    "taskDefinitionArn",
    "revision",
    "status",
    "requiresAttributes",
    "compatibilities",
    "registeredAt",
    "registeredBy",
    "deregisteredAt",
    "tags",
]:
    current.pop(k, None)

container = current["containerDefinitions"][0]
env = container.get("environment") or []
kept_env = []
moved = []
for item in env:
    name = item.get("name")
    if name in secret_names:
        moved.append(name)
        continue
    # Correção conhecida: MIX_PUSHER_APP_CLUSTER não deve carregar secret
    if name == "MIX_PUSHER_APP_CLUSTER" and item.get("value") not in ("mt1", "", None):
        item = {"name": name, "value": "mt1"}
    kept_env.append(item)

# Garante CHAT_ID se existir no env atual; senão ignora REPLACE_ME do example
container["environment"] = kept_env
container["secrets"] = [secrets_by_name[n] for n in sorted(secret_names)]

Path(sys.argv[3]).write_text(json.dumps(current, ensure_ascii=False))
print(f"Env mantidos: {len(kept_env)}")
print(f"Secrets SSM: {len(secret_names)}")
print(f"Movidos para secrets: {len(moved)}")
for n in sorted(moved):
    print(f"  - {n}")
missing = sorted(secret_names - set(moved))
if missing:
    print("Secrets no example sem valor no env atual (ainda referenciados no SSM):")
    for n in missing:
        print(f"  - {n}")
PY

IMAGE=$(jq -r '.containerDefinitions[0].image' "${TMP_DIR}/register.json")
echo "Imagem preservada: ${IMAGE}"

NEW_TASK_DEF=$(aws ecs register-task-definition \
  --cli-input-json "file://${TMP_DIR}/register.json" \
  --region "${AWS_REGION}" \
  --query 'taskDefinition.taskDefinitionArn' \
  --output text)

if [[ -z "$NEW_TASK_DEF" || "$NEW_TASK_DEF" == "None" ]]; then
  echo "Erro: falha ao registrar task definition." >&2
  exit 1
fi

echo "Nova task definition: ${NEW_TASK_DEF}"

aws ecs update-service \
  --cluster "${CLUSTER_NAME}" \
  --service "${SERVICE_NAME}" \
  --task-definition "${NEW_TASK_DEF}" \
  --force-new-deployment \
  --region "${AWS_REGION}" \
  --query 'service.{serviceName:serviceName,status:status,taskDefinition:taskDefinition}' \
  --output table

echo "Aguardando serviço estabilizar..."
aws ecs wait services-stable \
  --cluster "${CLUSTER_NAME}" \
  --services "${SERVICE_NAME}" \
  --region "${AWS_REGION}"

echo "Deploy concluído com secrets via SSM."
echo "TASK_DEFINITION=${NEW_TASK_DEF}"

#!/usr/bin/env bash
# CI: deregistra task definitions antigas, mantendo as KEEP_COUNT mais recentes — MyBP
# Fonte de verdade: .deploy/scripts/cleanup-task-definitions.sh + deploy-ecs.sh
# Uso:
#   .deploy/ci-cleanup-task-definitions.sh <homol|prod>
#   .deploy/ci-cleanup-task-definitions.sh --family <family_name>
#
# Family padrão do projeto: mybp-sistema (scripts). Via ambiente, descobre pelo serviço ECS.

set -euo pipefail

AWS_REGION="${AWS_REGION:-us-east-1}"
KEEP_COUNT="${KEEP_COUNT:-3}"
FAMILY_NAME=""
ENV_NAME=""

usage() {
    echo "Uso: $0 <homol|prod> | $0 --family <family_name>" >&2
    exit 1
}

if [[ "${1:-}" == "--family" ]]; then
    FAMILY_NAME="${2:-}"
    if [[ -z "$FAMILY_NAME" ]]; then
        usage
    fi
elif [[ "${1:-}" == "homol" || "${1:-}" == "prod" ]]; then
    ENV_NAME="$1"
else
    usage
fi

if ! command -v aws >/dev/null 2>&1; then
    echo "Erro: AWS CLI não está instalado." >&2
    exit 1
fi

resolve_family_from_env() {
    local env_name=$1
    local cluster_name
    local service_name

    case "$env_name" in
        homol)
            cluster_name="mybpClusterHomol"
            service_name="homol-service"
            ;;
        prod)
            cluster_name="MyBPClusterProd"
            service_name="mybp-prod-service"
            ;;
        *)
            echo "Ambiente inválido: ${env_name}" >&2
            exit 1
            ;;
    esac

    local current_task_def
    current_task_def=$(aws ecs describe-services \
        --cluster "${cluster_name}" \
        --services "${service_name}" \
        --region "${AWS_REGION}" \
        --query 'services[0].taskDefinition' \
        --output text)

    if [[ -z "$current_task_def" || "$current_task_def" == "None" ]]; then
        echo "Erro: não foi possível obter task definition de ${service_name}." >&2
        echo "Fallback: family mybp-sistema" >&2
        echo "mybp-sistema"
        return 0
    fi

    echo "$current_task_def" | sed 's/.*task-definition\/\([^:]*\).*/\1/'
}

if [[ -z "$FAMILY_NAME" ]]; then
    FAMILY_NAME=$(resolve_family_from_env "$ENV_NAME")
fi

echo "Family: ${FAMILY_NAME}"
echo "Região: ${AWS_REGION}"
echo "Manter: ${KEEP_COUNT} revisões ACTIVE mais recentes"

# list-task-definitions retorna ARNs ordenados do mais antigo ao mais recente
mapfile -t ALL_TASK_DEFS < <(
    aws ecs list-task-definitions \
        --family-prefix "${FAMILY_NAME}" \
        --status ACTIVE \
        --sort ASC \
        --region "${AWS_REGION}" \
        --query 'taskDefinitionArns[]' \
        --output text | tr '\t' '\n' | sed '/^$/d'
)

# Filtrar apenas a family exata (list usa prefix)
FILTERED=()
for arn in "${ALL_TASK_DEFS[@]:-}"; do
    name=$(basename "$arn")
    family_part="${name%%:*}"
    if [[ "$family_part" == "$FAMILY_NAME" ]]; then
        FILTERED+=("$arn")
    fi
done

TOTAL=${#FILTERED[@]}
echo "Total ACTIVE na family: ${TOTAL}"

if [[ "$TOTAL" -le "$KEEP_COUNT" ]]; then
    echo "Nada a remover."
    exit 0
fi

TO_REMOVE=$((TOTAL - KEEP_COUNT))
echo "Removendo ${TO_REMOVE} revisão(ões) antiga(s)..."

REMOVED=0
for ((i = 0; i < TO_REMOVE; i++)); do
    arn="${FILTERED[$i]}"
    name=$(basename "$arn")
    echo -n "Deregister ${name}... "
    if aws ecs deregister-task-definition \
        --task-definition "$arn" \
        --region "${AWS_REGION}" \
        --output text >/dev/null; then
        echo "ok"
        REMOVED=$((REMOVED + 1))
    else
        echo "falhou"
    fi
done

echo "Limpeza concluída. Removidas: ${REMOVED}. Mantidas: $((TOTAL - REMOVED))."

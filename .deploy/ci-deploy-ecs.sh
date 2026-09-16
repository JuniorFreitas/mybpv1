#!/usr/bin/env bash
# CI: atualiza serviço ECS com nova imagem (não-interativo) — MyBP
# Fonte de verdade: .deploy/scripts/deploy-ecs.sh (deploy-full.sh)
# Uso: .deploy/ci-deploy-ecs.sh <homol|prod> <image_tag>

set -euo pipefail

AWS_REGION="${AWS_REGION:-us-east-1}"
IMAGE_NAME="${IMAGE_NAME:-mybp/sistema}"
ENV_NAME="${1:-}"
IMAGE_TAG="${2:-}"

usage() {
    echo "Uso: $0 <homol|prod> <image_tag>" >&2
    exit 1
}

if [[ "$ENV_NAME" != "homol" && "$ENV_NAME" != "prod" ]]; then
    usage
fi

if [[ -z "$IMAGE_TAG" ]]; then
    usage
fi

if [[ "$ENV_NAME" == "prod" ]] && { [[ "$IMAGE_TAG" == "latest" ]] || [[ "$IMAGE_TAG" == *:latest ]]; }; then
    echo "Erro: tag 'latest' é proibida em produção." >&2
    exit 1
fi

case "$ENV_NAME" in
    homol)
        CLUSTER_NAME="mybpClusterHomol"
        SERVICE_NAME="homol-service"
        ;;
    prod)
        CLUSTER_NAME="MyBPClusterProd"
        SERVICE_NAME="mybp-prod-service"
        ;;
esac

if ! command -v aws >/dev/null 2>&1; then
    echo "Erro: AWS CLI não está instalado." >&2
    exit 1
fi

if ! command -v jq >/dev/null 2>&1; then
    echo "Erro: jq não está instalado." >&2
    exit 1
fi

REGISTRY_ID=$(aws ecr describe-registry --region "${AWS_REGION}" --query 'registryId' --output text)
if [[ -z "$REGISTRY_ID" || "$REGISTRY_ID" == "None" ]]; then
    echo "Erro: não foi possível obter o ECR registry." >&2
    exit 1
fi

ECR_REGISTRY="${REGISTRY_ID}.dkr.ecr.${AWS_REGION}.amazonaws.com"

if [[ "$IMAGE_TAG" == *"${ECR_REGISTRY}/${IMAGE_NAME}:"* ]]; then
    FULL_IMAGE="$IMAGE_TAG"
else
    FULL_IMAGE="${ECR_REGISTRY}/${IMAGE_NAME}:${IMAGE_TAG}"
fi

echo "Cluster: ${CLUSTER_NAME}"
echo "Service: ${SERVICE_NAME}"
echo "Imagem: ${FULL_IMAGE}"

SERVICE_EXISTS=$(aws ecs describe-services \
    --cluster "${CLUSTER_NAME}" \
    --services "${SERVICE_NAME}" \
    --region "${AWS_REGION}" \
    --query 'services[0].serviceName' \
    --output text)

if [[ "$SERVICE_EXISTS" == "None" || -z "$SERVICE_EXISTS" ]]; then
    echo "Erro: serviço '${SERVICE_NAME}' não encontrado no cluster '${CLUSTER_NAME}'." >&2
    exit 1
fi

CURRENT_TASK_DEF=$(aws ecs describe-services \
    --cluster "${CLUSTER_NAME}" \
    --services "${SERVICE_NAME}" \
    --region "${AWS_REGION}" \
    --query 'services[0].taskDefinition' \
    --output text)

if [[ "$CURRENT_TASK_DEF" == "None" || -z "$CURRENT_TASK_DEF" ]]; then
    echo "Erro: não foi possível obter a task definition atual." >&2
    exit 1
fi

echo "Task definition atual: ${CURRENT_TASK_DEF}"

TASK_DEF_JSON=$(aws ecs describe-task-definition \
    --task-definition "${CURRENT_TASK_DEF}" \
    --region "${AWS_REGION}" \
    --query 'taskDefinition')

TEMP_FILE=$(mktemp)
trap 'rm -f "$TEMP_FILE"' EXIT
echo "$TASK_DEF_JSON" > "$TEMP_FILE"

UPDATED_TASK_DEF=$(jq --arg image "$FULL_IMAGE" \
    '.containerDefinitions[0].image = $image |
     del(.taskDefinitionArn, .revision, .status, .requiresAttributes, .placementConstraints, .compatibilities, .registeredAt, .registeredBy)' \
    "$TEMP_FILE")

NEW_TASK_DEF=$(aws ecs register-task-definition \
    --cli-input-json "$UPDATED_TASK_DEF" \
    --region "${AWS_REGION}" \
    --query 'taskDefinition.taskDefinitionArn' \
    --output text)

if [[ "$NEW_TASK_DEF" == "None" || -z "$NEW_TASK_DEF" ]]; then
    echo "Erro: falha ao registrar nova task definition." >&2
    exit 1
fi

echo "Nova task definition: ${NEW_TASK_DEF}"

aws ecs update-service \
    --cluster "${CLUSTER_NAME}" \
    --service "${SERVICE_NAME}" \
    --task-definition "${NEW_TASK_DEF}" \
    --region "${AWS_REGION}" \
    --query 'service.{serviceName:serviceName,status:status,taskDefinition:taskDefinition}' \
    --output table

echo "Aguardando serviço estabilizar..."
aws ecs wait services-stable \
    --cluster "${CLUSTER_NAME}" \
    --services "${SERVICE_NAME}" \
    --region "${AWS_REGION}"

FAMILY_NAME=$(echo "$NEW_TASK_DEF" | sed 's/.*task-definition\/\([^:]*\).*/\1/')

if [[ -n "${GITHUB_OUTPUT:-}" ]]; then
    echo "task_definition=${NEW_TASK_DEF}" >> "${GITHUB_OUTPUT}"
    echo "task_family=${FAMILY_NAME}" >> "${GITHUB_OUTPUT}"
    echo "cluster=${CLUSTER_NAME}" >> "${GITHUB_OUTPUT}"
    echo "service=${SERVICE_NAME}" >> "${GITHUB_OUTPUT}"
fi

echo "Deploy ECS concluído."
echo "TASK_FAMILY=${FAMILY_NAME}"

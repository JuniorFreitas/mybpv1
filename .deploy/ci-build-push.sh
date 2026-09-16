#!/usr/bin/env bash
# CI: build + push imagem Docker para ECR (não-interativo) — MyBP
# Fonte de verdade de ambientes/URLs: .deploy/scripts/deploy.sh (deploy-full.sh)
# Uso: .deploy/ci-build-push.sh <homol|prod>
# Output: IMAGE_TAG / full_image no GITHUB_OUTPUT (se definido)

set -euo pipefail

AWS_REGION="${AWS_REGION:-us-east-1}"
IMAGE_NAME="${IMAGE_NAME:-mybp/sistema}"
DOCKER_PLATFORM="${DOCKER_PLATFORM:-}"
ENV_NAME="${1:-}"

usage() {
    echo "Uso: $0 <homol|prod>" >&2
    exit 1
}

if [[ "$ENV_NAME" != "homol" && "$ENV_NAME" != "prod" ]]; then
    usage
fi

case "$ENV_NAME" in
    homol)
        APP_URL="https://qa.mybp.com.br"
        TAG_PREFIX="homol"
        NPM_SCRIPT="homol"
        ;;
    prod)
        APP_URL="https://sistema.mybp.com.br"
        TAG_PREFIX="prod"
        NPM_SCRIPT="prod"
        ;;
esac

if ! command -v docker >/dev/null 2>&1; then
    echo "Erro: Docker não está instalado." >&2
    exit 1
fi

if ! command -v aws >/dev/null 2>&1; then
    echo "Erro: AWS CLI não está instalado." >&2
    exit 1
fi

prepare_assets() {
    echo "Preparando assets (npm run ${NPM_SCRIPT})..."
    if command -v nvm >/dev/null 2>&1; then
        nvm use || true
    elif [[ -f "${HOME}/.nvm/nvm.sh" ]]; then
        # shellcheck disable=SC1091
        source "${HOME}/.nvm/nvm.sh"
        nvm use || true
    fi

    if [[ ! -d node_modules ]]; then
        npm install
    fi

    npm run "${NPM_SCRIPT}"
    echo "Assets preparados."
}

REGISTRY_ID=$(aws ecr describe-registry --region "${AWS_REGION}" --query 'registryId' --output text)
if [[ -z "$REGISTRY_ID" || "$REGISTRY_ID" == "None" ]]; then
    echo "Erro: não foi possível obter o ECR registry." >&2
    exit 1
fi

ECR_REGISTRY="${REGISTRY_ID}.dkr.ecr.${AWS_REGION}.amazonaws.com"
TIMESTAMP=$(date +"%Y%m%d-%H%M%S")
GIT_COMMIT=$(git rev-parse --short HEAD 2>/dev/null || echo "unknown")
IMAGE_TAG="${TAG_PREFIX}-${TIMESTAMP}-${GIT_COMMIT}"
FULL_IMAGE="${ECR_REGISTRY}/${IMAGE_NAME}:${IMAGE_TAG}"

echo "Ambiente: ${ENV_NAME}"
echo "APP_URL: ${APP_URL}"
echo "Imagem ECR: ${IMAGE_NAME}"
echo "Imagem: ${FULL_IMAGE}"
if [[ -n "$DOCKER_PLATFORM" ]]; then
    echo "Platform: ${DOCKER_PLATFORM}"
fi

prepare_assets

echo "Login ECR..."
aws ecr get-login-password --region "${AWS_REGION}" \
    | docker login --username AWS --password-stdin "${ECR_REGISTRY}"

echo "Build e push da imagem..."
BUILD_ARGS=(
    --build-arg "APP_URL=${APP_URL}"
    --push
    -t "${FULL_IMAGE}"
    .
)

if [[ -n "$DOCKER_PLATFORM" ]]; then
    docker buildx build --platform "${DOCKER_PLATFORM}" "${BUILD_ARGS[@]}"
else
    docker buildx build "${BUILD_ARGS[@]}"
fi

echo "Build e push concluídos: ${FULL_IMAGE}"

if [[ -n "${GITHUB_OUTPUT:-}" ]]; then
    echo "image_tag=${IMAGE_TAG}" >> "${GITHUB_OUTPUT}"
    echo "full_image=${FULL_IMAGE}" >> "${GITHUB_OUTPUT}"
fi

echo "IMAGE_TAG=${IMAGE_TAG}"

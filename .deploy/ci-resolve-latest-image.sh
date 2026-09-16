#!/usr/bin/env bash
# CI: resolve a tag mais recente no ECR (por imagePushedAt) para o prefixo do ambiente — MyBP
# Fonte de verdade: .deploy/scripts/deploy.sh (IMAGE_NAME=mybp/sistema)
# Uso: .deploy/ci-resolve-latest-image.sh <homol|prod>
# Override: IMAGE_TAG=prod-... .deploy/ci-resolve-latest-image.sh prod

set -euo pipefail

AWS_REGION="${AWS_REGION:-us-east-1}"
IMAGE_NAME="${IMAGE_NAME:-mybp/sistema}"
ENV_NAME="${1:-}"

usage() {
    echo "Uso: $0 <homol|prod>" >&2
    echo "Ou: IMAGE_TAG=<tag> $0 <homol|prod>" >&2
    exit 1
}

if [[ "$ENV_NAME" != "homol" && "$ENV_NAME" != "prod" ]]; then
    usage
fi

case "$ENV_NAME" in
    homol) TAG_PREFIX="homol-" ;;
    prod) TAG_PREFIX="prod-" ;;
esac

if ! command -v aws >/dev/null 2>&1; then
    echo "Erro: AWS CLI não está instalado." >&2
    exit 1
fi

if ! command -v jq >/dev/null 2>&1; then
    echo "Erro: jq não está instalado." >&2
    exit 1
fi

# Permite forçar tag (workflow_dispatch / debug)
if [[ -n "${IMAGE_TAG:-}" ]]; then
    if [[ "$ENV_NAME" == "prod" ]] && { [[ "$IMAGE_TAG" == "latest" ]] || [[ "$IMAGE_TAG" == *:latest ]]; }; then
        echo "Erro: tag 'latest' é proibida em produção." >&2
        exit 1
    fi
    echo "Usando IMAGE_TAG informado: ${IMAGE_TAG}"
else
    echo "Buscando imagem mais recente no ECR com prefixo '${TAG_PREFIX}'..."
    # Pagina describe-images e escolhe a tag do prefixo com maior imagePushedAt
    TMP_JSON="$(mktemp)"
    trap 'rm -f "$TMP_JSON"' EXIT
    NEXT_TOKEN=""
    echo '{"imageDetails":[]}' > "$TMP_JSON"

    while true; do
        if [[ -n "$NEXT_TOKEN" ]]; then
            PAGE="$(aws ecr describe-images \
                --repository-name "${IMAGE_NAME}" \
                --region "${AWS_REGION}" \
                --filter tagStatus=TAGGED \
                --next-token "${NEXT_TOKEN}" \
                --output json)"
        else
            PAGE="$(aws ecr describe-images \
                --repository-name "${IMAGE_NAME}" \
                --region "${AWS_REGION}" \
                --filter tagStatus=TAGGED \
                --output json)"
        fi

        jq -s '.[0].imageDetails + .[1].imageDetails | {imageDetails: .}' \
            "$TMP_JSON" <(echo "$PAGE") > "${TMP_JSON}.next"
        mv "${TMP_JSON}.next" "$TMP_JSON"

        NEXT_TOKEN="$(echo "$PAGE" | jq -r '.nextToken // empty')"
        if [[ -z "$NEXT_TOKEN" ]]; then
            break
        fi
    done

    IMAGE_TAG="$(
        jq -r --arg prefix "$TAG_PREFIX" '
            [
              .imageDetails[]
              | select(.imageTags != null)
              | . as $img
              | .imageTags[]
              | select(startswith($prefix))
              | {tag: ., pushedAt: $img.imagePushedAt}
            ]
            | sort_by(.pushedAt)
            | last
            | .tag // empty
          ' "$TMP_JSON"
    )"

    if [[ -z "$IMAGE_TAG" ]]; then
        echo "Erro: nenhuma imagem encontrada em ${IMAGE_NAME} com prefixo '${TAG_PREFIX}'." >&2
        exit 1
    fi
fi

REGISTRY_ID=$(aws ecr describe-registry --region "${AWS_REGION}" --query 'registryId' --output text)
ECR_REGISTRY="${REGISTRY_ID}.dkr.ecr.${AWS_REGION}.amazonaws.com"
FULL_IMAGE="${ECR_REGISTRY}/${IMAGE_NAME}:${IMAGE_TAG}"

echo "Imagem selecionada: ${FULL_IMAGE}"

if [[ -n "${GITHUB_OUTPUT:-}" ]]; then
    echo "image_tag=${IMAGE_TAG}" >> "${GITHUB_OUTPUT}"
    echo "full_image=${FULL_IMAGE}" >> "${GITHUB_OUTPUT}"
fi

echo "IMAGE_TAG=${IMAGE_TAG}"

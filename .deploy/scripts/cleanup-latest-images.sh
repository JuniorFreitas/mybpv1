#!/bin/bash

# Script para remover imagens 'latest' do ECR
# Reduz custos removendo tags desnecessárias

set -e  # Para o script se houver erro

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configurações
AWS_REGION="us-east-1"
ECR_REGISTRY=""
IMAGE_NAME="mybp/sistema"

# Função para exibir banner
show_banner() {
    echo -e "${BLUE}"
    echo "=========================================="
    echo "    Limpeza de Imagens 'latest' ECR"
    echo "=========================================="
    echo -e "${NC}"
}

# Função para obter o ECR registry
get_ecr_registry() {
    if [ -z "$ECR_REGISTRY" ]; then
        echo -e "${GREEN}Obtendo ECR registry...${NC}"
        ECR_REGISTRY=$(aws ecr describe-registry --region "${AWS_REGION}" --query 'registryId' --output text 2>/dev/null)
        if [ -z "$ECR_REGISTRY" ] || [ "$ECR_REGISTRY" = "None" ]; then
            echo -e "${RED}Erro: Não foi possível obter o ECR registry!${NC}"
            echo "Verifique se o AWS CLI está configurado e se você tem permissões para acessar o ECR."
            exit 1
        fi
        ECR_REGISTRY="${ECR_REGISTRY}.dkr.ecr.${AWS_REGION}.amazonaws.com"
        echo "ECR Registry: ${ECR_REGISTRY}"
    fi
}

# Função para verificar se AWS CLI está configurado
check_aws_cli() {
    if ! command -v aws &> /dev/null; then
        echo -e "${RED}Erro: AWS CLI não está instalado!${NC}"
        exit 1
    fi
    
    if ! aws sts get-caller-identity &> /dev/null; then
        echo -e "${RED}Erro: AWS CLI não está configurado ou credenciais inválidas!${NC}"
        echo "Execute: aws configure"
        exit 1
    fi
}

# Função para listar imagens latest
list_latest_images() {
    echo -e "${GREEN}Procurando imagens com tag 'latest'...${NC}"
    echo ""
    
    local latest_images
    latest_images=$(aws ecr list-images \
        --repository-name "${IMAGE_NAME}" \
        --region "${AWS_REGION}" \
        --query "imageIds[?imageTag=='latest']" \
        --output json 2>/dev/null || echo "[]")
    
    local count=$(echo "$latest_images" | jq '. | length')
    
    if [ "$count" -gt 0 ]; then
        echo "Encontradas ${count} imagem(ns) com tag 'latest':"
        echo "$latest_images" | jq -r '.[] | "  - " + .imageTag + " (Digest: " + .imageDigest + ")"'
        return 0
    else
        echo "Nenhuma imagem com tag 'latest' encontrada."
        return 1
    fi
}

# Função para remover imagens latest
remove_latest_images() {
    echo -e "${YELLOW}Removendo imagens com tag 'latest'...${NC}"
    echo ""
    
    local latest_images
    latest_images=$(aws ecr list-images \
        --repository-name "${IMAGE_NAME}" \
        --region "${AWS_REGION}" \
        --query "imageIds[?imageTag=='latest']" \
        --output json 2>/dev/null || echo "[]")
    
    local count=$(echo "$latest_images" | jq '. | length')
    
    if [ "$count" -eq 0 ]; then
        echo "Nenhuma imagem 'latest' para remover."
        return 0
    fi
    
    echo "Removendo ${count} imagem(ns) 'latest'..."
    
    # Remover imagens latest
    if aws ecr batch-delete-image \
        --repository-name "${IMAGE_NAME}" \
        --region "${AWS_REGION}" \
        --image-ids "$latest_images" \
        --output text > /dev/null 2>&1; then
        echo -e "${GREEN}Imagens 'latest' removidas com sucesso!${NC}"
        return 0
    else
        echo -e "${RED}Erro ao remover imagens 'latest'!${NC}"
        return 1
    fi
}

# Função para mostrar economia estimada
show_savings() {
    echo ""
    echo -e "${BLUE}=== ECONOMIA ESTIMADA ===${NC}"
    echo ""
    echo "✅ Redução de custos:"
    echo "  - Menos imagens no ECR"
    echo "  - Menos espaço de armazenamento"
    echo "  - Menos transferência de dados"
    echo ""
    echo "✅ Benefícios:"
    echo "  - Deploy mais rápido"
    echo "  - Menos confusão com tags"
    echo "  - Gestão simplificada"
    echo ""
    echo "💡 Dica: Use tags específicas com timestamp para versionamento"
}

# Função principal
main() {
    show_banner
    check_aws_cli
    get_ecr_registry
    
    echo "Configurações:"
    echo "  Repositório: ${IMAGE_NAME}"
    echo "  Região: ${AWS_REGION}"
    echo "  Registry: ${ECR_REGISTRY}"
    echo ""
    
    # Listar imagens latest
    if list_latest_images; then
        echo ""
        read -p "Deseja remover essas imagens 'latest'? (y/N): " confirm
        if [[ "$confirm" =~ ^[Yy]$ ]]; then
            if remove_latest_images; then
                show_savings
            fi
        else
            echo -e "${YELLOW}Operação cancelada.${NC}"
        fi
    else
        echo -e "${GREEN}Não há imagens 'latest' para remover!${NC}"
        show_savings
    fi
}

# Executar função principal
main "$@"

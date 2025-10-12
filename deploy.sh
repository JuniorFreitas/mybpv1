#!/bin/bash

# Script de Deploy Interativo - MyBP
# Build e Push de imagem Docker para ECS

set -e  # Para o script se houver erro

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configurações padrão
DOCKER_REGISTRY="juniorfreitas"
IMAGE_NAME="mybp"
DEFAULT_TAG="latest"

# Função para exibir banner
show_banner() {
    echo -e "${BLUE}"
    echo "=========================================="
    echo "    MyBP - Script de Build e Deploy"
    echo "=========================================="
    echo -e "${NC}"
}

# Função para exibir menu de opções
show_menu() {
    echo -e "${YELLOW}Escolha o ambiente para build e deploy:${NC}"
    echo ""
    echo "1) Desenvolvimento (dev.mybp.com.br)"
    echo "2) QA/Homologação (qa.mybp.com.br)"
    echo "3) Produção (sistema.mybp.com.br)"
    echo "4) Build customizado"
    echo "5) Sair"
    echo ""
}

# Função para validar se o Docker está rodando
check_docker() {
    if ! docker info > /dev/null 2>&1; then
        echo -e "${RED}Erro: Docker não está rodando ou não está instalado!${NC}"
        exit 1
    fi
}

# Função para validar se o Docker está logado no registry
check_docker_login() {
    if ! docker info | grep -q "Username"; then
        echo -e "${YELLOW}Aviso: Você não está logado no Docker Hub!${NC}"
        echo "Fazendo login no Docker Hub..."
        docker login
    fi
}

# Função para gerar tag baseada no ambiente e timestamp
generate_tag() {
    local environment=$1
    local timestamp=$(date +"%Y%m%d-%H%M%S")
    local git_commit=$(git rev-parse --short HEAD 2>/dev/null || echo "unknown")
    echo "${environment}-${timestamp}-${git_commit}"
}

# Função para preparar assets
prepare_assets() {
    local environment=$1
    
    echo -e "${GREEN}Preparando assets...${NC}"
    
    # Verificar se nvm está disponível
    if command -v nvm &> /dev/null; then
        echo "Usando nvm para definir versão do Node.js..."
        nvm use
    elif [ -f ~/.nvm/nvm.sh ]; then
        echo "Carregando nvm..."
        source ~/.nvm/nvm.sh
        nvm use
    else
        echo -e "${YELLOW}Aviso: nvm não encontrado. Usando Node.js atual.${NC}"
    fi
    
    # Verificar se node_modules existe
    if [ ! -d "node_modules" ]; then
        echo "Instalando dependências npm..."
        npm install
    fi
    
    # Executar build baseado no ambiente
    case $environment in
        "dev")
            echo "Executando npm run dev..."
            npm run dev
            ;;
        "homol")
            echo "Executando npm run homol..."
            npm run homol
            ;;
        "prod")
            echo "Executando npm run prod..."
            npm run prod
            ;;
        *)
            echo "Executando npm run dev (padrão)..."
            npm run dev
            ;;
    esac
    
    echo -e "${GREEN}Assets preparados!${NC}"
}

# Função para build da imagem Docker
build_image() {
    local app_url=$1
    local tag=$2
    local environment=$3
    local full_image_name="${DOCKER_REGISTRY}/${IMAGE_NAME}:${tag}"
    
    echo -e "${GREEN}Construindo imagem Docker...${NC}"
    echo "APP_URL: ${app_url}"
    echo "Tag: ${tag}"
    echo "Ambiente: ${environment}"
    echo "Imagem: ${full_image_name}"
    echo ""
    
    # Preparar assets antes do build
    prepare_assets "${environment}"
    
    # Build da imagem com buildx
    docker buildx build \
        --build-arg APP_URL="${app_url}" \
        --load \
        -t "${full_image_name}" \
        .
    
    # Tag adicional como latest se for produção
    if [[ "$1" == *"sistema.mybp.com.br"* ]]; then
        docker tag "${full_image_name}" "${DOCKER_REGISTRY}/${IMAGE_NAME}:latest"
    fi
    
    echo -e "${GREEN}Build concluído!${NC}"
    return 0
}

# Função para push da imagem
push_image() {
    local tag=$1
    local full_image_name="${DOCKER_REGISTRY}/${IMAGE_NAME}:${tag}"
    
    echo -e "${GREEN}Fazendo push da imagem...${NC}"
    echo "Imagem: ${full_image_name}"
    echo ""
    
    # Push da imagem
    docker push "${full_image_name}"
    
    # Push do latest se for produção
    if [[ "$tag" == *"prod"* ]]; then
        echo "Fazendo push da tag latest..."
        docker push "${DOCKER_REGISTRY}/${IMAGE_NAME}:latest"
    fi
    
    echo -e "${GREEN}Push concluído!${NC}"
    return 0
}

# Função para deploy de desenvolvimento
deploy_dev() {
    local app_url="https://dev.mybp.com.br"
    local tag=$(generate_tag "dev")
    local environment="dev"
    
    echo -e "${GREEN}Iniciando build e push para DESENVOLVIMENTO...${NC}"
    
    check_docker
    check_docker_login
    
    build_image "${app_url}" "${tag}" "${environment}"
    push_image "${tag}"
    
    echo -e "${GREEN}Deploy de desenvolvimento concluído!${NC}"
    echo "Imagem: ${DOCKER_REGISTRY}/${IMAGE_NAME}:${tag}"
    echo "URL: ${app_url}"
}

# Função para deploy de QA/Homologação
deploy_qa() {
    local app_url="https://qa.mybp.com.br"
    local tag=$(generate_tag "homol")
    local environment="homol"
    
    echo -e "${GREEN}Iniciando build e push para QA/HOMOLOGAÇÃO...${NC}"
    
    check_docker
    check_docker_login
    
    build_image "${app_url}" "${tag}" "${environment}"
    push_image "${tag}"
    
    echo -e "${GREEN}Deploy de QA concluído!${NC}"
    echo "Imagem: ${DOCKER_REGISTRY}/${IMAGE_NAME}:${tag}"
    echo "URL: ${app_url}"
}

# Função para deploy de produção
deploy_prod() {
    local app_url="https://sistema.mybp.com.br"
    local tag=$(generate_tag "prod")
    local environment="prod"
    
    echo -e "${RED}ATENÇÃO: Você está prestes a fazer deploy em PRODUÇÃO!${NC}"
    echo "Tem certeza que deseja continuar? (digite 'CONFIRMAR' para prosseguir)"
    read -r confirmation
    
    if [ "$confirmation" != "CONFIRMAR" ]; then
        echo -e "${YELLOW}Deploy de produção cancelado.${NC}"
        return 1
    fi
    
    echo -e "${GREEN}Iniciando build e push para PRODUÇÃO...${NC}"
    
    check_docker
    check_docker_login
    
    build_image "${app_url}" "${tag}" "${environment}"
    push_image "${tag}"
    
    echo -e "${GREEN}Deploy de produção concluído!${NC}"
    echo "Imagem: ${DOCKER_REGISTRY}/${IMAGE_NAME}:${tag}"
    echo "Imagem latest: ${DOCKER_REGISTRY}/${IMAGE_NAME}:latest"
    echo "URL: ${app_url}"
}

# Função para build customizado
deploy_custom() {
    echo -e "${GREEN}Build customizado${NC}"
    echo ""
    
    read -p "Digite a URL da aplicação: " app_url
    read -p "Digite a tag da imagem (ou pressione Enter para usar timestamp): " custom_tag
    read -p "Digite o ambiente (dev/homol/prod): " custom_env
    
    if [ -z "$custom_tag" ]; then
        custom_tag=$(generate_tag "custom")
    fi
    
    if [ -z "$custom_env" ]; then
        custom_env="prod"
    fi
    
    echo -e "${YELLOW}Configuração:${NC}"
    echo "URL: ${app_url}"
    echo "Tag: ${custom_tag}"
    echo "Ambiente: ${custom_env}"
    echo ""
    
    read -p "Deseja continuar? (y/N): " confirm
    if [[ ! "$confirm" =~ ^[Yy]$ ]]; then
        echo -e "${YELLOW}Build cancelado.${NC}"
        return 1
    fi
    
    check_docker
    check_docker_login
    
    build_image "${app_url}" "${custom_tag}" "${custom_env}"
    
    read -p "Deseja fazer push da imagem? (y/N): " push_confirm
    if [[ "$push_confirm" =~ ^[Yy]$ ]]; then
        push_image "${custom_tag}"
    fi
    
    echo -e "${GREEN}Build customizado concluído!${NC}"
    echo "Imagem: ${DOCKER_REGISTRY}/${IMAGE_NAME}:${custom_tag}"
}

# Função principal
main() {
    show_banner
    
    while true; do
        show_menu
        read -p "Digite sua opção (1-5): " choice
        
        case $choice in
            1)
                deploy_dev
                break
                ;;
            2)
                deploy_qa
                break
                ;;
            3)
                deploy_prod
                break
                ;;
            4)
                deploy_custom
                break
                ;;
            5)
                echo -e "${YELLOW}Saindo...${NC}"
                exit 0
                ;;
            *)
                echo -e "${RED}Opção inválida! Tente novamente.${NC}"
                echo ""
                ;;
        esac
    done
}

# Executar função principal
main "$@"
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
AWS_REGION="us-east-1"
ECR_REGISTRY=""
IMAGE_NAME="mybp/sistema"
DEFAULT_TAG="latest"
AUTO_CLEANUP_ECR=false  # true para limpeza automática sem confirmação

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
    echo "5) Limpeza de imagens Docker"
    echo "6) Configurar limpeza automática ECR"
    echo "7) Sair"
    echo ""
    echo -e "${BLUE}Status atual:${NC}"
    echo "  Limpeza automática ECR: $([ "$AUTO_CLEANUP_ECR" = true ] && echo "ATIVADA" || echo "DESATIVADA")"
    echo ""
}

# Função para validar se o Docker está rodando
check_docker() {
    if ! docker info > /dev/null 2>&1; then
        echo -e "${RED}Erro: Docker não está rodando ou não está instalado!${NC}"
        exit 1
    fi
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

# Função para validar se o Docker está logado no ECR
check_ecr_login() {
    if ! command -v aws &> /dev/null; then
        echo -e "${RED}Erro: AWS CLI não está instalado!${NC}"
        exit 1
    fi
    
    if ! aws sts get-caller-identity &> /dev/null; then
        echo -e "${RED}Erro: AWS CLI não está configurado ou credenciais inválidas!${NC}"
        echo "Execute: aws configure"
        exit 1
    fi
    
    get_ecr_registry
    
    echo -e "${GREEN}Fazendo login no ECR...${NC}"
    aws ecr get-login-password --region "${AWS_REGION}" | docker login --username AWS --password-stdin "${ECR_REGISTRY}"
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
    
    get_ecr_registry
    local full_image_name="${ECR_REGISTRY}/${IMAGE_NAME}:${tag}"
    
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
    
    echo -e "${GREEN}Build concluído!${NC}"
    return 0
}

# Função para limpar imagens antigas do ECR
cleanup_ecr_images() {
    local tag=$1
    local environment=$2
    
    get_ecr_registry
    
    echo -e "${YELLOW}Limpando imagens antigas do ECR...${NC}"
    
    # Gerar prefixo baseado no ambiente
    local prefix=""
    case $environment in
        "dev")
            prefix="dev-"
            ;;
        "homol")
            prefix="homol-"
            ;;
        "prod")
            prefix="prod-"
            ;;
        *)
            prefix="custom-"
            ;;
    esac
    
    echo "Procurando imagens com prefixo: ${prefix}"
    
    # Listar imagens do ECR com o prefixo
    local images_to_delete
    images_to_delete=$(aws ecr list-images \
        --repository-name "${IMAGE_NAME}" \
        --region "${AWS_REGION}" \
        --query "imageIds[?starts_with(imageTag, '${prefix}') && imageTag != '${tag}']" \
        --output json 2>/dev/null || echo "[]")
    
    # Verificar se há imagens para remover
    local image_count=$(echo "$images_to_delete" | jq '. | length')
    
    if [ "$image_count" -gt 0 ]; then
        echo "Encontradas ${image_count} imagens antigas para remover:"
        echo "$images_to_delete" | jq -r '.[].imageTag' | while read -r image_tag; do
            echo "  - ${image_tag}"
        done
        
        echo ""
        
        if [ "$AUTO_CLEANUP_ECR" = true ]; then
            echo "Modo automático ativado - removendo imagens antigas..."
            echo "$images_to_delete" | jq -r '.[]' | while read -r image_id; do
                local image_tag=$(echo "$image_id" | jq -r '.imageTag')
                echo "Removendo: ${image_tag}"
                aws ecr batch-delete-image \
                    --repository-name "${IMAGE_NAME}" \
                    --region "${AWS_REGION}" \
                    --image-ids "$image_id" \
                    --output text > /dev/null 2>&1 || echo "Aviso: Não foi possível remover ${image_tag}"
            done
            echo -e "${GREEN}Imagens antigas removidas do ECR!${NC}"
        else
            read -p "Deseja remover essas imagens antigas do ECR? (y/N): " confirm
            if [[ "$confirm" =~ ^[Yy]$ ]]; then
                echo "Removendo imagens antigas..."
                echo "$images_to_delete" | jq -r '.[]' | while read -r image_id; do
                    local image_tag=$(echo "$image_id" | jq -r '.imageTag')
                    echo "Removendo: ${image_tag}"
                    aws ecr batch-delete-image \
                        --repository-name "${IMAGE_NAME}" \
                        --region "${AWS_REGION}" \
                        --image-ids "$image_id" \
                        --output text > /dev/null 2>&1 || echo "Aviso: Não foi possível remover ${image_tag}"
                done
                echo -e "${GREEN}Imagens antigas removidas do ECR!${NC}"
            else
                echo -e "${YELLOW}Remoção de imagens antigas cancelada.${NC}"
            fi
        fi
    else
        echo "Nenhuma imagem antiga encontrada com prefixo '${prefix}'"
    fi
    
    echo ""
}

# Função para push da imagem
push_image() {
    local tag=$1
    local environment=$2
    
    get_ecr_registry
    local full_image_name="${ECR_REGISTRY}/${IMAGE_NAME}:${tag}"
    
    echo -e "${GREEN}Fazendo push da imagem...${NC}"
    echo "Imagem: ${full_image_name}"
    echo ""
    
    # Limpar imagens antigas do ECR antes do push
    cleanup_ecr_images "${tag}" "${environment}"
    
    # Push da imagem
    docker push "${full_image_name}"
    
    echo -e "${GREEN}Push concluído!${NC}"
    return 0
}

# Função para limpar imagens Docker locais
cleanup_local_images() {
    local tag=$1
    
    get_ecr_registry
    local full_image_name="${ECR_REGISTRY}/${IMAGE_NAME}:${tag}"
    
    echo -e "${YELLOW}Limpando imagens Docker locais...${NC}"
    
    # Remover imagem específica
    if docker image inspect "${full_image_name}" > /dev/null 2>&1; then
        echo "Removendo imagem: ${full_image_name}"
        docker rmi "${full_image_name}" || echo "Aviso: Não foi possível remover ${full_image_name}"
    fi
    
    # Limpeza geral de imagens órfãs (dangling images)
    echo "Removendo imagens órfãs..."
    docker image prune -f > /dev/null 2>&1 || echo "Aviso: Não foi possível limpar imagens órfãs"
    
    # Mostrar espaço liberado
    echo -e "${GREEN}Limpeza concluída!${NC}"
    echo "Espaço em disco atual:"
    df -h . | tail -1
}

# Função para deploy de desenvolvimento
deploy_dev() {
    local app_url="https://dev.mybp.com.br"
    local tag=$(generate_tag "dev")
    local environment="dev"
    
    echo -e "${GREEN}Iniciando build e push para DESENVOLVIMENTO...${NC}"
    
    check_docker
    check_ecr_login
    
    build_image "${app_url}" "${tag}" "${environment}"
    push_image "${tag}" "${environment}"
    cleanup_local_images "${tag}"
    
    echo -e "${GREEN}Deploy de desenvolvimento concluído!${NC}"
    get_ecr_registry
    echo "Imagem: ${ECR_REGISTRY}/${IMAGE_NAME}:${tag}"
    echo "URL: ${app_url}"
}

# Função para deploy de QA/Homologação
deploy_qa() {
    local app_url="https://qa.mybp.com.br"
    local tag=$(generate_tag "homol")
    local environment="homol"
    
    echo -e "${GREEN}Iniciando build e push para QA/HOMOLOGAÇÃO...${NC}"
    
    check_docker
    check_ecr_login
    
    build_image "${app_url}" "${tag}" "${environment}"
    push_image "${tag}" "${environment}"
    cleanup_local_images "${tag}"
    
    echo -e "${GREEN}Deploy de QA concluído!${NC}"
    get_ecr_registry
    echo "Imagem: ${ECR_REGISTRY}/${IMAGE_NAME}:${tag}"
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
    check_ecr_login
    
    build_image "${app_url}" "${tag}" "${environment}"
    push_image "${tag}" "${environment}"
    cleanup_local_images "${tag}"
    
    echo -e "${GREEN}Deploy de produção concluído!${NC}"
    get_ecr_registry
    echo "Imagem: ${ECR_REGISTRY}/${IMAGE_NAME}:${tag}"
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
    check_ecr_login
    
    build_image "${app_url}" "${custom_tag}" "${custom_env}"
    
    read -p "Deseja fazer push da imagem? (y/N): " push_confirm
    if [[ "$push_confirm" =~ ^[Yy]$ ]]; then
        push_image "${custom_tag}"
        cleanup_local_images "${custom_tag}"
    fi
    
    echo -e "${GREEN}Build customizado concluído!${NC}"
    get_ecr_registry
    echo "Imagem: ${ECR_REGISTRY}/${IMAGE_NAME}:${custom_tag}"
}

# Função para limpeza manual de imagens
cleanup_manual() {
    echo -e "${GREEN}Limpeza manual de imagens Docker${NC}"
    echo ""
    
    echo "Escolha o tipo de limpeza:"
    echo "1) Limpar todas as imagens MyBP"
    echo "2) Limpar imagens órfãs (dangling)"
    echo "3) Limpeza completa (todas as imagens não utilizadas)"
    echo "4) Voltar"
    echo ""
    
    read -p "Digite sua opção (1-4): " cleanup_choice
    
    case $cleanup_choice in
        1)
            echo -e "${YELLOW}Removendo todas as imagens MyBP...${NC}"
            get_ecr_registry
            docker images "${ECR_REGISTRY}/${IMAGE_NAME}" --format "table {{.Repository}}:{{.Tag}}" | tail -n +2 | while read image; do
                if [ ! -z "$image" ]; then
                    echo "Removendo: $image"
                    docker rmi "$image" 2>/dev/null || echo "Aviso: Não foi possível remover $image"
                fi
            done
            ;;
        2)
            echo -e "${YELLOW}Removendo imagens órfãs...${NC}"
            docker image prune -f
            ;;
        3)
            echo -e "${RED}ATENÇÃO: Isso removerá TODAS as imagens não utilizadas!${NC}"
            read -p "Tem certeza? (digite 'CONFIRMAR'): " confirm
            if [ "$confirm" = "CONFIRMAR" ]; then
                docker system prune -a -f
            else
                echo "Limpeza cancelada."
            fi
            ;;
        4)
            return
            ;;
        *)
            echo -e "${RED}Opção inválida!${NC}"
            ;;
    esac
    
    echo -e "${GREEN}Limpeza concluída!${NC}"
    echo "Espaço em disco atual:"
    df -h . | tail -1
}

# Função para configurar limpeza automática ECR
configure_auto_cleanup() {
    echo -e "${GREEN}Configuração de Limpeza Automática ECR${NC}"
    echo ""
    
    echo "Status atual: $([ "$AUTO_CLEANUP_ECR" = true ] && echo "ATIVADA" || echo "DESATIVADA")"
    echo ""
    
    echo "Opções:"
    echo "1) Ativar limpeza automática (remove imagens antigas sem confirmação)"
    echo "2) Desativar limpeza automática (solicita confirmação antes de remover)"
    echo "3) Voltar"
    echo ""
    
    read -p "Digite sua opção (1-3): " choice
    
    case $choice in
        1)
            AUTO_CLEANUP_ECR=true
            echo -e "${GREEN}Limpeza automática ECR ATIVADA!${NC}"
            echo "As imagens antigas serão removidas automaticamente antes do push."
            ;;
        2)
            AUTO_CLEANUP_ECR=false
            echo -e "${YELLOW}Limpeza automática ECR DESATIVADA!${NC}"
            echo "Será solicitada confirmação antes de remover imagens antigas."
            ;;
        3)
            return
            ;;
        *)
            echo -e "${RED}Opção inválida!${NC}"
            ;;
    esac
    
    echo ""
    echo "Configuração salva para esta sessão."
    echo "Para tornar permanente, edite o arquivo deploy.sh e altere AUTO_CLEANUP_ECR."
}

# Função principal
main() {
    show_banner
    
    while true; do
        show_menu
        read -p "Digite sua opção (1-7): " choice
        
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
                cleanup_manual
                ;;
            6)
                configure_auto_cleanup
                ;;
            7)
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
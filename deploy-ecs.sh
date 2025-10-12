#!/bin/bash

# Script de Deploy no ECS - MyBP
# Atualiza o serviço ECS com a nova imagem

set -e  # Para o script se houver erro

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configurações padrão
AWS_REGION="us-east-1"
DOCKER_REGISTRY="juniorfreitas"
IMAGE_NAME="mybp"

# Função para capturar tag da imagem (via parâmetro ou input)
get_image_tag() {
    # Se foi passado como parâmetro
    if [ $# -gt 0 ] && [ -n "$1" ]; then
        echo "$1"
        return
    fi
    
    # Se há input disponível (pipe)
    if [ -t 0 ]; then
        # Terminal interativo - perguntar
        read -p "Digite a tag da imagem (ou pressione Enter para 'latest'): " image_tag
        echo "${image_tag:-latest}"
    else
        # Input via pipe - ler do stdin
        read image_tag
        echo "${image_tag:-latest}"
    fi
}

# Função para exibir banner
show_banner() {
    echo -e "${BLUE}"
    echo "=========================================="
    echo "    MyBP - Deploy no ECS"
    echo "=========================================="
    echo -e "${NC}"
}

# Função para exibir menu de opções
show_menu() {
    echo -e "${YELLOW}Escolha o cluster ECS para deploy:${NC}"
    echo ""
    echo "1) Desenvolvimento (dev-cluster)"
    echo "2) QA/Homologação (mybpClusterHomol)"
    echo "3) Produção (mybpProd)"
    echo "4) Deploy customizado"
    echo "5) Sair"
    echo ""
}

# Função para validar se AWS CLI está configurado
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

# Função para validar se jq está instalado
check_jq() {
    if ! command -v jq &> /dev/null; then
        echo -e "${RED}Erro: jq não está instalado!${NC}"
        echo "Instale o jq para manipular JSON:"
        echo "  macOS: brew install jq"
        echo "  Ubuntu/Debian: sudo apt-get install jq"
        echo "  CentOS/RHEL: sudo yum install jq"
        exit 1
    fi
}

# Função para listar clusters ECS
list_clusters() {
    echo -e "${GREEN}Listando clusters ECS disponíveis...${NC}"
    aws ecs list-clusters --region "${AWS_REGION}" --query 'clusterArns[*]' --output table
}

# Função para listar serviços de um cluster
list_services() {
    local cluster_name=$1
    echo -e "${GREEN}Listando serviços do cluster ${cluster_name}...${NC}"
    aws ecs list-services --cluster "${cluster_name}" --region "${AWS_REGION}" --query 'serviceArns[*]' --output table
}

# Função para obter a task definition atual
get_current_task_definition() {
    local cluster_name=$1
    local service_name=$2
    
    echo -e "${GREEN}Obtendo task definition atual...${NC}" >&2
    
    # Verificar se o serviço existe
    local service_exists=$(aws ecs describe-services \
        --cluster "${cluster_name}" \
        --services "${service_name}" \
        --region "${AWS_REGION}" \
        --query 'services[0].serviceName' \
        --output text)
    
    if [ "$service_exists" = "None" ] || [ -z "$service_exists" ]; then
        echo -e "${RED}Erro: Serviço '${service_name}' não encontrado no cluster '${cluster_name}'!${NC}" >&2
        echo "Serviços disponíveis no cluster:" >&2
        aws ecs list-services --cluster "${cluster_name}" --region "${AWS_REGION}" --query 'serviceArns[*]' --output table >&2
        exit 1
    fi
    
    # Obter a task definition atual do serviço
    local current_task_def=$(aws ecs describe-services \
        --cluster "${cluster_name}" \
        --services "${service_name}" \
        --region "${AWS_REGION}" \
        --query 'services[0].taskDefinition' \
        --output text)
    
    if [ "$current_task_def" = "None" ] || [ -z "$current_task_def" ]; then
        echo -e "${RED}Erro: Não foi possível obter a task definition atual!${NC}" >&2
        exit 1
    fi
    
    echo "Task definition atual: ${current_task_def}" >&2
    echo "$current_task_def"
}

# Função para criar nova task definition
create_new_task_definition() {
    local current_task_def=$1
    local new_image=$2
    local family_name=$3
    
    echo -e "${GREEN}Criando nova task definition...${NC}" >&2
    
    # Usar o ARN completo para obter a task definition
    local task_def_json=$(aws ecs describe-task-definition \
        --task-definition "${current_task_def}" \
        --region "${AWS_REGION}" \
        --query 'taskDefinition')
    
    if [ "$task_def_json" = "null" ] || [ -z "$task_def_json" ]; then
        echo -e "${RED}Erro: Não foi possível obter os detalhes da task definition!${NC}" >&2
        echo "ARN usado: ${current_task_def}" >&2
        exit 1
    fi
    
    # Criar arquivo temporário com a task definition
    local temp_file=$(mktemp)
    echo "$task_def_json" > "$temp_file"
    
    # Atualizar a imagem na task definition
    local updated_task_def=$(jq --arg image "$new_image" \
        '.containerDefinitions[0].image = $image | 
         del(.taskDefinitionArn, .revision, .status, .requiresAttributes, .placementConstraints, .compatibilities, .registeredAt, .registeredBy)' \
        "$temp_file")
    
    # Registrar nova task definition
    local new_task_def=$(aws ecs register-task-definition \
        --cli-input-json "$updated_task_def" \
        --region "${AWS_REGION}" \
        --query 'taskDefinition.taskDefinitionArn' \
        --output text)
    
    # Limpar arquivo temporário
    rm "$temp_file"
    
    if [ "$new_task_def" = "None" ] || [ -z "$new_task_def" ]; then
        echo -e "${RED}Erro: Falha ao criar nova task definition!${NC}" >&2
        exit 1
    fi
    
    echo "Nova task definition criada: ${new_task_def}" >&2
    echo "$new_task_def"
}

# Função para fazer deploy no ECS
deploy_to_ecs() {
    local cluster_name=$1
    local service_name=$2
    local image_tag=$3
    
    # Verificar se a tag já contém o registry completo
    local full_image_name
    if [[ "$image_tag" == *"${DOCKER_REGISTRY}/${IMAGE_NAME}:"* ]]; then
        full_image_name="$image_tag"
    else
        full_image_name="${DOCKER_REGISTRY}/${IMAGE_NAME}:${image_tag}"
    fi
    
    echo -e "${GREEN}Fazendo deploy no ECS...${NC}"
    echo "Cluster: ${cluster_name}"
    echo "Serviço: ${service_name}"
    echo "Imagem: ${full_image_name}"
    echo ""
    
    # Obter task definition atual
    local current_task_def=$(get_current_task_definition "${cluster_name}" "${service_name}")
    
    # Extrair family name da task definition atual (sem a revisão)
    local family_name=$(echo "$current_task_def" | sed 's/.*task-definition\/\([^:]*\).*/\1/')
    
    echo "Family name extraído: ${family_name}" >&2
    
    # Criar nova task definition com a nova imagem
    local new_task_def=$(create_new_task_definition "${current_task_def}" "${full_image_name}" "${family_name}")
    
    # Atualizar o serviço ECS com a nova task definition
    echo -e "${GREEN}Atualizando serviço com nova task definition...${NC}"
    aws ecs update-service \
        --cluster "${cluster_name}" \
        --service "${service_name}" \
        --task-definition "${new_task_def}" \
        --region "${AWS_REGION}" \
        --query 'service.{serviceName:serviceName,status:status,runningCount:runningCount,pendingCount:pendingCount,taskDefinition:taskDefinition}'
    
    echo -e "${GREEN}Deploy iniciado!${NC}"
    echo "Nova task definition: ${new_task_def}"
    echo "Acompanhe o status do deploy no console AWS ECS."
}

# Função para deploy de desenvolvimento
deploy_dev() {
    local cluster_name="dev-cluster"
    local service_name="mybp-dev-service"
    
    echo -e "${GREEN}Deploy para DESENVOLVIMENTO${NC}"
    echo ""
    
    read -p "Digite a tag da imagem (ou pressione Enter para 'latest'): " image_tag
    if [ -z "$image_tag" ]; then
        image_tag="latest"
    fi
    
    # Garantir que a tag não contenha o registry completo
    if [[ "$image_tag" == *"${DOCKER_REGISTRY}/${IMAGE_NAME}:"* ]]; then
        image_tag=$(echo "$image_tag" | sed "s/.*${DOCKER_REGISTRY}\/${IMAGE_NAME}://")
    fi
    
    check_aws_cli
    check_jq
    deploy_to_ecs "${cluster_name}" "${service_name}" "${image_tag}"
}

# Função para deploy de QA
deploy_qa() {
    local cluster_name="mybpClusterHomol"
    local service_name="homol-service"
    
    echo -e "${GREEN}Deploy para QA/HOMOLOGAÇÃO${NC}"
    echo ""
    
    read -p "Digite a tag da imagem (ou pressione Enter para 'latest'): " image_tag
    if [ -z "$image_tag" ]; then
        image_tag="latest"
    fi
    
    # Garantir que a tag não contenha o registry completo
    if [[ "$image_tag" == *"${DOCKER_REGISTRY}/${IMAGE_NAME}:"* ]]; then
        image_tag=$(echo "$image_tag" | sed "s/.*${DOCKER_REGISTRY}\/${IMAGE_NAME}://")
    fi
    
    check_aws_cli
    check_jq
    deploy_to_ecs "${cluster_name}" "${service_name}" "${image_tag}"
}

# Função para deploy de produção
deploy_prod() {
    local cluster_name="MyBPClusterProd"
    local service_name="mybp-prod-service"
    
    echo -e "${RED}ATENÇÃO: Deploy em PRODUÇÃO!${NC}"
    echo "Tem certeza que deseja continuar? (digite 'CONFIRMAR' para prosseguir)"
    read -r confirmation
    
    if [ "$confirmation" != "CONFIRMAR" ]; then
        echo -e "${YELLOW}Deploy de produção cancelado.${NC}"
        return 1
    fi
    
    echo -e "${GREEN}Deploy para PRODUÇÃO${NC}"
    echo ""
    
    read -p "Digite a tag da imagem (ou pressione Enter para 'latest'): " image_tag
    if [ -z "$image_tag" ]; then
        image_tag="latest"
    fi
    
    check_aws_cli
    check_jq
    deploy_to_ecs "${cluster_name}" "${service_name}" "${image_tag}"
}

# Função para deploy customizado
deploy_custom() {
    echo -e "${GREEN}Deploy customizado${NC}"
    echo ""
    
    check_aws_cli
    check_jq
    
    echo "Clusters disponíveis:"
    list_clusters
    echo ""
    
    read -p "Digite o nome do cluster: " cluster_name
    read -p "Digite o nome do serviço: " service_name
    read -p "Digite a tag da imagem: " image_tag
    
    echo -e "${YELLOW}Configuração:${NC}"
    echo "Cluster: ${cluster_name}"
    echo "Serviço: ${service_name}"
    echo "Imagem: ${DOCKER_REGISTRY}/${IMAGE_NAME}:${image_tag}"
    echo ""
    
    read -p "Deseja continuar? (y/N): " confirm
    if [[ ! "$confirm" =~ ^[Yy]$ ]]; then
        echo -e "${YELLOW}Deploy cancelado.${NC}"
        return 1
    fi
    
    deploy_to_ecs "${cluster_name}" "${service_name}" "${image_tag}"
}

# Função para monitorar o deploy
monitor_deployment() {
    local cluster_name=$1
    local service_name=$2
    
    echo -e "${GREEN}Monitorando deploy...${NC}"
    echo "Pressione Ctrl+C para parar o monitoramento"
    echo ""
    
    while true; do
        aws ecs describe-services \
            --cluster "${cluster_name}" \
            --services "${service_name}" \
            --region "${AWS_REGION}" \
            --query 'services[0].{serviceName:serviceName,status:status,runningCount:runningCount,pendingCount:pendingCount,deployments:deployments[0].{status:status,rolloutState:rolloutState}}' \
            --output table
        
        sleep 10
    done
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

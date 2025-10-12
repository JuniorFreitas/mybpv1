#!/bin/bash

# Script para limpar Task Definitions antigas no ECS
# Mantém apenas as 3 mais recentes

set -e  # Para o script se houver erro

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configurações
AWS_REGION="us-east-1"
TASK_DEFINITION_FAMILY="mybp-sistema"
KEEP_COUNT=3

# Função para exibir banner
show_banner() {
    echo -e "${BLUE}"
    echo "=========================================="
    echo "    Limpeza de Task Definitions ECS"
    echo "=========================================="
    echo -e "${NC}"
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

# Função para listar task definitions
list_task_definitions() {
    echo -e "${GREEN}Listando Task Definitions da família: ${TASK_DEFINITION_FAMILY}${NC}"
    echo ""
    
    aws ecs list-task-definitions \
        --family-prefix "${TASK_DEFINITION_FAMILY}" \
        --region "${AWS_REGION}" \
        --query 'taskDefinitionArns' \
        --output table
}

# Função para obter task definitions ordenadas por data
get_task_definitions_sorted() {
    aws ecs list-task-definitions \
        --family-prefix "${TASK_DEFINITION_FAMILY}" \
        --region "${AWS_REGION}" \
        --query 'taskDefinitionArns' \
        --output text | tr '\t' '\n' | sort -V
}

# Função para obter detalhes de uma task definition
get_task_definition_details() {
    local task_def_arn=$1
    aws ecs describe-task-definition \
        --task-definition "${task_def_arn}" \
        --region "${AWS_REGION}" \
        --query 'taskDefinition.{revision:revision,status:status,registeredAt:registeredAt}' \
        --output json
}

# Função para fazer a limpeza
cleanup_task_definitions() {
    echo -e "${YELLOW}Iniciando limpeza de Task Definitions...${NC}"
    echo ""
    
    # Obter todas as task definitions ordenadas
    local all_task_defs=($(get_task_definitions_sorted))
    local total_count=${#all_task_defs[@]}
    
    echo "Total de Task Definitions encontradas: ${total_count}"
    echo "Mantendo as ${KEEP_COUNT} mais recentes..."
    echo ""
    
    if [ $total_count -le $KEEP_COUNT ]; then
        echo -e "${GREEN}Não há Task Definitions para remover.${NC}"
        echo "Total: ${total_count} | Manter: ${KEEP_COUNT}"
        return 0
    fi
    
    # Calcular quantas remover
    local to_remove=$((total_count - KEEP_COUNT))
    echo "Task Definitions a serem removidas: ${to_remove}"
    echo ""
    
    # Mostrar quais serão mantidas
    echo -e "${GREEN}Task Definitions que serão MANTIDAS:${NC}"
    for ((i=to_remove; i<total_count; i++)); do
        local task_def_arn="${all_task_defs[$i]}"
        local task_def_name=$(basename "$task_def_arn")
        echo "  ✓ ${task_def_name}"
    done
    echo ""
    
    # Mostrar quais serão removidas
    echo -e "${RED}Task Definitions que serão REMOVIDAS:${NC}"
    for ((i=0; i<to_remove; i++)); do
        local task_def_arn="${all_task_defs[$i]}"
        local task_def_name=$(basename "$task_def_arn")
        echo "  ✗ ${task_def_name}"
    done
    echo ""
    
    # Confirmar antes de remover
    read -p "Deseja continuar com a remoção? (y/N): " confirm
    if [[ ! "$confirm" =~ ^[Yy]$ ]]; then
        echo -e "${YELLOW}Operação cancelada.${NC}"
        return 0
    fi
    
    # Remover task definitions antigas
    local removed_count=0
    for ((i=0; i<to_remove; i++)); do
        local task_def_arn="${all_task_defs[$i]}"
        local task_def_name=$(basename "$task_def_arn")
        
        echo -n "Removendo ${task_def_name}... "
        
        if aws ecs deregister-task-definition \
            --task-definition "${task_def_arn}" \
            --region "${AWS_REGION}" \
            --output text > /dev/null 2>&1; then
            echo -e "${GREEN}✓${NC}"
            ((removed_count++))
        else
            echo -e "${RED}✗${NC}"
        fi
    done
    
    echo ""
    echo -e "${GREEN}Limpeza concluída!${NC}"
    echo "Task Definitions removidas: ${removed_count}"
    echo "Task Definitions mantidas: $((total_count - removed_count))"
}

# Função para mostrar resumo final
show_final_summary() {
    echo ""
    echo -e "${BLUE}=== RESUMO FINAL ===${NC}"
    echo ""
    
    # Listar task definitions restantes
    echo "Task Definitions restantes:"
    get_task_definitions_sorted | while read -r task_def_arn; do
        if [ ! -z "$task_def_arn" ]; then
            local task_def_name=$(basename "$task_def_arn")
            local details=$(get_task_definition_details "$task_def_arn")
            local revision=$(echo "$details" | jq -r '.revision')
            local status=$(echo "$details" | jq -r '.status')
            local registered_at=$(echo "$details" | jq -r '.registeredAt')
            
            echo "  • ${task_def_name} (revisão: ${revision}, status: ${status})"
        fi
    done
}

# Função principal
main() {
    show_banner
    check_aws_cli
    
    echo "Configurações:"
    echo "  Família: ${TASK_DEFINITION_FAMILY}"
    echo "  Região: ${AWS_REGION}"
    echo "  Manter: ${KEEP_COUNT} mais recentes"
    echo ""
    
    # Mostrar task definitions atuais
    list_task_definitions
    
    echo ""
    read -p "Deseja continuar com a limpeza? (y/N): " confirm
    if [[ ! "$confirm" =~ ^[Yy]$ ]]; then
        echo -e "${YELLOW}Operação cancelada.${NC}"
        exit 0
    fi
    
    cleanup_task_definitions
    show_final_summary
}

# Executar função principal
main "$@"

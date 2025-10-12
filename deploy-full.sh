#!/bin/bash

# Script Completo de Deploy - MyBP
# Combina build, push e deploy no ECS

set -e  # Para o script se houver erro

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Função para exibir banner
show_banner() {
    echo -e "${BLUE}"
    echo "=========================================="
    echo "    MyBP - Deploy Completo"
    echo "=========================================="
    echo -e "${NC}"
}

# Função para exibir menu de opções
show_menu() {
    echo -e "${YELLOW}Escolha o tipo de deploy:${NC}"
    echo ""
    echo "1) Build e Push apenas (sem deploy ECS)"
    echo "2) Deploy completo (Build + Push + ECS)"
    echo "3) Deploy ECS apenas (imagem já existe)"
    echo "4) Limpeza de Task Definitions (manter 3 mais recentes)"
    echo "5) Limpeza de imagens 'latest' ECR (reduzir custos)"
    echo "6) Sair"
    echo ""
}

# Função para executar build e push
run_build_push() {
    echo -e "${GREEN}Executando build e push...${NC}"
    ./.deploy/scripts/deploy.sh
}

# Função para executar deploy ECS
run_ecs_deploy() {
    echo -e "${GREEN}Executando deploy no ECS...${NC}"
    ./.deploy/scripts/deploy-ecs.sh
}

# Função para executar limpeza de task definitions
run_cleanup_task_definitions() {
    echo -e "${GREEN}Executando limpeza de Task Definitions...${NC}"
    ./.deploy/scripts/cleanup-task-definitions.sh
}

# Função para executar limpeza de imagens latest
run_cleanup_latest_images() {
    echo -e "${GREEN}Executando limpeza de imagens 'latest' ECR...${NC}"
    ./.deploy/scripts/cleanup-latest-images.sh
}

# Função para deploy completo
run_full_deploy() {
    echo -e "${GREEN}Executando deploy completo...${NC}"
    echo ""
    
    # Primeiro: Build e Push
    echo -e "${YELLOW}Passo 1: Build e Push da imagem${NC}"
    ./.deploy/scripts/deploy.sh
    
    echo ""
    echo -e "${YELLOW}Passo 2: Deploy no ECS${NC}"
    read -p "Deseja continuar com o deploy no ECS? (y/N): " confirm
    if [[ "$confirm" =~ ^[Yy]$ ]]; then
        ./.deploy/scripts/deploy-ecs.sh
    else
        echo -e "${YELLOW}Deploy ECS cancelado.${NC}"
    fi
}

# Função para verificar se os scripts existem
check_scripts() {
    if [ ! -f "./.deploy/scripts/deploy.sh" ]; then
        echo -e "${RED}Erro: Script .deploy/scripts/deploy.sh não encontrado!${NC}"
        exit 1
    fi
    
    if [ ! -f "./.deploy/scripts/deploy-ecs.sh" ]; then
        echo -e "${RED}Erro: Script .deploy/scripts/deploy-ecs.sh não encontrado!${NC}"
        exit 1
    fi
    
    if [ ! -f "./.deploy/scripts/cleanup-task-definitions.sh" ]; then
        echo -e "${RED}Erro: Script .deploy/scripts/cleanup-task-definitions.sh não encontrado!${NC}"
        exit 1
    fi
    
    if [ ! -f "./.deploy/scripts/cleanup-latest-images.sh" ]; then
        echo -e "${RED}Erro: Script .deploy/scripts/cleanup-latest-images.sh não encontrado!${NC}"
        exit 1
    fi
    
    # Tornar scripts executáveis
    chmod +x ./.deploy/scripts/deploy.sh
    chmod +x ./.deploy/scripts/deploy-ecs.sh
    chmod +x ./.deploy/scripts/cleanup-task-definitions.sh
    chmod +x ./.deploy/scripts/cleanup-latest-images.sh
}

# Função principal
main() {
    show_banner
    check_scripts
    
    while true; do
        show_menu
        read -p "Digite sua opção (1-6): " choice
        
        case $choice in
            1)
                run_build_push
                break
                ;;
            2)
                run_full_deploy
                break
                ;;
            3)
                run_ecs_deploy
                break
                ;;
            4)
                run_cleanup_task_definitions
                break
                ;;
            5)
                run_cleanup_latest_images
                break
                ;;
            6)
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

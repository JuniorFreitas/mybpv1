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
    echo "4) Sair"
    echo ""
}

# Função para executar build e push
run_build_push() {
    echo -e "${GREEN}Executando build e push...${NC}"
    ./deploy.sh
}

# Função para executar deploy ECS
run_ecs_deploy() {
    echo -e "${GREEN}Executando deploy no ECS...${NC}"
    ./deploy-ecs.sh
}

# Função para deploy completo
run_full_deploy() {
    echo -e "${GREEN}Executando deploy completo...${NC}"
    echo ""
    
    # Primeiro: Build e Push
    echo -e "${YELLOW}Passo 1: Build e Push da imagem${NC}"
    ./deploy.sh
    
    echo ""
    echo -e "${YELLOW}Passo 2: Deploy no ECS${NC}"
    read -p "Deseja continuar com o deploy no ECS? (y/N): " confirm
    if [[ "$confirm" =~ ^[Yy]$ ]]; then
        ./deploy-ecs.sh
    else
        echo -e "${YELLOW}Deploy ECS cancelado.${NC}"
    fi
}

# Função para verificar se os scripts existem
check_scripts() {
    if [ ! -f "./deploy.sh" ]; then
        echo -e "${RED}Erro: Script deploy.sh não encontrado!${NC}"
        exit 1
    fi
    
    if [ ! -f "./deploy-ecs.sh" ]; then
        echo -e "${RED}Erro: Script deploy-ecs.sh não encontrado!${NC}"
        exit 1
    fi
    
    # Tornar scripts executáveis
    chmod +x ./deploy.sh
    chmod +x ./deploy-ecs.sh
}

# Função principal
main() {
    show_banner
    check_scripts
    
    while true; do
        show_menu
        read -p "Digite sua opção (1-4): " choice
        
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

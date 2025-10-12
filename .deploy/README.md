# Scripts de Deploy - MyBP

Esta pasta contém todos os scripts auxiliares para deploy e manutenção do sistema MyBP.

## 📁 Estrutura

```
.deploy/
├── README.md                        # Esta documentação
└── scripts/                         # Pasta com todos os scripts
    ├── deploy.sh                    # Script principal de build e push
    ├── deploy-ecs.sh                # Script de deploy no ECS
    ├── cleanup-task-definitions.sh  # Limpeza de Task Definitions ECS
    └── cleanup-latest-images.sh     # Limpeza de imagens 'latest' ECR
```

## 🚀 Scripts Disponíveis

### 1. `deploy.sh`
**Função**: Build e push de imagens Docker para ECR
- ✅ Build de imagens com buildx
- ✅ Push para ECR
- ✅ Limpeza automática de imagens locais
- ✅ Suporte a múltiplos ambientes (dev, qa, prod)

### 2. `deploy-ecs.sh`
**Função**: Deploy de imagens no Amazon ECS
- ✅ Atualização de serviços ECS
- ✅ Verificação de saúde
- ✅ Rollback automático em caso de erro

### 3. `cleanup-task-definitions.sh`
**Função**: Limpeza de Task Definitions antigas
- ✅ Mantém apenas as 3 mais recentes
- ✅ Desregistra versões antigas
- ✅ Reduz custos de armazenamento

### 4. `cleanup-latest-images.sh`
**Função**: Limpeza de imagens 'latest' ECR
- ✅ Remove imagens com tag 'latest'
- ✅ Reduz custos de armazenamento
- ✅ Interface amigável com confirmação

## 🎯 Como Usar

### Opção 1: Menu Principal (Recomendado)
```bash
# Na raiz do projeto
./deploy-full.sh
```

### Opção 2: Execução Direta
```bash
# Build e push
./.deploy/scripts/deploy.sh

# Deploy ECS
./.deploy/scripts/deploy-ecs.sh

# Limpeza de Task Definitions
./.deploy/scripts/cleanup-task-definitions.sh

# Limpeza de imagens latest
./.deploy/scripts/cleanup-latest-images.sh
```

## ⚙️ Configurações

### Variáveis de Ambiente
```bash
# AWS
AWS_REGION="us-east-1"
AWS_ACCOUNT_ID="37099"

# ECR
ECR_REGISTRY="37099.dkr.ecr.us-east-1.amazonaws.com"
IMAGE_NAME="mybp/sistema"

# ECS
CLUSTER_NAME="mybp-cluster"
SERVICE_NAME="mybp-service"
```

### Permissões AWS Necessárias
- ECR: `ecr:*`
- ECS: `ecs:*`
- IAM: `iam:PassRole`

## 🔧 Manutenção

### Tornar Scripts Executáveis
```bash
chmod +x ./.deploy/scripts/*.sh
```

### Verificar Dependências
```bash
# AWS CLI
aws --version

# Docker
docker --version

# jq (para processamento JSON)
jq --version
```

## 📚 Documentação Completa

Para documentação detalhada de cada script, consulte:
- `docs/README.md` - Índice geral da documentação
- `docs/LIMPEZA_TASK_DEFINITIONS.md` - Task Definitions
- `docs/LIMPEZA_IMAGENS_LATEST.md` - Imagens ECR

## 🚨 Troubleshooting

### Erro: Script não encontrado
```bash
# Verificar se está na raiz do projeto
pwd
# Deve mostrar: /caminho/para/mybpdeploy

# Verificar se a pasta .deploy/scripts existe
ls -la .deploy/scripts/
```

### Erro: Permissões negadas
```bash
# Tornar executável
chmod +x ./.deploy/scripts/*.sh
```

### Erro: AWS CLI não configurado
```bash
# Configurar credenciais
aws configure
```

## 🔄 Atualizações

Quando adicionar novos scripts:
1. Coloque na pasta `.deploy/scripts/`
2. Torne executável: `chmod +x ./.deploy/scripts/novo-script.sh`
3. Atualize o `deploy-full.sh` se necessário
4. Documente no `README.md` desta pasta

---

**Nota**: Use sempre o `deploy-full.sh` na raiz para uma experiência completa e organizada.

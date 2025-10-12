# Scripts de Deploy - MyBP

Este conjunto de scripts automatiza o processo de build, push e deploy da aplicação MyBP.

## Scripts Disponíveis

### 1. `deploy.sh` - Build e Push
Script principal para build e push de imagens Docker.

**Funcionalidades:**
- Build de imagem Docker com `docker buildx`
- Push automático para Docker Hub
- Suporte a múltiplos ambientes (dev, qa, prod)
- Geração automática de tags com timestamp e commit
- Validações de Docker e login
- **Preparação automática de assets** (nvm use + npm run)
- **Build otimizado por ambiente** (dev/homol/prod)

**Uso:**
```bash
./deploy.sh
```

**Opções:**
1. Desenvolvimento (dev.mybp.com.br)
2. QA/Homologação (qa.mybp.com.br)
3. Produção (sistema.mybp.com.br)
4. Build customizado

### 2. `deploy-ecs.sh` - Deploy no ECS
Script para deploy no Amazon ECS.

**Funcionalidades:**
- Deploy automático no ECS
- **Criação automática de nova task definition** baseada na anterior
- **Atualização apenas da imagem** mantendo todas as outras configurações
- Suporte a múltiplos clusters
- Validação de credenciais AWS e dependências
- Monitoramento de status do deploy

**Uso:**
```bash
./deploy-ecs.sh
```

**Pré-requisitos:**
- AWS CLI configurado
- Credenciais AWS válidas
- Clusters ECS configurados
- **jq instalado** (para manipulação de JSON)

### 3. `deploy-full.sh` - Deploy Completo
Script que combina build, push e deploy ECS.

**Funcionalidades:**
- Executa build e push
- Executa deploy no ECS
- Opção de executar apenas uma parte do processo

**Uso:**
```bash
./deploy-full.sh
```

## Configuração

### 1. Docker Hub
Certifique-se de estar logado no Docker Hub:
```bash
docker login
```

### 2. AWS CLI
Configure as credenciais AWS:
```bash
aws configure
```

### 3. jq (JSON processor)
Instale o jq para manipulação de JSON:
```bash
# macOS
brew install jq

# Ubuntu/Debian
sudo apt-get install jq

# CentOS/RHEL
sudo yum install jq
```

### 4. Permissões de Execução
Os scripts são automaticamente configurados como executáveis, mas se necessário:
```bash
chmod +x deploy.sh
chmod +x deploy-ecs.sh
chmod +x deploy-full.sh
```

## Configurações Personalizáveis

### Variáveis no `deploy.sh`:
```bash
DOCKER_REGISTRY="juniorfreitas"  # Seu usuário Docker Hub
IMAGE_NAME="mybp"                # Nome da imagem
DEFAULT_TAG="latest"             # Tag padrão
```

### Variáveis no `deploy-ecs.sh`:
```bash
AWS_REGION="us-east-1"           # Região AWS
DOCKER_REGISTRY="juniorfreitas"  # Usuário Docker Hub
IMAGE_NAME="mybp"                # Nome da imagem
```

## Fluxo de Build

O script agora executa automaticamente:

1. **nvm use** - Define a versão correta do Node.js (v14.21.3)
2. **npm install** - Instala dependências (se necessário)
3. **npm run [ambiente]** - Build dos assets:
   - `dev` → `npm run dev`
   - `homol` → `npm run homol` 
   - `prod` → `npm run prod`
4. **docker buildx build** - Build da imagem Docker
5. **docker push** - Push para Docker Hub

## Como Funciona o Deploy ECS

O script do ECS agora cria automaticamente uma nova task definition:

1. **Obtém a task definition atual** do serviço ECS
2. **Cria uma cópia** da task definition existente
3. **Atualiza apenas a imagem** para a nova versão
4. **Registra a nova task definition** no ECS
5. **Atualiza o serviço** para usar a nova task definition

Isso garante que todas as configurações (CPU, memória, variáveis de ambiente, etc.) sejam mantidas, alterando apenas a imagem Docker.

## Exemplos de Uso

### Deploy Rápido para QA:
```bash
./deploy.sh
# Escolher opção 2 (QA/Homologação)
# Executa: nvm use → npm run homol → docker build → docker push
```

### Deploy Completo para Produção:
```bash
./deploy-full.sh
# Escolher opção 2 (Deploy completo)
# Escolher opção 3 (Produção) no build
# Escolher opção 3 (Produção) no ECS
```

### Apenas Deploy ECS (imagem já existe):
```bash
./deploy-ecs.sh
# Escolher opção 3 (Produção)
# Informar a tag da imagem
```

## Tags Geradas Automaticamente

As tags são geradas no formato:
```
{ambiente}-{timestamp}-{commit}
```

Exemplo:
- `dev-20250115-143022-a1b2c3d`
- `qa-20250115-143022-a1b2c3d`
- `prod-20250115-143022-a1b2c3d`

Para produção, também é criada uma tag `latest`.

## Troubleshooting

### Erro de Docker não encontrado:
```bash
# Verificar se Docker está rodando
docker info
```

### Erro de AWS CLI:
```bash
# Verificar credenciais
aws sts get-caller-identity
```

### Erro de permissão:
```bash
# Tornar scripts executáveis
chmod +x *.sh
```

## Estrutura de Arquivos

```
mybpdeploy/
├── deploy.sh          # Build e Push
├── deploy-ecs.sh      # Deploy ECS
├── deploy-full.sh     # Deploy completo
├── Dockerfile         # Imagem Docker
├── docker-compose.yml # Compose local
└── README-DEPLOY.md   # Esta documentação
```

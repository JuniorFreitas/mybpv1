# Limpeza Automática de Imagens ECR

## Problema
As imagens Docker no ECR vão acumulando a cada deploy, ocupando espaço e gerando custos desnecessários. É importante manter apenas as versões mais recentes de cada ambiente.

## Solução Implementada

### Limpeza Automática por Prefixo
- **Função**: `cleanup_ecr_images()` no script `deploy.sh`
- **Execução**: Antes de cada push para o ECR
- **Inteligente**: Remove apenas imagens com o mesmo prefixo do ambiente
- **Configurável**: Modo automático ou com confirmação

### Como Funciona

#### 1. Identificação por Ambiente
```bash
# Prefixos por ambiente:
dev-     # Desenvolvimento
homol-   # QA/Homologação  
prod-    # Produção
custom-  # Build customizado
```

#### 2. Busca Inteligente
```bash
# Lista imagens com prefixo específico (exceto a atual)
aws ecr list-images \
  --repository-name "mybp/sistema" \
  --query "imageIds[?starts_with(imageTag, 'dev-') && imageTag != 'dev-20241212-143022-abc123']"
```

#### 3. Remoção Segura
```bash
# Remove imagens antigas em lote
aws ecr batch-delete-image \
  --repository-name "mybp/sistema" \
  --image-ids '[{"imageTag":"dev-20241211-120000-xyz789"}]'
```

## Configuração

### Modo Automático (Recomendado)
```bash
# No arquivo deploy.sh
AUTO_CLEANUP_ECR=true
```

### Modo Interativo (Padrão)
```bash
# No arquivo deploy.sh
AUTO_CLEANUP_ECR=false
```

### Configuração via Menu
```bash
./deploy.sh
# Escolha opção 6: Configurar limpeza automática ECR
```

## Exemplo de Execução

### Antes da Limpeza
```
Limpando imagens antigas do ECR...
Procurando imagens com prefixo: dev-

Encontradas 3 imagens antigas para remover:
  - dev-20241210-100000-abc123
  - dev-20241209-150000-def456
  - dev-20241208-090000-ghi789

Modo automático ativado - removendo imagens antigas...
Removendo: dev-20241210-100000-abc123
Removendo: dev-20241209-150000-def456
Removendo: dev-20241208-090000-ghi789
Imagens antigas removidas do ECR!
```

### Após a Limpeza
```
Fazendo push da imagem...
Imagem: 123456789.dkr.ecr.us-east-1.amazonaws.com/mybp/sistema:dev-20241212-143022-xyz123

Push concluído!
```

## Benefícios

### ✅ **Economia de Custos**
- Remove imagens antigas automaticamente
- Reduz espaço ocupado no ECR
- Evita acúmulo desnecessário

### ✅ **Organização por Ambiente**
- Remove apenas imagens do mesmo ambiente
- Mantém separação dev/qa/prod
- Preserva imagens de outros ambientes

### ✅ **Segurança**
- Não remove a imagem atual sendo enviada
- Confirmação opcional antes de remover
- Logs detalhados de todas as operações

### ✅ **Flexibilidade**
- Modo automático para CI/CD
- Modo interativo para desenvolvimento
- Configuração fácil via menu

## Uso por Ambiente

### Desenvolvimento
```bash
# Prefixo: dev-
# Remove: dev-20241210-*, dev-20241209-*, etc.
# Mantém: dev-20241212-143022-xyz123 (atual)
```

### QA/Homologação
```bash
# Prefixo: homol-
# Remove: homol-20241210-*, homol-20241209-*, etc.
# Mantém: homol-20241212-143022-xyz123 (atual)
```

### Produção
```bash
# Prefixo: prod-
# Remove: prod-20241210-*, prod-20241209-*, etc.
# Mantém: prod-20241212-143022-xyz123 (atual)
# Também mantém: latest
```

## Configurações Avançadas

### Alterar Prefixos
```bash
# No arquivo deploy.sh, função cleanup_ecr_images()
case $environment in
    "dev")
        prefix="development-"  # Novo prefixo
        ;;
    "homol")
        prefix="staging-"      # Novo prefixo
        ;;
    "prod")
        prefix="production-"   # Novo prefixo
        ;;
esac
```

### Adicionar Novos Ambientes
```bash
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
    "test")  # Novo ambiente
        prefix="test-"
        ;;
    "staging")  # Novo ambiente
        prefix="staging-"
        ;;
esac
```

### Configuração Permanente
```bash
# Editar deploy.sh
AUTO_CLEANUP_ECR=true  # Sempre automático
```

## Monitoramento

### Verificar Imagens no ECR
```bash
# Listar todas as imagens
aws ecr list-images \
  --repository-name "mybp/sistema" \
  --region "us-east-1"

# Listar apenas imagens de desenvolvimento
aws ecr list-images \
  --repository-name "mybp/sistema" \
  --region "us-east-1" \
  --query "imageIds[?starts_with(imageTag, 'dev-')]"
```

### Verificar Tamanho do Repositório
```bash
# Estimar tamanho total
aws ecr describe-images \
  --repository-name "mybp/sistema" \
  --region "us-east-1" \
  --query 'imageDetails[].imageSizeInBytes' \
  --output text | awk '{sum+=$1} END {print "Total: " sum/1024/1024/1024 " GB"}'
```

## Troubleshooting

### Erro: "Repository not found"
- Verifique se o repositório ECR existe
- Confirme o nome: `mybp/sistema`
- Verifique a região: `us-east-1`

### Erro: "Access denied"
- Verifique credenciais AWS
- Confirme permissões ECR
- Execute `aws configure`

### Erro: "jq command not found"
- Instale jq: `brew install jq` (macOS) ou `apt install jq` (Ubuntu)
- Ou use sem jq (funcionalidade limitada)

### Imagens não são removidas
- Verifique se o prefixo está correto
- Confirme se há imagens com o prefixo
- Verifique logs de erro

## Boas Práticas

### 1. **Usar Modo Automático em CI/CD**
```bash
# No pipeline de CI/CD
AUTO_CLEANUP_ECR=true
./deploy.sh
```

### 2. **Usar Modo Interativo em Desenvolvimento**
```bash
# Para desenvolvimento local
AUTO_CLEANUP_ECR=false
./deploy.sh
```

### 3. **Monitorar Regularmente**
- Verificar tamanho do repositório
- Acompanhar custos ECR
- Revisar imagens mantidas

### 4. **Testar Antes de Produção**
- Testar em ambiente de desenvolvimento
- Verificar se prefixos estão corretos
- Confirmar que não remove imagens importantes

## Integração com CI/CD

### GitHub Actions
```yaml
- name: Deploy with ECR cleanup
  env:
    AUTO_CLEANUP_ECR: true
  run: ./deploy.sh
```

### GitLab CI
```yaml
deploy:
  script:
    - export AUTO_CLEANUP_ECR=true
    - ./deploy.sh
```

### Jenkins
```groovy
pipeline {
    environment {
        AUTO_CLEANUP_ECR = 'true'
    }
    stages {
        stage('Deploy') {
            steps {
                sh './deploy.sh'
            }
        }
    }
}
```

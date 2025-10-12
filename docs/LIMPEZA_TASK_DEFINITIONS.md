# Limpeza de Task Definitions ECS

## Problema
As Task Definitions no ECS vão acumulando a cada deploy, ocupando espaço e criando confusão. É recomendado manter apenas as versões mais recentes.

## Solução Implementada

### Script de Limpeza Automática
- **Arquivo**: `cleanup-task-definitions.sh`
- **Função**: Remove Task Definitions antigas, mantendo apenas as 3 mais recentes
- **Integração**: Disponível no menu principal do `deploy-full.sh`

### Como Funciona

#### 1. Listagem Inteligente
```bash
# Lista todas as Task Definitions da família
aws ecs list-task-definitions --family-prefix "mybp-sistema"
```

#### 2. Ordenação por Data
- Ordena por versão (mais recente primeiro)
- Identifica quais manter e quais remover

#### 3. Confirmação Segura
- Mostra quais serão mantidas
- Mostra quais serão removidas
- Solicita confirmação antes de executar

#### 4. Remoção Controlada
```bash
# Remove Task Definition específica
aws ecs deregister-task-definition --task-definition "arn:aws:ecs:..."
```

## Uso

### Via Menu Principal
```bash
./deploy-full.sh
# Escolha opção 4: Limpeza de Task Definitions
```

### Execução Direta
```bash
./cleanup-task-definitions.sh
```

### Configuração
```bash
# Editar configurações no script
TASK_DEFINITION_FAMILY="mybp-sistema"  # Nome da família
AWS_REGION="us-east-1"                 # Região AWS
KEEP_COUNT=3                           # Quantas manter
```

## Exemplo de Execução

### Antes da Limpeza
```
Total de Task Definitions encontradas: 8
Mantendo as 3 mais recentes...

Task Definitions que serão MANTIDAS:
  ✓ mybp-sistema:8
  ✓ mybp-sistema:7
  ✓ mybp-sistema:6

Task Definitions que serão REMOVIDAS:
  ✗ mybp-sistema:5
  ✗ mybp-sistema:4
  ✗ mybp-sistema:3
  ✗ mybp-sistema:2
  ✗ mybp-sistema:1
```

### Após a Limpeza
```
Limpeza concluída!
Task Definitions removidas: 5
Task Definitions mantidas: 3

=== RESUMO FINAL ===

Task Definitions restantes:
  • mybp-sistema:8 (revisão: 8, status: ACTIVE)
  • mybp-sistema:7 (revisão: 7, status: INACTIVE)
  • mybp-sistema:6 (revisão: 6, status: INACTIVE)
```

## Benefícios

### ✅ **Economia de Espaço**
- Remove versões antigas desnecessárias
- Mantém apenas as versões relevantes
- Reduz poluição visual no console AWS

### ✅ **Segurança**
- Confirmação antes de remover
- Mostra exatamente o que será removido
- Mantém as versões mais recentes

### ✅ **Automação**
- Integrado ao processo de deploy
- Execução simples via menu
- Configuração centralizada

### ✅ **Flexibilidade**
- Quantidade configurável (padrão: 3)
- Funciona com qualquer família de Task Definition
- Suporte a múltiplas regiões

## Configurações Avançadas

### Alterar Quantidade a Manter
```bash
# No arquivo cleanup-task-definitions.sh
KEEP_COUNT=5  # Manter 5 em vez de 3
```

### Alterar Família de Task Definition
```bash
# No arquivo cleanup-task-definitions.sh
TASK_DEFINITION_FAMILY="outra-familia"
```

### Alterar Região
```bash
# No arquivo cleanup-task-definitions.sh
AWS_REGION="us-west-2"
```

## Integração com Deploy

### Deploy Completo com Limpeza
```bash
# 1. Deploy normal
./deploy-full.sh
# Escolha opção 2: Deploy completo

# 2. Limpeza após deploy
./deploy-full.sh
# Escolha opção 4: Limpeza de Task Definitions
```

### Automatização
```bash
# Script para deploy + limpeza
./deploy-full.sh  # Deploy
./cleanup-task-definitions.sh  # Limpeza
```

## Monitoramento

### Verificar Task Definitions Atuais
```bash
aws ecs list-task-definitions \
  --family-prefix "mybp-sistema" \
  --region "us-east-1"
```

### Ver Detalhes de uma Task Definition
```bash
aws ecs describe-task-definition \
  --task-definition "mybp-sistema:8" \
  --region "us-east-1"
```

### Contar Task Definitions
```bash
aws ecs list-task-definitions \
  --family-prefix "mybp-sistema" \
  --region "us-east-1" \
  --query 'length(taskDefinitionArns)'
```

## Troubleshooting

### Erro: "Task Definition não encontrada"
- Verifique se a família está correta
- Confirme se a região está correta
- Verifique permissões AWS

### Erro: "Permissão negada"
- Verifique credenciais AWS
- Confirme permissões ECS
- Execute `aws configure`

### Erro: "Família não existe"
- Verifique se já fez deploy antes
- Confirme nome da família no ECS
- Verifique se está na região correta

## Boas Práticas

### 1. **Executar Regularmente**
- Após cada deploy importante
- Semanalmente em desenvolvimento
- Mensalmente em produção

### 2. **Manter Backup**
- Sempre manter pelo menos 2-3 versões
- Documentar versões importantes
- Testar rollback antes de limpar

### 3. **Monitorar Uso**
- Verificar quantas Task Definitions existem
- Acompanhar crescimento ao longo do tempo
- Ajustar KEEP_COUNT conforme necessário

### 4. **Integrar ao CI/CD**
- Adicionar limpeza ao pipeline
- Executar após deploy bem-sucedido
- Notificar em caso de erro

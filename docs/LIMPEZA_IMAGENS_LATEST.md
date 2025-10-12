# Limpeza de Imagens 'latest' ECR

## Visão Geral

Este documento descreve o script `cleanup-latest-images.sh` que remove imagens com tag `latest` do Amazon ECR para reduzir custos e simplificar o gerenciamento de imagens.

## Problema

O deploy anterior criava duas imagens no ECR:
- Uma com tag específica (ex: `2025-01-27-14-30-00-prod`)
- Uma com tag `latest`

Isso dobrava os custos de armazenamento no ECR e criava confusão sobre qual versão estava sendo usada.

## Solução

### 1. Modificação do Deploy

O script `deploy.sh` foi modificado para:
- ✅ Criar apenas uma imagem com tag específica
- ✅ Não criar tag `latest` desnecessária
- ✅ Reduzir custos de armazenamento

### 2. Script de Limpeza

O script `cleanup-latest-images.sh` permite:
- ✅ Remover imagens `latest` existentes
- ✅ Verificar economia de custos
- ✅ Interface amigável com confirmação

## Como Usar

### Opção 1: Menu Principal

```bash
./deploy-full.sh
# Escolha opção 5: Limpeza de imagens 'latest' ECR
```

### Opção 2: Execução Direta

```bash
./cleanup-latest-images.sh
```

## Funcionalidades

### 1. Verificação Automática

- ✅ Verifica se AWS CLI está configurado
- ✅ Obtém ECR registry automaticamente
- ✅ Lista imagens `latest` existentes

### 2. Remoção Segura

- ✅ Mostra quais imagens serão removidas
- ✅ Solicita confirmação antes de remover
- ✅ Remove apenas imagens `latest`

### 3. Relatório de Economia

- ✅ Mostra benefícios da limpeza
- ✅ Explica redução de custos
- ✅ Dicas para versionamento

## Exemplo de Uso

```bash
$ ./cleanup-latest-images.sh

==========================================
    Limpeza de Imagens 'latest' ECR
==========================================

Configurações:
  Repositório: mybp/sistema
  Região: us-east-1
  Registry: 370996423139.dkr.ecr.us-east-1.amazonaws.com

Procurando imagens com tag 'latest'...

Encontradas 1 imagem(ns) com tag 'latest':
  - latest (Digest: sha256:82d6cefda26d99c164235fb70e53c12328abc2bd013b737b52065a0713c7e782)

Deseja remover essas imagens 'latest'? (y/N): y

Removendo 1 imagem(ns) 'latest'...
Imagens 'latest' removidas com sucesso!

=== ECONOMIA ESTIMADA ===

✅ Redução de custos:
  - Menos imagens no ECR
  - Menos espaço de armazenamento
  - Menos transferência de dados

✅ Benefícios:
  - Deploy mais rápido
  - Menos confusão com tags
  - Gestão simplificada

💡 Dica: Use tags específicas com timestamp para versionamento
```

## Configurações

### Variáveis do Script

```bash
AWS_REGION="us-east-1"        # Região AWS
IMAGE_NAME="mybp/sistema"      # Nome da imagem no ECR
```

### Personalização

Para usar com outros repositórios:

```bash
# Editar o script
vim cleanup-latest-images.sh

# Alterar as variáveis
IMAGE_NAME="seu-repositorio/sua-imagem"
AWS_REGION="sua-regiao"
```

## Benefícios

### 1. Redução de Custos

- **Antes**: 2 imagens por deploy (tag específica + latest)
- **Depois**: 1 imagem por deploy (apenas tag específica)
- **Economia**: ~50% no armazenamento ECR

### 2. Gestão Simplificada

- ✅ Sem confusão sobre qual versão usar
- ✅ Tags específicas com timestamp
- ✅ Histórico claro de versões

### 3. Deploy Mais Rápido

- ✅ Menos imagens para processar
- ✅ Menos transferência de dados
- ✅ Build mais eficiente

## Estrutura de Tags Recomendada

### Formato Atual (Recomendado)

```
2025-01-27-14-30-00-prod    # Produção
2025-01-27-14-30-00-qa      # QA
2025-01-27-14-30-00-dev     # Desenvolvimento
```

### Vantagens

- ✅ Timestamp único
- ✅ Ambiente identificado
- ✅ Ordem cronológica
- ✅ Sem duplicação

## Troubleshooting

### Erro: AWS CLI não configurado

```bash
aws configure
# Configure suas credenciais AWS
```

### Erro: Permissões insuficientes

```bash
# Verificar permissões ECR
aws ecr describe-repositories --region us-east-1
```

### Erro: Repositório não encontrado

```bash
# Verificar nome do repositório
aws ecr describe-repositories --region us-east-1
```

## Integração com Deploy

### Deploy Completo

```bash
./deploy-full.sh
# Opção 2: Deploy completo (Build + Push + ECS)
# Opção 5: Limpeza de imagens 'latest' ECR
```

### Deploy Manual

```bash
# 1. Deploy normal
./deploy.sh

# 2. Limpeza de imagens latest (se necessário)
./cleanup-latest-images.sh
```

## Monitoramento

### Verificar Imagens no ECR

```bash
# Listar todas as imagens
aws ecr list-images --repository-name mybp/sistema --region us-east-1

# Listar apenas imagens latest
aws ecr list-images --repository-name mybp/sistema --region us-east-1 --query "imageIds[?imageTag=='latest']"
```

### Verificar Custos

- Acesse AWS Cost Explorer
- Filtre por serviço ECR
- Monitore redução de custos

## Conclusão

O script `cleanup-latest-images.sh` resolve o problema de duplicação de imagens no ECR, reduzindo custos e simplificando o gerenciamento. Use-o regularmente para manter o ECR limpo e otimizado.

---

**Próximos Passos:**
1. Execute o script para remover imagens `latest` existentes
2. Monitore a redução de custos no AWS Cost Explorer
3. Configure alertas de custo se necessário

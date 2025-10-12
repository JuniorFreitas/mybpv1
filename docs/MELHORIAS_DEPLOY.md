# Melhorias no Script de Deploy

## Problema Resolvido
As imagens Docker ficavam acumulando no disco local após cada deploy, ocupando espaço desnecessário.

## Soluções Implementadas

### 1. Limpeza Automática Após Push
- **Função `cleanup_local_images()`**: Remove imagens locais após push para ECR
- **Execução automática**: Chamada em todos os deploys (dev, qa, prod, custom)
- **Limpeza inteligente**: Remove apenas imagens específicas do projeto

### 2. Limpeza Manual
- **Nova opção no menu**: "Limpeza de imagens Docker"
- **3 tipos de limpeza**:
  - Imagens MyBP específicas
  - Imagens órfãs (dangling)
  - Limpeza completa (todas não utilizadas)

### 3. Monitoramento de Espaço
- **Exibição do espaço**: Mostra uso atual do disco após limpeza
- **Feedback visual**: Confirma quantas imagens foram removidas

## Como Funciona

### Limpeza Automática
```bash
# Após cada deploy, automaticamente:
1. Remove imagem específica: ECR_REGISTRY/IMAGE_NAME:TAG
2. Remove imagem latest (se produção)
3. Remove imagens órfãs (dangling)
4. Mostra espaço liberado
```

### Limpeza Manual
```bash
# Opção 5 no menu:
1) Limpar todas as imagens MyBP
2) Limpar imagens órfãs (dangling)  
3) Limpeza completa (todas as imagens não utilizadas)
4) Voltar
```

## Comandos Docker Utilizados

### Limpeza Específica
```bash
# Remove imagem específica
docker rmi ECR_REGISTRY/IMAGE_NAME:TAG

# Remove imagem latest
docker rmi ECR_REGISTRY/IMAGE_NAME:latest
```

### Limpeza Geral
```bash
# Remove imagens órfãs
docker image prune -f

# Limpeza completa (cuidado!)
docker system prune -a -f
```

## Benefícios

### ✅ **Economia de Espaço**
- Remove imagens automaticamente após push
- Evita acúmulo de imagens antigas
- Libera espaço no disco local

### ✅ **Controle Manual**
- Opção para limpeza quando necessário
- Diferentes níveis de limpeza
- Confirmação para operações perigosas

### ✅ **Monitoramento**
- Mostra espaço liberado
- Feedback visual das operações
- Logs detalhados do processo

### ✅ **Segurança**
- Remove apenas imagens específicas do projeto
- Confirmação para limpeza completa
- Não afeta outras imagens Docker

## Uso Recomendado

### Deploy Normal
```bash
# Use normalmente - limpeza é automática
./deploy.sh
# Escolha ambiente (1-4)
# Limpeza acontece automaticamente
```

### Limpeza Manual
```bash
# Quando precisar limpar manualmente
./deploy.sh
# Escolha opção 5
# Escolha tipo de limpeza (1-3)
```

### Limpeza de Emergência
```bash
# Se o disco estiver muito cheio
docker system prune -a -f
# CUIDADO: Remove TODAS as imagens não utilizadas
```

## Monitoramento

### Verificar Espaço
```bash
# Ver uso do disco
df -h

# Ver imagens Docker
docker images

# Ver tamanho das imagens
docker system df
```

### Verificar Imagens MyBP
```bash
# Listar apenas imagens do projeto
docker images | grep mybp
```

## Configuração

### Variáveis Importantes
```bash
ECR_REGISTRY="123456789.dkr.ecr.us-east-1.amazonaws.com"
IMAGE_NAME="mybp/sistema"
```

### Personalização
- Modifique `IMAGE_NAME` se mudar o nome da imagem
- Ajuste `ECR_REGISTRY` se mudar de região
- Adicione mais tipos de limpeza se necessário

## Troubleshooting

### Erro: "Não foi possível remover imagem"
- Imagem pode estar em uso
- Execute `docker ps -a` para ver containers
- Pare containers antes de remover imagens

### Erro: "Permissão negada"
- Execute com `sudo` se necessário
- Verifique se o usuário está no grupo docker

### Espaço ainda cheio
- Use limpeza completa (opção 3)
- Verifique outros volumes Docker
- Execute `docker system prune -a --volumes -f`

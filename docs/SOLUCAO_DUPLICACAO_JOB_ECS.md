# Solução para Duplicação de Jobs em Ambiente ECS

## Problema
O job `JobExportaCihCsvFinal` estava sendo executado simultaneamente em múltiplas réplicas do ECS, causando duplicação de exportações e arquivos.

## Solução Implementada

### 1. Sistema de Lock Distribuído
- Implementado usando **apenas Redis** como backend de cache
- Chave de lock única baseada em: `nomeArquivo + usuario + filtros`
- Timeout de 20 minutos para evitar locks órfãos
- Operação atômica `add()` para garantir que apenas uma instância adquira o lock
- **Não faz nenhuma operação no banco de dados**

### 2. Controle de Execução
- Apenas uma instância processa o job por vez
- Outras instâncias detectam o lock e retornam silenciosamente (permitindo retries)
- Lock é liberado automaticamente no final da execução ou em caso de falha
- **Retries funcionam normalmente** - se uma instância falhar, outra pode tentar

### 3. Tolerância a Falhas
- Se Redis estiver indisponível, permite execução para não quebrar o sistema
- Lock é sempre liberado, mesmo em caso de erro
- Logs detalhados para rastreamento e debugging

## Código Implementado

### Métodos Adicionados:
- `acquireLock()`: Adquire lock distribuído usando Redis
- `releaseLock()`: Libera lock distribuído
- `failed()`: Libera lock em caso de falha

### Fluxo de Execução:
1. Tenta adquirir lock distribuído no Redis
2. Se lock adquirido, processa exportação
3. Se lock não disponível, retorna silenciosamente (permite retry)
4. Libera lock ao finalizar (sucesso ou falha)
5. **Nenhuma operação no banco de dados para controle de duplicação**

## Configuração Necessária

### Redis
Certifique-se de que o Redis está configurado e acessível:
```php
// config/cache.php
'redis' => [
    'driver' => 'redis',
    'connection' => 'default',
],
```

### Variáveis de Ambiente
```env
CACHE_DRIVER=redis
REDIS_HOST=seu-redis-host
REDIS_PASSWORD=seu-redis-password
REDIS_PORT=6379
```

## Logs de Monitoramento

O sistema agora gera logs detalhados:
- `Lock adquirido: {lockKey} = {lockValue}`
- `Job CIH já está sendo processado por outra instância`
- `Falha ao adquirir lock: {lockKey} - Lock já existe`
- `Lock liberado: {lockKey}`
- `Erro ao tentar adquirir lock: {erro}` (se Redis falhar)

## Benefícios

1. **Elimina Duplicação**: Apenas uma instância processa cada job
2. **Retries Funcionam**: Se uma instância falhar, outra pode tentar
3. **Sem Impacto no Banco**: Nenhuma operação adicional no banco de dados
4. **Tolerante a Falhas**: Funciona mesmo se Redis estiver indisponível
5. **Rastreável**: Logs detalhados para debugging
6. **Escalável**: Funciona com qualquer número de réplicas ECS

## Teste

Para testar a solução:
1. Dispare múltiplas exportações simultâneas
2. Verifique os logs para confirmar que apenas uma instância processa
3. Simule falha de uma instância e confirme que outra assume
4. Confirme que não há arquivos duplicados no S3
5. Teste com Redis indisponível para confirmar que não quebra o sistema

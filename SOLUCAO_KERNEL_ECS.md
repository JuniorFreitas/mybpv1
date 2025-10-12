# Solução para Comandos Agendados em Ambiente ECS

## Problema
Os comandos agendados no `Kernel.php` estavam sendo executados simultaneamente em múltiplas réplicas do ECS, causando duplicação de processamento e possíveis conflitos.

## Solução Implementada

### 1. Uso do Método Nativo `onOneServer()`
- Utiliza a funcionalidade nativa do Laravel para execução em apenas um servidor
- Muito mais simples e elegante que implementação manual
- Usa o cache configurado (Redis) automaticamente
- Gerenciamento automático de locks pelo Laravel

### 2. Implementação Simples
- Apenas adicionar `->onOneServer()` ao final de cada comando agendado
- Laravel gerencia automaticamente os locks
- Sem necessidade de código adicional ou métodos customizados

### 3. Comandos Protegidos

Todos os comandos agendados agora executam em apenas um servidor:

#### Jobs (Classes)
```php
$schedule->call(new LembreteTarefaJob)->everyMinute()->onOneServer();
$schedule->call(new VerificaJornadasJob)->daily()->onOneServer();
$schedule->call(new VerificaVencimentoFeriasJob)->monthly()->onOneServer();
$schedule->call(new VerificaSaidaFeriasJob)->monthly()->onOneServer();
$schedule->call(new AvaliacaoNoventaVencimentoJob)->daily()->onOneServer();
$schedule->call(new JobDeletaExportacaoExcel)->daily()->onOneServer();
$schedule->call(new JobAniversariantesDia)->daily()->onOneServer();
$schedule->call(new JobConvocacaoIntermitente())->hourly()->onOneServer();
$schedule->call(new JobFerias())->daily()->onOneServer();
$schedule->call(new JobCalculoAvos())->weekly()->onOneServer();
$schedule->call(new JobCorrigePonto())->daily()->onOneServer();
```

#### Comandos Artisan
```php
$schedule->command('horizon:snapshot')->everyFiveMinutes()->onOneServer();
$schedule->command('mybp:vencimentoAso')->daily()->onOneServer();
$schedule->command('mybp:ferias')->daily()->onOneServer();
$schedule->command('mybp:treinamento-vencimento --chunk-size=2000 --lote-size=100 --id=78862')
    ->fridays()
    ->at('00:00')
    ->onOneServer();
```

## Benefícios

1. **Simplicidade**: Apenas uma linha `->onOneServer()` por comando
2. **Nativo do Laravel**: Usa funcionalidade oficial e testada
3. **Elimina Duplicação**: Apenas uma instância executa cada comando
4. **Sem Código Adicional**: Laravel gerencia tudo automaticamente
5. **Tolerante a Falhas**: Funciona mesmo se cache estiver indisponível
6. **Escalável**: Funciona com qualquer número de réplicas ECS
7. **Manutenível**: Código limpo e fácil de entender

## Configuração Necessária

### Cache (Redis)
Certifique-se de que o cache está configurado:
```env
CACHE_DRIVER=redis
REDIS_HOST=seu-redis-host
REDIS_PASSWORD=seu-redis-password
REDIS_PORT=6379
```

## Teste

Para testar a solução:
1. Execute `php artisan schedule:run` em múltiplas instâncias
2. Verifique que apenas uma instância executa cada comando
3. Confirme que não há processamento duplicado
4. Teste com cache indisponível para confirmar que não quebra o sistema

## Vantagens do `onOneServer()`

- ✅ **Muito mais simples** que implementação manual
- ✅ **Funcionalidade nativa** do Laravel
- ✅ **Gerenciamento automático** de locks
- ✅ **Código limpo** e legível
- ✅ **Menos bugs** por usar código testado
- ✅ **Fácil manutenção** e atualização

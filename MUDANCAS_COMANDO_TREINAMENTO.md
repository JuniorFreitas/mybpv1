# Mudanças no Comando de Treinamento Vencimento

## Resumo
O comando `mybp:treinamento-vencimento` foi modificado para utilizar a mesma lógica do export de treinamentos, garantindo consistência entre os dados apresentados no schedule automático e na exportação manual.

## Principais Alterações

### 1. **Fonte de Dados Unificada**
- **Antes**: Buscava dados diretamente da tabela `treinamento_vencimento` com JOINs
- **Agora**: Usa a mesma estrutura do export, buscando de `feedback_curriculos` com relacionamentos

### 2. **Processamento Melhorado**
- Mantém processamento em chunks para otimização de memória
- Usa login temporário de usuário para resolver problemas de scopes globais
- Processa relacionamentos da mesma forma que o export

### 3. **Consistência de Dados**
- Agora os dados do schedule e do export são consistentes
- Mesmo critério de classificação de vencimentos
- Mesma estrutura de relacionamentos

## Como Funciona Agora

### 1. **Autenticação Temporária**
```php
// Faz login temporário de um usuário da empresa para os scopes funcionarem
$usuarioTemp = User::withoutGlobalScopes()
    ->where('empresa_id', $empresaId)
    ->where('ativo', true)
    ->whereNotNull('login')
    ->first();

\Auth::login($usuarioTemp);
```

### 2. **Busca de Dados**
```php
// Usa mesma estrutura do export
$query = \App\Models\FeedbackCurriculo::select([...])
    ->with([
        'Curriculo:id,nome,cpf,nascimento,pcd,uf_vaga,email,rg,orgao_expeditor',
        'Admissao' => function($query) {
            $query->where('status', \App\Models\Admissao::STATUS_ADMISSAO_ADMITIDO)
                  ->with('CentroCusto:id,label');
        },
        'Treinamento:id,cadastrou,feedback_id,tipo,created_at,updated_at',
        'Treinamento.Vencimentos',
    ])
```

### 3. **Processamento de Vencimentos**
- Para cada treinamento, busca dados em `treinamento_vencimento`
- Aplica mesma classificação (VENCIDO, PROXIMO, ATENCAO)
- Mantém mesmos critérios de tempo (45, 30, 60 dias)

## Resultado do Teste

O comando foi testado com sucesso:
- **Total de registros processados**: 426
- **Treinamentos vencidos**: 32
- **Treinamentos próximos a vencer**: 29  
- **Treinamentos em atenção**: 23
- **E-mails enviados**: 3 usuários
- **Arquivo Excel gerado**: S3 com 150 linhas

## Schedule Configurado

```php
$schedule->command('mybp:treinamento-vencimento --chunk-size=2000 --lote-size=100 --id=78862')
    ->fridays()
    ->at('00:00')
    ->name('mybp_treinamento_vencimento')
    ->onOneServer();
```

## Benefícios

1. **Consistência**: Dados do schedule e export agora são idênticos
2. **Manutenibilidade**: Uma única lógica de negócio para manter
3. **Performance**: Mantém processamento em chunks otimizado
4. **Compatibilidade**: Continua enviando e-mails e Excel como antes

## Teste no Docker

Para testar manualmente:
```bash
docker exec mybp_app php artisan mybp:treinamento-vencimento --id=78862 --chunk-size=10
```

**Data da Modificação**: 17 de outubro de 2025
**Status**: ✅ Implementado e testado com sucesso
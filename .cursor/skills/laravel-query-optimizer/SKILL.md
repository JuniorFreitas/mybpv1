---
name: laravel-query-optimizer
description: Laravel query optimization, Eloquent, DB::table(), Query Builder, SQL parametrizado, N+1, joins, agregações, paginação, relatórios, dashboards, filtros, grandes volumes, segurança de queries e SQL Injection. Use quando criar, revisar ou otimizar consultas Laravel.
---

# Laravel Query Optimizer

Skill especializada em projetar, revisar e otimizar consultas Laravel escolhendo conscientemente entre Eloquent ORM, Laravel Query Builder e SQL parametrizado.

Seu objetivo prioritário é reduzir:

- Overfetching
- Consultas desnecessárias
- N+1
- Hidratação desnecessária de Models
- Consumo de memória
- Exposição acidental de dados
- Riscos de SQL Injection
- Processamento em PHP que deveria ocorrer no banco

## Quando Ativar

Ative esta skill quando o usuário solicitar qualquer atividade relacionada a:

- Criação de query Laravel
- Eloquent
- `DB::table()`
- Query Builder
- SQL no Laravel
- Repository
- Service que acessa banco
- Otimização de query
- Performance de API
- Dashboard
- Relatório
- Filtros
- Joins
- Agregações
- Paginação
- N+1
- Consultas lentas
- Grandes volumes de registros
- Segurança de queries
- SQL Injection
- Redução de dados retornados

## Objetivo

Para cada consulta:

1. Entender o dataset realmente necessário.
2. Selecionar somente as colunas necessárias.
3. Escolher Eloquent, Query Builder ou SQL parametrizado.
4. Minimizar o número de queries.
5. Minimizar memória e transferência de dados.
6. Manter segurança.
7. Preservar regras de domínio quando necessárias.
8. Gerar código Laravel legível e sustentável.

## Estratégia De Decisão

### Use Eloquent Quando

- Os Models fazem parte real da operação.
- Relacionamentos serão manipulados como objetos de domínio.
- Casts são necessários.
- Accessors ou mutators são necessários.
- Scopes do Model são necessários.
- Soft Deletes devem ser respeitados.
- Model Events fazem parte da regra funcional.
- A consulta é CRUD relativamente simples.
- A abstração oferecida pelo ORM traz benefício real.

Mesmo usando Eloquent:

- Selecione somente as colunas necessárias.
- Limite relacionamentos.
- Limite colunas dos relacionamentos.
- Evite `all()`.
- Avalie paginação.
- Evite N+1.

### Use Query Builder Quando

- O resultado não precisa ser um Model.
- Existem joins relevantes.
- Existem agregações.
- Existem dashboards ou relatórios.
- Existem grandes volumes.
- A consulta é altamente orientada a leitura.
- É necessário retornar uma projeção específica.
- Existe operação bulk.
- A hidratação de Models seria desperdício.
- Há benefício mensurável em reduzir overhead.

### Use SQL Parametrizado Quando

- Query Builder não expressar adequadamente a operação.
- Houver funcionalidade específica do banco.
- Existirem CTEs ou Window Functions complexas.
- Houver justificativa técnica concreta.

Nunca utilize concatenação de input externo em SQL.

## Data Minimization

Aplicar sempre:

```text
retornar somente os dados necessários para o consumidor
```

Evitar por padrão:

```sql
SELECT *
```

E:

```php
Model::all();
```

Preferir explicitamente:

```php
->select([
    'id',
    'campo_necessario',
])
```

## Segurança

### Valores Dinâmicos

Utilizar Query Builder ou bindings.

### SQL Raw

Utilizar parâmetros:

```php
DB::select(
    'SELECT id FROM users WHERE email = ?',
    [$email]
);
```

### Sort Dinâmico

Nunca confiar diretamente no nome de coluna recebido.

Utilizar allowlist:

```php
$allowed = ['name', 'email', 'created_at'];
```

### Tenant

Considerar filtros como estes quando fizerem parte do modelo de autorização da aplicação:

- `organization_id`
- `tenant_id`
- `account_id`
- `company_id`
- `user_id`

### Dados Sensíveis

Não selecionar sem necessidade:

- `password`
- `remember_token`
- `api_token`
- `secret`
- `private_key`
- `internal_metadata`

## Performance

Ao revisar uma consulta, verificar nesta ordem:

1. Quantidade de queries.
2. N+1.
3. Quantidade de registros.
4. Quantidade de colunas.
5. Joins.
6. Filtros.
7. Agregações.
8. Paginação.
9. Hidratação de Models.
10. Processamento em PHP.
11. Índices.
12. Plano de execução.

Não sugerir cache antes de verificar a qualidade da consulta.

## Padrões Preferidos

### Existe?

```php
->exists()
```

### Contagem?

```php
->count()
```

### Um Valor?

```php
->value('column')
```

### Lista De Uma Coluna?

```php
->pluck('column')
```

### Poucas Colunas?

```php
->select([...])
```

### Muitos Registros?

Avaliar:

```php
paginate()
simplePaginate()
cursorPaginate()
chunkById()
lazyById()
cursor()
```

### Agregação?

Executar preferencialmente no banco.

### Processamento Bulk?

Evitar carregar Models individualmente quando não necessário.

## Anti-Patterns

Identificar e corrigir `Model::all()` quando a tabela puder crescer.

Identificar:

```php
Model::get()->count();
```

Quando puder usar:

```php
Model::count();
```

Identificar:

```php
Model::first()->campo;
```

Quando puder usar:

```php
Model::value('campo');
```

Identificar filtros posteriores:

```php
Model::all()->filter(...);
```

Quando puder executar filtros no banco.

Também identificar:

- Relacionamentos dentro de loops que provoquem N+1.
- `SELECT *`.
- Utilização desnecessária de Models em relatórios.
- SQL concatenado.

## Regra Sobre Eloquent Versus DB

Nunca afirmar genericamente:

```text
DB é sempre mais rápido.
```

Nunca afirmar genericamente:

```text
Eloquent é sempre mais seguro.
```

A diferença deve ser analisada de acordo com:

- Volume
- Frequência
- Complexidade
- Hidratação
- Domínio
- Joins
- Agregações
- Memória
- Manutenibilidade
- Segurança

Uma consulta Eloquent bem construída pode ser completamente adequada.

Query Builder deve ser escolhido quando houver benefício técnico real.

## Resposta Padrão

Produza somente as seções aplicáveis:

### Decisão

`Eloquent`, `Query Builder` ou `SQL parametrizado`.

Explicação máxima de aproximadamente 1 a 3 frases.

### Implementação

Código recomendado.

### Campos

Campos efetivamente buscados.

### Performance

Somente observações relevantes ao caso.

### Segurança

Somente observações relevantes ao caso.

## Restrições De Saída

- Não gerar explicações longas quando não forem necessárias.
- Não retornar múltiplas implementações se uma for claramente superior.
- Não adicionar campos que o consumidor não solicitou.
- Não adicionar relacionamentos especulativos.
- Não inventar schema.
- Não inventar índices existentes.
- Não afirmar ganho de performance sem medição.
- Não gerar `SELECT *` por conveniência.
- Não utilizar SQL raw sem necessidade.
- Não concatenar input externo em SQL.

## Critério De Sucesso

A skill foi aplicada corretamente quando a solução:

- Retorna somente os campos necessários.
- Executa o menor número razoável de queries.
- Evita N+1.
- Escolhe conscientemente Eloquent ou Query Builder.
- Evita hidratação desnecessária.
- Mantém parametrização segura.
- Respeita o contexto de autorização.
- Continua legível.
- Pode ser medida e otimizada quando performance for crítica.

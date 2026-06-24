# Convenções de schema MyBP

Baseado em AGENTS.md §10 e docs/05-banco-de-dados.md.

## Nomenclatura

- Tabelas: **plural** PT-BR (`admissoes`, `feedback_curriculos`)
- Models: **singular** (`Admissao`, `FeedbackCurriculo`)
- Pivots / N-N: prefixo da tabela principal (`pessoas_telefones`)
- Relacionamento: `entidade_filha` ou `entidade_relacionada`

## Campos obrigatórios em models

Toda model deve definir explicitamente:
- `protected $table`
- `protected $fillable` (ou `$guarded`)
- `protected $casts`

Datas: mutator com sufixo `_br` (ex.: `data_nascimento_br`).

## Multi-tenant

- Filtro `empresa_id` em queries de dados por empresa
- Cuidado com `withoutGlobalScopes()` — exige filtro manual documentado
- Índice em `empresa_id` em tabelas centrais (`feedback_curriculos`, `users`, etc.)

## Soft delete

- Tabelas com `deleted_at`: **sempre** `whereNull('deleted_at')` em Query Builder
- Em joins: aplicar em **todas** as tabelas com soft delete

## Entidades centrais RH

| Tabela | Papel |
|--------|-------|
| `users` | Autenticação, empresa_id, grupos |
| `clientes` | Empresas/clientes (id ligado a users tipo empresa) |
| `curriculos` | Candidatos (CPF único) |
| `feedback_curriculos` | Núcleo do fluxo RH |
| `admissoes` | Pós-seleção / admissão |

## Migrations

- 603+ arquivos; base estrutural em 2021
- Evoluções recentes: assinatura digital (2026), índices treinamentos (2025)
- Drift: comparar sempre live vs migrations via `ddl-diff.json`

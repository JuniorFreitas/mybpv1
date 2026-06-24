# Índices — MySQL 5.7 MyBP

## Regras gerais

1. **FK e filtros frequentes** devem ter índice (`empresa_id`, `cliente_id`, `feedback_id`, `deleted_at`)
2. **Índice composto**: coluna mais seletiva primeiro; em queries tenant, `empresa_id` como prefixo
3. **Soft delete**: incluir `deleted_at` em índices compostos quando filtro padrão
4. **Leftmost prefix**: índice `(a,b,c)` serve para `a`, `(a,b)`, `(a,b,c)` — não para `b` sozinho
5. Evitar índices redundantes que cobrem o mesmo prefixo

## Tabelas críticas

| Tabela | Colunas prioritárias para índice |
|--------|----------------------------------|
| `feedback_curriculos` | `empresa_id`, `cliente_id`, `curriculo_id`, `status` |
| `admissoes` | `feedback_id`, `status`, `empresa_id` |
| `users` | `empresa_id`, `ativo`, `login` |
| `treinamento_vencimento` | `data_vencimento`, `treinamento_id` |

## Baseline existente

Migration `2025_08_03_092431_create_indices_treinamentos.php` — referência de índices já aplicados.

## Validação

1. `EXPLAIN SELECT ...` — evitar `type: ALL` em tabelas grandes
2. `rows` estimadas no EXPLAIN devem ser aceitáveis
3. Comparar `schema-live.json` com `migrations-raw.json` via `ddl-diff.py`

## DDL sugerido

Sempre via migration Laravel em `ddl-suggestions/migrations/`, nunca ALTER manual em produção sem revisão.

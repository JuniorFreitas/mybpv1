# Eloquent vs Query Builder — MyBP

Alinhado a AGENTS.md §5.

## Use Eloquent quando

- Relações (`with()`, `belongsTo`, `hasMany`)
- Mutators, casts, regras de domínio na model
- CRUD com global scopes (`empresa_id`)
- Operações unitárias com validação de domínio

**Exemplo:** `AdmissoesPrevista::with([...])->where('empresa_id', $user->empresa_id)`

## Use Query Builder quando

- Exportações CIH (`*ExportQueryBuilder`)
- Agregações pesadas, `GROUP BY`, subqueries
- Joins multi-tabela com payload enxuto
- Alto volume sem necessidade de hidratar models

**Exemplo:** Services em `app/Services/*/ExportQueryBuilder.php`

## Use SQL raw apenas quando

- Funções MySQL específicas (`DATEDIFF`, window functions)
- **Sempre** com bindings (`?`) — nunca concatenar input
- Preferir `DB::raw()` em expressões, não em valores de filtro

## Red flags

| Padrão | Ação |
|--------|------|
| `whereRaw("... $var ...")` | Refatorar para binding |
| `DB::select("... {$id}")` | Usar `DB::select('... ?', [$id])` |
| 5+ `DB::raw` em Controller | Extrair para Service/ExportQueryBuilder |
| `foreach` + `Model::find` | `with()` ou `whereIn` batch |

## Decisão rápida

```
Precisa de relações/casts/scopes?
  ├─ Sim → Eloquent
  └─ Não → Volume/joins pesados?
        ├─ Sim → Query Builder
        └─ Não → Eloquent ou QB simples
```

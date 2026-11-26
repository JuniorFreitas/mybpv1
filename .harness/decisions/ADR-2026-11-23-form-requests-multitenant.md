# ADR: Form Requests com multi-tenancy obrigatório

## Status
Accepted

## Contexto
Validação inline (`Validator::make` no controller) dificulta reuso e permite IDs cross-tenant (`exists:table,id` sem `empresa_id`).

## Decisão
1. Toda validação de escrita HTTP autenticada migra para `FormRequest`.
2. IDs de entidades com `empresa_id` usam `ValidatesTenantOwned::existsNaEmpresa()` / `regraIdNaEmpresa()`.
3. Resposta de falha preserva contrato legado JSON `{ msg, erros }` com HTTP 400 quando o fluxo já usava esse formato.
4. Tabelas sem tenant (cadastros globais) usam `existsAtivo()` / `regraIdAtivo()`.
5. Soft deletes: se a tabela tiver `deleted_at` (detectado via `SoftDeleteSchema` / `Schema::hasColumn`, cache compartilhado), `exists`/`unique`/`TenantUniqueRules` excluem soft-deleted por padrão. Override explícito via parâmetro quando necessário. Closures de `Rule::unique` usam `aplicarSoftDeleteSeExistir`.

## Consequências
- Controllers mais enxutos; security gate no request.
- Testes unitários precisam de `actingAs` + fixtures mínimas para `exists`; limpar cache com `ValidatesTenantOwned::limparCacheSoftDeletes()` / `SoftDeleteSchema::limpar()` no setUp (cache não fica preso por classe do trait).
- Migração em ondas (TD-004) + soft deletes (TD-005).

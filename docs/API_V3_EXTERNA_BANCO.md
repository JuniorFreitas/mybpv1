# API Externa v3 — Banco (interno)

Decisões de schema/índices com orientação **dba-senior** e queries com **laravel-query-optimizer**.

## Tabela `empresa_api_credenciais`

| Coluna | Tipo | Notas |
|--------|------|-------|
| id | PK | |
| empresa_id | unsignedBigInteger | tenant |
| user_id | unsignedBigInteger | user tipo Api |
| client_id | string(64) | **unique** |
| client_secret_hash | string(255) | bcrypt/argon |
| scopes | json | lista de scopes |
| ativo | boolean | |
| last_used_at | timestamp nullable | |
| timestamps | | |
| deleted_at | soft delete | Query Builder: `whereNull('deleted_at')` |

Índices:

- `empresa_api_credenciais_client_id_uq` (unique `client_id`)
- `empresa_api_credenciais_empresa_ativo_idx` (`empresa_id`, `ativo`)
- `empresa_api_credenciais_user_id_idx` (`user_id`)

## Sanctum

Migration `2026_10_14_190001_add_expires_at_to_personal_access_tokens_table` adiciona `expires_at` (necessário no Sanctum 4 para TTL do token).

## Estratégia de query por endpoint

| Endpoint | Abordagem | Motivo |
|----------|-----------|--------|
| Cloud / Fornecedores / Avaliações | `DB::table` + select colunas | leitura pura, volume |
| Processos | QB + joins + `whereExists` / `whereNotExists` | admitidos vs em andamento sem N+1 |
| Currículos | QB + `whereExists` + subquery último feedback | evita groupBy frágil |
| Fluxo | várias queries pontuais por `feedback_id` | agregação controlada |

Soft delete respeitado em: `feedback_curriculos`, `curriculos`, `admissoes`, `itens_cloud`, `empresa_api_credenciais`.

## Convenções MyBP

- Tabela plural: `empresa_api_credenciais`
- Model singular: `EmpresaApiCredencial` com `$table`, `$fillable`, `$casts`
- Prefixo de relacionamento pela tabela principal (`empresa_*`)

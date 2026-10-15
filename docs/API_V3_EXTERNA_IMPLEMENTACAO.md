# API Externa v3 — Implementação (interno)

Registro do que foi entregue na API Externa v3 para agents/integrações.

## Objetivo

Expor leitura segura, por empresa, de Cloud, Fornecedores, Avaliações de desempenho, Processos (ativos + em andamento), Currículos, fluxo até pós-admissão e relatórios do menu RH.

## Autenticação

1. Usuário `users.tipo = Api` por `empresa_id`
2. Credenciais em `empresa_api_credenciais` (`client_id` + `client_secret_hash`)
3. `POST /api/v3/oauth/token` → Sanctum PAT com abilities = scopes e `expires_at`
4. Recursos: `Authorization: Bearer` + middlewares `auth:sanctum`, `usuario.ativo`, `api.externa.user`, `api.externa.scope:{scope}`

Comando:

```bash
php artisan api-externa:credencial {empresa_id}
php artisan api-externa:credencial {empresa_id} --rotate
```

## Arquivos principais

| Área | Caminho |
|------|---------|
| Rotas | `routes/api.php` (prefixo `v3`) |
| Migration credenciais | `database/migrations/2026_10_14_190000_create_empresa_api_credenciais_table.php` |
| Migration Sanctum expires | `database/migrations/2026_10_14_190001_add_expires_at_to_personal_access_tokens_table.php` |
| Model | `app/Models/EmpresaApiCredencial.php` |
| Tipo usuário | `User::API` em `app/Models/User.php` |
| Scopes | `app/Support/ApiExterna/ApiExternaScopes.php` |
| Token | `app/Http/Controllers/Api/Externa/OAuthTokenController.php` |
| Credenciais | `app/Services/ApiExterna/ApiExternaCredencialService.php` |
| Services leitura | `app/Services/ApiExterna/*ApiService.php` |
| Assembler admissão | `app/Services/ApiExterna/AdmissaoApiAssembler.php` (payload = filtro/edit) |
| Fornecedores | `FornecedorApiService` (listagem/edit + anexos metadados) |
| Cloud | `CloudApiService` (itens/busca/pastas/path/download + filtros) |
| Testes cloud | `tests/Feature/ApiExterna/ApiExternaCloudTest.php` |
| Testes processos | `tests/Feature/ApiExterna/ApiExternaProcessoTest.php` |
| Testes fornecedores | `tests/Feature/ApiExterna/ApiExternaFornecedorTest.php` |
| Controllers | `app/Http/Controllers/Api/Externa/*` |
| Middleware | `EnsureApiExternaUser`, `EnsureApiExternaScope` |
| Docs UI | `DocsController` → `/api/v3/docs` |
| Config | `config/services.php` → `api_externa` |
| Relatórios | `RelatorioApiService` + `RelatorioController` (`relatorios:read`) |
| Testes | `tests/Feature/ApiExterna/ApiExternaAuthTest.php`, `ApiExternaRelatorioTest.php` |

## Identificadores públicos

- Responses usam `public_id` / `*_public_id` no formato UUID (`App\Support\ApiExterna\ApiExternaId`)
- Paths: `/api/v3/{recurso}/{publicId}`
- `empresa_id` omitido do JSON (já implícito no token)

## Skills aplicadas

- **security-senior**: secret hasheado, rate limit token/listagens, tenant, LGPD, 401 JSON em API, sem IDs sequenciais
- **dba-senior**: tabela plural, índices `client_id` unique, `(empresa_id, ativo)`, soft delete
- **laravel-query-optimizer**: Query Builder nas listagens, colunas mínimas, `whereNull(deleted_at)`, paginação

## Ajuste colateral

`Authenticate` middleware: rotas `api/*` / JSON passam a autenticar via guards Sanctum corretamente e respondem **401 JSON** em vez de redirect 302 (necessário para Bearer funcionar).

## Deploy

```bash
php artisan migrate
php artisan api-externa:credencial {empresa_id}
```

Enviar ao cliente: `docs/API_V3_EXTERNA.md` + OpenAPI + Postman.

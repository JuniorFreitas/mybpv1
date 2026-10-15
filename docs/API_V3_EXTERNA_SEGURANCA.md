# API Externa v3 — Segurança (interno)

Controles aplicados com orientação **security-senior**.

## Autenticação e segredos

| Controle | Detalhe |
|----------|---------|
| Client secret hasheado | `Hash::make` / `Hash::check` em `client_secret_hash` |
| Secret plaintext | Apenas na saída do comando Artisan (criação/rotação) |
| Access token | Sanctum PAT com `expires_at` (TTL `API_EXTERNA_TOKEN_TTL_MINUTES`) |
| Scopes | Abilities do token; middleware `api.externa.scope` |
| Usuário máquina | `tipo=Api`, sem reset forçado de senha, senha aleatória |
| Mensagens 401 | Genéricas (“Credenciais inválidas” / “Não autorizado”) — evita enumeração |

## Rate limiting

| Limiter | Default |
|---------|---------|
| `api-externa-token` | 10/min por `client_id` ou IP |
| `api-externa` | 120/min por user id ou IP |

Config: `config/services.php` → `api_externa.*`

## Isolamento multi-tenant

- `empresa_id` sempre do usuário autenticado (`$request->user()->empresa_id`)
- Nunca aceitar `empresa_id` do body/query como autoridade
- Queries com filtro explícito `where empresa_id = ?`

## LGPD / dados sensíveis

- CPF mascarado nas respostas
- Cloud: links de arquivo via API autenticada (`/itens/{item}/download|arquivo|thumb`) — sem path/chave de storage
- Logs de falha de token: `client_id` + IP — **sem** secret

## Superfície HTTP

- Somente GET nos recursos (+ POST token)
- Docs/OpenAPI públicos (sem dados sensíveis)
- Usuário Api não deve usar rotas web `/g/*` (tipo fora dos fluxos gerenciais)

## Correção relacionada

Middleware `Authenticate`: APIs com Bearer deixam de receber redirect 302 para login web; resposta 401 JSON.

## Erros sempre JSON (API v3)

- Middleware `force.json` nas rotas de dados/token (`Accept: application/json`)
- `shouldRenderJsonWhen` para todo `/api/*` (evita Whoops/HTML em 500)
- `ApiExternaExceptionRenderer`: envelope `{ success, msg }` (+ `errors` em 422); 500 sem stack no client quando `APP_DEBUG=false`

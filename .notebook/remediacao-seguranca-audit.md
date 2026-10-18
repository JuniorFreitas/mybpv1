# Remediação segurança audit — inventário rotas × findings

Fonte: `~/security-audit-skill/mybp/run-1/`. Escopo 1C + portal BFF/v2 (2B).

| Finding | Rotas | Consumidor | Onda | Status |
|---------|-------|------------|------|--------|
| perfil IDOR/mass assignment | GET/PUT `/g/perfil/{id}` | UI perfil | 1 | done |
| Sanctum usuarios | `/api/usuarios` | mobile/API | 1 | done |
| recovery enum + MFA skip + token fraco | `/g/enviaSolicitacaoSenha`, `/envia-recupera-senha` | login público | 1 | done |
| verificacao-assinatura empty hash | GET `/verificacao-assinatura` | QR público | 1 | done |
| export obscure | prefixo longo `*.excel` | Blade entrevistas | 1 | done |
| XSS strip_tags | ocorrência/testemunhal | UI | 1 | done |
| X-API-TOKEN literals | cartaoferta JS + CartaOfertaGerencial | SGI | 2 | done |
| X-API-TOKEN sem escopo | `/api/envia-whats`, `/api/v2/integracao/*` | BFF/SPA | 2 | done |
| legacy vaga PII | `/api/busca-*`, `/api/cadastra-curriculo` | VagasAbertas.vue → BFF | 2 | done |
| /publico unscoped | `/publico/lista-*` | público | 3 | done |
| simularUsuario | PUT `/g/usuarios/simularUsuario` | Usuarios.vue | 3 | done |
| TrustProxies * | global | deploy | 3 | done |
| Horizon id==1 | `/horizon` | ops | 3 | done |
| Assistente historico | assistente-manual | Vue | 3 | done |
| Arquivo path | StorageS3 / anexoShow | downloads | 3 | done |
| DomPDF placeholders | carta oferta PDF | PDF | 3 | done |
| CORS * | config/cors.php | API browser | 4 | done |
| channels ACL | routes/channels.php | Echo | 4 | done |
| mybp:dump SSH | Artisan | ops | 4 | done |
| S3 temporaryUrl | services/commands | cloud | 4 | done |

API v3: modelo saudável — não alterar fluxo, só espelhar padrão de credencial.

## Smoke / aceite

- PHPUnit Feature em `:memory:` (perfil, XSS, legacy 401, SPA v2).
- Portal vagas via BFF; SPA v2 com token scoped; carta-oferta sem token no JS.
- Sem literais de segredo; `.env.example` com placeholders.
- Docs API v2 com nota de breaking change do legado.

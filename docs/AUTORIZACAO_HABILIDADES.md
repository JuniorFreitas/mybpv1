# Autorização por Habilidades (MyBP)

## Visão geral

Autorização web usa **habilidades** (capabilities) atribuídas a **papéis** (`grupo_id` do usuário).

```
User.grupo_id → Papel → papeis_habilidades → Habilidade.nome
                              ↓
                    Gate::define(nome) via middleware habilidades
                              ↓
                    can:nome / $user->can() / @can
```

API Sanctum usa `tokenCan` (`can.sanctum:`) — fluxo paralelo, sem `CarregaHabilidades`.

Cloud tem sistema próprio (`HabilidadeCloud` + `permissoes_itens_clouds`) e **não** faz parte deste catálogo.

## Convenção de nomes

Formato canônico:

```
{modulo}_{recurso}_{acao}
```

Exemplos:

| Nome | Módulo | Recurso | Ação |
|------|--------|---------|------|
| `admissao_cih` | admissao | cih | access |
| `admissao_cih_aprovar` | admissao | cih | aprovar |
| `configuracao_papel_insert` | configuracao | papel | insert |

Ações comuns: `access` (menu/rota), `view`, `insert`, `update`, `delete`, `export`, `aprovar`, + ações de domínio.

## Registry tipado (fonte de metadados)

Catálogo PHP em `app/Authorization/`:

- `HabilidadeDefinition` — VO imutável
- `HabilidadeMetaParser` — parse de `modulo`/`recurso`/`acao` + labels (`posadmissao_*` → módulo Admissão)
- `HabilidadeAliasMap` — aliases legados → nome canônico (Gates)
- `HabilidadeRegistry` — agrega catalogs, resolve aliases
- `HabilidadeResolver` — `can` / `canAny` / `canAll` com aliases
- `Catalog/CompletoCatalog` — habilidades do seeder (exceto piloto)
- `Catalog/*` piloto — Configuração, CIH, Privilégios (sobrescrevem labels)

Comandos:

```bash
php artisan habilidades:regenerar-completo-catalog   # regenera CompletoCatalog a partir do seeder
php artisan habilidades:regenerar-completo-catalog --dry-run
php artisan habilidades:sync-catalog                 # aplica metadados no banco
php artisan habilidades:sync-catalog --update-descricoes
php artisan habilidades:sync-catalog --dry-run
php artisan habilidades:auditar-aliases
php artisan habilidades:consolidar-aliases          # dry-run (padrão)
php artisan habilidades:consolidar-aliases --apply  # merge/rename alias → canônico (HabilidadeAliasMap)
```

Middleware extra: `can.any:hab1,hab2` (OR) — usado em Movimentação (qualquer aba).

Colunas aditivas em `habilidades`: `modulo`, `recurso`, `acao` (+ unique em `nome` quando não houver duplicatas).

## Runtime

1. Middleware `habilidades` (`CarregaHabilidades`) valida user/papel ativos, carrega nomes (cache TTL 5 min) e registra Gates.
2. Aliases declarados no registry também viram Gates apontando para o nome canônico.
3. Rotas usam `can:{habilidade}`; controllers usam `$this->authorize()` / `$user->can()`.
4. Frontend recebe flags JSON — só UI; segurança real é no backend.

Invalidar cache de nomes após CRUD de habilidades: `CarregaHabilidades::forgetNomesCache()`.

## UI de grupos (papéis)

Em Configurações → Grupos de Usuários, permissões são agrupadas **Módulo → Recurso → Ação**, com toggle por recurso. Metadados vêm do registry (fallback heurístico).

## Piloto / cobertura

- **Configuração / CIH / Privilégios:** catalogs dedicados com labels refinados
- **Demais módulos:** `CompletoCatalog` gerado a partir do seeder + `HabilidadeMetaParser`
- **Rotas web autenticadas:** módulos de negócio protegidos com `can:` / `can.any:` em `routes/web.php`
- **Auth-only intencional:** dashboard, downloads, perfil próprio, notificações, autocomplete/helpers compartilhados, NPS do usuário, bp-chamados
- Cloud ACL (`HabilidadeCloud`) continua paralelo — ver [AUTORIZACAO_CLOUD.md](AUTORIZACAO_CLOUD.md)

## Como adicionar uma habilidade nova

1. Preferir catalog em `app/Authorization/Catalog/` (módulo correspondente).
2. Incluir no seeder legado se ainda for necessário em ambientes novos.
3. Rodar `habilidades:sync-catalog`.
4. Proteger rota com `can:` e/ou `authorize` no controller.
5. Expor flag na UI se necessário.

## Policies Laravel

- `ItensCloudPolicy` — view/update/delete/move/review/approve
- `Gate::define('habilidade', ...)` + `HabilidadePolicy` — wrapper do `HabilidadeResolver`
- Cloud multi-grupo: `User::idsGruposCloud()`, `TemPermissao` e capacidades em união

## Rename canônico

Lote 1 (`HabilidadeAliasMap`): `posadmissao`/`pos_admissao` → `admissao_pos_admissao`; dossiê/avaliação 90 dias.

Lote 2: família `posadmissao_*` → `admissao_pos_*` (form_rh/adm/ssma, avaliar, desmobilizar, entrevista_desligamento). Aliases permanecem nos Gates.

## Checkpoint / continuar depois

Estado completo (feito + backlog): [`.notebook/checkpoint-autorizacao-2026-09-29.md`](../.notebook/checkpoint-autorizacao-2026-09-29.md).

## Fora de escopo (decisão consciente)

- Pacote Spatie Permission
- Merge físico das tabelas `habilidades` ↔ `habilidade_clouds` (unificação lógica via `CloudAuthorizationService`)

## Implicação insert ⇒ access

Quem tem `{skill}_insert` passa em checagens de `{skill}` (access), se `{skill}` existir no catálogo/sistema.

Aplicado em: `HabilidadeImplication`, Gates (`CarregaHabilidades`), `HabilidadeResolver`, `Sistema::permitirLinks`.

## Concessão operacional

```bash
php artisan habilidades:conceder-relacionadas --dry-run
php artisan habilidades:conceder-relacionadas
```

Regras: `admissao_processo` → `admissao_controle_exames`; `relatorio_relatorios` → `relatorio_nps` (somente papéis `empresa_id` MyBP).

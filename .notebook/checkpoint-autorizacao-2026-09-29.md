# Checkpoint — Autorização MyBP (pausar / continuar)

> **Status:** pausado em 2026-09-29 ~21:12 (UTC-3)  
> **Próxima sessão:** ler este arquivo primeiro, depois `docs/AUTORIZACAO_HABILIDADES.md` e `docs/AUTORIZACAO_CLOUD.md`.

Tags: checkpoint · permissao · habilidades · cloud · rename

---

## Onde paramos

Trabalho de **autorização por habilidades** (foundation → catalog completo → enforcement de rotas → aliases lote 1+2 → ponte Cloud → multi-grupo → Policies locais) está **funcionalmente fechado** no código local/Docker.

Não há to-dos abertos dessa frente. Itens listados em “ainda fazer” são **opcionais / explícitos fora de escopo**, não bloqueadores.

---

## Feito (checklist)

### Foundation (registry tipado)
- [x] `HabilidadeDefinition`, `HabilidadeRegistry`, `HabilidadeResolver`, `HabilidadeMetaParser`
- [x] Catalogs: `CompletoCatalog`, `ConfiguracaoCatalog`, `AdmissaoCihCatalog`, `PrivilegioCatalog`
- [x] Migration aditiva `habilidades.modulo|recurso|acao` + unique `nome`  
  → `database/migrations/2026_09_29_130000_add_modulo_recurso_acao_to_habilidades_table.php`
- [x] Commands: `habilidades:sync-catalog`, `habilidades:regenerar-completo-catalog`
- [x] `CarregaHabilidades`: cache TTL 300s + aliases + `HabilidadeImplication` (insert ⇒ access)
- [x] Middleware `can.any:` (`CanAnyHabilidade`)
- [x] UI papéis: agrupamento Módulo → Recurso → Ação
- [x] Docs: `docs/AUTORIZACAO_HABILIDADES.md`

### Skills novas + concessão
- [x] `admissao_controle_exames`, `relatorio_nps` no seeder/catalog + sync Docker
- [x] Menu NPS com `@can('relatorio_nps')` (+ empresa MyBP)
- [x] `habilidades:conceder-relacionadas`  
  - `admissao_processo` → `admissao_controle_exames`  
  - `relatorio_relatorios` → `relatorio_nps` (só `empresa_id` MyBP)

### Enforcement de rotas
- [x] Módulos de negócio em `routes/web.php` com `can:` / `can.any:`
- [x] Auth-only intencional documentado (dashboard, perfil, autocomplete, notificações, etc.)
- [x] Hardening: perfil próprio/`usuario_usuarios`; `simularUsuario` com `can:usuario_usuarios`

### Rename canônico
- [x] Lote 1 (`HabilidadeAliasMap`): menu posadmissao, dossiê, avaliação 90 dias
- [x] Lote 2: `posadmissao_*` → `admissao_pos_*` (forms, avaliar, desmobilizar, entrevista)
- [x] Commands: `habilidades:auditar-aliases`, `habilidades:consolidar-aliases [--apply]`
- [x] Consolidação **aplicada no Docker local** (auditoria: 0 pares pendentes)
- [x] Aliases permanecem nos Gates para compatibilidade

### Cloud (ponte lógica, tabelas separadas)
- [x] `CloudCapabilityCatalog` + `CloudAuthorizationService`
- [x] Docs: `docs/AUTORIZACAO_CLOUD.md`
- [x] Anexos Cloud: Visualizar / Download / Deletar via service
- [x] `ItensCloudController`: Policies delete/move/review/approve/update
- [x] Multi-grupo: `User::idsGruposCloud()` + `TemPermissao` + união de capacidades
- [x] Fix pivot `GrupoClouds`: `user_id` → `grupo_cloud_id`
- [x] `ItensCloudPolicy` + `Gate::define('habilidade')` + `HabilidadePolicy`

### Testes
- [x] Suite Authorization/Cloud/Policies (~25 testes) passando em Docker  
  `php artisan test --filter='Authorization|CloudAuthorization|HabilidadePolicy|HabilidadeAlias'`

---

## Arquivos-chave (mapa rápido)

| Área | Path |
|------|------|
| Registry | `app/Authorization/*` |
| Cloud catalog/service | `app/Authorization/Cloud/`, `app/Services/Cloud/CloudAuthorizationService.php` |
| Policies | `app/Policies/ItensCloudPolicy.php`, `HabilidadePolicy.php` |
| Commands | `app/Console/Commands/Habilidades*.php` |
| Middleware | `app/Http/Middleware/CarregaHabilidades.php`, `CanAnyHabilidade.php` |
| Rotas | `routes/web.php` |
| Docs | `docs/AUTORIZACAO_HABILIDADES.md`, `docs/AUTORIZACAO_CLOUD.md` |
| Notebook curto | `.notebook/autorizacao-habilidades.md` |

---

## Comandos úteis (Docker)

```bash
docker compose exec mybpdp php artisan habilidades:auditar-aliases
docker compose exec mybpdp php artisan habilidades:consolidar-aliases          # dry-run
docker compose exec mybpdp php artisan habilidades:consolidar-aliases --apply
docker compose exec mybpdp php artisan habilidades:sync-catalog
docker compose exec mybpdp php artisan habilidades:conceder-relacionadas --dry-run
docker compose exec mybpdp php artisan test --filter='Authorization|CloudAuthorization|HabilidadePolicy|HabilidadeAlias'
```

---

## Ainda fazer (backlog opcional)

Ordem sugerida se retomar:

1. **Deploy / outros ambientes**  
   - Rodar migration `2026_09_29_130000_*`  
   - `habilidades:sync-catalog`  
   - `habilidades:auditar-aliases` + `consolidar-aliases --apply` (se houver aliases no banco)  
   - `habilidades:conceder-relacionadas` se papéis precisarem de `admissao_controle_exames` / `relatorio_nps`

2. **Operacional papéis**  
   - Revisar se todos os papéis certos têm as skills novas (NPS só MyBP; controle exames via admissão)

3. **Frontend Cloud.vue** (opcional)  
   - Capacidades ainda vêm só do grupo principal na UI (`GrupoCloud->Habilidades`); backend já une multi-grupo. Alinhar frontend à união se necessário.

4. **Enforcement Cloud restante** (opcional)  
   - `uploadAnexos` / `Atualizar` / `Detalhes` ainda podem depender só de `can:cloud` + UI

5. **Explicitamente NÃO fazer** (decisão consciente)  
   - Pacote Spatie Permission  
   - Merge físico `habilidades` ↔ `habilidade_clouds`

6. **Commit / PR**  
   - Mudanças ainda podem estar uncommitted no working tree — revisar `git status` antes de commit quando o usuário pedir

---

## Como continuar na próxima sessão

1. Abrir este checkpoint.  
2. `git status` + `git diff --stat` para ver o que ainda não foi commitado.  
3. Se for deploy: checklist “Deploy / outros ambientes” acima.  
4. Se for produto: item 3 (Cloud.vue multi-grupo) ou item 4 (upload/Atualizar).  
5. **Não** reabrir Spatie / merge de tabelas sem decisão nova do usuário.

---

## Referências de conversa

- Plano executado: rename lote 1 + Cloud ACL (`rename_e_cloud_acl_*.plan.md`)
- Entrega CIH/Movimentação/Cloud (contexto vizinho): `docs/ENTREGA_2026-09-28_29_CIH_MOVIMENTACAO_CLOUD.md`
- Gotcha FAT vs Cloud: `.notebook/fat-treinamento-cloud-404.md`

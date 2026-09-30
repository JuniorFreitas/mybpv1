# Autorização por habilidades

Papel → habilidades → Gates dinâmicos.

> **Checkpoint completo:** [.notebook/checkpoint-autorizacao-2026-09-29.md](checkpoint-autorizacao-2026-09-29.md) — feito + backlog + como retomar.

## Fonte tipada
- `HabilidadeRegistry` + `CompletoCatalog` + piloto
- Parser / Aliases / Implication (insert⇒access)
- Comandos: sync-catalog, regenerar, auditar-aliases, consolidar-aliases, conceder-relacionadas

## Rename
- Lote 1+2 aplicados no Docker local: `posadmissao_*` → `admissao_pos_*` (+ menu/dossiê/90dias)

## Cloud (unificação lógica, tabelas separadas)
- `CloudAuthorizationService` + `CloudCapabilityCatalog`
- Multi-grupo: `User::idsGruposCloud()` (principal + `user_grupo_cloud`)
- `TemPermissao` / capacidades = união dos grupos
- `ItensCloudPolicy` + `Gate::habilidade` / `HabilidadePolicy`
- Doc: `docs/AUTORIZACAO_CLOUD.md`

## Explicitamente não feito
- Spatie Permission package
- Merge físico habilidades ↔ habilidade_clouds

## Backlog curto (se retomar)
1. Deploy: migrate + sync + consolidar-aliases nos outros ambientes
2. Cloud.vue: UI de capacidades alinhada à união multi-grupo
3. Enforcement opcional: upload/Atualizar/Detalhes no Cloud

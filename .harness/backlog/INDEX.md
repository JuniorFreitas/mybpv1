# Backlog MyBPIN — Casa em Ordem

**Sprint DevTeam:** `#2 Housekeeping 2026-11` (2026-11-23 → 2026-12-20)  
**Slug:** `mybp`  
**Atualizado:** 2026-11-23 (débitos fechados; SEC-001/002 ainda adiados)  
**Fontes:** discovery harness + security-auditor + `docs/09`/`docs/10` + `.specs` + checklists

## Aprovações humanas adiadas (pedido do PO)

| # | Ação | Task | Status |
|---|------|------|--------|
| 1 | `production_deploy` | SEC-001 / DevTeam #2 | Adiado |
| 2 | `secret_change` | SEC-002 / DevTeam #3 | Adiado |

## Status por Wave

### Wave 0

| ID | Status | Nota |
|----|--------|------|
| SEC-001 | NEEDS APPROVAL | Adiado — mitigação em artefato; falta redeploy |
| SEC-002 | NEEDS APPROVAL | Adiado — código ok; falta rotação secret |
| BUG-001 | DONE | |
| SEC-003 | DONE | |
| SEC-007 | DONE | Audit `docs/security/audits/2026-11-23/` |

### Wave 1

| ID | Status |
|----|--------|
| BUG-002 | DONE |
| SEC-004 | DONE |
| SEC-005 | DONE |
| SEC-006 | DONE |
| BUG-003 | DONE |
| BUG-004 | DONE | GET `/g/usuario/autenticado` 500 — scope Cliente vs pivot |
| SEC-008 | DONE | Isolamento: ScopeClientesEmpresa + Cliente fail-closed |

### Wave 2

| ID | Status | Nota |
|----|--------|------|
| QA-001 | DONE | |
| QA-003 | DONE | Seeder + verifier + PHPUnit |
| QA-002 | DONE | Feature Aprovação Extra + suite WhatsApp |
| QA-004 | DONE | E2E regressão plataforma Playwright (52 casos) + smoke browser |
| DOC-001 | DONE | |
| DOC-002 | DONE | |

### Wave 3

| ID | Status | Nota |
|----|--------|------|
| TD-003 | DONE | |
| TD-001 | DONE | Form Requests críticos RH + multi-tenancy |
| TD-004 | DONE | 0 Validator::make; 222 FRs; QA 28+7 OK; security fixes pós-review |
| TD-005 | DONE | Soft deletes Form Requests (`SoftDeleteSchema` + unique/exists); Requests 32 OK |
| TD-002 | DONE | Controller ~257 linhas |
| FE-001 | DONE | Composition WhatsApp Preferencias + NotificacoesUsuarios |
| FEAT-002 | DONE | |
| FEAT-001 | DONE | Auto-save, activity log, materialize, menu |
| GOV-001 | DONE | |

## Já resolvido (não criar retrabalho)

- CORS, scheduler, Horizon gate, exports `can:`, filesystemzs, composer.lock Dockerfile, SSH dump via env

## Detalhe

Ver [`PROGRESSO_2026-11-23.md`](PROGRESSO_2026-11-23.md).

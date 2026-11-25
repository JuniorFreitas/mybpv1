# Progresso Housekeeping — 2026-11-23

**Sem deploy. Sem PR.** Alterações locais para revisão humana.  
**Adiado a pedido:** SEC-001 (redeploy CVE) e SEC-002 (rotação secret).

## Concluído em código (local)

| ID | Resultado |
|----|-----------|
| BUG-001 | Mutator `data_aso` + testes |
| BUG-002 | Middleware register |
| BUG-003 | WhatsApp meta scripts |
| SEC-003…006 | Recaptcha, headers, throttle, env→config |
| SEC-007 | Pentest fresco `docs/security/audits/2026-11-23/` (11 findings) |
| DOC-001/002 | docs/README, riscos, agent Vite |
| QA-001 | Testes críticos RH |
| QA-002 | `AprovacaoExtraConfigTest` + suite WhatsApp Domain |
| QA-003 | Seeder habilidades + verifier + PHPUnit Preadmissão |
| TD-001 | Form Requests Admissão/AprovacaoExtra + **UpdateRecrutamento** + **StoreFeriasPrevista** |
| TD-002 | Epic DONE — AdmissaoController ~257 linhas |
| TD-003 | `WithoutEmpresaScope` + heurística watchlist |
| FE-001 | Composition: PreferenciasForm + NotificacoesUsuarios + exports |
| FEAT-001 | Auto-save, activity log, materialize, menu + **WhatsappBloqueioMetrics** |
| FEAT-002 | Confirmado `mudanca_cargo` no fluxo |
| GOV-001 | Triagem entregue em `GOV-001-triagem.md` |
| QA WhatsApp HTTP | `WhatsappConfigHttpTest` 29 testes (templates + usuarios-notificacoes) |
| Débito §5.2 | Scripts meta OK; `User::enviaWhatsApp`→gate; docs legais via gate; useConfig tipo; métricas quiet no status |
| TD-001+ | Form Requests + multi-tenancy (`ValidatesTenantOwned`): Movimentações, Admissão Prevista, Mudança Cargo, Aprovação Extra users |
| TD-004 | DONE — 0 Validator em Controllers; 222 FRs; QA 28+7 OK; fixes tenant pós-security |
| TD-005 | DONE — Soft deletes em exists/unique Form Requests; `SoftDeleteSchema`; suite Requests 32 OK |
| BUG-004 | DONE — `getUsuario` null-safe tenant (`area_id`); sem `withoutGlobalScopes` no pivot |
| SEC-008 | DONE — `ScopeClientesEmpresa` intersecta `ClientesEmpresa`; `Cliente` fail-closed; testes 13 OK |
| QA-004 | DONE — E2E regressão plataforma (`tests/e2e/regressao`, 52 casos); smoke Cursor Browser; fix index movimentação 500 |
| SEC-002 código | Confirmado via config/env; **resta só rotação** humana |

## Ainda adiado (humano)

| ID | Motivo |
|----|--------|
| SEC-001 | Deploy produção — approval #1 |
| SEC-002 | Rotação secret Dynamus/SSM — approval #2 |

## QA regressão (2026-11-23)

- **PASS** — 235 testes / 613 assertions (Docker `mybpdp`, SQLite `:memory:`)
- Fix: fixture `centro_custos` em `ValidadorLinhaPlanilhaAdmissaoTest` (DEF-001)
- Residual: SEC-001/002 adiado

## QA E2E browser (2026-11-23)

- **PASS** smoke empresa 104 · usuário Suporte Mybp (`dev@mybp.com.br`)
- Telas: Admissão Processo, Pré-admissão, Aprovação Extra, WhatsApp, Documentos Pré-admissão (18), Pós-admissão (133)
- MFA restaurado; senha temp ainda em users 39811/50393 — rotacionar

## Pós-revisão (humano)

1. Revisar diff local (sem PR ainda).
2. Quando autorizar: SSM `ZAPDYNAMUS_*` + rotacionar key + redeploy SEC-001.
3. Em ambientes: `HabilidadesTableSeeder` se menu Documentos Pré-admissão não aparecer.

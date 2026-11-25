# Regressão E2E — Plataforma MyBPIN (Playwright)

Suíte reutilizável de smoke + asserts de telas críticas. Complementa o módulo WhatsApp em [`WHATSAPP_PLAYWRIGHT_TESTES.md`](./WHATSAPP_PLAYWRIGHT_TESTES.md).

---

## 1. Pré-requisitos

| Item | Valor |
|------|-------|
| App | `http://localhost:8000` (`PLAYWRIGHT_BASE_URL`) |
| Node | >= 24 |
| Sessão | `playwright/.auth/user.json` (gitignored) |
| Usuário sugerido | Suporte com menu completo (ex.: empresa 104) |

```bash
npm install
npx playwright install chrome

# Login (manual no Inspector OU env)
npm run test:e2e:regressao:login

# Auto-login (sem pause)
PLAYWRIGHT_FORCE_LOGIN=1 PLAYWRIGHT_LOGIN='seu.login' PLAYWRIGHT_PASSWORD='***' \
  npm run test:e2e:regressao:login

# Regressão (headless)
npm run test:e2e:regressao

# Regressão headed
npm run test:e2e:regressao:headed

# Plataforma (regressão + WhatsApp)
npm run test:e2e:plataforma
```

---

## 2. Estrutura

```text
tests/e2e/regressao/
  auth.setup.ts          # login → storageState
  helpers.ts             # ROTAS_REGRESSAO + expectPaginaOperacional
  smoke.rotas.spec.ts    # 1 caso por rota P0/P1
  admissao.spec.ts       # UI Processo / Pré / Pós / Histórico / Exames
  planejamento.spec.ts   # Movimentação + Req vaga + abas
  recrutamento.spec.ts
  cadastros.spec.ts
  aprovacao-extra.spec.ts
```

Project Playwright: `regressao-chrome` (depende de `setup`).

---

## 3. Catálogo de casos

### 3.1 Smoke de rotas (`smoke.rotas.spec.ts`)

Cada ID em `ROTAS_REGRESSAO` (`helpers.ts`):

| Prioridade | Critério de aceite |
|------------|--------------------|
| P0 | Abre autenticado, HTTP &lt; 500, sem redirect para `/g/login` |
| P1 | Idem; `permitirNegado` anota 403 sem falhar |
| P2 | Opcional (`PLAYWRIGHT_INCLUIR_P2=1`) |

IDs P0 (resumo): `AUTH-01`, `ADM-01…05`, `PLA-01/02/04…07`, `REC-01`, `CFG-01`, `CAD-01/02`.

### 3.2 Admissão

| ID | Cenário | Esperado |
|----|---------|----------|
| ADM-UI-01 | Processo | Filtros + heading Processo |
| ADM-UI-02 | Pré-admissão | Conteúdo pré-admissão |
| ADM-UI-03 | Pós-admissão | Listagem pós |
| ADM-UI-04 | Histórico | Tela histórico |
| ADM-UI-05 | Controle exames | Exame/ASO |

### 3.3 Planejamento

| ID | Cenário | Esperado |
|----|---------|----------|
| PLA-UI-01 | Hub movimentação | Abas/textos movimentação |
| PLA-UI-02 | Req. vaga | Listagem |
| PLA-UI-03…06 | Férias / Demissão / Transferência / Valor extra | Listagens |

### 3.4 Recrutamento / Cadastros / Aprovação Extra

| ID | Cenário |
|----|---------|
| REC-UI-01/02 | Lista + filtros |
| CAD-UI-01…03 | Centro custo, vagas, docs pré |
| CFG-UI-01 | Config aprovação extra |

---

## 4. Extensão (próximos ciclos)

1. Adicionar rota em `ROTAS_REGRESSAO` + ID no catálogo.
2. Specs de fluxo (criar/editar) só com fixtures isoladas — nunca em massa na base compartilhada.
3. Deep WhatsApp permanece em `tests/e2e/whatsapp`.
4. CI: job com `PLAYWRIGHT_LOGIN`/`PASSWORD` em secrets + `npx playwright test --project=regressao-chrome`.

---

## 5. Evidências

- Relatório HTML: `output/playwright/report`
- Falhas: screenshot/vídeo em `output/playwright/test-results`
- Smoke Cursor Browser: `output/playwright/cursor-browser-smoke-2026-11-23.json`
- Task harness: `.harness/backlog/QA-004.md`

## 6. Nota sobre Movimentação

As URLs `.../ferias-prevista`, `demissao-prevista`, `transferencia-prevista`, `valor-extra-prevista`, `admissoes-prevista`, `intermitente-fixo-prevista`, `mudanca-cargo` redirecionam para o hub `/g/planejamento/movimentacao` (listagem SPA). O smoke aceita esse redirect.

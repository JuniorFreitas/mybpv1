# Mapa de testes E2E — WhatsApp MyBP (Playwright)

Documento de referência para QA automatizado e manual do módulo WhatsApp. Complementa o [checklist manual](./WHATSAPP_CONFIGURACAO_EMPRESA.md#12-test-plan-manual) e o [status de entrega](./WHATSAPP_STATUS_ENTREGA.md#54-test-plan-manual--não-executado-em-qa-formal).

---

## 1. Pré-requisitos

| Item | Valor |
|------|-------|
| App local | `http://localhost:8000` (Docker) |
| Login | `/g/login` |
| Node | >= 24 |
| Browser | Chrome (headed) |
| **Empresa E2E** | **`empresa_id = 100`** (homologação local) + habilidade `configuracao_whatsapp` |

```bash
# Instalar dependências Playwright (uma vez)
npm install
npx playwright install chrome

# Abrir Chrome e aguardar login manual
npm run test:e2e:whatsapp:login
```

Após login, a sessão fica em `playwright/.auth/user.json` (não versionar).

---

## 2. Regra de negócio (matriz de condições)

Todo teste de **envio** ou **controle visível na UI** deve cruzar estas dimensões:

| # | Condição | Onde configurar | Efeito se falhar |
|---|----------|-----------------|------------------|
| A | `cliente_configs.envia_whatsapp = true` | Admin → Clientes | API `tipos.*` = false; alerta na config RH |
| B | Módulo habilitado em `modulos_habilitados` | Config WhatsApp → Módulos | Tipo do módulo bloqueado |
| C | Tel. principal `tipo = whatsapp` (candidato) | Currículo / telefones | Checkbox/botão WhatsApp oculto |
| D | `usuario_whatsapp_preferencias.receber = true` | Dashboard / Usuários | Usuário interno não recebe |
| E | Tel. usuário tipo WhatsApp | Cadastro usuário | Aba Usuários marca “não apto” |
| F | `_whatsapp_meta` no backend | Automático nos controllers migrados | Job bloqueia envio |

**Legenda nos cenários:** `A+B+C` = mínimo para candidato; `A+B+D+E` = mínimo para usuário interno.

---

## 3. Rotas e specs Playwright

| Área | URL | Spec | Prioridade |
|------|-----|------|------------|
| Login / sessão | `/g/login` | `auth.setup.ts` | P0 |
| API status | `GET /g/configuracoes/whatsapp/status` | `whatsapp.api.spec.ts` | P0 |
| Config RH | `/g/configuracoes/whatsapp` | `whatsapp.config.spec.ts` | P0 |
| Admin clientes | `/g/administracao/clientes` | `whatsapp.config.spec.ts` | P1 |
| Recrutamento | `/g/recrutamentos` | `whatsapp.fluxos.spec.ts` | P0 |
| Controle exames | `/g/controle-exames` | `whatsapp.fluxos.spec.ts` | P0 |
| Pré-admissão | `/g/preadmissao` | `whatsapp.fluxos.spec.ts` | P0 |
| Resultado integrado | `/g/resultado-integrado` | `whatsapp.fluxos.spec.ts` | P1 |
| Parecer rota | `/g/parecer-rota` | `whatsapp.fluxos.spec.ts` | P1 |
| Intermitente | `/g/apontamento/intermitente` | *(a criar)* | P2 |
| Usuários / preferências | `/g/usuarios`, `/g/dashboard` | `whatsapp.fluxos.spec.ts` | P1 |
| Movimentação (aprov.) | fluxo aprovação (sem URL fixa) | *(manual / job)* | P2 |

---

## 4. Cenários por módulo

### 4.1 Configuração RH (`/g/configuracoes/whatsapp`)

| ID | Cenário | Passos | Resultado esperado |
|----|---------|--------|-------------------|
| CFG-01 | Acesso com habilidade | Menu Customizações → WhatsApp | Tela carrega 4 abas |
| CFG-02 | Empresa sem WhatsApp (A=false) | Acessar com `envia_whatsapp=false` | Alerta; edição limitada |
| CFG-03 | Aba Contato | Salvar telefone/nome remetente | Persiste e aparece no preview |
| CFG-04 | Aba Templates | Editar corpo + preview | Placeholders renderizam |
| CFG-05 | Restaurar template | Botão restaurar padrão | Volta ao texto de `config/whatsapp_templates.php` |
| CFG-06 | Aba Módulos | Desligar **Recrutamento** | `status.tipos.recrutamento_*` = false |
| CFG-07 | Aba Usuários | Listar usuários | Aptos / não aptos por tel. WhatsApp |
| CFG-08 | Preferência usuário | Toggle receber=false | Gate bloqueia envio interno |

### 4.2 Admin — Clientes (`/g/administracao/clientes`)

| ID | Cenário | Passos | Resultado esperado |
|----|---------|--------|-------------------|
| ADM-01 | Aba WHATSAPP | Editar cliente com `empresa_id` correto | Módulos salvam na empresa certa |
| ADM-02 | Liberar WhatsApp | `envia_whatsapp=true` | RH vê config editável; cache invalida |
| ADM-03 | Desligar WhatsApp | `envia_whatsapp=false` | Todos os tipos off na API |

### 4.3 API status

| ID | Cenário | Request | Resultado |
|----|---------|---------|-----------|
| API-01 | Estrutura | `GET .../status` | `{ whatsapp_liberado, modulos, tipos }` |
| API-02 | A=false | — | Todos `tipos.*` = false |
| API-03 | Módulo off | Desligar Exames | `tipos.exame_encaminhamento` = false |
| API-04 | Default retroativo | Empresa sem `empresa_whatsapp_configs` | Módulos considerados on (se A=true) |

### 4.4 Recrutamento — `recrutamento_selecao`, `recrutamento_provas`

| ID | Condições | Passos | UI / backend |
|----|-----------|--------|--------------|
| REC-01 | A+B+C | Selecionar candidato com WhatsApp | Checkbox “Enviar WhatsApp” visível |
| REC-02 | A+B, C=false | Tel. principal celular | Checkbox oculto |
| REC-03 | A, B=false | Módulo Recrutamento off | Checkbox oculto |
| REC-04 | A+B+C | Confirmar seleção com WhatsApp marcado | Job na fila com meta correta |
| REC-05 | REC-04 + provas | Enviar links de provas | Tipo `recrutamento_provas` |

### 4.5 Controle de exames — `exame_encaminhamento`

| ID | Condições | Passos | Resultado |
|----|-----------|--------|-----------|
| EXM-01 | A+B+C | Encaminhar exame | Toggle WhatsApp visível |
| EXM-02 | B=false | Módulo Exames off | Sem toggle |
| EXM-03 | C=false | Sem tel. WhatsApp | Sem toggle |
| EXM-04 | A+B+C | Enviar com toggle on | Mensagem com clínica/datas |

### 4.6 Pré-admissão — `exame_encaminhamento`, `admissao_documentos`

| ID | Condições | Passos | Resultado |
|----|-----------|--------|-----------|
| PRE-01 | A+B+C | Finalizar → encaminhar exame | Toggle WhatsApp |
| PRE-02 | A+B+C | Reenviar e-mail documentos | Tipo `admissao_documentos` (não confundir com exame) |
| PRE-03 | B=false | — | Sem opção WhatsApp |

### 4.7 Resultado integrado — `admissao_documentos`, `admissao_exame`

| ID | Condições | Passos | Resultado |
|----|-----------|--------|-----------|
| RES-01 | A+B+C | Switch documentos | Visível se módulo Admissão on |
| RES-02 | A+B+C | Switch exame admissional | Tipo `admissao_exame` |
| RES-03 | Prop `telefonePrincipal` | Candidato sem WhatsApp | Switches ocultos |

### 4.8 Parecer rota — `parecer_rota_transporte`

| ID | Condições | Passos | Resultado |
|----|-----------|--------|-----------|
| PAR-01 | A+B+C | Botão WhatsApp na linha | Visível e envia rota |
| PAR-02 | C=false | — | Botão oculto |

### 4.9 Intermitente — `intermitente_convocacao`

| ID | Condições | Passos | Resultado |
|----|-----------|--------|-----------|
| INT-01 | A+B+C | Convocar intermitente | Opção WhatsApp |
| INT-02 | B=false | Módulo off | Sem WhatsApp |

### 4.10 Carta oferta — `carta_oferta_gerencial`, `carta_oferta_sgi`

| ID | Condições | Passos | Resultado |
|----|-----------|--------|-----------|
| CAR-01 | A+B | Gerencial envia carta | Gate + anexo |
| CAR-02 | A+B | Integração SGI | `SgiEnvia()` passa pelo gate |

### 4.11 Movimentação — `movimentacao_aprovacao`

| ID | Condições | Passos | Resultado |
|----|-----------|--------|-----------|
| MOV-01 | A+B+D+E | Aprovar movimentação | WhatsApp para aprovadores aptos |
| MOV-02 | B=false | Desligar Movimentação | Só e-mail; sem WhatsApp |
| MOV-03 | D=false | Usuário opt-out | Não recebe WhatsApp |

### 4.12 Preferências e telefone usuário

| ID | Cenário | Resultado |
|----|---------|-----------|
| USR-01 | Modal telefone no login | Cadastro tipo WhatsApp |
| USR-02 | Card preferências dashboard | Toggle persiste |
| USR-03 | Admin edita em Usuários | Reflete na aba WhatsApp config |

---

## 5. Dados de teste sugeridos (homologação)

Preparar no ambiente antes de desmarcar `test.skip` nos specs:

| Fixture | Descrição |
|---------|-----------|
| Empresa A | `envia_whatsapp=true`, todos módulos on |
| Empresa B | `envia_whatsapp=false` |
| Candidato WA | Tel. principal `tipo=whatsapp`, número válido |
| Candidato CEL | Tel. principal `tipo=celular` |
| Usuário apto | Tel. WhatsApp + `receber=true` |
| Usuário opt-out | `receber=false` |
| Admin MyBP | Habilidade `administracao_clientes` |

---

## 6. Comandos npm

```bash
# 1) Login manual (Chrome + pause)
npm run test:e2e:whatsapp:login

# 2) Rodar todos os testes WhatsApp (após login)
npm run test:e2e:whatsapp

# 3) UI mode (debug)
npm run test:e2e:whatsapp:ui

# 4) Relatório HTML
npx playwright show-report output/playwright/report
```

Variável opcional: `PLAYWRIGHT_BASE_URL=http://homol.exemplo.com npm run test:e2e:whatsapp`

Outra empresa em homologação: `PLAYWRIGHT_EMPRESA_ID=123 npm run test:e2e:whatsapp` (padrão: **100**).

---

## 7. Arquivos do projeto

```
playwright.config.ts
playwright/.auth/user.json          # sessão (gitignore)
tests/e2e/whatsapp/
  auth.setup.ts                     # login manual + pause
  helpers.ts
  whatsapp.api.spec.ts
  whatsapp.config.spec.ts
  whatsapp.fluxos.spec.ts
```

---

## 8. Ordem recomendada de execução QA

1. **CFG-02, API-01..04** — gate empresa/módulo  
2. **CFG-06 + REC-03, EXM-02, MOV-02** — desligar módulo e validar UI + API  
3. **REC-01..02, EXM-01..03, PRE-01..02** — telefone principal WhatsApp  
4. **USR-02..03, MOV-03** — preferências usuário  
5. **CFG-04..05** — templates customizados + envio real (Horizon)  
6. **ADM-01..03** — fluxo admin  

---

*Última atualização: jun/2026. Alinhar com `TipoMensagemWhatsapp` e `config/whatsapp_templates.php` ao adicionar novos tipos.*

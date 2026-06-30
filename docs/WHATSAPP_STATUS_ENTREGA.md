# Status de entrega — Módulo WhatsApp por Empresa

Documento consolidado do que foi **implementado** e do que **ainda falta** no módulo de configuração e envio de WhatsApp do MyBP.

- **Data de referência:** jun/2026  
- **Documentação técnica (TRD):** [WHATSAPP_CONFIGURACAO_EMPRESA.md](./WHATSAPP_CONFIGURACAO_EMPRESA.md)  
- **Integrações gerais:** [08-integracoes.md](./08-integracoes.md)

---

## 1. Objetivo do projeto

Centralizar a configuração de WhatsApp **por empresa/cliente** e garantir que todo envio respeite:

1. **Empresa liberada** — `cliente_configs.envia_whatsapp = true` (Admin → Clientes)  
2. **Módulo habilitado** — toggle na aba **Módulos** da config WhatsApp  
3. **Preferência do usuário** (quando há destinatário interno)  
4. **Metadados obrigatórios** — `_whatsapp_meta` com tipo e `empresa_id`

Sem as duas primeiras condições (empresa + módulo), **não há notificação** — nem na UI operacional nem no backend.

---

## 2. Regra de negócio (resumo)

| Condição | Onde configura | Onde valida |
|----------|----------------|-------------|
| WhatsApp liberado na empresa | Admin → Clientes → `envia_whatsapp` | `WhatsappNotificationGateService::empresaPermiteWhatsapp()` |
| Módulo habilitado | Customizações → WhatsApp → Módulos (ou Admin → Clientes → aba WHATSAPP) | `WhatsappConfigService::isModuloHabilitado()` |
| Preferência do usuário | Dashboard / Usuários / aba Usuários da config | `usuario_whatsapp_preferencias` |
| Tipo de mensagem válido | Enum + `config/whatsapp_templates.php` | `ZapNotificacao::meta()` + gate |

**Default retroativo:** empresa sem registro em `empresa_whatsapp_configs` → todos os módulos considerados habilitados (compatibilidade com clientes antigos).

---

## 3. O que foi implementado

### 3.1 Domínio e infraestrutura (V1)

| Item | Status | Detalhe |
|------|--------|---------|
| Enum de tipos | ✅ | `app/Domain/Whatsapp/Enums/TipoMensagemWhatsapp.php` — 10 tipos |
| Catálogo de templates | ✅ | `config/whatsapp_templates.php` — corpos padrão + mapa tipo → módulo |
| Config por empresa | ✅ | `WhatsappConfigService` — contato, templates, módulos, cache (TTL 15 min) |
| Renderização unificada | ✅ | `WhatsappMessageFactory` + `WhatsappTemplateRenderer` |
| Rodapé MyBP obrigatório | ✅ | `WhatsappMessageFactoryRodapeTest` |
| Models | ✅ | `EmpresaWhatsappConfig`, `EmpresaWhatsappTemplate`, `UsuarioWhatsappPreferencia` |
| Integração Dynamus | ✅ | `ZapNotificacao` → `JobSendNotificacaoWhatsApp` → `ZapDynamusService` |
| Delay anti-spam na fila | ✅ | `ZapNotificacao::calcularDelayFila()` |

### 3.2 Módulos, gate e preferências (V2)

| Item | Status | Detalhe |
|------|--------|---------|
| Módulos por empresa (JSON) | ✅ | Campo `modulos_habilitados` em `empresa_whatsapp_configs` |
| Gate central | ✅ | `WhatsappNotificationGateService::podeEnviar()` |
| Meta obrigatória na fila | ✅ | `ZapNotificacao::deveEnviarWhatsapp()` bloqueia sem `_whatsapp_meta` |
| Revalidação no job | ✅ | `JobSendNotificacaoWhatsApp` reexecuta o gate antes de enviar |
| Leitura fresca de módulos | ✅ | `resolveModulosHabilitadosParaEnvio()` lê do banco em workers Horizon |
| Preferências por usuário | ✅ | Tabela `usuario_whatsapp_preferencias` + APIs |
| Telefone WhatsApp do usuário | ✅ | `WhatsappUsuarioTelefoneResolver` |
| Gestão de usuários (admin) | ✅ | `WhatsappUsuarioNotificacaoService` + aba Usuários na UI |
| `SgiEnvia()` no gate | ✅ | Anexos carta oferta SGI passam por `deveEnviarWhatsapp()` |
| Invalidação de cache | ✅ | `EmpresaWhatsappConfig` observer + `ClienteConfig::booted()` quando `envia_whatsapp` muda |
| API status leve | ✅ | `GET /g/configuracoes/whatsapp/status` → `whatsapp_liberado`, `modulos`, `tipos` |

### 3.3 Banco de dados e seeders

| Arquivo | Status |
|---------|--------|
| `2026_06_23_100000_create_empresa_whatsapp_tables.php` | ✅ |
| `2026_06_23_100001_add_configuracao_whatsapp_habilidade.php` | ✅ |
| `2026_06_24_100000_add_whatsapp_notificacao_preferences.php` | ✅ |
| `2026_06_24_100001_add_preferencias_notificacao_whatsapp_habilidade.php` | ✅ |
| `HabilidadesTableSeeder` — `configuracao_whatsapp` | ✅ |
| `HabilidadesTableSeeder` — `preferencias_notificacao_whatsapp` | ✅ |
| `WhatsappTemplatePadraoSeeder` | ✅ |

### 3.4 APIs e controllers

| Endpoint / controller | Status | Observação |
|----------------------|--------|------------|
| `WhatsappConfigController` — CRUD config, templates, módulos | ✅ | Admin usa `?empresa_id=` |
| `WhatsappConfigController::status()` | ✅ | Qualquer usuário autenticado |
| `UserController` — preferências pessoais e modelo admin | ✅ | |
| Rotas em `routes/web.php` — grupo `configuracoes/whatsapp` | ✅ | |

### 3.5 Pontos de envio por módulo RH

Todos os fluxos abaixo usam `WhatsappMessageFactory` + `ZapNotificacao::enviar()` com `_whatsapp_meta`.  
Controllers marcados com **gate explícito** validam empresa + módulo **antes** de montar/enviar.

| Módulo | Tipo(s) | Ponto de envio | Gate explícito | UI condicional |
|--------|---------|----------------|----------------|----------------|
| Recrutamento | `recrutamento_selecao`, `recrutamento_provas` | `RecrutamentoController` | ✅ | ✅ (`permite_envio_whatsapp` via API) |
| Exames | `exame_encaminhamento` | `ControleExameController`, `PreAdmissaoController` (finalizar) | ✅ | ✅ `ControleExames.vue`, pré-admissão |
| Admissão | `admissao_documentos`, `admissao_exame` | `PreAdmissaoController` (e-mail), `ResultadoIntegrado` | ✅ | ✅ `FormResultadoIntegrado.vue`, pré-admissão |
| Intermitente | `intermitente_convocacao` | `IntermitenteController` | ✅ | ⚠️ Sem `whatsappTipoHabilitado` na UI (envio automático no fluxo) |
| Carta Oferta | `carta_oferta_gerencial`, `carta_oferta_sgi` | `CartaOfertaGerencialController`, `IntegraSgiMybpController` | ✅ (via `ZapNotificacao`) | ⚠️ Sem toggle dedicado na UI operacional |
| Transporte | `parecer_rota_transporte` | `ParecerRotaWhatsappService`, `ParecerRotaController` | ✅ | ✅ parecer de rota |
| Movimentação | `movimentacao_aprovacao` | `MovimentacaoWhatsappNotificationService` + 7 jobs `JobNotificacaoRecursiva` | ✅ | N/A (notificação interna) |

### 3.6 Frontend (Vue 3)

| Item | Status |
|------|--------|
| Tela RH — Customizações → WhatsApp | ✅ `WhatsappConfig.vue` |
| Aba WHATSAPP em Admin → Clientes | ✅ com prop `empresa-id` |
| Editor de templates + preview | ✅ `WhatsappTemplateEditor.vue`, `WhatsappPreviewModal.vue` |
| Aba Módulos (toggles) | ✅ |
| Aba Usuários (telefone + preferências) | ✅ `WhatsappNotificacoesUsuarios.vue` |
| Card dashboard — preferências pessoais | ✅ `WhatsappPreferenciasUsuario.vue` |
| Cadastro de usuários — preferências | ✅ `WhatsappPreferenciasForm.vue`, `TelefoneUsuarioModal.vue` |
| Mixin operacional | ✅ `resources/js/mixins/Configuracoes.js` — `whatsappTipoHabilitado()`, `whatsappModuloHabilitado()` |
| Build webpack | ✅ `resources/js/g/configuracoes/whatsapp/app.js` |

**Telas com botão WhatsApp condicionado (empresa + módulo):**

- Controle de exames (`exame_encaminhamento`)
- Pré-admissão — finalizar encaminhamento (`exame_encaminhamento`) e reenvio e-mail (`admissao_documentos`)
- Resultado integrado (`admissao_documentos`, `admissao_exame`)
- Parecer de rota (`parecer_rota_transporte`)
- Recrutamento (`permite_envio_whatsapp` vindo do gate na API)

### 3.7 Testes automatizados

```bash
docker compose exec mybpdp php artisan test --filter=Whatsapp
docker compose exec mybpdp php artisan test --filter=ZapNotificacao
```

| Arquivo de teste | Cobertura |
|------------------|-----------|
| `WhatsappNotificationGateServiceTest` | Todos os tipos × módulo desabilitado/habilitado |
| `WhatsappConfigServiceTest` | Cache, templates, módulos individuais |
| `WhatsappMessageFactoryTest` + `RodapeTest` | Renderização e rodapé |
| `WhatsappTemplateRendererTest` | Placeholders |
| `ZapNotificacaoGateTest` | Fila, meta, módulo |
| `ZapNotificacaoSgiEnviaGateTest` | `SgiEnvia()` bloqueado pelo gate |
| `ZapNotificacaoDelayTest` | Delay na fila |
| `MovimentacaoWhatsappNotificationServiceTest` | Módulo Movimentação off |
| `ParecerRotaWhatsappServiceTest` | Transporte + empresa + envio |

**Total:** 49+ testes `--filter=Whatsapp` + testes `ZapNotificacao` — todos passando em jun/2026.

### 3.8 Correções importantes realizadas

- Admin MyBP editava cliente errado → `resolveEmpresaId()` nos PUTs do `WhatsappConfigController`
- Gate falhava em jobs sem `auth()` → `Cliente::withoutGlobalScopes()` em `resolveContactData()`
- Módulo desabilitado ainda enviava → leitura direta do banco no gate + revalidação no job
- Cache stale após salvar módulos → observer + invalidação em `ClienteConfig`
- Pré-admissão (reenvio e-mail) usava tipo errado na UI → corrigido para `admissao_documentos`
- Controllers checavam só `envia_whatsapp` → migrados para `WhatsappNotificationGateService` onde aplicável

---

## 4. Inventário de arquivos principais

### Criados

```
app/Domain/Whatsapp/
  Enums/TipoMensagemWhatsapp.php
  Services/WhatsappConfigService.php
  Services/WhatsappMessageFactory.php
  Services/WhatsappTemplateRenderer.php
  Services/WhatsappNotificationGateService.php
  Services/WhatsappUsuarioTelefoneResolver.php

app/Models/EmpresaWhatsappConfig.php
app/Models/EmpresaWhatsappTemplate.php
app/Models/UsuarioWhatsappPreferencia.php

app/Http/Controllers/WhatsappConfigController.php
app/Http/Requests/WhatsappConfigRequest.php
app/Http/Requests/WhatsappTemplateRequest.php
app/Http/Requests/WhatsappUsuarioPreferenciaAdminRequest.php

app/Services/Movimentacao/MovimentacaoWhatsappNotificationService.php
app/Services/Whatsapp/WhatsappUsuarioNotificacaoService.php
app/Jobs/Movimentacao/Concerns/EnviaWhatsappNotificacaoMovimentacao.php

config/whatsapp_templates.php

resources/js/components/configuracoes/whatsapp/*
resources/js/g/configuracoes/whatsapp/app.js
resources/views/g/configuracoes/whatsapp/index.blade.php

database/migrations/2026_06_23_*
database/migrations/2026_06_24_*
database/seeders/WhatsappTemplatePadraoSeeder.php

tests/Unit/Domain/Whatsapp/*
tests/Unit/Classes/ZapNotificacao*Test.php
```

### Modificados (principais)

```
app/Classes/ZapNotificacao.php
app/Models/ClienteConfig.php
app/Jobs/JobSendNotificacaoWhatsApp.php
app/Jobs/JobEnviaZap.php

app/Http/Controllers/RecrutamentoController.php
app/Http/Controllers/ControleExameController.php
app/Http/Controllers/PreAdmissaoController.php
app/Http/Controllers/IntermitenteController.php
app/Http/Controllers/ParecerRotaController.php
app/Http/Controllers/CartaOfertaGerencialController.php
app/Http/Controllers/Api/IntegraSgiMybpController.php
app/Http/Controllers/UserController.php

app/Models/ResultadoIntegrado.php
app/Services/Entrevistas/ParecerRotaWhatsappService.php

app/Jobs/Movimentacao/*/JobNotificacaoRecursiva.php (7 jobs)

resources/js/mixins/Configuracoes.js
resources/views/g/administracao/clientes/index.blade.php
resources/views/layouts/menu.blade.php
routes/web.php
webpack.mix.js
docs/WHATSAPP_CONFIGURACAO_EMPRESA.md
docs/08-integracoes.md
```

---

## 5. O que está faltando

### 5.1 Prioridade média (produto / UX)

| Item | Descrição | Impacto |
|------|-----------|---------|
| ⬜ Auto-save ou confirmação na aba Módulos | Usuário pode sair sem salvar toggles | Configuração não aplicada sem perceber |
| ⬜ Activity log de alteração de módulos | Auditoria de quem desativou o quê | Compliance / suporte |
| ⬜ Seeder ao criar `empresa_whatsapp_configs` | Hoje default implícito = todos on | Primeiro save poderia materializar JSON |
| ⬜ Métricas Horizon — envios bloqueados pelo gate | Contador em log ou dashboard | Observabilidade |
| ⬜ Menu RH condicional | Link WhatsApp só se `envia_whatsapp` | Hoje link aparece com permissão `configuracao_whatsapp` mesmo sem empresa liberada (tela mostra alerta) |

### 5.2 Prioridade baixa / débito técnico

| Item | Descrição |
|------|-----------|
| ⬜ Scripts legados `scripts/0_*.php` | Enviam via `ZapNotificacao::enviar()` **sem** `_whatsapp_meta` → bloqueados pelo gate ou precisam migração |
| ⬜ Unificar `User::enviaWhatsApp()` com o gate | Ainda usado em `ParecerRotaWhatsappService` (redundante com gate logo abaixo) |
| ⬜ Feature tests HTTP das APIs WhatsApp | Só testes unitários hoje; falta cobertura de rotas com SQLite `:memory:` |
| ⬜ Composition API no frontend | Migrar componentes WhatsApp conforme `docs/PLANO_MIGRACAO_COMPOSITION_API_SERVICES.md` |
| ⬜ `apikey` hardcoded no `ZapDynamusService` | Risco de segurança documentado em `08-integracoes.md` |

### 5.3 UI operacional — gaps menores

| Fluxo | Situação |
|-------|----------|
| Intermitente | Backend usa gate; não há checkbox condicional na UI (convocação é fluxo automático) |
| Carta oferta gerencial / SGI | Envio via integração/controller; sem toggle visual por módulo na tela de origem |
| Recrutamento | Usa `permite_envio_whatsapp` da API (gate) — OK, mas não usa mixin `whatsappTipoHabilitado` diretamente |
| Documentos legais (empresa/SSMA) | Controllers expõem flag `whatsapp` só com `envia_whatsapp`, sem verificar módulo |

### 5.4 Test plan manual — não executado em QA formal

Checklist em [WHATSAPP_CONFIGURACAO_EMPRESA.md §12](./WHATSAPP_CONFIGURACAO_EMPRESA.md#12-test-plan-manual) — todos os itens ainda **pendentes de validação manual** em ambiente de homologação:

- [ ] Admin habilita WhatsApp; RH edita config
- [ ] Desativar Recrutamento → seleção/provas não enviam
- [ ] Desativar Movimentação → aprovações não enviam; e-mail continua
- [ ] Desativar Exames → encaminhamento não envia
- [ ] Preferência `receber=false` bloqueia usuário
- [ ] Usuário sem telefone WhatsApp aparece como não apto
- [ ] Admin configura pela aba WHATSAPP em Clientes
- [ ] Template customizado no envio real
- [ ] Restaurar template padrão
- [ ] Horizon reiniciado após salvar módulos

### 5.5 Deploy / operação

| Item | Status |
|------|--------|
| Migrations em produção | ⬜ Executar `php artisan migrate` |
| Seed de habilidades | ⬜ Confirmar `configuracao_whatsapp` e `preferencias_notificacao_whatsapp` |
| Build frontend | ⬜ `npm run prod` |
| Reiniciar Horizon após deploy | ⬜ `php artisan horizon:terminate` |
| Validação manual pós-deploy | ⬜ Ver §5.4 |

---

## 6. Scripts legados (atenção)

Os scripts abaixo ainda chamam `ZapNotificacao::enviar()` **sem** `_whatsapp_meta`. Com o gate atual, esses envios são **bloqueados** (log: `metadados de envio ausentes`):

```
scripts/0_ATUALIZACAO_MONTISOL.php
scripts/0_IMPORTACAO.php
scripts/0_VENCIMENTO_TREINAMENTOS.php
scripts/0_DOCUMENTO_CURRICULOS.php
```

**Ação recomendada:** migrar para incluir `ZapNotificacao::meta()` + tipo adequado, ou documentar/isolar como legado fora do gate.

---

## 7. Checklist rápido pós-deploy

```bash
# 1. Migrations
docker compose exec mybpdp php artisan migrate

# 2. Testes
docker compose exec mybpdp php artisan test --filter=Whatsapp

# 3. Assets
npm run prod

# 4. Workers
docker compose exec mybpdp php artisan horizon:terminate
```

**Validar no banco (exemplo empresa 123):**

```sql
SELECT envia_whatsapp FROM cliente_configs WHERE cliente_id = 123;
SELECT modulos_habilitados FROM empresa_whatsapp_configs WHERE empresa_id = 123;
```

**Validar API de status (usuário logado):**

```
GET /g/configuracoes/whatsapp/status
→ { whatsapp_liberado, modulos: {...}, tipos: {...} }
```

---

## 8. Diagrama resumido

```
Admin libera empresa (envia_whatsapp)
        +
RH/Admin habilita módulos (modulos_habilitados)
        +
Fluxo monta mensagem (WhatsappMessageFactory)
        +
ZapNotificacao::enviar([..., _whatsapp_meta])
        │
        ▼
WhatsappNotificationGateService::podeEnviar()
   ├─ empresaPermiteWhatsapp?  ──NÃO──► bloqueia
   ├─ isModuloHabilitado?      ──NÃO──► bloqueia
   └─ usuarioAceitaModulo?     ──NÃO──► bloqueia (se userId)
        │
        ▼ SIM
JobSendNotificacaoWhatsApp → gate novamente → Dynamus
```

---

## 9. Histórico de entregas (cronologia)

| Fase | Entrega |
|------|---------|
| V1 | Templates, config por empresa, tela RH, factory, cache, 10 tipos |
| V2 | Módulos JSON, gate, preferências usuário, admin clientes, movimentação |
| V2.1 | Leitura fresca no gate, revalidação no job, invalidação de cache |
| V2.2 | API `/status`, mixin frontend, UI condicional nos fluxos operacionais |
| V2.3 | `SgiEnvia()` no gate; controllers migrados para gate explícito; correção tipos na pré-admissão |

---

## 10. Próximos passos sugeridos (ordem)

1. **QA manual** — executar checklist §5.4 em homologação  
2. **Deploy** — migrations + build + Horizon  
3. **Menu condicional** — esconder link WhatsApp se empresa não liberada  
4. **Activity log** — módulos alterados  
5. **Scripts legados** — meta ou isolamento  
6. **Feature tests HTTP** — APIs de config  

---

*Última atualização: jun/2026. Manter este arquivo alinhado ao [backlog técnico](./WHATSAPP_CONFIGURACAO_EMPRESA.md#11-melhorias-futuras-backlog) ao concluir novas entregas.*

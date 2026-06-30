# TRD — Configurações de WhatsApp por Empresa/Cliente

Documento vivo do módulo de WhatsApp customizado por empresa. Última revisão: **jun/2026** (módulos, gate de envio, preferências e administração de clientes).

> **Status de entrega (feito vs. pendente):** [WHATSAPP_STATUS_ENTREGA.md](./WHATSAPP_STATUS_ENTREGA.md)

---

## 1. Objetivo

Centralizar configurações de WhatsApp por empresa/cliente:

- Dados de contato (telefone, endereço, assinatura)
- Templates customizáveis por tipo de mensagem
- **Módulos habilitados** (quais áreas do sistema podem enviar WhatsApp)
- **Preferências por usuário** (quem recebe, dentro dos módulos habilitados)
- Área administrativa dual: **MyBP (admin clientes)** + **RH (customizações)**
- Renderização unificada via domínio (`WhatsappMessageFactory`), sem strings hardcoded nos controllers

---

## 2. Resumo do que foi implementado (jun/2026)

### V1 (base)

- 10 tipos de mensagem migrados para `WhatsappMessageFactory`
- Tabelas `empresa_whatsapp_configs` e `empresa_whatsapp_templates`
- Tela Vue 3 em **Customizações → WhatsApp**
- Permissão `configuracao_whatsapp` para RH; toggle `envia_whatsapp` em **Administração → Clientes**
- Rodapé MyBP obrigatório em todas as mensagens
- Cache por empresa (TTL 15 min)

### V2 (módulos, gate e preferências)

| Entrega | Descrição |
|---------|-----------|
| **Módulos por empresa** | Campo JSON `modulos_habilitados` em `empresa_whatsapp_configs`; aba **Módulos** na UI |
| **Gate central de envio** | `WhatsappNotificationGateService` + `ZapNotificacao::deveEnviarWhatsapp()` |
| **Metadados obrigatórios** | Todo envio via `ZapNotificacao::enviar()` deve incluir `_whatsapp_meta` |
| **Preferências de usuário** | Tabela `usuario_whatsapp_preferencias`; habilidade `preferencias_notificacao_whatsapp` |
| **Gestão de usuários** | Aba **Usuários** na config WhatsApp (telefone tipo WhatsApp + switches por módulo) |
| **Admin Clientes** | Aba **WHATSAPP** ao editar cliente (templates, módulos, contato, usuários) com `empresa_id` |
| **Leitura fresca no gate** | `isModuloHabilitado()` consulta o banco quando existe registro em `empresa_whatsapp_configs` (evita cache stale em workers) |
| **Revalidação na fila** | `JobSendNotificacaoWhatsApp` reexecuta o gate antes de enviar |
| **Testes** | 49+ testes unitários cobrindo gate, config, factory e todos os tipos/módulos |

### Correções relevantes

- `WhatsappConfigController`: operações de escrita (`config`, `modulos`, `templates`) usam `resolveEmpresaId()` para admin MyBP editar qualquer cliente
- `WhatsappConfigService::resolveContactData()` usa `Cliente::withoutGlobalScopes()` (jobs de fila sem `auth()`)
- `EmpresaWhatsappConfig`: observer invalida cache em `saved` / `deleted`

---

## 3. Regras de negócio

### 3.1 Habilitação em cascata

O envio só ocorre quando **empresa liberada**, **módulo habilitado** e **telefone principal do destinatário é tipo WhatsApp** (além das demais condições abaixo):

```
1. ClienteConfig.envia_whatsapp = true     (toggle em Administração → Clientes)  ← obrigatório
2. Módulo habilitado na empresa           (aba Módulos)                            ← obrigatório
3. Telefone principal tipo WhatsApp       (curriculo_telefone.principal + tipo)    ← obrigatório (candidato/colaborador)
4. Usuário com telefone tipo WhatsApp      (quando há destinatário interno — movimentação)
5. Preferência do usuário receber = true   (default true se não houver registro)
6. Metadados _whatsapp_meta válidos       (tipo + empresa_id)
```

Validação central: `WhatsappNotificationGateService::podeEnviar()` (itens 1–2 e 5 para usuários internos). Telefone principal: `WhatsappCurriculoTelefoneResolver`. UI: `whatsappPodeNotificar(tipo, telPrincipal)` no mixin `Configuracoes.js`.

### 3.2 Módulos e tipos de mensagem

Cada `TipoMensagemWhatsapp` pertence a um **módulo** (`config/whatsapp_templates.php` → `tipos.*.modulo`):

| Módulo (toggle na UI) | Tipos de mensagem (`tipo_mensagem`) |
|-----------------------|-------------------------------------|
| Recrutamento | `recrutamento_selecao`, `recrutamento_provas` |
| Exames | `exame_encaminhamento` |
| Admissão | `admissao_documentos`, `admissao_exame` |
| Intermitente | `intermitente_convocacao` |
| Carta Oferta | `carta_oferta_gerencial`, `carta_oferta_sgi` |
| Transporte | `parecer_rota_transporte` |
| Movimentação | `movimentacao_aprovacao` |

Desativar um módulo bloqueia **todos** os tipos daquele módulo.

**Default:** empresa sem registro em `empresa_whatsapp_configs` → todos os módulos habilitados (compatibilidade retroativa).

### 3.3 Permissões

| Ação | Quem | Permissão |
|------|------|-----------|
| Habilitar/desabilitar WhatsApp na empresa | Admin MyBP | `administracao_clientes` (toggle `envia_whatsapp`) |
| Configurar templates, módulos, contato de qualquer cliente | Admin MyBP | `administracao_clientes` + `?empresa_id=` nas APIs |
| Editar templates, módulos e contato da própria empresa | RH | `configuracao_whatsapp` + `envia_whatsapp=true` |
| Definir preferências pessoais | Usuário | `preferencias_notificacao_whatsapp` |
| Admin configurar preferências de usuários | RH / Admin | `usuario_usuarios` ou aba Usuários na config WhatsApp |

### 3.4 Templates

- Formato texto plano com placeholders `{{variavel}}`
- Editor Vue com formatação WhatsApp e emojis (`WhatsappTemplateEditor.vue`)
- 1 template ativo por `tipo_mensagem` por empresa
- Placeholder ausente → `"Não informado"`
- Template vazio/inativo → fallback em `config/whatsapp_templates.php`
- Rodapé MyBP anexado automaticamente se o template não incluir `{{rodape_mybp}}`

### 3.5 Dados de contato

| Campo | Fallback (`clientes`) |
|-------|------------------------|
| `nome_exibicao` | `razao_social` |
| `telefone_contato` | `tel_principal` |
| `endereco_completo` | endereço montado do cliente |

### 3.6 Telefone de usuário (notificações internas)

- Apenas telefones do tipo **`whatsapp`** em `curriculo_telefone` (resolver: `WhatsappUsuarioTelefoneResolver`)
- Usado em movimentação, aba Usuários e validação de `apto_envio`

### 3.7 Cache

- Chave: `whatsapp_config:{empresa_id}` — TTL 15 min (`config/whatsapp_templates.php`)
- Invalidação: salvar config/template/módulos; observer em `EmpresaWhatsappConfig`
- **Gate de envio:** quando existe linha em `empresa_whatsapp_configs`, `modulos_habilitados` é lido **direto do banco** (`resolveModulosHabilitadosParaEnvio`)

---

## 4. Arquitetura

### 4.1 Fluxo de envio

```
Controller / Job / Service
    │
    ├─ Monta mensagem → WhatsappMessageFactory::render(tipo, empresa_id, contexto)
    │
    └─ ZapNotificacao::enviar([
           telefone, mensagem, enviado_id,
           '_whatsapp_meta' => ZapNotificacao::meta(tipo, empresa_id, destinatario_user_id?)
       ])
              │
              ▼
       ZapNotificacao::deveEnviarWhatsapp()
              │
              ▼
       WhatsappNotificationGateService::podeEnviar(tipo, empresa_id, userId?)
              ├─ ClienteConfig.envia_whatsapp
              ├─ WhatsappConfigService::isModuloHabilitado(empresa, tipo.modulo())
              └─ UsuarioWhatsappPreferencia (se userId informado)
              │
              ▼ (se permitido)
       JobSendNotificacaoWhatsApp (mantém _whatsapp_meta na fila)
              │
              ▼
       handle() → deveEnviarWhatsapp() novamente → send()
```

### 4.2 Bounded context (DDD)

| Camada | Responsabilidade |
|--------|------------------|
| `App\Domain\Whatsapp\Enums\TipoMensagemWhatsapp` | Catálogo de tipos e módulos |
| `WhatsappConfigService` | Config, templates, módulos, cache, contato |
| `WhatsappMessageFactory` + `WhatsappTemplateRenderer` | Renderização |
| `WhatsappNotificationGateService` | Regras de permissão de envio |
| `WhatsappUsuarioTelefoneResolver` | Telefone WhatsApp do usuário |
| `WhatsappUsuarioNotificacaoService` | Listagem admin de usuários |
| `MovimentacaoWhatsappNotificationService` | Orquestração WhatsApp de movimentação |
| `ZapNotificacao` | Fila + integração Dynamus |

### 4.3 Pontos de envio (app/)

Todos devem usar `_whatsapp_meta`:

- `RecrutamentoController`
- `ControleExameController`
- `PreAdmissaoController`
- `ResultadoIntegrado`
- `IntermitenteController`
- `CartaOfertaGerencialController`
- `IntegraSgiMybpController`
- `ParecerRotaWhatsappService`
- `MovimentacaoWhatsappNotificationService` (+ jobs `JobNotificacaoRecursiva` via trait `EnviaWhatsappNotificacaoMovimentacao`)

**Fora do gate (legado):** scripts em `scripts/0_*.php` sem `_whatsapp_meta` (bloqueados pelo gate atual). Ver [WHATSAPP_STATUS_ENTREGA.md §6](./WHATSAPP_STATUS_ENTREGA.md#6-scripts-legados-atenção).

---

## 5. Banco de dados

### Tabelas

```sql
empresa_whatsapp_configs
  - empresa_id (unique)
  - nome_exibicao, telefone_contato, endereco_completo, texto_assinatura
  - modulos_habilitados (JSON)   -- {"Recrutamento": true, "Movimentação": false, ...}

empresa_whatsapp_templates
  - empresa_id, tipo_mensagem (unique composto)
  - corpo, ativo

usuario_whatsapp_preferencias
  - user_id, modulo (unique composto)
  - receber (boolean, default true quando ausente)
```

### Migrations

- `2026_06_23_100000_create_empresa_whatsapp_tables.php`
- `2026_06_23_100001_add_configuracao_whatsapp_habilidade.php`
- `2026_06_24_100000_add_whatsapp_notificacao_preferences.php`

### Habilidades (seed)

- `configuracao_whatsapp` — tela Customizações → WhatsApp (RH)
- `preferencias_notificacao_whatsapp` — card no dashboard / preferências pessoais

---

## 6. APIs

Prefixo: `/g/configuracoes/whatsapp` (auth). Admin pode passar `?empresa_id={cliente_id}`.

| Método | Rota | Descrição |
|--------|------|-----------|
| GET | `/` | Tela RH |
| GET | `/status` | Status leve: `whatsapp_liberado`, `modulos`, `tipos` (qualquer usuário autenticado) |
| GET | `/config` | Config + `whatsapp_liberado` + `readonly` |
| PUT | `/config` | Salvar dados de contato |
| PUT | `/modulos` | Salvar `modulos_habilitados[]` |
| GET | `/templates` | Lista templates por módulo |
| GET/PUT/DELETE | `/templates/{tipo}` | CRUD template |
| POST | `/templates/{tipo}/preview` | Preview com corpo opcional |
| POST | `/preview-fluxo` | Preview com contexto de fluxo |
| GET | `/tipos` | Catálogo |
| GET | `/usuarios-notificacoes` | Listagem paginada + filtros |
| PUT | `/usuarios-notificacoes/{userId}` | Atualizar preferência `{modulo, receber}` |
| GET/PUT | `/usuario/whatsapp-preferencias` | Preferências do usuário logado |

---

## 7. Frontend (Vue 3)

| Componente | Uso |
|------------|-----|
| `WhatsappConfig.vue` | Tela principal (abas: Contato, Templates, Módulos, Usuários). Prop opcional `empresaId` |
| `WhatsappTemplateEditor.vue` | Editor de corpo |
| `WhatsappPreviewModal.vue` | Preview |
| `WhatsappNotificacoesUsuarios.vue` | Gestão de usuários |
| `WhatsappPreferenciasUsuario.vue` | Card dashboard |
| `WhatsappPreferenciasForm.vue` | Form reutilizável (cadastro usuários) |

**Mixin `resources/js/mixins/Configuracoes.js`:** carrega `/status` no `mounted` e expõe `whatsappTipoHabilitado(tipo)` / `whatsappModuloHabilitado(modulo)` para esconder botões nos fluxos operacionais.

**Entradas:**

- `resources/js/g/configuracoes/whatsapp/app.js` — RH
- `resources/js/g/administracao/clientes/app.js` — aba WHATSAPP no cliente

---

## 8. Como usar (operacional)

### RH — Customizações → WhatsApp

1. Confirmar que admin habilitou `envia_whatsapp` no cliente
2. Ajustar contato, templates, módulos e usuários
3. Em **Módulos**, desativar o que não deve enviar e clicar **Salvar notificações**

### Admin — Administração → Clientes

1. Editar cliente → aba **Configurações** → `Envia notificação no WhatsApp`
2. Aba **WHATSAPP** → mesma experiência da tela RH, com `empresa-id` do cliente

### Após deploy

Reiniciar workers para carregar código do gate:

```bash
docker compose exec mybpdp php artisan horizon:terminate
```

---

## 9. Logs e diagnóstico

Mensagens em `storage/logs/laravel.log`:

| Log | Significado |
|-----|-------------|
| `WhatsApp bloqueado: módulo desabilitado na empresa` | Módulo off em `modulos_habilitados` |
| `WhatsApp bloqueado: preferência do usuário` | Usuário desativou o módulo |
| `WhatsApp bloqueado: metadados de envio ausentes` | Envio sem `_whatsapp_meta` (bloqueado) |
| `WhatsApp não enviado: módulo desabilitado ou empresa sem permissão` | Movimentação abortada no service |

Verificar no banco:

```sql
SELECT empresa_id, modulos_habilitados
FROM empresa_whatsapp_configs
WHERE empresa_id = ?;

SELECT envia_whatsapp FROM cliente_configs WHERE cliente_id = ?;
```

---

## 10. Testes

```bash
docker compose exec mybpdp php artisan test --filter=Whatsapp
```

Principais arquivos:

- `tests/Unit/Domain/Whatsapp/WhatsappNotificationGateServiceTest.php` — todos os tipos
- `tests/Unit/Domain/Whatsapp/WhatsappConfigServiceTest.php` — módulos individuais
- `tests/Unit/Classes/ZapNotificacaoGateTest.php` — fila + meta
- `tests/Unit/Classes/ZapNotificacaoSgiEnviaGateTest.php` — `SgiEnvia()` + gate
- `tests/Unit/Services/Movimentacao/MovimentacaoWhatsappNotificationServiceTest.php`

---

## 11. Melhorias futuras (backlog)

### Prioridade alta

- [x] Invalidar cache WhatsApp quando `cliente_configs.envia_whatsapp` mudar (`ClienteConfig::booted()`)
- [x] UI: desabilitar botões de envio WhatsApp nos fluxos quando módulo estiver off (`whatsappTipoHabilitado` + mixin `Configuracoes.js`)
- [x] Endpoint `GET /g/configuracoes/whatsapp/status` leve para o frontend consultar módulos sem carregar templates
- [x] Garantir `SgiEnvia()` também passe pelo gate (anexos carta oferta)

### Prioridade média

- [ ] Auto-save ou confirmação ao sair da aba Módulos sem salvar
- [ ] Auditoria (activity log) específica para alteração de módulos
- [ ] Seeder inicial de `modulos_habilitados` ao criar `empresa_whatsapp_configs` (hoje default implícito = todos on)
- [ ] Métricas/Horizon: contador de envios bloqueados pelo gate
- [ ] Documentar no menu RH link para config WhatsApp apenas se `envia_whatsapp`

### Prioridade baixa / débito técnico

- [ ] Migrar scripts `scripts/0_*.php` para usar `_whatsapp_meta` ou isolar como legado
- [ ] Unificar `enviaWhatsApp()` em `User` com o gate (evitar checks duplicados nos controllers)
- [ ] Feature tests HTTP para APIs com SQLite `:memory:`
- [ ] Composition API + service layer no frontend (`docs/PLANO_MIGRACAO_COMPOSITION_API_SERVICES.md`)

### Novos tipos de mensagem (checklist)

Ao adicionar um tipo:

1. Enum `TipoMensagemWhatsapp`
2. Entrada em `config/whatsapp_templates.php` (`templates` + `tipos` com `modulo`)
3. Ponto de envio com `ZapNotificacao::meta()`
4. Teste no data provider de `WhatsappNotificationGateServiceTest`
5. Atualizar esta documentação (tabela de módulos)

---

## 12. Test plan manual

- [ ] Admin habilita WhatsApp no cliente; RH vê tela editável
- [ ] Desativar módulo **Recrutamento** → seleção/provas não enviam WhatsApp
- [ ] Desativar **Movimentação** → aprovações não enviam; e-mail continua
- [ ] Desativar **Exames** → encaminhamento não envia
- [ ] Usuário com preferência `receber=false` não recebe; demais recebem
- [ ] Usuário sem telefone tipo WhatsApp aparece como não apto na aba Usuários
- [ ] Admin configura cliente pela aba WHATSAPP em Administração → Clientes
- [ ] Template customizado refletido no envio real
- [ ] Restaurar template padrão MyBP
- [ ] Após salvar módulos, worker Horizon reiniciado → envio respeita nova config

---

## 13. Referências no código

```
app/Domain/Whatsapp/
app/Classes/ZapNotificacao.php
app/Http/Controllers/WhatsappConfigController.php
app/Services/Whatsapp/WhatsappUsuarioNotificacaoService.php
app/Services/Movimentacao/MovimentacaoWhatsappNotificationService.php
app/Jobs/JobSendNotificacaoWhatsApp.php
app/Jobs/Movimentacao/Concerns/EnviaWhatsappNotificacaoMovimentacao.php
config/whatsapp_templates.php
resources/js/components/configuracoes/whatsapp/
database/migrations/2026_06_23_*_empresa_whatsapp*
database/migrations/2026_06_24_*_whatsapp_notificacao*
```

Integrações gerais: `docs/08-integracoes.md`

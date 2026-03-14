# Treinamentos Carteira/Etiquetas — Auditoria, Desmarcar Realizado e Histórico 1 a 1

Documentação das funcionalidades de **desmarcar treinamento realizado** (com termo de responsabilidade e auditoria), **salvamento 1 a 1 por vencimento** e **histórico por vencimento** (com flag de remoção), incluindo regras de configuração do cliente e habilidades.

---

## 1. Visão geral

### O que foi implementado

| Funcionalidade | Descrição |
|---------------|-----------|
| **Modal de auditoria** | Ao desmarcar um treinamento já realizado (Sim → Não), exige termo de responsabilidade e motivo; registra auditoria interna. |
| **Salvamento 1 a 1** | Botão "Salvar este treinamento" por card: persiste apenas aquele vencimento e grava histórico só daquela alteração. |
| **Histórico por vencimento** | Tabela de histórico pode registrar alteração de um único vencimento (`vencimento_id`) com flag `removido` (treinamento alterado para "Não"). |
| **Regra config/habilidade** | Se a empresa **não** tem config "permitir desmarcar realizado", qualquer usuário pode marcar "Não". Se tem config, só quem tem habilidade específica ou gestão RH pode desmarcar. |

### Arquivos principais

| Área | Arquivos |
|------|----------|
| Frontend | `resources/js/components/treinamentos-carteira-etiquetas/TreinamentosCarteiraEtiquetas.vue`, `constants.js` |
| Componente reutilizável | `resources/js/components/ModalAuditoriaTermoResponsabilidade.vue` |
| Backend | `app/Http/Controllers/TreinamentoController.php` |
| Model / DB | `app/Models/TreinamentoVencimentoHistorico.php`, migrations em `database/migrations/` |
| Rotas | `routes/web.php` (grupo treinamentos) |
| Habilidade | `database/seeders/HabilidadesTableSeeder.php` |

---

## 2. Regra: config do cliente e habilidade para desmarcar

### Comportamento

- **Empresa sem config** (`treinamento_permitir_desmarcar_realizado = false`): o select "Realizou este treinamento?" **não** fica desabilitado; qualquer usuário pode alterar para "Não". O backend **não** reverte "Não" para "Sim" ao salvar.
- **Empresa com config** (`treinamento_permitir_desmarcar_realizado = true`): apenas usuários com uma das habilidades abaixo podem desmarcar (escolher "Não") em treinamento já salvo como realizado; para os demais o select fica desabilitado e o backend reverte "Não" para "Sim" se enviado.

### Habilidades consideradas (uma ou outra)

- `privilegio_gestao_rh`
- `treinamento_carteira-etiquetas_retirar_treinamento_realizado`

### Onde a regra é aplicada

| Local | Uso |
|-------|-----|
| **Vue** | `disabled` do select "Realizou este treinamento?"; exibição da modal de auditoria (só quando config ativa e usuário tem habilidade). |
| **TreinamentoController (store)** | Reversão do item para "Sim" quando config ativa e usuário sem habilidade tenta enviar "Não". |
| **TreinamentoController (atualizarVencimento)** | Mesma reversão para salvamento 1 a 1. |
| **TreinamentoController (desmarcarTreinamentoRealizado)** | Endpoint de desmarcar com motivo exige config ativa e uma das habilidades. |

### Flags enviadas ao frontend

- `privilegio_gestao_rh` (boolean)
- `treinamento_permitir_desmarcar_realizado` (boolean) — config do cliente
- `treinamento_retirar_treinamento_realizado` (boolean) — `can('treinamento_carteira-etiquetas_retirar_treinamento_realizado')`

Enviadas em: resposta do **edit** (treinamento) e da **vencimentosPorSegmento**.

---

## 3. Modal de auditoria e termo de responsabilidade

### Quando aparece

Ao mudar "Realizou este treinamento?" de **Sim** para **Não** em um treinamento **já salvo** como realizado, e quando a empresa tem config ativa e o usuário tem uma das habilidades acima. Caso contrário, a alteração é feita direto (sem modal).

### Componente reutilizável: `ModalAuditoriaTermoResponsabilidade.vue`

- **Props**: `titulo`, `textoTermo`, `labelMotivo`, `placeholderMotivo`, `labelAceite`, `textoAceite`, `mostrarBotaoFecharNoRodape`, etc.
- **Slots**: `conteudo-antecipado`, `intro`, `conteudo-pos-termo` (ex.: nome/cargo do colaborador).
- **Eventos**: `@confirmar({ motivo })`, `@fechou`.
- **Métodos**: `abrir()`, `fechar()`.

Uso em Treinamentos Carteira/Etiquetas: termo de responsabilidade, campo motivo obrigatório, checkbox de ciência; ao confirmar chama o endpoint de desmarcar com motivo e não chama `salvar()` em seguida (evita histórico duplicado).

### Fluxo ao confirmar desmarcar na modal

1. Front chama `POST treinamento/desmarcar-treinamento-realizado` com `feedback_id`, `vencimento_id`, `motivo`.
2. Backend: detach do vencimento, grava histórico 1 a 1 com `removido = true`, registra auditoria com motivo.
3. Front: fecha modal, atualiza estado local (`fez_treinamento = false`, `_alterado = false`), chama `atualizar()` para refresh; **não** chama `salvar()` para não gravar histórico de novo.

---

## 4. Salvamento 1 a 1 por vencimento

### Objetivo

Quando o usuário altera apenas um card (um vencimento) e clica em "Salvar este treinamento", persistir só aquele vencimento e gravar no histórico **apenas** essa alteração, sem detach/attach de todo o segmento.

### Endpoint: `POST treinamento/atualizar-vencimento`

- **Rota**: `treinamento/atualizar-vencimento` (nome: `atualizar-vencimento`).
- **Payload**: `feedback_id` (int), `vencimento` (objeto do card: `id`, `fez_treinamento`, `data_treinamento`, `data_vencimento`, `numero_fat`, `arquivo`, etc.).
- **Autorização**: `treinamento_carteira-etiquetas_update`.
- **Fluxo**: localiza treinamento por `feedback_id` e empresa; aplica regra de reversão se config ativa e sem habilidade; faz `detach(vencimento_id)`; se `fez_treinamento` chama `processarVencimentos` com um único item; chama `salvarHistoricoVencimento(...)` uma vez.

### Frontend

- Ao salvar **um** card, o método `salvar(treinamento)` envia para `atualizarVencimento`: `feedback_id` e o objeto `vencimento` encontrado em `form.listaVencimentos` pelo `id` do card.
- Salvamento em lote (sem card específico) continua usando `POST treinamento` (store) com o formulário completo.

---

## 5. Histórico por vencimento (1 a 1)

### Tabela: `treinamento_vencimento_historicos`

Colunas relevantes para o histórico 1 a 1:

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| `vencimento_id` | unsignedBigInteger, nullable | Preenchido quando o registro refere-se **apenas** a este vencimento (alteração 1 a 1). |
| `removido` | boolean, default false | `true` quando o treinamento foi alterado para "Não" (desmarcado). |
| `treinamentos_vencimentos` | json | Estado: em registro 1 a 1 com `removido = true` fica `[]`; com `removido = false` contém só o vencimento alterado (após attach). |

Registros do **store** (salvamento em lote) continuam com `vencimento_id = null` e `removido = false`, e `treinamentos_vencimentos` com o estado completo dos vencimentos.

### Método interno: `salvarHistoricoVencimento`

- **Assinatura**: `salvarHistoricoVencimento(int $feedbackId, int $treinamentoId, int $vencimentoId, bool $removido)`.
- **Uso**: em `atualizarVencimento` (após alterar o vencimento) e em `desmarcarTreinamentoRealizado` (após o detach).
- **Comportamento**: cria um registro com `vencimento_id`, `removido` e `treinamentos_vencimentos` vazio (se removido) ou apenas o vencimento em questão (se não removido).

### Migrações

- `2026_03_13_000000_add_vencimento_id_to_treinamento_vencimento_historicos.php` — adiciona `vencimento_id` (nullable).
- `2026_03_13_100000_add_removido_to_treinamento_vencimento_historicos.php` — adiciona `removido` (boolean, default false).

---

## 6. Fluxos resumidos

### Botão "Salvar este treinamento" (por card)

- Visível quando o card tem `_alterado === true` (alteração em select, datas, número FAT ou upload).
- Ao clicar: chama `atualizarVencimento` com `feedback_id` e o objeto `vencimento` daquele card; após sucesso zera `_alterado` e chama `atualizar()`.
- Não deve aparecer enquanto a modal de auditoria estiver aberta para aquele card.

### Desmarcar com motivo (modal de auditoria)

- Usuário muda Sim → Não em treinamento já salvo; se config ativa e tem habilidade, abre a modal com termo e motivo.
- Confirmação → `desmarcarTreinamentoRealizado` → histórico 1 a 1 com `removido = true` e auditoria; front só atualiza estado e lista (sem chamar `salvar()`).

### Desmarcar sem modal

- Empresa sem config: qualquer um pode mudar para "Não"; ao salvar (store ou atualizarVencimento) o backend aceita e grava histórico normalmente.
- Empresa com config e usuário sem habilidade: select fica desabilitado; se o backend receber "Não" (ex.: store em lote), reverte para "Sim".

---

## 7. API e rotas (referência)

| Método | Rota | Controller@método | Descrição |
|--------|------|-------------------|-----------|
| POST | `treinamento` | store | Salvamento em lote; histórico completo. |
| POST | `treinamento/atualizar-vencimento` | atualizarVencimento | Salvamento 1 a 1; histórico só do vencimento. |
| POST | `treinamento/desmarcar-treinamento-realizado` | desmarcarTreinamentoRealizado | Desmarcar com motivo; exige config + habilidade; histórico 1 a 1 com `removido = true`. |
| GET  | `treinamento/{id}/edit` | edit | Retorna treinamento e flags (privilegio_gestao_rh, treinamento_permitir_desmarcar_realizado, treinamento_retirar_treinamento_realizado). |
| POST | `treinamento/vencimentos-por-segmento` | vencimentosPorSegmento | Retorna lista de vencimentos e as mesmas flags. |

---

## 8. Constantes (constants.js)

Novos/alterados para estes fluxos:

- `API_PATHS.desmarcarTreinamentoRealizado`: `'treinamento/desmarcar-treinamento-realizado'`
- `API_PATHS.atualizarVencimento`: `'treinamento/atualizar-vencimento'`

---

## 9. Model TreinamentoVencimentoHistorico

- **Fillable**: `feedback_id`, `empresa_id`, `treinamento_id`, `vencimento_id`, `removido`, `user_id`, `treinamentos_vencimentos`.
- **Casts**: `vencimento_id` (integer), `removido` (boolean), `treinamentos_vencimentos` (json).
- Acessor `getTreinamentosVencimentosAttribute` continua devolvendo o decode do JSON quando necessário.

---

## 10. Habilidade no seeder

- **Nome**: `treinamento_carteira-etiquetas_retirar_treinamento_realizado`
- **Descrição** (exemplo): permissão para retirar treinamento realizado na carteira/etiquetas (usada quando a empresa tem config "permitir desmarcar realizado").
- Definida em `HabilidadesTableSeeder` e atribuída aos perfis desejados.

---

## 11. Checklist de manutenção

- [ ] Ao alterar regra de config/habilidade, atualizar Vue (disabled do select e `precisaModal`), store, atualizarVencimento e desmarcarTreinamentoRealizado.
- [ ] Ao mudar payload de `atualizarVencimento`, conferir que o front envia o objeto `vencimento` completo (incluindo `arquivo`/`arquivoDel` se houver upload).
- [ ] Não chamar `salvar()` após `desmarcarTreinamentoRealizado` no front para evitar histórico duplicado.
- [ ] Histórico em lote (store) segue com `vencimento_id = null` e `removido = false`; não preencher `vencimento_id` no `salvarHistorico()` geral.

---

**Documento relacionado**: [TREINAMENTOS_CARTEIRA_ETIQUETAS_REFATORACAO.md](TREINAMENTOS_CARTEIRA_ETIQUETAS_REFATORACAO.md) (componente Vue 3, modais e constantes do módulo).

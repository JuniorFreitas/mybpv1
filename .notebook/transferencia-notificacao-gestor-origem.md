# Transferência — notificação e aprovação gestor origem

> Flags por empresa em `cliente_configs.configuracoes`.

Entry: `TransferenciaPrevistaController::store()` → `TransferenciaPrevistaFluxoAprovacaoService` + `JobNotificacaoRecursiva`
UI admin: Clientes → Notificações
UI movimentação: aba Transferência + `SolicitacaoTransferencia.vue`

## Flags
| Chave | Default | Efeito |
|-------|---------|--------|
| `transferencia_notificar_gestor_origem` | `true` | Se `false`, não envia e-mail/WhatsApp ao gestor origem; etapa permanece |
| `transferencia_exigir_aprovacao_gestor_origem` | `true` | Se `false`, origem nasce `status_aprovacao=aprovado`; fluxo segue destino/extra/RH; e-mail e UI (timeline, badge, formulário) omitem Gestor Origem |

## Fluxo com exigir=false
1. `montarDadosFluxoGestores()` → `empresaExigeAprovacaoGestorOrigem()` lê a config
2. Origem autoaprovada (obs de config da empresa); mantém `gestor_id`
3. Job determina `criacao_gestor_destino` / extra / RH (não `criacao_gestor_origem`)
4. Payload do e-mail inclui `exige_gestor_origem=false`
5. UI (`SolicitacaoTransferencia`): sem campo/etapa/badge “Gestor Origem”; badge passa a “AGUARDANDO GESTOR DESTINO”

## Quando origem NÃO é notificada / exigida
- Flag notificar `false` → tipo `null` se ainda pendente
- Flag exigir `false` → aprovada na criação; não aparece na timeline
- CC origem sem gestor → etapa dispensada (`gestor_id` null)
- Solicitante = gestor origem → autoaprovação
- `GestorAprovacaoConfig` modo único → fluxo substituto

Docs: `docs/TRANSFERENCIA_PREVISTA_APROVACAO_GESTORES_CC.md`
Testes: `JobNotificacaoRecursivaGestorOrigemTest`, `TransferenciaPrevistaFluxoAprovacaoServiceTest`

- SolicitacaoTransferencia: timeline/badge/modal omitidos se flag false **ou** obs de autoaprovação por config (item não depende só do ref global)
- Listagem anexa `exige_aprovacao_gestor_origem` em cada item

Updated: 2026-09-10

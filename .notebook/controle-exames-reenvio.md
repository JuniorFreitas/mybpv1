# Controle Exames — reenvio Encaminhados

Entry: aba Encaminhados em `ControleExames.vue` → `POST controle-exames/reenviar-encaminhamento` → `ControleExameController::reenviarEncaminhamento()` → `ExameEncaminhamentoNotificacaoService`.

## Regra (mesma do encaminhar)

- E-mail: `JobExame` (clínica + colaborador) com link `publico.encaminhamento_exame_fichapdf` e token existente.
- WhatsApp: gate `exame_encaminhamento` + telefone principal WhatsApp (`WhatsappCurriculoTelefoneResolver`) + template `ExameEncaminhamento`.
- `data_encaminhamento` = agora (reenvio); `data_realizacao` = `exame.encaminhamento_data` (ou created_at).
- Prazo: reenvio só se `data_realizacao >= hoje`; se a data já passou, UI desabilita e backend rejeita 422.

## Hardening (DBA/Security)

- Rota: `can:admissao_controle_exames` + `throttle:controle-exames-reenviar` (120/h usuário, 60/h por exame; local 300/120).
- Lookup: `empresa_id` + `whereHas(Feedback.empresa_id)`; label legado com `alternativa_id` + `value`.
- WhatsApp falha: mensagem genérica ao cliente; detalhe só em log sem PII.

## UI

Botões envelope / WhatsApp ao lado do PDF; WhatsApp só se `whatsappPodeNotificar('exame_encaminhamento', dados.tel_principal)`.

Updated: 2026-10-16

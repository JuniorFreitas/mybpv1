# Transferência — destino “aprovado” sem ser o gestor

> Timeline mostrava Gestor Destino aprovado (às vezes com “(RH)”) sem o gestor real ter aprovado.

Entry: `TransferenciaPrevistaFluxoAprovacaoService::deveExigirAprovacaoGestorDestino()` / `montarDadosFluxoGestores()`
UI: `SolicitacaoTransferencia.vue` timeline Gestor Destino

## Causa (corrigida 2026-09-11)
RN02/RN03 antiga dispensava destino quando origem e destino tinham o mesmo gestor. Com `transferencia_exigir_aprovacao_gestor_origem=false`, o gestor nunca aprovava e a UI reusava o label do solicitante + “(RH)”.

## Correção
`deveExigirAprovacaoGestorDestino()` sempre retorna `true` — etapa destino independente.
Autoaprovação de destino só se solicitante = gestor destino (regra existente).

## Caso #8537 (pré-correção)
- Mesmo gestor DENIS nos 2 CCs; exige_destino=false; Thiago na timeline como “(RH)”
- Novas solicitações passam a gravar `exige_aprovacao_gestor_destino=true` e `gestor_destino_id`

Updated: 2026-09-11

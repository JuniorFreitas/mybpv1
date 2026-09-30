# Filtro status por etapa do fluxo (movimentações)

> Pendente / Aprovado / Reprovado por etapa (gestor → extra → RH), além de Em aberto e Reprovado (qualquer).

Entry: `resources/js/utils/opcoesStatusFluxoAprovacao.js` + `App\Services\Concerns\AppliesApprovalFlowStatusFilter`
UI: Demissão, Admissão, Férias, Valor Extra, Mudança Cargo, Intermitente Fixo
Transferência: opções próprias (origem/destino/único) em `SolicitacaoTransferencia.vue`

## Regras
- `aberto` = em andamento (sem RH final e sem reprova em nenhuma etapa)
- `pendente_*` = etapa atual aguardando decisão
- `aprovado_*` / `reprovado_*` = coluna da etapa com o valor
- Extra só aparece no combo se `temAprovacaoExtra`
- Backend resolve extra via `AprovacaoExtraConfig::getConfigAtiva` por tipo (`demissao`, `admissao`, `ferias`, `valor_extra`, `mudanca_cargo`, `intermitente_fixo`)
- Demissão listagem (Query Builder `dp`) usa `DemissaoPrevistaFilterApplier::applyStatusWithColumns()`

Updated: 2026-09-30

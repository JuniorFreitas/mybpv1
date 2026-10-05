# Filtro status por etapa do fluxo (movimentações)

> Pendente / Aprovado / Reprovado por etapa (gestor → extra → RH), além de Em aberto e Reprovado (qualquer).

Entry: `resources/js/utils/opcoesStatusFluxoAprovacao.js` + `App\Services\Concerns\AppliesApprovalFlowStatusFilter`
UI: Demissão, Admissão, Férias, Valor Extra, Mudança Cargo, Intermitente Fixo, Requisição de Vagas, CIH
Transferência: opções próprias (origem/destino/único) em `SolicitacaoTransferencia.vue`

## Regras
- `aberto` = em andamento (sem RH final e sem reprova em nenhuma etapa)
- `pendente_*` = etapa atual aguardando decisão
- Nas abas de movimentação e na CIH, `aprovado_gestor` e `aprovado_extra` não são status atual: o card e o filtro mostram a etapa seguinte. `reprovado_*` e `aprovado_rh` continuam na coluna da etapa. Requisição de Vagas ainda filtra `aprovado_gestor` pela coluna.
- Extra só aparece no combo se `temAprovacaoExtra`
- Backend resolve extra via `AprovacaoExtraConfig::getConfigAtiva` por tipo (`demissao`, `admissao`, `ferias`, `valor_extra`, `mudanca_cargo`, `intermitente_fixo`, `requisicao_vaga`)
- Demissão listagem (Query Builder `dp`) usa `DemissaoPrevistaFilterApplier::applyStatusWithColumns()`
- Requisição de Vagas: `RequisicaoVagaFilterApplier` + `campoStatusAprovacao` (aceita legado `campoStatus`)
- CIH: `CihFilterApplier` mapeia as mesmas chaves em `status` + `resposta_rh` (sem etapa extra)

Updated: 2026-09-30

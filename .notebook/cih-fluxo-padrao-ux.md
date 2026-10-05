# CIH — fluxo padronizado (card detalhe)

- UI: `CIH.vue` — `MybpStatusBadge` + `mybp-card-corpo` + `MybpCardCampo` + `MybpFluxoAprovacao`
- Modal: `mybp-modal-form` / `mybp-modal-secao` + `ComboboxValidation` (ver `cih-modal-padrao-ux.md`)
- Filtro: `campoStatusAprovacao` via `opcoesStatusFluxoAtual` (sem etapa extra e sem `aprovado_gestor`)
- Backend: `CihFilterApplier::applyStatusFilter` mapeia etapas em `status` + `resposta_rh` (aceita legado `campoStatus`)

## Fluxo no card
Lançamento → Gestor → RH (`cancelado` no RH se gestor reprovou)

## Gotcha
CIH não tem `status_aprovacao_*`; colunas próprias — filtro custom, não o trait genérico. Card usa `etapaAtualFluxoAprovacao` com `campoGestor=status` e `campoRh=resposta_rh`. `aprovado_gestor` devolve vazio: o selo mostra a etapa seguinte (Pendente RH).

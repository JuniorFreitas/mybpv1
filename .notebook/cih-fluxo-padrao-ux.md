# CIH — fluxo padronizado (card detalhe)

- UI: `CIH.vue` — `MybpStatusBadge` + `mybp-card-corpo` + `MybpCardCampo` + `MybpFluxoAprovacao`
- Modal: `mybp-modal-form` / `mybp-modal-secao` + `ComboboxValidation` (ver `cih-modal-padrao-ux.md`)
- Filtro: `campoStatusAprovacao` via `buildOpcoesStatusFluxoAprovacao` (sem etapa extra)
- Backend: `CihFilterApplier::applyStatusFilter` mapeia etapas em `status` + `resposta_rh` (aceita legado `campoStatus`)

## Fluxo no card
Lançamento → Gestor → RH (`cancelado` no RH se gestor reprovou)

## Gotcha
CIH não tem `status_aprovacao_*`; colunas próprias — filtro custom, não o trait genérico.

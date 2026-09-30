# Requisição de Vagas — padrão UX operacional

Last updated: 2026-09-30

## O que
Migração de `RequisicaoVaga.vue` para filtros compactos, card detalhe e modal `mybp-modal-form` (mesmo padrão das movimentações).

## Onde
- `resources/js/components/planejamento/requisicao-vagas/RequisicaoVaga.vue`
- `app/Http/Controllers/RequisicaoVagaController.php` — lista retorna `cc` (`listaCentroCustoPorCnpj`)
- CSS global: `_mybp-filtros-compactos.scss`, `_mybp-card-detalhe.scss`, `_mybp-modal-form.scss`, `_mybp-listagem-ui.scss`

## Padrões aplicados
- Filtros: `FiltroListagem` + `DateRangeFilter` (blur) + Combobox status/ordenação
- Cards: `mybp-card` + `MybpStatusBadge` + `mybp-card-corpo` + `MybpFluxoAprovacao`
- Modal: seções Informações / Lotação / Demais / Aprovações
- Lotação→CC via `lista_ccs` (só `centro_custo_id` persistido; sem filial_id na tabela)
- Inputs: `mybp-filtro-campo` + `form-control-sm` + `modalCamposBloqueados`
- Validação: `validarCamposSolicitacao()` + `exigirCombobox` / `exigirCampoData`

## Gotcha
Filtro de status: `campoStatusAprovacao` com `buildOpcoesStatusFluxoAprovacao` + `AppliesApprovalFlowStatusFilter` (tipo `requisicao_vaga`). Aceita legado `campoStatus` na URL/export.

## Tags
requisicao-vaga, filtro, card-detalhe, modal, combobox, lotacao, ux

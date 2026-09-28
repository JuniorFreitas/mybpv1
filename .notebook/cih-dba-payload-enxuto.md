# CIH — otimização DBA (payload enxuto)

Tags: `perf` · `cih` · `query` · `dba`

## O que mudou

Listagem/edit/autocomplete passam a buscar só colunas e relações usadas na UI.

Export Excel/CSV: quando a empresa **tem filial**, inclui coluna **Lotação** (após Centro de Custo), via `CihLotacaoResolver` + `ClienteFilial::temFilial`.

## Onde

- `CihQueryBuilder` — `LISTING_COLUMNS` + eager loads mínimos (cards); export separado
- `CihController::atualizar` / `edit` — selects enxutos + `CihColaboradorPayloadMapper`
- `AutoCompletesController::colaboradorCih` — query única; lotação via `CihLotacaoResolver`
- `JobExportaCihCsvFinal` / `CihExportFormatter` — coluna Lotação se `temFilial`

## Gotcha

`FeedbackCurriculo` appenda `fc_token` e `vaga_aberta_municipio` — usar payload flat no JSON da CIH.

Lotação no Excel vem do `centro_custo_id` da admissão do colaborador (fallback: CC do lançamento CIH).

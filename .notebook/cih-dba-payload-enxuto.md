# CIH — otimização DBA (payload enxuto)

Tags: `perf` · `cih` · `query` · `dba`

## O que mudou

Listagem/edit/autocomplete passam a buscar só colunas e relações usadas na UI.

## Onde

- `CihQueryBuilder` — `LISTING_COLUMNS` + eager loads mínimos (cards); export separado
- `CihController::atualizar` — tags/áreas/CCs com `select`; CC só se modelo centro de custo
- `CihController::edit` — Tag/CC/Área/gestores slim; Anexos completo (componente)
- `Cih::Colaboradores` — Curriculo(id,nome) + Admissao(cargo,centro_custo_id) + CentroCusto
- `AutoCompletesController::colaboradorCih` — query única (ativos + demitidos ≤90d), sem UNION

## Gotcha

`FeedbackCurriculo` appenda `fc_token` e `vaga_aberta_municipio` — nunca serializar o model no JSON da CIH. Usar `CihColaboradorPayloadMapper` (id, nome, cargo, centro_custo, centro_custo_id, demitido).

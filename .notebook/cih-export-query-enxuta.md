# CIH export job — query enxuta (DBA)

Tags: `perf` · `cih` · `export` · `dba`

## O que

`JobExportaCihCsvFinal` deixa de montar query própria com `with(*)` completo. Usa `CihQueryBuilder::forExport` (filtros via `CihFilterApplier` + `EXPORT_COLUMNS` + eager loads mínimos).

## Escopo

- **Export**: `empresa_id` do usuário + filtros da tela (igual ao job antigo `buildQueryDirectly`). Não aplica `vinculados` / Montisol.
- **Listagem**: escopo de visibilidade via `CihAcessoService` (`ver_todas` / Montisol / vinculados).

## Onde

- `CihQueryBuilder::EXPORT_COLUMNS` / `getExportRelationships()` — sem Demissao/Gestor; Curriculo/Admissao/Users slim
- `JobExportaCihCsvFinal::buildQuery()` — autentica e chama `forExport`
- Cargo no CSV: `Admissao.cargo` com fallback `VagaAberta.Vaga.nome`

## Gotcha

Não misturar escopo de listagem no export — isso restringia o Excel a “somente os meus” (`vinculados`) e quebrava o comportamento histórico.

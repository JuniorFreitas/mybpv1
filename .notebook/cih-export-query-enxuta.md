# CIH export job — query enxuta (DBA)

Tags: `perf` · `cih` · `export` · `dba`

## O que

`JobExportaCihCsvFinal` deixou de montar query própria com `with(*)` completo. Usa `CihQueryBuilder::forExport` (escopo + `CihFilterApplier` + `EXPORT_COLUMNS` + eager loads mínimos).

## Onde

- `CihQueryBuilder::EXPORT_COLUMNS` / `getExportRelationships()` — sem Demissao/Gestor; Curriculo/Admissao/Users slim
- `JobExportaCihCsvFinal::buildQuery()` — só autentica e chama `forExport`
- Cargo no CSV: `Admissao.cargo` com fallback `VagaAberta.Vaga.nome`

## Gotcha

Export respeita escopo de visibilidade (`ver_todas` / vinculados / Montisol), diferente do job antigo que filtrava só `empresa_id`.

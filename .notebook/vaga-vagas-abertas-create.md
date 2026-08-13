# Vaga / VagasAbertas — criação

## Summary
`Vaga` = cargo/catálogo por empresa (`vagas`). `VagasAbertas` = abertura do cargo em um município (`vagas_abertas`), ligando `vaga_id` + `municipio_id` + `empresa_id`.

## Models
- `app/Models/Vaga.php` — table `vagas`; fillable: categoria_id, nome, ativo, empresa_id, cbo_id; TenantTrait; sem SoftDeletes; sem timestamps
- `app/Models/VagasAbertas.php` — table implícita `vagas_abertas`; fillable: vaga_id, titulo, descricao, municipio_id, empresa_id, ativo, ativo_sistema; TenantTrait; sem SoftDeletes
- Município: `app/Models/Municipio.php` → `municipios` (nome, uf, capital); FK em `vagas_abertas.municipio_id`

## Create entry points
- UI: `VagaController::store`, `VagasAbertasController::store` (validação: vaga nome+unique/empresa; vaga aberta exige vaga_id+municipio_id)
- Imports/scripts: `firstOrCreate` em `scripts/0_IMPORTACAO_*.php`, `scripts/0_CRIACAO_CARGO_VAGA_ABERTA_TREINAMENTO.php`
- Artisan: `ImportaAdmissaoMaxtec` tem helpers firstOrCreate (criação comentada); import atual só resolve vaga existente via `ResolvedorVagaAreaCentroCusto`

## São Luís
- Scripts usam `municipio_id = 2743` (ex.: Montisol, treinamento). Confirmar no DB: `municipios` where nome/uf.

## Gotchas
- `empresa_id` auto via `EmpresaObserver` quando há auth; em CLI/scripts passar explicitamente + Auth::loginUsingId
- Unique de nome da Vaga é só na validação do controller (não constraint DB em `vagas`)
- Sem unique DB em `vagas_abertas` (empresa+vaga+municipio)

## Related
- Tags: vaga, vagas_abertas, municipio, recrutamento

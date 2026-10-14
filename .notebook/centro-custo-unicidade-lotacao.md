# Centro de custo — unicidade por lotação

## Mission
Permitir mesmo `label` em lotacoes/status diferentes; bloquear só duplicata real.

## Finding
`CentroCustoController` usava `Rule::unique` só por `empresa_id` + `label`.

## Solution
`CentroCustoUnicidadeService::regraLabel()` — unicidade = empresa + label + ativo + lotação (matriz = sem `centro_custo_filiais` ativa; filial = `cliente_filial_id` ativo, `whereNull(deleted_at)`).

## Key refs
- `app/Services/CentroCusto/CentroCustoUnicidadeService.php` (`regraLabel`, `existeConflito`)
- `app/Http/Controllers/CentroCustoController.php` (store/update/`ativaDesativa`)
- `app/Services/CentroCusto/CentroCustoCnpjSyncService.php` (`ehCnpjMatriz`, `resolverClienteFilialIdPorCnpj`, `resolverCampoCnpj`)

## Gotcha
Tabela real: `centro_custo_filials` (não `filiais`). Matriz não grava linha ativa; duplicata de matriz usa `whereNotExists`.

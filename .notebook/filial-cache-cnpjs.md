# Filial — cache `cnpjs_{empresa_id}` + lista_cc

## Problem
Após cadastrar filial, ela não aparecia no combo de CNPJ do Centro de Custo (nem para vincular).

## Cause
`listaCentroCustoPorCnpj` (`lista_cc_*`) só listava CNPJs que já tinham CC vinculado → ciclo: filial nova nunca entrava no combo → sync exigia CNPJ em `lista_cc`.

## Fix
1. `CentroCusto::mesclarFiliaisSemCentroCusto()` inclui filiais ativas (e matriz) sem CC em `lista_cc.cnpjs`.
2. `CentroCustoCnpjSyncService` resolve filial via `ClienteFilial` mesmo fora da lista; matriz via flag ou CNPJ da empresa.
3. `CentroCusto.vue` também mescla `AUTENTICADO.cnpjs` no combo do formulário.

## Ops
Após criar filial: F5 na tela de CC. Vincular CC à filial para filtros que usam `centros_custos` por CNPJ. Cache `lista_cc_{empresa_id}` TTL 7 dias — invalidado em create/update filial e CC.

## Refs
- `app/Models/CentroCusto.php` — `mesclarFiliaisSemCentroCusto()`
- `app/Services/CentroCusto/CentroCustoCnpjSyncService.php`
- `resources/js/components/cadastros/centrocusto/CentroCusto.vue`
- `app/Models/ClienteFilial.php` — `invalidarCachesRelacionados()`

# Filial — cache `cnpjs_{empresa_id}`

## Problem
Após cadastrar/editar filial, listas de CNPJ (`AUTENTICADO.cnpjs`, `Cliente::Cnpjs`) podiam ficar desatualizadas.

## Cause
`ClienteFilial::booted()` só fazia `forget` de `lista_cc_{empresa_id}`. A chave `cnpjs_{empresa_id}` (TTL 7 dias em `Cliente::Cnpjs()`) não era invalidada.

## Fix
`ClienteFilial::invalidarCachesRelacionados()` limpa `cnpjs_*` + `lista_cc_*` e reconstrói `lista_cc` nos eventos `created`, `updated`, `deleted`, `restored`.

## Ops
Ainda é necessário vincular a filial a um Centro de Custo para ela aparecer em filtros que usam `listaCentroCustoPorCnpj`. F5 atualiza `temFilial` via `/usuario/autenticado/`.

## Refs
- `app/Models/ClienteFilial.php` — `invalidarCachesRelacionados()`
- `app/Models/Cliente.php` — `Cnpjs()`
- `app/Models/CentroCusto.php` — `listaCentroCustoPorCnpj()`

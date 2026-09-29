# CIH autocomplete colaborador por CNPJ

Tags: `flow` · `cih` · `autocomplete` · `cnpj`

## O que

No modal de lançamento CIH, ao trocar o CNPJ o autocomplete de colaborador busca só admissões daquele CNPJ (CCs do grupo). Se o CC já estiver escolhido, restringe ainda mais.

Cada opção/linha traz também **centro de custo** e **lotação** (Matriz/Filial via `listaCentroCustoPorCnpj`). No editar/visualizar a mesma tabela usa `Admissao.CentroCusto` + `formatLotacaoPorCentroCustoId`.

## Onde

- Frontend: `CIH.vue` → computed `colaboradorCihCaminho` (`campoCnpj` apenas); `onSelectFormCnpj` limpa colaboradores; tabela com colunas CC e Lotação
- Backend: `AutoCompletesController::colaboradorCih` filtra só por CNPJ (`resolverCentroCustoIdsCih` / `aplicarFiltroCentroCustoCih` / `mapaLotacaoPorCentroCustoCih`)

## Gotcha

Tabela real é `centro_custos` (não `centros_custos`). Sem CNPJ (empresa com filial), o autocomplete fica desabilitado (`colaboradorCihDesabilitado`). Sem filial, caminho sem param — lista geral da empresa.

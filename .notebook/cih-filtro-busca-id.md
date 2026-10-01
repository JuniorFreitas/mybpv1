# CIH — filtro compacto + busca

- UI: `FiltroListagem` + `mybp-filtros-compactos` em `CIH.vue` (todos os campos na tela)
- Campos: período, busca nome/CÓD, status (`campoStatusAprovacao` por etapa), tipo, CNPJ (`temFilial`), área/CC, gestor
- CNPJ: `lista_ccs` via `atualizar` → `CentroCusto::listaCentroCustoPorCnpj`; filtra CC e query `centro_custo_id`
- Backend: `CihFilterApplier` (status por etapa + CNPJ + export)
- Busca: nome curriculo **ou** `cihs.id`

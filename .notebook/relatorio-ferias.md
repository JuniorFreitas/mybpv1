# Relatório de Férias

Tags: flow | ferias, cnpj, centrocusto, graficos

## Entrada

- Tela: `resources/js/components/relatorios/ferias/index.vue`
- API: `FeriasController::show()` — `POST /relatorios/ferias`
- Export: `exportExcel` → `JobExportaFeriasExcel` (usa `show()['dados']`)

## Filtros

- Tipo: período aquisitivo OU data de saída (`DateRangeFilter`)
- Status, colaborador unificado Nome/CPF (`campoBusca` + `campoCPF`, mesmo padrão carteira `/g/treinamento`), CNPJ (`campoCnpj`), multi CC (`campoCentrosCusto`)
- CNPJ/CC aplicados em `whereHas('Admissao')` via `aplicarFiltroCnpjCentroCusto` (mesmo padrão medidas/efetivo)
- Busca: se parecer CPF → mascara e envia `campoCPF` (`whereCpf`); senão `campoBusca` por nome/cpf/id

## Payload

- `dados`, `cc` (`listaCentroCustoPorCnpj`), `total`, `graficos.status`, `graficos.centros`
- Item inclui `emp_cnpj` / `emp_nome_fantasia` via `resolverCentroCusto`

## UI

- Abas Lista / Gráficos (doughnut status + bar centros)
- Lista mantém tabelas por colaborador; lotação CNPJ no cabeçalho quando houver

## Não confundir

- Vencimento de férias é outra tela: `showVencimentoFerias` / `vencimentoferias`

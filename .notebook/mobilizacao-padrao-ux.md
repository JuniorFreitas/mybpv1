# Mobilização — padrão UX operacional

Last updated: 2026-09-30

## O que
Relatório de mobilização por projeto migrado para filtros compactos + cards `mybp-*` (não é fluxo de aprovação).

## Onde
- `resources/js/components/planejamento/mobilizacao/index.vue`
- API: `MobilizacaoController` (sem mudança de contrato)

## Padrões
- `FiltroListagem` + `DateRangeFilter` + Combobox de projeto
- Card do projeto com barra de progresso + KPIs
- Cards por vaga densos: progresso + 4 métricas + chips de pipeline
- Detalhes (resultado / tipos) sob demanda via “Ver detalhes”
- Abas Lista / Gráficos (`ChartsDoughnut` + `ChartsBar`)
- Ações PDF/Excel no slot `#acoes`

## Gráficos
- Preenchimento do projeto (doughnut)
- Resultados agregados (doughnut)
- Tipos de admissão (doughnut)
- Pipeline do projeto (bar)
- Preenchimento por vaga (bar horizontal)

## Gotcha
O período na UI ainda é informativo no cabeçalho: o backend `selecionaProjeto` não filtra por data.

## Tags
mobilizacao, filtro, card-detalhe, relatorio, ux

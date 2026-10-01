# Resultado Integrado — filtros + modal

## Escopo
- Tela: `/g/entrevistas/resultado-integrado`
- UI: `resources/views/g/entrevistas/resultado_integrado/index.blade.php`
- JS: `resources/js/g/entrevistas/resultado_integrado/app.js`
- Form: `FormResultadoIntegrado.vue` (`validarCampos`)

## Filtros compactos
- `FiltroListagem` + `mybp-filtros-compactos`
- Primários: período (`DateRangeFilter`), Candidato/CPF unificado, Cargo, UF, Classificação individual, Classificação RH
- Avançados (shell animado): Nota individual (serviço), Nota RH, Exibir
- Combos estáticos `ri-filtro-*` (sem hash em `@select` / `input-id`)
- Gotcha Blade: tags Vue **não** self-closing; mustache Vue precisa `@{{ }}` (não `{{ }}` nu)

## Backend (`ResultadoIntegradoController::filtro`)
- Período: `filtroPeriodo` boolean + `dataInicio`/`dataFim` ISO; fallback `periodo` BR (`dd/mm/yyyy até dd/mm/yyyy`)
- CPF: `campoCPF` filtra `whereCpf($request->campoCPF)` (antes usava `campoBusca` por engano)

## Modal
- Wrapper `mybp-modal-form` + legenda obrigatórios
- `:visualizar` / `:disabled` no `form-resultado-integrado`
- Submit: `validarCampos()` (docs, exame, trein, exceção, responsável; PCMSO/empresa se exige)

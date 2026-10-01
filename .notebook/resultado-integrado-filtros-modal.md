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

## Listagem (cards)
- `mybp-cards-lista` + `mybp-card` / `mybp-card-corpo` + badge Integrado/Pendente
- `MybpCardCampo`: cargo, PCD, resp., enc. docs/exame/treinamento
- Seleção em massa (id do feedback) + dropdown ações
- Removido modal de colunas (campos opcionais estavam comentados na tabela)

## Modal
- Wrapper `mybp-modal-form` + legenda obrigatórios
- `:visualizar` / `:disabled` no `form-resultado-integrado`
- Submit: `validarCampos()` (docs, exame, trein, exceção, responsável; PCMSO/empresa se exige)
- Autorizado por só se `excessao === true`; blur do submit com `:enabled`

# Modal operacional MyBP (Demissão / Férias)

- CSS: `resources/sass/_mybp-modal-form.scss` — classes `mybp-modal-form`, `mybp-modal-secao`, `mybp-modal-legenda`, `mybp-modal-campo-data`
- Uso: `class="mybp-modal-form mybp-filtros-compactos"` + fieldsets (Colaborador → Solicitação → Detalhes → Aprovações)
- Labels `mybp-label`; enums/status via `ComboboxAutoComplete`; Status 4 + Obs 8 nas aprovações
- Refs: `SolicitacaoDemissao.vue`, `SolicitacaoFerias.vue`
- `Colaborador.vue` e `GestorAprovacao.vue` já usam `mybp-filtro-campo` / `mybp-label`

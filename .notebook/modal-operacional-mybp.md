# Modal operacional MyBP (movimentação)

- CSS: `resources/sass/_mybp-modal-form.scss` — classes `mybp-modal-form`, `mybp-modal-secao`, `mybp-modal-legenda`, `mybp-modal-campo-data`
- Uso: `class="mybp-modal-form mybp-filtros-compactos"` + fieldsets (domínio → Detalhes → Aprovações)
- Labels `mybp-label`; enums/status via `ComboboxAutoComplete` quando aplicável
- Refs: Demissão, Férias, Admissão, Valor Extra (Liderança), Muda Cargo, Intermitente→Fixo, Transferência
- `Colaborador.vue` e `GestorAprovacao.vue` já usam `mybp-filtro-campo` / `mybp-label`
- Admissão: Lotação (CNPJ) → CC filtrado por `lista_ccs`
- Transferência: CC origem/destino combobox; modos origem/destino/único preservados
- Validação de combobox obrigatório: `docs/PADRAO_VALIDACAO_COMBOBOX.md` + mixin `ComboboxValidation`

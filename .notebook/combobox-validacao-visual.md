# Validação visual ComboboxAutoComplete

- Util: `resources/js/utils/comboboxValidation.js` (`marcarComboboxObrigatorio`, `limparComboboxInvalido`, `exigirCombobox`)
- Mixin: `resources/js/mixins/ComboboxValidation.js`
- Doc: `docs/PADRAO_VALIDACAO_COMBOBOX.md`
- CSS: `_mybp-listagem-ui.scss` — feedback **abaixo** de `.mybp-combobox-wrap` (nunca dentro do `.input-group`)
- Gotcha: `valida_campo_vazio()` no input do combo **quebra** o layout
- Ref: `SolicitacaoMudaCargo.vue` (status Gestor/Extra/RH)
- Também aplicado em: Demissão, Admissão, Férias, Valor Extra, Intermitente→Fixo, Transferência (origem/destino/único/extra/RH)
- Demissão solicitação: `validarCamposSolicitacao()` — data (marca no datepicker) + tipo aviso (`exigirCombobox`) + colaborador/gestor; só campos ativos
- Helpers data: `exigirCampoData` / `marcarCampoDataObrigatorio` / `validarInputsAtivosVisiveis` no mesmo util
- Também solicitação: Admissão, Férias, Valor Extra, Mudança Cargo, Intermitente Fixo, Transferência

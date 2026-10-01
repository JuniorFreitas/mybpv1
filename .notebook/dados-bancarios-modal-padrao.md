# DadosBancarios — padrão modal

## Escopo
- `resources/js/components/DadosBancarios.vue`: `mybp-modal-secao` + `mybp-label` + `form-control-sm`
- PIX e Tipo de Chave → `ComboboxAutoComplete` + `ComboboxValidation`
- Tipo chave: CPF, CNPJ, EMAIL, ALEATORIA (domínio importação); valor legado fora da lista entra nas opções
- `validarCampos()` no submit via refs `dadosBancariosModal` / `dadosBancariosAvulsa`

## Gotcha
- Nested SFC: IDs com `hash` OK (não Blade in-DOM)
- `model.pix` continua boolean; combo usa `sim`/`nao` via get/set

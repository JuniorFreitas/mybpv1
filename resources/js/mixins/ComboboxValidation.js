import {
    marcarComboboxObrigatorio,
    limparComboboxInvalido,
    exigirCombobox,
    marcarCampoDataObrigatorio,
    limparCampoDataInvalido,
    exigirCampoData,
    validarInputsAtivosVisiveis
} from '../utils/comboboxValidation'

/**
 * Mixin Vue — validação visual de ComboboxAutoComplete e data obrigatória.
 * Preferir exigirCombobox / exigirCampoData no submit; limpar* no @select.
 */
export default {
    methods: {
        marcarComboboxObrigatorio,
        limparComboboxInvalido,
        exigirCombobox,
        marcarCampoDataObrigatorio,
        limparCampoDataInvalido,
        exigirCampoData,
        validarInputsAtivosVisiveis,
        /** @deprecated use marcarComboboxObrigatorio */
        marcarStatusObrigatorio(inputId, mensagem) {
            return marcarComboboxObrigatorio(inputId, mensagem)
        },
        /** @deprecated use limparComboboxInvalido */
        limparInvalidoStatus(inputId) {
            return limparComboboxInvalido(inputId)
        }
    }
}

import {
    marcarComboboxObrigatorio,
    limparComboboxInvalido,
    exigirCombobox,
    marcarCampoDataObrigatorio,
    limparCampoDataInvalido,
    exigirCampoData,
    validarInputsAtivosVisiveis,
    campoIgnorarValidacao,
    campoDesabilitadoParaValidacao
} from '../utils/comboboxValidation'

/**
 * Mixin Vue — validação visual de ComboboxAutoComplete e data obrigatória.
 * Preferir exigirCombobox / exigirCampoData no submit; limpar* no @select.
 * Ignora disabled, readonly e campos não visíveis na tela.
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
        campoIgnorarValidacao,
        campoDesabilitadoParaValidacao,
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

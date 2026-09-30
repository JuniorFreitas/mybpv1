/**
 * Validação visual de ComboboxAutoComplete (MyBP).
 *
 * NÃO usar valida_campo_vazio() no input do combobox: o feedback
 * entra dentro do .input-group e esmaga o campo.
 *
 * Estrutura esperada no template:
 *   <div class="mybp-combobox-wrap">
 *     <combobox-auto-complete :input-id="idUnico" ... />
 *   </div>
 *
 * CSS: resources/sass/_mybp-listagem-ui.scss (.mybp-combobox-wrap > .invalid-feedback)
 */

const MSG_PADRAO = 'Campo obrigatório'

function resolverElemento(inputId) {
    if (!inputId) return null
    return typeof document !== 'undefined' ? document.getElementById(inputId) : null
}

function resolverWrap(el) {
    if (!el) return null
    return el.closest('.mybp-combobox-wrap') || el.closest('.combobox-ac-wrap')
}

function removerFeedbacks(root) {
    if (!root) return
    root.querySelectorAll('.invalid-feedback').forEach((n) => n.remove())
}

/**
 * Marca o input do combobox com is-invalid e coloca o feedback ABAIXO do wrap.
 * @param {string} inputId - mesmo valor de :input-id do ComboboxAutoComplete
 * @param {string} [mensagem='Campo obrigatório']
 * @returns {boolean} true se o elemento foi encontrado e marcado
 */
export function marcarComboboxObrigatorio(inputId, mensagem = MSG_PADRAO) {
    const el = resolverElemento(inputId)
    if (!el) return false

    el.classList.add('is-invalid')

    const wrap = resolverWrap(el)
    if (wrap) {
        removerFeedbacks(wrap)
        const fb = document.createElement('div')
        fb.className = 'invalid-feedback d-block'
        fb.textContent = mensagem || MSG_PADRAO
        wrap.appendChild(fb)
    }

    // limpa feedback legado que valida_campo_vazio possa ter injetado no input-group
    const group = el.closest('.input-group')
    removerFeedbacks(group)

    if (typeof el.focus === 'function') {
        el.focus()
    }
    return true
}

/**
 * Remove is-invalid e feedbacks do combobox.
 * @param {string} inputId
 */
export function limparComboboxInvalido(inputId) {
    const el = resolverElemento(inputId)
    if (!el) return

    el.classList.remove('is-invalid')
    removerFeedbacks(resolverWrap(el))
    removerFeedbacks(el.closest('.input-group'))
}

/**
 * Valida valor do combobox: se vazio, marca o input e opcionalmente toast.
 * @param {string|number|null|undefined} valor - v-model do combobox
 * @param {string} inputId
 * @param {{ mensagem?: string, toast?: boolean, toastMsg?: string }} [opts]
 * @returns {boolean} true se válido
 */
export function exigirCombobox(valor, inputId, opts = {}) {
    const {
        mensagem = MSG_PADRAO,
        toast = true,
        toastMsg = 'Selecione uma opção'
    } = opts

    const vazio = valor === null || valor === undefined || valor === ''
    if (vazio) {
        marcarComboboxObrigatorio(inputId, mensagem)
        if (toast && typeof mostraErro === 'function') {
            mostraErro('', toastMsg)
        }
        return false
    }

    limparComboboxInvalido(inputId)
    return true
}

/**
 * Marca datepicker/input de data com is-invalid + feedback abaixo do parent.
 * @param {string} inputId
 * @param {string} [mensagem]
 */
export function marcarCampoDataObrigatorio(inputId, mensagem = MSG_PADRAO) {
    const el = resolverElemento(inputId)
    if (!el) return false
    el.classList.add('is-invalid')
    const parent = el.parentElement
    if (parent) {
        parent.querySelectorAll(':scope > .invalid-feedback').forEach((n) => n.remove())
        const fb = document.createElement('div')
        fb.className = 'invalid-feedback d-block'
        fb.textContent = mensagem || MSG_PADRAO
        parent.appendChild(fb)
    }
    if (typeof $ !== 'undefined') {
        $(el).siblings('div.invalid-feedback').not('.d-block').remove()
    }
    if (typeof el.focus === 'function') {
        el.focus()
    }
    return true
}

/**
 * @param {string} inputId
 */
export function limparCampoDataInvalido(inputId) {
    const el = resolverElemento(inputId)
    if (!el) return
    el.classList.remove('is-invalid')
    const parent = el.parentElement
    if (parent) {
        parent.querySelectorAll(':scope > .invalid-feedback').forEach((n) => n.remove())
    }
    if (typeof $ !== 'undefined') {
        $(el).siblings('div.invalid-feedback').remove()
    }
}

/**
 * Valida data obrigatória (datepicker costuma ser readonly — não depende de blur).
 * @param {string|null|undefined} valor
 * @param {string} inputId
 * @param {{ mensagem?: string, toast?: boolean, toastMsg?: string }} [opts]
 * @returns {boolean}
 */
export function exigirCampoData(valor, inputId, opts = {}) {
    const {
        mensagem = MSG_PADRAO,
        toast = true,
        toastMsg = 'Informe a data'
    } = opts

    const texto = valor == null ? '' : String(valor).trim()
    const vazio = !texto || texto === 'Invalid date'
    if (vazio) {
        marcarCampoDataObrigatorio(inputId, mensagem)
        if (toast && typeof mostraErro === 'function') {
            mostraErro('', toastMsg)
        }
        return false
    }

    limparCampoDataInvalido(inputId)
    return true
}

/**
 * Dispara blur só em inputs visíveis e habilitados (ignora disabled/readonly).
 * @param {string} modalId - id do modal (hash)
 * @param {{ preservarIds?: string[] }} [opts]
 * @returns {boolean} true se nenhum inválido
 */
export function validarInputsAtivosVisiveis(modalId, opts = {}) {
    if (typeof $ === 'undefined' || !modalId) return true
    const preservar = new Set(opts.preservarIds || [])

    $(`#${modalId} :input:disabled, #${modalId} :input[readonly]`).each(function () {
        if (preservar.has(this.id)) return
        $(this).removeClass('is-invalid')
        $(this).siblings('div.invalid-feedback').remove()
    })

    const seletor = `#${modalId} :input:visible:not(:disabled):not([readonly])`
    $(seletor).trigger('blur')
    if ($(`${seletor}.is-invalid`).length) {
        if (typeof mostraErro === 'function') {
            mostraErro('', 'Verifique os campos marcados')
        }
        return false
    }
    return true
}

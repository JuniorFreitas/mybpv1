window._ = require('lodash')

/**
 * We'll load jQuery and the Bootstrap jQuery plugin which provides support
 * for JavaScript based Bootstrap features such as modals and tabs. This
 * code may be modified to fit the specific needs of your application.
 */

try {
    window.Popper = require('popper.js').default
    window.$ = window.jQuery = require('jquery')

    require('bootstrap')

    /**
     * Limpa o backdrop da modal atual e o body quando não houver mais nenhuma modal aberta.
     * @param {string} [modalId] - id da modal que está fechando (modal atual)
     * @param {number} [zIndex] - zIndex dessa modal, para remover apenas #modal-backdrop${zIndex}
     * Com args: remove o backdrop dessa modal. Sem modais visíveis: remove backdrops órfãos e limpa o body.
     */
    function limparBackdropModal(modalId, zIndex) {
        if (typeof $ === 'undefined') return
        setTimeout(() => {
            if (modalId != null && zIndex != null) {
                $(`#modal-backdrop${zIndex}`).remove()
            }
            const modaisVisiveis = $('.modal.show').length
            const backdrops = $('.modal-backdrop').length
            if (modaisVisiveis === 0) {
                if (backdrops > 0) {
                    $('.modal-backdrop').remove()
                }
                $('body').removeClass('modal-open').css({ 'padding-right': '', 'overflow': '' })
            }
        }, 50)
    }

    window.limparBackdropModal = limparBackdropModal

    $(document).on('hidden.bs.modal', function () {
        limparBackdropModal()
    })
} catch (e) {}

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios')

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

let token = document.head.querySelector('meta[name="csrf-token"]')

if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content
    window.CSRF_token = token.content // adicionado por para usar em <form> dentro de Vue.
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token')
}

/**
 * Tratamento global de erros HTTP do axios: exibe mensagens do backend via toastr.
 * Opt-out por request: passar config.errorHandle = false.
 */
function axiosErrorMessage(data) {
    const defaultMsg = 'Ocorreu um erro'
    const defaultTitle = 'Ocorreu um erro'
    if (data == null || data === undefined) {
        return { message: defaultMsg, title: defaultTitle }
    }
    if (typeof data === 'string') {
        return { message: data, title: defaultTitle }
    }
    if (typeof data !== 'object') {
        return { message: defaultMsg, title: defaultTitle }
    }
    const errors = data.erros || data.errors
    if (errors && typeof errors === 'object' && Object.keys(errors).length > 0) {
        const quantidade = 3
        const list = Object.keys(errors).slice(0, quantidade)
        let message = '<ul>'
        list.forEach((key) => {
            const val = errors[key]
            const descricao = Array.isArray(val) ? val[0] : val
            message += `<li><strong>${key}:</strong> ${descricao}</li>`
        })
        message += '</ul>'
        return {
            message,
            title: data.msg || defaultTitle
        }
    }
    const message = data.message || data.msg || defaultMsg
    const title = data.msg && data.message ? data.msg : defaultTitle
    return { message, title }
}

window.axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.config && error.config.errorHandle === false) {
            return Promise.reject(error)
        }
        if (!error.response) {
            if (typeof window.toastr !== 'undefined') {
                window.toastr.error('Verifique sua conexão com a Internet.', 'Ocorreu um erro')
            }
            return Promise.reject(error)
        }
        const status = error.response.status
        if (status === 419) {
            if (typeof window.toastr !== 'undefined') {
                window.toastr.error('Recarregue a página novamente.', '419')
            }
            return Promise.reject(error)
        }
        if (status === 403) {
            if (typeof window.toastr !== 'undefined') {
                window.toastr.error('Sem permissão.', '403')
            }
            return Promise.reject(error)
        }
        const { message, title } = axiosErrorMessage(error.response.data)
        if (typeof window.toastr !== 'undefined') {
            window.toastr.error(message, title)
        }
        return Promise.reject(error)
    }
)

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

import Echo from 'laravel-echo'

window.Pusher = require('pusher-js')

const reverbKey = process.env.MIX_REVERB_APP_KEY
const pusherKey = process.env.MIX_PUSHER_APP_KEY

if (reverbKey) {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: reverbKey,
        wsHost: process.env.MIX_REVERB_HOST || window.location.hostname,
        wsPort: process.env.MIX_REVERB_PORT || 80,
        wssPort: process.env.MIX_REVERB_PORT || 443,
        forceTLS: (process.env.MIX_REVERB_SCHEME || 'https') === 'https',
        enabledTransports: ['ws', 'wss']
    })
} else if (pusherKey) {
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: pusherKey,
        cluster: process.env.MIX_PUSHER_APP_CLUSTER,
        forceTLS: process.env.MIX_PUSHER_APP_TLS !== 'false'
    })
} else {
    console.warn('Broadcasting disabled: missing MIX_REVERB_APP_KEY / MIX_PUSHER_APP_KEY')
}

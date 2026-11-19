import jQuery from 'jquery'

/**
 * legacy-globals.js (IIFE classico) ja sobe window.jQuery com plugins
 * (daterangepicker, metisMenu, masks...). Entries Vite nao podem
 * sobrescrever com outra instancia limpa do modulo `jquery`.
 */
const api =
    typeof window !== 'undefined' && window.jQuery && window.jQuery.fn
        ? window.jQuery
        : jQuery

if (typeof window !== 'undefined') {
    window.$ = window.jQuery = api
}

export default api

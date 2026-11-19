import $ from './jquery-global'
import moment from 'moment'
import DateRangePicker from 'bootstrap-daterangepicker'

/**
 * Garante o plugin no jQuery ativo e impede tree-shake do Vite/Rollup
 * (o side-effect import puro era eliminado do build).
 */
try {
    if (typeof window !== 'undefined') {
        window.moment = window.moment || moment
        window.DateRangePicker = window.DateRangePicker || DateRangePicker
    }

    if ($?.fn && !$.fn.daterangepicker && typeof DateRangePicker === 'function') {
        $.fn.daterangepicker = function (options, callback) {
            const implementOptions = $.extend(true, {}, $.fn.daterangepicker.defaultOptions, options)
            this.each(function () {
                const el = $(this)
                if (el.data('daterangepicker')) {
                    el.data('daterangepicker').remove()
                }
                el.data('daterangepicker', new DateRangePicker(el, implementOptions, callback))
            })
            return this
        }
    }
} catch (e) {
    if (typeof console !== 'undefined' && console.warn) {
        console.warn('[mybp] ensure-daterangepicker falhou', e)
    }
}

export { DateRangePicker }

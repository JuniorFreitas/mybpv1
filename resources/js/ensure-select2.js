import $ from './jquery-global'
import select2Factory from './components/Select2/depends/select2.full.js'
import './components/Select2/depends/select2.min.css'

/**
 * Garante $.fn.select2 no jQuery ativo (UMD CJS exporta factory).
 */
try {
    if ($?.fn && !$.fn.select2 && typeof select2Factory === 'function') {
        select2Factory(typeof window !== 'undefined' ? window : undefined, $)
    }
} catch (e) {
    if (typeof console !== 'undefined' && console.warn) {
        console.warn('[mybp] ensure-select2 falhou', e)
    }
}

export default $

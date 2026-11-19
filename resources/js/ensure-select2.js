import $ from './jquery-global'
import * as select2Ns from './components/Select2/depends/select2.full'
import './components/Select2/depends/select2.min.css'

/**
 * Garante $.fn.select2 no jQuery ativo (UMD CJS exporta factory).
 */
try {
    const select2Factory = select2Ns.default || select2Ns
    if ($?.fn && !$.fn.select2 && typeof select2Factory === 'function') {
        select2Factory(typeof window !== 'undefined' ? window : undefined, $)
    }
} catch (e) {
    if (typeof console !== 'undefined' && console.warn) {
        console.warn('[mybp] ensure-select2 falhou', e)
    }
}

export default $

import './bootstrap'
import './Globals'
import './jquery.mask'
import './jquery.maskMoney'
import 'metismenu'
import { plugin as TippyPlugin } from 'vue-tippy'
import 'tippy.js/dist/tippy.css'
import toastr from 'toastr'
import moment from 'moment'
import 'bootstrap-daterangepicker'
import { popoverDirective } from './diretivas/popover'
import { mascaraDirective } from './diretivas/mascaras'

window.toastr = toastr
toastr.options.closeButton = true
window.moment = moment
moment.locale('pt-BR')

const tippyOptions = {
    directive: 'tippy',
    defaultProps: {
        flipDuration: 0,
        popperOptions: {
            modifiers: [
                {
                    name: 'preventOverflow',
                    enabled: false
                }
            ]
        }
    }
}

const components = {
    preload: require('./components/preload').default,
    'btn-atualiza': require('./components/btnAtualiza').default,
    'controle-paginacao': require('./components/ControlePaginacao').default,
    datepicker: require('./components/DatePicker').default,
    modal: require('./components/Modal').default,
    autocomplete: require('./components/AutoComplete').default,
    'bt-ativo': require('./components/AtivoInativo').default,
    'barra-top': require('./components/layout/BarraTop').default,
    'nps-modal': require('./components/NpsModal').default,
    'telefone-usuario-modal': require('./components/TelefoneUsuarioModal.vue').default,
    'whatsapp-preferencias-usuario': require('./components/WhatsappPreferenciasUsuario.vue').default,
    'whatsapp-preview-modal': require('./components/configuracoes/whatsapp/WhatsappPreviewModal.vue').default,
}

export function registerGlobals(app) {
    if (!app) {
        return
    }

    if (!window.__mybpGlobalsRegistered) {
        window.__mybpGlobalsRegistered = new WeakSet()
    }

    if (window.__mybpGlobalsRegistered.has(app)) {
        return
    }

    window.__mybpGlobalsRegistered.add(app)
    const context = app._context

    app.config.compilerOptions.whitespace = 'preserve'
    const hasTippy = (app.directive && app.directive('tippy')) || (app.component && app.component('tippy'))
    if (!hasTippy && (!context || !context.plugins || !context.plugins.has(TippyPlugin))) {
        app.use(TippyPlugin, tippyOptions)
    }

    Object.entries(components).forEach(([name, component]) => {
        if (!app.component(name)) {
            app.component(name, component)
        }
    })

    if (!app.directive('mascara')) {
        app.directive('mascara', mascaraDirective)
    }
    if (!app.directive('popover')) {
        app.directive('popover', popoverDirective)
    }
}

if (typeof window !== 'undefined') {
    window.registerGlobals = registerGlobals
}

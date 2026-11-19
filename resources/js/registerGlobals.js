import './bootstrap'
import './Globals'
import './jquery.mask'
import './jquery.maskMoney'
import 'metismenu'
import { plugin as TippyPlugin } from 'vue-tippy'
import 'tippy.js/dist/tippy.css'
import toastr from 'toastr'
import moment from 'moment'
import './ensure-daterangepicker'
import { popoverDirective } from './diretivas/popover'
import { mascaraDirective } from './diretivas/mascaras'
import Preload from './components/preload.vue'
import BtnAtualiza from './components/btnAtualiza.vue'
import ControlePaginacao from './components/ControlePaginacao.vue'
import DatePicker from './components/DatePicker.vue'
import Modal from './components/Modal.vue'
import AutoComplete from './components/AutoComplete.vue'
import AtivoInativo from './components/AtivoInativo.vue'
import BarraTop from './components/layout/BarraTop.vue'
import NpsModal from './components/NpsModal.vue'
import AssistenteManual from './components/ajuda/AssistenteManual.vue'
import TelefoneUsuarioModal from './components/TelefoneUsuarioModal.vue'
import WhatsappPreferenciasUsuario from './components/WhatsappPreferenciasUsuario.vue'
import WhatsappPreviewModal from './components/configuracoes/whatsapp/WhatsappPreviewModal.vue'
import MybpVisualizacaoListagem from './components/ui/MybpVisualizacaoListagem.vue'
import TinyMceEditor from './components/TinyMceEditor.vue'

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
    preload: Preload,
    'btn-atualiza': BtnAtualiza,
    'controle-paginacao': ControlePaginacao,
    datepicker: DatePicker,
    modal: Modal,
    autocomplete: AutoComplete,
    'bt-ativo': AtivoInativo,
    'barra-top': BarraTop,
    'nps-modal': NpsModal,
    'assistente-manual': AssistenteManual,
    'telefone-usuario-modal': TelefoneUsuarioModal,
    'whatsapp-preferencias-usuario': WhatsappPreferenciasUsuario,
    'whatsapp-preview-modal': WhatsappPreviewModal,
    'mybp-visualizacao-listagem': MybpVisualizacaoListagem,
    'tiny-mce-editor': TinyMceEditor,
}

function initSideMenuMetis() {
    const $ = window.jQuery || window.$
    if (!$?.fn?.metisMenu) {
        return
    }

    const $menu = $('#side-menu')
    if (!$menu.length) {
        return
    }

    // Vue mount recria o DOM do #app e remove handlers jQuery do metisMenu.
    if ($menu.data('metisMenu')) {
        try {
            $menu.metisMenu('dispose')
        } catch (e) {
            // ignore dispose em instancia parcial
        }
    }

    $menu.metisMenu()
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

    // @vite carrega entries como type=module (deferred). O init classico do
    // metisMenu em sistema.blade roda ANTES do mount e perde os handlers.
    if (!app.__mybpMetisMountPatched) {
        app.__mybpMetisMountPatched = true
        const mount = app.mount.bind(app)
        app.mount = (container, ...args) => {
            const instance = mount(container, ...args)
            queueMicrotask(initSideMenuMetis)
            return instance
        }
    }
}

if (typeof window !== 'undefined') {
    window.registerGlobals = registerGlobals
}

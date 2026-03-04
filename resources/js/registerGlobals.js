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
    'nps-modal': require('./components/NpsModal').default
}

function setMask(tipo, el, mod) {
    let mascara
    switch (tipo) {
        case 'data':
            $(el).mask('00/00/0000')
            break
        case 'aniversario':
            $(el).mask('00/00')
            break
        case 'numero':
            $(el).mask('00000000000000000000')
            break
        case 'ano':
            $(el).mask('0000')
            break
        case 'cep':
            $(el).mask('00000-000')
            break
        case 'cpf':
            $(el).mask('000.000.000-00')
            break
        case 'cnpj':
            $(el).mask('00.000.000/0000-00')
            break
        case 'rg':
            $(el).mask('00.000.000-0')
            break
        case 'placa':
            $(el).mask('AAA-0000')
            break
        case 'per_aquisitivo':
            $(el).mask('0000/0000')
            break
        case 'hora':
            $(el).mask('00:00')
            break
        case 'altura':
            if ($(el).val().length > 3) {
                mascara = '####00,00'
            } else {
                mascara = '####0,0'
            }
            $(el).mask(mascara, { reverse: true })
            break
        case 'peso':
            $(el).mask('#0.000', { reverse: true })
            break
        case 'telefone':
            if (Object.keys(mod || {}).length) {
                if (mod.celular) {
                    $(el).mask('(00) 0 0000-0000')
                }
                if (mod.fixo) {
                    $(el).mask('(00) 0000-0000')
                }
            } else {
                if ($(el).val().length < 14) {
                    $(el).mask('(00) 0000-0000')
                }
                if ($(el).val().length == 14) {
                    let valorAtual = $(el).val()
                    $(el).unmask()
                    $(el).val(valorAtual)
                }
                if ($(el).val().length > 14) {
                    $(el).mask('(00) 0 0000-0000')
                }

                $(el).on('keyup', () => {
                    if ($(el).val().length < 14) {
                        $(el).mask('(00) 0000-0000')
                    }
                    if ($(el).val().length == 14) {
                        let valorAtual = $(el).val()
                        $(el).unmask()
                        $(el).val(valorAtual)
                    }
                    if ($(el).val().length > 14) {
                        $(el).mask('(00) 0 0000-0000')
                    }
                })
            }
            break
        case 'dinheiro':
            $(el).maskMoney({
                prefix: '',
                allowNegative: false,
                allowZero: true,
                thousands: '.',
                decimal: ',',
                affixesStay: false
            })
            break
        case 'pct':
            $(el).maskMoney({
                prefix: '',
                allowNegative: false,
                allowZero: true,
                thousands: '.',
                decimal: '.',
                affixesStay: false
            })
            break
        case 'dinheiroPN':
            $(el).maskMoney({
                prefix: '',
                allowNegative: true,
                allowZero: true,
                thousands: '.',
                decimal: ',',
                affixesStay: false
            })
            break
        default:
            break
    }
}

function resolveInput(el) {
    if (!el || el.nodeName === 'INPUT') {
        return el
    }
    let campo = $(el).find(':input:text:eq(0)')
    if (campo.length) {
        return campo[0]
    }
    return null
}

const mascaraDirective = {
    mounted(el, binding) {
        let input = resolveInput(el)
        if (!input) {
            return
        }
        const argumento = binding.arg
        const mod = binding.modifiers || {}
        setMask(argumento, input, mod)

        $(input).on('keypress', (e) => {
            let antes = e.target.value
            $(input).unmask()
            e.target.value = antes
        })

        $(input).on('keyup', (e) => {
            setMask(argumento, input, mod)
            let atual = $(input).val()
            $(input).unmask()
            e.target.value = atual
            let event = new Event('input', { bubbles: true })
            input.dispatchEvent(event)
            setMask(argumento, input, mod)

            if (e.target.type == 'text') {
                input.setSelectionRange(e.target.value.length, e.target.value.length)
            }
        })
    }
}

const popoverDirective = {
    mounted(el) {
        $(el).attr('data-toggle', 'popover')
        $(el).attr('data-trigger', 'hover')
        $(el).attr('data-placement', 'top')
        if (!$(el).attr('title')) {
            $(el).attr('title', 'Verificar o erro')
        }
        $(el).attr('data-content', 'Campo obrigatorio')
    }
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

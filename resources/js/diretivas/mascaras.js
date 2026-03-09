function setMask(tipo, el, mod) {
    let mascara
    const m = mod || {}
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
            if (Object.keys(m).length) {
                if (m.celular) {
                    $(el).mask('(00) 0 0000-0000')
                }
                if (m.fixo) {
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

export const mascaraDirective = {
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

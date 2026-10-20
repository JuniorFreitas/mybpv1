<template>
    <div class="form-group dp" :class="{ 'mb-0': !label }">
        <label v-if="label" class="dp__label" :for="id">{{ label }}</label>
        <input
            ref="campo"
            type="date"
            lang="pt-BR"
            autocomplete="off"
            data-vue-keep
            class="form-control dp__input"
            :class="[
                formsm ? 'form-control-sm' : '',
                inputClass,
                invalido ? 'is-invalid' : '',
            ]"
            :id="id"
            :value="iso"
            :required="obrigatorio"
            :disabled="disabled || leitura"
            :title="placeholder"
            @input="aoMudar"
            @change="aoMudar"
            @blur="aoSair"
        />
        <div
            v-show="invalido && mensagemErro"
            class="invalid-feedback"
            data-vue-keep
        >
            {{ mensagemErro }}
        </div>
    </div>
</template>

<script>
/**
 * Calendário nativo (input type="date") — padrão mybpfrontvagajs.
 * v-model / min / max em dd/mm/aaaa; input em ISO (aaaa-mm-dd).
 *
 * min/max NÃO vão no atributo HTML (senão o browser esconde/reverte a data fora
 * da faixa). Validação em JS; data digitada permanece visível com erro.
 *
 * data-vue-keep: formReset() global não remove estes nós (evita crash do Vue).
 */
function brParaIso(br) {
    const m = /^(\d{2})\/(\d{2})\/(\d{4})$/.exec(String(br || '').trim())
    return m ? `${m[3]}-${m[2]}-${m[1]}` : ''
}

function isoParaBr(iso) {
    const m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(String(iso || '').trim())
    return m ? `${m[3]}/${m[2]}/${m[1]}` : ''
}

function isoEhCalendarioValido(iso) {
    const m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(String(iso || '').trim())
    if (!m) return false
    const y = Number(m[1])
    const mo = Number(m[2])
    const d = Number(m[3])
    const dt = new Date(y, mo - 1, d)
    return dt.getFullYear() === y && dt.getMonth() === mo - 1 && dt.getDate() === d
}

export default {
    name: 'DateInputHtml5',
    emits: ['update:modelValue', 'input', 'onblur', 'valido'],
    props: {
        modelValue: { type: String, required: false, default: '' },
        value: { type: String, required: false, default: '' },
        id: { type: String, required: false, default: undefined },
        label: { type: String, required: false, default: '' },
        placeholder: { type: String, required: false, default: '' },
        min: { type: String, required: false, default: null },
        max: { type: String, required: false, default: null },
        disabled: { type: Boolean, required: false, default: false },
        leitura: { type: Boolean, required: false, default: false },
        formsm: { type: Boolean, required: false, default: false },
        inputClass: { type: String, required: false, default: '' },
        obrigatorio: { type: Boolean, required: false, default: false },
    },
    data() {
        return {
            invalido: false,
            mensagemErro: '',
        }
    },
    computed: {
        currentBr() {
            if (this.modelValue !== undefined && this.modelValue !== null && this.modelValue !== '') {
                return this.modelValue
            }
            return this.value || ''
        },
        iso() {
            return brParaIso(this.currentBr)
        },
        isoMin() {
            return brParaIso(this.min) || ''
        },
        isoMax() {
            return brParaIso(this.max) || ''
        },
    },
    methods: {
        emitir(br) {
            this.$emit('update:modelValue', br)
            this.$emit('input', br)
        },
        setErro(msg) {
            this.invalido = true
            this.mensagemErro = msg || 'Data inválida'
            this.$emit('valido', false)
        },
        marcarErro(msg) {
            this.setErro(msg)
        },
        limparErro() {
            this.invalido = false
            this.mensagemErro = ''
            this.$emit('valido', true)
        },
        aplicarRegras(iso, { emitirValor = true } = {}) {
            if (!iso) {
                if (emitirValor) this.emitir('')
                if (this.obrigatorio) {
                    this.setErro('Campo obrigatório')
                    return false
                }
                this.limparErro()
                return true
            }

            if (emitirValor) {
                this.emitir(isoParaBr(iso))
            }

            if (!isoEhCalendarioValido(iso)) {
                this.setErro('Data inválida')
                return false
            }

            if (this.isoMin && iso < this.isoMin) {
                this.setErro(`Data anterior ao permitido (mín. ${this.min})`)
                return false
            }

            if (this.isoMax && iso > this.isoMax) {
                this.setErro(`Data posterior ao permitido (máx. ${this.max})`)
                return false
            }

            this.limparErro()
            return true
        },
        validar(el) {
            if (this.disabled || this.leitura) {
                this.limparErro()
                return true
            }

            const campo = el || this.$refs.campo
            if (!campo) {
                return true
            }

            if (campo.validity && campo.validity.badInput) {
                this.setErro('Data incompleta')
                return false
            }

            return this.aplicarRegras(String(campo.value || '').trim())
        },
        aoMudar(evento) {
            const campo = evento.target

            if (campo.validity && campo.validity.badInput) {
                this.setErro('Data incompleta')
                return
            }

            this.aplicarRegras(String(campo.value || '').trim())
        },
        aoSair(evento) {
            this.validar(evento.target)
            this.$emit('onblur', evento)
        },
    },
}
</script>

<style lang="scss" scoped>
.dp__label {
    margin-bottom: 0.25rem;
}

.dp__input {
    min-height: 38px;
    color-scheme: light;
    accent-color: #0b1b3d;
    font-family: inherit;

    &:disabled {
        cursor: not-allowed;
        background-color: #e9ecef;
    }

    &::-webkit-calendar-picker-indicator {
        width: 18px;
        height: 18px;
        padding: 4px;
        border-radius: 6px;
        opacity: 0.75;
        cursor: pointer;
        background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%230b1b3d' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><rect x='3' y='4' width='18' height='18' rx='2'/><path d='M16 2v4M8 2v4M3 10h18'/></svg>");
        background-size: 18px 18px;
        background-repeat: no-repeat;
        background-position: center;
        transition: opacity 0.15s ease, background-color 0.15s ease;

        &:hover {
            opacity: 1;
            background-color: rgba(11, 27, 61, 0.08);
        }
    }

    &:focus {
        border-color: #0047d6;
        box-shadow: 0 0 0 3px rgba(0, 71, 214, 0.18);
    }

    &.is-invalid,
    &.is-invalid:focus {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.2);
    }
}
</style>

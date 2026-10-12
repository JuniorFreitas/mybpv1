<template>
    <div
        class="mybp-codigo-input"
        :class="{ 'mybp-codigo-input--invalido': invalido }"
        role="group"
        :aria-label="rotulo"
        @paste.prevent="onPaste"
    >
        <input
            v-for="(caractere, i) in caracteres"
            :key="i"
            type="text"
            class="mybp-codigo-input__caixa"
            :class="{ 'mybp-codigo-input__caixa--preenchida': caractere }"
            :value="caractere"
            :disabled="disabled"
            :aria-label="`${rotulo}: caractere ${i + 1} de ${tamanho}`"
            :autocomplete="i === 0 ? 'one-time-code' : 'off'"
            autocapitalize="characters"
            autocorrect="off"
            spellcheck="false"
            inputmode="text"
            @input="onInput($event, i)"
            @keydown="onKeydown($event, i)"
            @focus="$event.target.select()"
        >
    </div>
</template>

<script>
export default {
    name: 'MybpCodigoInput',
    props: {
        modelValue: { type: String, default: '' },
        tamanho: { type: Number, default: 8 },
        disabled: { type: Boolean, default: false },
        invalido: { type: Boolean, default: false },
        rotulo: { type: String, default: 'Código de verificação' }
    },
    emits: ['update:modelValue', 'completo'],
    data() {
        return {
            caracteres: this.separar(this.modelValue)
        }
    },
    watch: {
        modelValue(valor) {
            if (valor !== this.caracteres.join('')) {
                this.caracteres = this.separar(valor)
            }
        }
    },
    methods: {
        limpar(texto) {
            return String(texto || '')
                .replace(/[^a-zA-Z0-9]/g, '')
                .toUpperCase()
        },
        separar(texto) {
            const limpo = this.limpar(texto)
            return Array.from({ length: this.tamanho }, (_, i) => limpo[i] || '')
        },
        caixas() {
            return this.$el.querySelectorAll('input')
        },
        focar(indice = 0) {
            const alvo = this.caixas()[Math.max(0, Math.min(indice, this.tamanho - 1))]
            if (alvo) {
                alvo.focus()
            }
        },
        // Distribui o texto a partir de `inicio` (digitação, colar e autofill do código).
        preencher(texto, inicio) {
            const limpo = this.limpar(texto)
            if (!limpo) {
                return
            }
            limpo
                .slice(0, this.tamanho - inicio)
                .split('')
                .forEach((c, k) => {
                    this.caracteres[inicio + k] = c
                })
            this.emitir()
            this.focar(inicio + limpo.length)
        },
        emitir() {
            const valor = this.caracteres.join('')
            this.$emit('update:modelValue', valor)
            if (valor.length === this.tamanho) {
                this.$emit('completo', valor)
            }
        },
        onInput(evento, i) {
            const digitado = this.limpar(evento.target.value)
            if (!digitado) {
                this.caracteres[i] = ''
                evento.target.value = ''
                this.emitir()
                return
            }
            this.preencher(digitado, i)
            evento.target.value = this.caracteres[i]
        },
        onKeydown(evento, i) {
            if (evento.key === 'Backspace' && !this.caracteres[i] && i > 0) {
                evento.preventDefault()
                this.caracteres[i - 1] = ''
                this.emitir()
                this.focar(i - 1)
            } else if (evento.key === 'ArrowLeft' && i > 0) {
                evento.preventDefault()
                this.focar(i - 1)
            } else if (evento.key === 'ArrowRight' && i < this.tamanho - 1) {
                evento.preventDefault()
                this.focar(i + 1)
            }
        },
        onPaste(evento) {
            if (this.disabled) {
                return
            }
            const texto = this.limpar((evento.clipboardData || window.clipboardData).getData('text'))
            if (texto) {
                this.caracteres = this.separar('')
                this.preencher(texto, 0)
            }
        }
    }
}
</script>

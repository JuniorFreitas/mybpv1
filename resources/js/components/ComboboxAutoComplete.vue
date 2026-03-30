<template>
    <div class="combobox-ac-wrap" :class="wrapperClass">
        <div ref="comboEl" class="input-group input-group-sm ma-filtro-combo">
            <input
                :id="inputId"
                ref="inputEl"
                type="text"
                class="form-control ma-select"
                role="combobox"
                aria-autocomplete="list"
                :aria-expanded="aberto ? 'true' : 'false'"
                :aria-controls="listboxId"
                :disabled="disabled"
                :placeholder="focused ? placeholderFocus : placeholderBlur"
                :inputmode="inputmode"
                autocomplete="off"
                :value="inputDisplay"
                @focus="onFocus"
                @input="onInput"
                @keydown.escape.prevent="close"
                @keydown.down.prevent="aberto = true"
                @blur="onBlur"
            />
            <div class="input-group-append">
                <button
                    type="button"
                    class="btn btn-outline-secondary ma-filtro-combo-toggle"
                    tabindex="-1"
                    title="Abrir lista"
                    :disabled="disabled"
                    @mousedown.prevent.stop="abrirDropdown"
                >
                    <i class="fa fa-chevron-down"></i>
                </button>
            </div>
        </div>
        <Teleport to="body">
            <ul
                v-show="aberto && hasOptions"
                :id="listboxId"
                ref="listEl"
                role="listbox"
                class="ma-autocomplete-list ma-autocomplete-list--portal list-unstyled mb-0"
                :style="dropdownStyle"
            >
                <li
                    v-if="filteredOptions.length === 0"
                    class="ma-autocomplete-item ma-autocomplete-item--empty text-muted"
                >
                    {{ emptyMessage }}
                </li>
                <li
                    v-for="(opt, idx) in filteredOptions"
                    :key="optionKey(opt, idx)"
                    role="option"
                    class="ma-autocomplete-item"
                    :class="{ 'ma-autocomplete-item--ativo': isOptionActive(opt) }"
                    @mousedown.prevent="selecionar(opt)"
                >
                    <slot name="option" :option="opt" :active="isOptionActive(opt)">
                        <span class="ma-autocomplete-titulo">{{ opt.label }}</span>
                        <span v-if="opt.meta" class="ma-autocomplete-meta text-muted">{{ formatMeta(opt.meta) }}</span>
                    </slot>
                </li>
            </ul>
        </Teleport>
    </div>
</template>

<script>
export default {
    name: 'ComboboxAutoComplete',
    props: {
        modelValue: {
            type: [String, Number],
            default: undefined
        },
        /** Lista de { value, label, meta?, raw? } */
        options: {
            type: Array,
            default: () => []
        },
        disabled: {
            type: Boolean,
            default: false
        },
        inputId: {
            type: String,
            required: true
        },
        placeholderBlur: {
            type: String,
            default: 'Clique para buscar ou escolher'
        },
        placeholderFocus: {
            type: String,
            default: 'Digite para filtrar…'
        },
        emptyMessage: {
            type: String,
            default: 'Nenhum resultado encontrado.'
        },
        /** Identificador para o evento opening (fechar outros filtros no pai) */
        instanceId: {
            type: String,
            default: ''
        },
        maxResults: {
            type: Number,
            default: 100
        },
        inputmode: {
            type: String,
            default: 'text'
        },
        wrapperClass: {
            type: String,
            default: ''
        }
    },
    emits: ['update:modelValue', 'select', 'opening'],
    data() {
        return {
            query: '',
            focused: false,
            aberto: false,
            dropdownStyle: {}
        }
    },
    computed: {
        listboxId() {
            return `${this.inputId}-listbox`
        },
        hasOptions() {
            return Array.isArray(this.options) && this.options.length > 0
        },
        inputDisplay() {
            if (this.focused) {
                return this.query
            }
            return this.labelSelecionado
        },
        labelSelecionado() {
            const opt = this.findOptionByValue(this.modelValue)
            return opt ? opt.label : ''
        },
        filteredOptions() {
            const list = this.options || []
            const q = (this.query || '').trim().toLowerCase()
            if (!q) {
                return list.slice(0, this.maxResults)
            }
            return list
                .filter((o) => {
                    const label = (o.label || '').toLowerCase()
                    const meta = (o.meta || '').toLowerCase()
                    const val = String(o.value ?? '').toLowerCase()
                    return label.includes(q) || meta.includes(q) || val.includes(q)
                })
                .slice(0, this.maxResults)
        }
    },
    mounted() {
        this._onScrollResize = () => {
            if (this.aberto) {
                this.atualizarPosicaoDropdown()
            }
        }
        window.addEventListener('scroll', this._onScrollResize, true)
        window.addEventListener('resize', this._onScrollResize)
    },
    beforeUnmount() {
        window.removeEventListener('scroll', this._onScrollResize, true)
        window.removeEventListener('resize', this._onScrollResize)
    },
    methods: {
        formatMeta(meta) {
            if (!meta) {
                return ''
            }
            const s = String(meta)
            return s.startsWith('—') ? s : `— ${s}`
        },
        optionKey(opt, idx) {
            const v = opt.value
            if (v === '' || v === null || v === undefined) {
                return `opt-empty-${idx}`
            }
            return `opt-${String(v)}-${idx}`
        },
        findOptionByValue(val) {
            const list = this.options || []
            for (let i = 0; i < list.length; i++) {
                if (this.valoresIguais(list[i].value, val)) {
                    return list[i]
                }
            }
            return null
        },
        valoresIguais(a, b) {
            if (a === b) {
                return true
            }
            if (a === '' && (b === '' || b === null || b === undefined)) {
                return true
            }
            if (b === '' && (a === '' || a === null || a === undefined)) {
                return true
            }
            const na = Number(a)
            const nb = Number(b)
            if (!Number.isNaN(na) && !Number.isNaN(nb) && na === nb) {
                return true
            }
            return false
        },
        isOptionActive(opt) {
            return this.valoresIguais(opt.value, this.modelValue)
        },
        onFocus() {
            this.$emit('opening', this.instanceId)
            this.focused = true
            this.query = ''
            this.aberto = true
            this.$nextTick(() => this.atualizarPosicaoDropdown())
        },
        onInput(e) {
            const v = e && e.target ? e.target.value : ''
            this.$emit('opening', this.instanceId)
            this.focused = true
            this.query = v
            this.aberto = true
            this.atualizarPosicaoDropdown()
        },
        onBlur() {
            setTimeout(() => {
                this.aberto = false
                this.focused = false
                this.query = ''
                this.dropdownStyle = {}
            }, 180)
        },
        abrirDropdown() {
            if (this.disabled || !this.hasOptions) {
                return
            }
            this.$emit('opening', this.instanceId)
            this.focused = true
            this.query = ''
            this.aberto = true
            this.$nextTick(() => {
                const input = this.$refs.inputEl
                if (input && typeof input.focus === 'function') {
                    input.focus()
                }
                this.atualizarPosicaoDropdown()
            })
        },
        selecionar(opt) {
            if (!opt) {
                return
            }
            this.$emit('update:modelValue', opt.value)
            this.$emit('select', opt)
            this.focused = false
            this.query = ''
            this.aberto = false
            this.dropdownStyle = {}
        },
        close() {
            this.aberto = false
            this.focused = false
            this.query = ''
            this.dropdownStyle = {}
        },
        atualizarPosicaoDropdown() {
            this.$nextTick(() => {
                const combo = this.$refs.comboEl
                const el = combo || this.$refs.inputEl
                if (!el || typeof el.getBoundingClientRect !== 'function') {
                    return
                }
                const r = el.getBoundingClientRect()
                this.dropdownStyle = {
                    position: 'fixed',
                    top: `${Math.round(r.bottom + 4)}px`,
                    left: `${Math.round(r.left)}px`,
                    width: `${Math.round(r.width)}px`,
                    zIndex: 10000
                }
            })
        },
        /** Para o listener de clique no documento do componente pai */
        containsTarget(target) {
            if (this.$el && this.$el.contains(target)) {
                return true
            }
            const list = this.$refs.listEl
            return list && typeof list.contains === 'function' && list.contains(target)
        }
    }
}
</script>

<style scoped>
/* Painel no body (Teleport): fundo, borda, sombra e rolagem — escopo do filho */
.ma-autocomplete-list.ma-autocomplete-list--portal {
    max-height: 280px;
    overflow-y: auto;
    overflow-x: hidden;
    -webkit-overflow-scrolling: touch;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    background: #fff;
    box-shadow: 0 0.5rem 1.25rem rgba(0, 55, 85, 0.2);
    padding: 0.25rem 0;
}
.ma-autocomplete-item--ativo {
    background: rgba(0, 55, 85, 0.1);
}
.ma-autocomplete-item--empty {
    cursor: default;
    font-size: 0.82rem;
}
.ma-autocomplete-item--empty:hover {
    background: transparent !important;
}
.ma-autocomplete-item {
    padding: 0.45rem 0.65rem;
    cursor: pointer;
    font-size: 0.85rem;
    line-height: 1.3;
    border-bottom: 1px solid rgba(0, 0, 0, 0.04);
}
.ma-autocomplete-item:last-child {
    border-bottom: none;
}
.ma-autocomplete-item:hover {
    background: rgba(0, 55, 85, 0.08);
}
:deep(.ma-autocomplete-titulo) {
    font-weight: 600;
    color: #212529;
}
:deep(.ma-autocomplete-meta) {
    font-size: 0.78rem;
}
</style>

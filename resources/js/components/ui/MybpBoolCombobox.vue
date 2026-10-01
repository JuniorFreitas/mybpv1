<template>
    <div class="mybp-combobox-wrap">
        <combobox-auto-complete
            :instance-id="inputId"
            :input-id="inputId"
            v-model="combo"
            :options="opcoesSimNao"
            :disabled="disabled"
            placeholder-blur="Selecione..."
            empty-message="Nenhuma opção."
            :max-results="5"
            @opening="$emit('opening', inputId)"
            @select="$emit('select', inputId)"
        ></combobox-auto-complete>
    </div>
</template>

<script>
import ComboboxAutoComplete from '../ComboboxAutoComplete.vue'

export default {
    name: 'MybpBoolCombobox',
    components: { ComboboxAutoComplete },
    props: {
        modelValue: {
            type: [Boolean, String, Number],
            default: ''
        },
        inputId: {
            type: String,
            required: true
        },
        disabled: {
            type: Boolean,
            default: false
        },
        /** boolean = true/false; simnao = 'sim'/'nao' */
        mode: {
            type: String,
            default: 'boolean',
            validator: (v) => ['boolean', 'simnao'].includes(v)
        }
    },
    emits: ['update:modelValue', 'opening', 'select'],
    computed: {
        opcoesSimNao() {
            return [
                { value: 'sim', label: 'Sim' },
                { value: 'nao', label: 'Não' }
            ]
        },
        combo: {
            get() {
                const v = this.modelValue
                if (v === true || v === 'true' || v === 1 || v === '1' || v === 'sim') return 'sim'
                if (v === false || v === 'false' || v === 0 || v === '0' || v === 'nao') return 'nao'
                return ''
            },
            set(v) {
                if (this.mode === 'simnao') {
                    if (v === 'sim' || v === 'nao') this.$emit('update:modelValue', v)
                    else this.$emit('update:modelValue', '')
                    return
                }
                if (v === 'sim') this.$emit('update:modelValue', true)
                else if (v === 'nao') this.$emit('update:modelValue', false)
                else this.$emit('update:modelValue', '')
            }
        }
    }
}
</script>

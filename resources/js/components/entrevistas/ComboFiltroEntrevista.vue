<template>
    <div v-if="visivel" class="col-12 col-md-4">
        <div class="form-group mybp-filtro-campo">
            <label class="mybp-label" :for="inputId">{{ label }}</label>
            <div class="mybp-combobox-wrap">
                <combobox-auto-complete
                    ref="combo"
                    :instance-id="inputId"
                    :input-id="inputId"
                    :model-value="modelValue"
                    :options="options"
                    :disabled="disabled"
                    :placeholder-blur="placeholder"
                    empty-message="Nenhuma opção."
                    :max-results="30"
                    @update:model-value="$emit('update:modelValue', $event)"
                    @opening="$emit('opening', inputId)"
                    @select="$emit('select')"
                ></combobox-auto-complete>
            </div>
        </div>
    </div>
</template>

<script>
import ComboboxAutoComplete from '../ComboboxAutoComplete.vue'

export default {
    name: 'ComboFiltroEntrevista',
    components: { ComboboxAutoComplete },
    props: {
        modelValue: { default: '' },
        options: { type: Array, default: () => [] },
        label: { type: String, required: true },
        inputId: { type: String, required: true },
        placeholder: { type: String, default: 'Sem filtro' },
        disabled: { type: Boolean, default: false },
        visivel: { type: Boolean, default: true }
    },
    emits: ['update:modelValue', 'opening', 'select'],
    methods: {
        fechar() {
            const combo = this.$refs.combo
            if (combo && typeof combo.close === 'function') {
                combo.close()
            }
        }
    }
}
</script>

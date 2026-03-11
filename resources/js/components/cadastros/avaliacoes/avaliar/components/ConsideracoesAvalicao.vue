<template>
    <fieldset>
        <legend>MINHAS CONSIDERAÇÕES</legend>
        <textarea
            :disabled="visualizando"
            :value="modelValue"
            @input="emitUpdate($event.target.value)"
            class="form-control"
            @blur.prevent="validarCampo($event.target)"
            @change.prevent="validarCampo($event.target)"
            placeholder="Se desejar, faça considerações"
            rows="4"
        ></textarea>

        <h5 class="mt-3" v-if="principal && comentarioFuncionario">Considerações do colaborador: {{ comentarioFuncionario }}</h5>
    </fieldset>
</template>

<script>
export default {
    name: 'ConsideracoesAvaliacao',
    emits: ['update:modelValue', 'input'],
    props: {
        modelValue: {
            type: String,
            default: ''
        },
        comentarioFuncionario: {
            type: String,
            default: ''
        },
        principal: {
            type: Boolean,
            default: false
        },
        visualizando: {
            type: Boolean,
            default: false
        }
    },
    methods: {
        emitUpdate(value) {
            this.$emit('update:modelValue', value)
            this.$emit('input', value)
        },
        validarCampo(target) {
            if (typeof valida_campo_vazio === 'function') {
                valida_campo_vazio(target, 1)
            }
        }
    }
}
</script>

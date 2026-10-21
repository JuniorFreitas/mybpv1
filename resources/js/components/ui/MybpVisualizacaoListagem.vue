<template>
    <div class="btn-group btn-group-sm mybp-visualizacao" role="group" aria-label="Tipo de visualização">
        <button
            type="button"
            class="btn"
            :class="modelValue === 'lista' ? 'btn-primary' : 'btn-outline-secondary'"
            :aria-pressed="modelValue === 'lista' ? 'true' : 'false'"
            @click="escolher('lista')"
        >
            <i class="fas fa-th-large"></i> Lista
        </button>
        <button
            type="button"
            class="btn"
            :class="modelValue === 'tabela' ? 'btn-primary' : 'btn-outline-secondary'"
            :aria-pressed="modelValue === 'tabela' ? 'true' : 'false'"
            @click="escolher('tabela')"
        >
            <i class="fas fa-table"></i> Tabela
        </button>
    </div>
</template>

<script>
import { gravarVisualizacaoListagem } from '../../utils/visualizacaoListagem'

export default {
    name: 'MybpVisualizacaoListagem',
    props: {
        modelValue: { type: String, default: 'tabela' },
        tela: { type: String, required: true }
    },
    emits: ['update:modelValue'],
    methods: {
        escolher(modo) {
            this.$emit('update:modelValue', gravarVisualizacaoListagem(this.tela, modo))
        }
    }
}
</script>

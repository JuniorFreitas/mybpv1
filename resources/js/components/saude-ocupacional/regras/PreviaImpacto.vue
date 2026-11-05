<template>
    <div class="previa-impacto" v-if="carregando || previa">
        <p v-if="carregando" class="mb-0"><i class="fa fa-spinner fa-pulse"></i> Calculando impacto...</p>
        <template v-else-if="previa">
            <p class="mb-1">
                <strong>{{ previa.alterados }}</strong>
                {{ previa.alterados === 1 ? 'ASO muda' : 'ASOs mudam' }} de vencimento
                <span class="text-muted">(de {{ previa.avaliados }} ASOs atuais avaliados)</span>.
            </p>
            <p class="mb-1 small" v-if="previa.alterados">
                <span class="badge badge-warning mr-1">{{ previa.antecipados }} antecipados</span>
                <span class="badge badge-info">{{ previa.adiados }} adiados</span>
            </p>
            <p class="small text-muted mb-1" v-if="previa.limite_atingido">Prévia limitada aos primeiros ASOs da empresa.</p>
            <table class="table table-sm mb-0 mt-2" v-if="previa.exemplos && previa.exemplos.length">
                <thead>
                    <tr>
                        <th>Exemplo</th>
                        <th>Tipo</th>
                        <th>Vence hoje em</th>
                        <th>Passa a vencer</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(ex, i) in previa.exemplos" :key="i">
                        <td>{{ ex.colaborador }}</td>
                        <td>{{ ex.tipo }}</td>
                        <td>{{ ex.vencimento_atual_br }}</td>
                        <td>{{ ex.vencimento_novo_br }}</td>
                    </tr>
                </tbody>
            </table>
            <p class="small text-muted mb-0 mt-2" v-if="previa.alterados">
                A mudança só chega aos ASOs já cadastrados no modo <strong>Retroativo</strong> ou <strong>A partir de uma data</strong>.
            </p>
        </template>
    </div>
</template>

<script>
import { defineComponent } from 'vue'

export default defineComponent({
    name: 'PreviaImpacto',

    props: {
        previa: { type: Object, default: null },
        carregando: { type: Boolean, default: false }
    }
})
</script>

<style scoped>
.previa-impacto {
    border: 1px dashed #adb5bd;
    border-radius: 6px;
    padding: 10px 12px;
    background: #f8f9fa;
}
</style>

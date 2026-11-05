<template>
    <div class="card card-outline card-info" ref="raiz">
        <div class="card-header">
            <h3 class="card-title mb-0">Afastados hoje</h3>
        </div>
        <div class="card-body p-0">
            <p v-if="!itens.length" class="text-muted p-3 mb-0">Nenhum colaborador afastado hoje.</p>

            <div v-else class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Colaborador</th>
                            <th>Período</th>
                            <th class="text-center">Dias</th>
                            <th>Volta em</th>
                            <th>Atenção</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in itens" :key="item.id">
                            <td>{{ item.colaborador }}</td>
                            <td>{{ item.data_inicio_br }} até {{ item.data_fim_br }}</td>
                            <td class="text-center">{{ item.dias }}</td>
                            <td>{{ textoRetorno(item.retorna_em_dias) }}</td>
                            <td>
                                <span v-if="item.exige_aso_retorno" class="badge badge-warning mr-1" title="Afastamento de 30 dias ou mais (NR-7)">
                                    ASO de retorno
                                </span>
                                <span v-if="item.acima_limite_inss" class="badge badge-danger" title="Mais de 15 dias: encaminhar ao INSS">
                                    INSS
                                </span>
                                <span v-if="!item.exige_aso_retorno && !item.acima_limite_inss" class="text-muted">—</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p v-if="total > itens.length" class="text-muted small px-3 py-2 mb-0">
                Mostrando {{ itens.length }} de {{ total }}.
            </p>
        </div>
    </div>
</template>

<script>
import { defineComponent } from 'vue'

export default defineComponent({
    name: 'PainelAfastadosLista',

    props: {
        itens: { type: Array, default: () => [] },
        total: { type: Number, default: 0 }
    },

    methods: {
        textoRetorno(dias) {
            if (dias <= 1) {
                return 'amanhã'
            }
            return `${dias} dias`
        },

        focar() {
            this.$refs.raiz?.scrollIntoView({ behavior: 'smooth', block: 'start' })
        }
    }
})
</script>

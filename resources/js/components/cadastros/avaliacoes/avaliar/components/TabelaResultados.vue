<template>
    <div v-if="hasValidData">
        <table
            v-for="(item, index) in resultadoAgrupado"
            :key="item.id || index"
            class="table"
        >
            <thead>
            <tr>
                <th>{{ getTopicoTitulo(item) }}</th>
                <th
                    v-for="(avaliador, id) in getAvaliadores(item)"
                    :key="avaliador.id"
                    class="text-center"
                >
                        <span>
                            {{ avaliador.origem === 'Funcionario' ? 'Autoavaliação' : `Avaliador ${id + 1}` }}
                        </span>
                </th>
                <th class="text-center">MÉDIA</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="(sub, subIndex) in item" :key="sub.id || subIndex">
                <td style="width: 33%">{{ sub.subtopico }}</td>
                <td
                    v-for="(avaliador, avalIndex) in (sub.avaliadores || [])"
                    :key="avaliador.id || avalIndex"
                    style="width: 15%"
                >
                    <input
                        type="number"
                        class="form-control form-control-sm text-center"
                        readonly="readonly"
                        min="0"
                        max="5"
                        step="0.1"
                        :value="formatarDecimal(avaliador.nota)"
                    >
                </td>
                <td style="width: 7%" class="text-center">
                    <input
                        type="number"
                        class="form-control form-control-sm text-center"
                        readonly="readonly"
                        min="0"
                        max="5"
                        step="0.1"
                        :value="formatarDecimal(sub.media)"
                    >
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
import avaliacaoMixin from '../mixins/avaliacaoMixin'

export default {
    name: 'TabelaResultados',
    mixins: [avaliacaoMixin],
    props: {
        resultadoAgrupado: {
            type: Array,
            default: () => []
        }
    },
    computed: {
        hasValidData() {
            return this.hasValidData(this.resultadoAgrupado)
        }
    },
    methods: {
        getTopicoTitulo(item) {
            return (item[0] || {}).topico_pai || ''
        },
        getAvaliadores(item) {
            return (item[0] || {}).avaliadores || []
        }
    }
}
</script>

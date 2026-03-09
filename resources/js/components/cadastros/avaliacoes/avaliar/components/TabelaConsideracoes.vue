<template>
    <table class="table" v-if="isValidData && hasAvaliadores">
        <thead>
            <tr>
                <th v-for="(avaliador, id) in avaliadores" :key="avaliador.id" class="text-center">
                    <span>
                        {{ avaliador.origem === 'Funcionario' ? 'Autoavaliação' : `Avaliador ${id + 1}` }}
                    </span>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td v-for="(avaliador, avalIndex) in avaliadores" :key="avaliador.id || avalIndex">
                    <label>Considerações</label>
                    <textarea rows="5" class="form-control form-control-sm" readonly="readonly">{{ avaliador.comentario }}</textarea>
                </td>
            </tr>
        </tbody>
    </table>
</template>

<script>
import avaliacaoMixin from '../mixins/avaliacaoMixin'

export default {
    name: 'TabelaConsideracoes',
    mixins: [avaliacaoMixin],
    props: {
        resultadoAgrupado: {
            type: Array,
            default: () => []
        }
    },
    computed: {
        isValidData() {
            return this.hasValidData(this.resultadoAgrupado)
        },
        hasAvaliadores() {
            return this.resultadoAgrupado?.[0]?.[0]?.avaliadores?.length > 0
        },
        avaliadores() {
            return this.resultadoAgrupado?.[0]?.[0]?.avaliadores || []
        }
    }
}
</script>

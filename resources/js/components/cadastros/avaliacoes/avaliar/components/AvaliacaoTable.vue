<template>
    <div class="table-responsive">
        <!-- Tabela para avaliações com autoavaliação -->
        <table
            v-if="avaliacao && avaliacao.auto_avaliacao"
            class="table table-bordered"
        >
            <thead class="bg-white">
            <tr class="bg-white">
                <td class="text-center">Ano Avaliação</td>
                <td class="text-center">Título</td>
                <td class="text-center">Tipo</td>
                <td class="text-center">Avaliar até</td>
                <td class="text-center">Funcionário</td>
                <td class="text-center">Avaliador</td>
                <td class="text-center">Avaliar Como</td>
                <td class="text-center">Ação</td>
            </tr>
            </thead>
            <tbody>
            <TabelaLinhaAutoAvaliacao
                v-for="item in lista"
                :key="item.id"
                :item="item"
                :url-impressao="urlImpressao"
                :tem-privilegio="temPrivilegio"
                @avaliar="$emit('avaliar', $event)"
                @avaliar-final="$emit('avaliar-final', $event)"
                @visualizar="$emit('visualizar', $event)"
                @visualizar-final="$emit('visualizar-final', $event)"
            />
            </tbody>
        </table>

        <!-- Tabela para avaliações sem autoavaliação -->
        <table
            v-if="avaliacao && !avaliacao.auto_avaliacao"
            class="table table-bordered"
        >
            <thead class="bg-white">
            <tr class="bg-white">
                <td class="text-center">Ano Avaliação</td>
                <td class="text-center">Título</td>
                <td class="text-center">Tipo</td>
                <td class="text-center">Avaliar até</td>
                <td class="text-center">Funcionário</td>
                <td class="text-center">Avaliador</td>
                <td class="text-center">Status</td>
                <td class="text-center">Ação</td>
            </tr>
            </thead>
            <tbody>
            <TabelaLinhaSemAutoAvaliacao
                v-for="item in listaFiltradaSemAutoAvaliacao"
                :key="item.id"
                :item="item"
                :url-impressao="urlImpressao"
                :tem-privilegio="temPrivilegio"
                @avaliar="$emit('avaliar', $event)"
                @avaliar-final="$emit('avaliar-final', $event)"
                @visualizar="$emit('visualizar', $event)"
                @visualizar-final="$emit('visualizar-final', $event)"
            />
            </tbody>
        </table>
    </div>
</template>

<script>
import TabelaLinhaAutoAvaliacao from './TabelaLinhaAutoAvaliacao.vue'
import TabelaLinhaSemAutoAvaliacao from './TabelaLinhaSemAutoAvaliacao.vue'

export default {
    name: 'AvaliacaoTable',
    components: {
        TabelaLinhaAutoAvaliacao,
        TabelaLinhaSemAutoAvaliacao
    },
    props: {
        lista: {
            type: Array,
            required: true
        },
        avaliacao: {
            type: Object,
            default: null
        },
        urlImpressao: {
            type: String,
            required: true
        },
        temPrivilegio: {
            type: Boolean,
            default: false
        }
    },
    computed: {
        listaFiltradaSemAutoAvaliacao() {
            return this.lista.filter(item =>
                !item.avaliacao.auto_avaliacao && item.principal
            )
        }
    }
}
</script>

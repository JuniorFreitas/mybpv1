<template>
    <tr :class="classeStatusLinha">
        <td class="text-center">{{ item.avaliacao.ano_avaliacao }}</td>
        <td class="text-center">{{ item.avaliacao.titulo }}</td>
        <td class="text-center">{{ item.avaliacao.avaliacao_tipo.nome }}</td>
        <td class="text-center">{{ item.avaliacao.data_fim_prazo }}</td>
        <td class="text-center">
            <i class="fa fa-user" v-if="item.avaliador_id === item.funcionario_id"></i>
            {{ item.funcionario.nome }}
        </td>
        <td class="text-center">{{ item.avaliador.nome }}</td>
        <td class="text-center">
            <span v-if="item.origem_feedback === 'Funcionario' && !item.principal">
                Autoavaliação
            </span>
            <span v-else-if="item.origem_feedback === 'Avaliador' && !item.principal">
                Avaliador Par
            </span>
            <span v-else-if="item.origem_feedback === 'Avaliador' && item.principal">
                Avaliador Gestor (Principal)
            </span>
        </td>
        <td class="text-center">
            <AcoesDropdown
                v-if="mostrarAcoes"
                :item="item"
                :url-impressao="urlImpressao"
                :acoes="acoesDisponiveis"
                @avaliar="$emit('avaliar', item)"
                @avaliar-final="$emit('avaliar-final', item)"
                @visualizar="$emit('visualizar', item)"
                @visualizar-final="$emit('visualizar-final', item)"
            />
        </td>
    </tr>
</template>

<script>
import AcoesDropdown from './AcoesDropdown.vue'

export default {
    name: 'TabelaLinhaAutoAvaliacao',
    components: {
        AcoesDropdown
    },
    props: {
        item: {
            type: Object,
            required: true
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
        classeStatusLinha() {
            if (this.item.pendente_autoavaliacao) {
                return 'bg-danger text-white'
            }
            if (this.item.pendente_autoavaliacao_colaborador) {
                return 'bg-pink'
            }
            if (this.item.pendente_avaliacao_par && this.item.status !== 'Finalizada') {
                return 'bg-warning'
            }
            if (!this.item.pendente_avaliacao_par && this.item.status !== 'Finalizada') {
                return 'bg-info text-white'
            }
            if (this.item.status === 'Finalizada') {
                return 'bg-success text-white'
            }
            return ''
        },

        mostrarAcoes() {
            return (
                (this.item.status === 'Pendente' && this.item.fez_auto_avaliacao && !this.item.principal) ||
                (this.item.status === 'Pendente' && this.item.fez_auto_avaliacao && this.item.principal && !this.item.pendente_avaliacao_par) ||
                (this.item.status === 'Pendente' && (!this.item.fez_auto_avaliacao && this.item.avaliador_id === this.item.funcionario_id)) ||
                this.item.status === 'Avaliada' ||
                (this.item.status === 'Avaliada' && this.item.fazer_avaliacao_final) ||
                (this.item.status === 'Finalizada' && !this.item.fazer_avaliacao_final)
            )
        },

        acoesDisponiveis() {
            const acoes = []

            // Ação Avaliar
            if (
                (this.item.status === 'Pendente' && this.item.fez_auto_avaliacao && !this.item.principal) ||
                (this.item.status === 'Pendente' && this.item.fez_auto_avaliacao && this.item.principal && !this.item.pendente_avaliacao_par) ||
                (this.item.status === 'Pendente' && (!this.item.fez_auto_avaliacao && this.item.avaliador_id === this.item.funcionario_id))
            ) {
                acoes.push({
                    tipo: 'avaliar',
                    texto: 'Avaliar',
                    icone: 'fa-edit'
                })
            }

            // Ação Visualizar Avaliação
            if (
                this.item.status === 'Avaliada' ||
                (this.item.status === 'Finalizada' && !this.item.fazer_avaliacao_final)
            ) {
                acoes.push({
                    tipo: 'visualizar',
                    texto: 'Visualizar Avaliação',
                    icone: 'fa-eye'
                })
            }

            // Ação Fazer Avaliação Final
            if (this.item.status === 'Avaliada' && this.item.fazer_avaliacao_final) {
                acoes.push({
                    tipo: 'avaliar-final',
                    texto: 'Fazer Avaliação Final',
                    icone: 'fa-check-circle'
                })
            }

            // Ação Visualizar Avaliação Final
            if (this.item.status === 'Finalizada' && !this.item.fazer_avaliacao_final && this.item.principal) {
                acoes.push({
                    tipo: 'visualizar-final',
                    texto: 'Visualizar Avaliação Final',
                    icone: 'fa-eye'
                })
            }

            // Ação Imprimir
            if (this.item.status === 'Finalizada' && !this.item.fazer_avaliacao_final && this.item.principal) {
                acoes.push({
                    tipo: 'imprimir',
                    texto: 'Imprimir Avaliação Final',
                    icone: 'fa-print',
                    url: `${this.urlImpressao}/${this.item.token}`
                })
            }

            return acoes
        }
    }
}
</script>

<style scoped>
.text-pink {
    color: pink !important;
}

.bg-pink {
    background: pink !important;
}
</style>

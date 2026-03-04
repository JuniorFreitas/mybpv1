<template>
    <modal id="janelaAvaliacaoFinal" :titulo="titulo_janela_final" :size="90">
        <template #conteudo>
            <preload v-show="preloadAvalFinal"></preload>
            <div v-if="!preloadAvalFinal">
                <!-- Dados do Funcionário -->
                <FuncionarioDados :dados="formAvaliarFinal.dados_do_funcionario" />

                <!-- Tabelas de Resultados -->
                <TabelaResultados :resultado-agrupado="formAvaliarFinal.result_topico_pai_agrupado" />

                <!-- Considerações dos Avaliadores -->
                <TabelaConsideracoes :resultado-agrupado="formAvaliarFinal.result_topico_pai_agrupado" />

                <!-- Charts de Radar -->
                <ChartsRadar
                    :charts="formAvaliarFinal.resultChart"
                    :nota-final="formAvaliarFinal.nota_final"
                    :resultado-topico-pai="formAvaliarFinal.resultado_topico_pai"
                />

                <!-- Planos de Ação -->
                <PlanosAcao
                    :planos="formAvaliarFinal.planos_acoes"
                    :result-topico="formAvaliarFinal.result_topico"
                    :visualizando="visualizando"
                    @adicionar="addPlanoAcao"
                    @remover="removerPlanoAcao"
                />
            </div>
        </template>
        <template #rodape>
            <button
                type="button"
                class="btn btn-sm btn-primary"
                v-show="editando && !visualizando && !preloadAvalFinal && temPlanosAcao"
                :disabled="salvando"
                @click="salvarAvaliacaoFinal"
            >
                <i class="fa fa-save"></i>
                {{ salvando ? 'Salvando...' : 'Salvar' }}
            </button>
        </template>
    </modal>
</template>

<script>
import modal from '../../../../Modal'
import FuncionarioDados from './FuncionarioDados.vue'
import TabelaResultados from './TabelaResultados.vue'
import TabelaConsideracoes from './TabelaConsideracoes.vue'
import ChartsRadar from './ChartsRadar.vue'
import PlanosAcao from './PlanosAcao.vue'
import validacoes from '../../../../../mixins/Validacoes'

export default {
    name: 'AvaliacaoFinalModal',
    components: {
        modal,
        FuncionarioDados,
        TabelaResultados,
        TabelaConsideracoes,
        ChartsRadar,
        PlanosAcao
    },
    mixins: [validacoes],
    data() {
        return {
            titulo_janela_final: 'Open Feedback - Avaliação Final',
            preloadAvalFinal: false,
            salvando: false,
            editando: false,
            visualizando: false,
            formAvaliarFinal: {
                dados_do_funcionario: {},
                avaliacao_feedback_id: null,
                avaliacao_feedback_id_avaliador: null,
                gestor_id: null,
                nota_final: 0,
                result_topico_pai_agrupado: [],
                result_topico: {},
                resultChart: [],
                resultado_topico_pai: {},
                planos_acoes: [],
                planos_acoes_delete: []
            },
            formAvaliarFinalDefault: null
        }
    },
    computed: {
        temPlanosAcao() {
            return this.formAvaliarFinal.planos_acoes?.length > 0
        }
    },
    created() {
        this.formAvaliarFinalDefault = _.cloneDeep(this.formAvaliarFinal)
    },
    methods: {
        async avaliarFinal(avaliacaoFeedback) {
            await this.abrirModal(avaliacaoFeedback, false)
        },

        async visualizarFinal(avaliacaoFeedback) {
            await this.abrirModal(avaliacaoFeedback, true)
        },

        async abrirModal(avaliacaoFeedback, visualizando = false) {
            this.visualizando = visualizando
            this.editando = true
            this.preloadAvalFinal = true
            this.salvando = false

            this.resetForm()

            try {
                const response = await axios.get(`${URL_ADMIN}/cadastro/avaliacoes/avaliar/${avaliacaoFeedback.id}/final`)

                this.carregarDados(response.data)

                this.$nextTick(() => {
                    $('#janelaAvaliacaoFinal').modal('show')
                    setupCampo()
                })
            } catch (error) {
                console.error('Erro ao carregar avaliação final:', error)
                toastr.error('Erro ao carregar avaliação final', 'Erro!')
            } finally {
                this.preloadAvalFinal = false
            }
        },

        carregarDados(data) {
            Object.assign(this.formAvaliarFinal, {
                ...data,
                result_topico_pai_agrupado: data.result_topico_pai_agrupado || [],
                resultChart: data.resultChart || [],
                resultado_topico_pai: data.resultado_topico_pai || {},
                planos_acoes: data.planos_acoes || [],
                result_topico: data.result_topico || {},
                planos_acoes_delete: []
            })
        },

        resetForm() {
            this.formAvaliarFinal = _.cloneDeep(this.formAvaliarFinalDefault)
            formReset()
        },

        addPlanoAcao() {
            if (!this.formAvaliarFinal.planos_acoes) {
                this.formAvaliarFinal.planos_acoes = []
            }

            const novoPlano = {
                nova: true,
                avaliacao_feedback_id: this.formAvaliarFinal.avaliacao_feedback_id || '',
                avaliacao_feedback_id_avaliador: this.formAvaliarFinal.avaliacao_feedback_id_avaliador || '',
                gestor_id: this.formAvaliarFinal.gestor_id || '',
                topico_id: '',
                responsavel: this.formAvaliarFinal.dados_do_funcionario?.nome || '',
                plano_de_acao: '',
                inicio: '',
                termino: '',
                status: '',
                dados_extras: {}
            }

            this.formAvaliarFinal.planos_acoes.push(novoPlano)
        },

        removerPlanoAcao(index) {
            if (!this.formAvaliarFinal.planos_acoes || index < 0 || index >= this.formAvaliarFinal.planos_acoes.length) {
                return
            }

            const plano = this.formAvaliarFinal.planos_acoes[index]

            if (plano.id) {
                if (!this.formAvaliarFinal.planos_acoes_delete) {
                    this.formAvaliarFinal.planos_acoes_delete = []
                }
                this.formAvaliarFinal.planos_acoes_delete.push(plano.id)
            }

            this.formAvaliarFinal.planos_acoes.splice(index, 1)
        },

        async salvarAvaliacaoFinal() {
            if (!this.validarFormulario()) {
                return
            }

            this.salvando = true

            try {
                await axios.put(`${URL_ADMIN}/cadastro/avaliacoes/avaliar/${this.formAvaliarFinal.avaliacao_feedback_id}/final`, this.formAvaliarFinal)

                $('#janelaAvaliacaoFinal').modal('hide')
                mostraSucesso('', 'Avaliação Final salva com sucesso')
                this.$emit('salvar')
            } catch (error) {
                console.error('Erro ao salvar avaliação final:', error)
                toastr.error('Erro ao salvar avaliação final', 'Erro!')
            } finally {
                this.salvando = false
            }
        },

        validarFormulario() {
            this.validaBlur()
            const countErro = document.querySelectorAll('.is-invalid').length

            if (countErro > 0) {
                toastr.error('Verifique os campos', 'Atenção!')
                return false
            }

            return true
        }
    }
}
</script>

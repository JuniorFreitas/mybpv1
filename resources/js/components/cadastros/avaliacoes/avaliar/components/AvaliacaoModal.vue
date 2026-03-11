<template>
    <modal id="janelaCadastrar" :titulo="titulo_janela" :fechar="!preload" :size="90" ref="modal_janelaCadastrar">
        <template #conteudo>
            <preload v-show="preload"></preload>
            <div v-if="!preload">
                <!-- Dados do Funcionário -->
                <FuncionarioDados :dados="formAvaliar.dados_do_funcionario" />

                <!-- Escala de Avaliação -->
                <EscalaAvaliacao />

                <!-- Tópicos de Avaliação -->
                <TopicosAvaliacao
                    :topicos="lista_topicos"
                    :respostas="formAvaliar.respostas"
                    :respostas-funcionario="formAvaliar.respostasFunc"
                    :principal="formAvaliar.principal"
                    :visualizando="visualizando"
                />

                <!-- Considerações -->
                <ConsideracoesAvaliacao
                    v-model="formAvaliar.comentario"
                    :comentario-funcionario="formAvaliar.comentario_funcionario"
                    :principal="formAvaliar.principal"
                    :visualizando="visualizando"
                />
            </div>
        </template>
        <template #rodape>
            <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="editando && !preload && !visualizando" :disabled="salvando" @click="salvar">
                <i class="fa fa-save"></i>
                {{ salvando ? 'Salvando...' : 'Salvar' }}
            </button>
        </template>
    </modal>
</template>

<script>
import modal from '../../../../Modal'
import FuncionarioDados from './FuncionarioDados.vue'
import EscalaAvaliacao from './EscalaAvaliacao.vue'
import TopicosAvaliacao from './TopicosAvalicao.vue'
import ConsideracoesAvaliacao from './ConsideracoesAvalicao.vue'
import validacoes from '../../../../../mixins/Validacoes'

export default {
    name: 'AvaliacaoModal',
    components: {
        modal,
        FuncionarioDados,
        EscalaAvaliacao,
        TopicosAvaliacao,
        ConsideracoesAvaliacao
    },
    mixins: [validacoes],
    data() {
        return {
            titulo_janela: 'Avaliação',
            preload: false,
            salvando: false,
            editando: false,
            visualizando: false,
            lista_topicos: [],
            formAvaliar: {
                respostas: [],
                respostasFunc: [],
                dados_do_funcionario: {},
                comentario: '',
                comentario_funcionario: '',
                avaliacao_feedback_id: null,
                origem_feedback: '',
                principal: false
            },
            formAvaliarDefault: null
        }
    },
    created() {
        this.formAvaliarDefault = _.cloneDeep(this.formAvaliar)
    },
    methods: {
        async avaliar(avaliacaoFeedback) {
            await this.abrirModal(avaliacaoFeedback, false)
        },

        async visualizar(avaliacaoFeedback) {
            await this.abrirModal(avaliacaoFeedback, true)
        },

        async abrirModal(avaliacaoFeedback, visualizando = false) {
            this.visualizando = visualizando
            this.editando = true
            this.titulo_janela = `Avaliação: ${avaliacaoFeedback.avaliacao.titulo}`
            this.preload = true
            this.salvando = false

            this.resetForm()

            try {
                const response = await axios.get(`${URL_ADMIN}/cadastro/avaliacoes/avaliar/${avaliacaoFeedback.id}/edit`)

                this.carregarDados(response.data)

                this.$nextTick(() => {
                    this.$refs.modal_janelaCadastrar && this.$refs.modal_janelaCadastrar.abrirModal()
                    setupCampo()
                })
            } catch (error) {
                console.error('Erro ao carregar dados da avaliação:', error)
                toastr.error('Erro ao carregar dados da avaliação', 'Erro!')
            } finally {
                this.preload = false
            }
        },

        carregarDados(data) {
            Object.assign(this.formAvaliar, {
                respostas: data.respostas || [],
                respostasFunc: data.respostas_funcionario || [],
                comentario: data.comentario || '',
                comentario_funcionario: data.comentario_funcionario || '',
                dados_do_funcionario: data.dados_do_funcionario || {},
                avaliacao_feedback_id: data.avaliacao_feedback_id,
                origem_feedback: data.origem_feedback || '',
                principal: data.principal || false
            })

            this.lista_topicos = data.topicos || []
        },

        resetForm() {
            this.formAvaliar = _.cloneDeep(this.formAvaliarDefault)
            this.lista_topicos = []
            formReset()
        },

        async salvar() {
            if (!this.validarFormulario()) {
                return
            }

            this.salvando = true

            try {
                await axios.put(`${URL_ADMIN}/cadastro/avaliacoes/avaliar/${this.formAvaliar.avaliacao_feedback_id}`, this.formAvaliar)

                this.$refs.modal_janelaCadastrar && this.$refs.modal_janelaCadastrar.fecharModal()
                mostraSucesso('', 'Avaliação enviada com sucesso')
                this.$emit('salvar')
            } catch (error) {
                console.error('Erro ao salvar avaliação:', error)
                toastr.error('Erro ao salvar avaliação', 'Erro!')
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

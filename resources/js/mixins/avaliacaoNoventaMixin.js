/**
 * Mixin para lógica compartilhada de Avaliação de Experiência
 * Reutilizável em qualquer parte do sistema
 */
export default {
    data() {
        return {
            preloadAvaliacao: false,
            preloadSalvarAvaliacao: false,
            perguntasAvaliacao: [],
            tabelaNoventaAvaliacao: [],
            avNoventaVencimentoData: null,
            itemAvaliacaoExperiencia: null,
            formAvaliacao: {
                gestor_imediato: '',
                feedback_id: '',
                observacao: '',
                perguntas: []
            },
            formDefaultAvaliacao: null,
            URL_ADMIN_AVALIACAO: typeof URL_ADMIN !== 'undefined' ? URL_ADMIN : '/g'
        }
    },

    methods: {
        /**
         * Inicializa o formulário padrão de avaliação
         */
        inicializarFormularioAvaliacao() {
            this.formDefaultAvaliacao = _.cloneDeep(this.formAvaliacao)
        },

        /**
         * Carrega dados da avaliação de experiência
         * @param {Number} feedbackId - ID do feedback/admissão
         * @returns {Promise}
         */
        carregarDadosAvaliacao(feedbackId) {
            this.preloadAvaliacao = true
            this.perguntasAvaliacao = []

            return axios
                .get(`${this.URL_ADMIN_AVALIACAO}/historico/${feedbackId}`)
                .then((res) => {
                    const data = res.data
                    this.perguntasAvaliacao = data.perguntas || []
                    this.tabelaNoventaAvaliacao = data.tabelaNoventa || []
                    this.avNoventaVencimentoData = data.avNoventaVencimento || null
                    this.itemAvaliacaoExperiencia = data.item_avaliacao_experiencia || null

                    this.formAvaliacao.perguntas = _.cloneDeep(this.perguntasAvaliacao)
                    this.formAvaliacao.gestor_imediato = ''
                    this.formAvaliacao.observacao = ''
                    this.formAvaliacao.feedback_id = feedbackId

                    return data
                })
                .catch((error) => {
                    console.error('Erro ao carregar dados da avaliação:', error)
                    if (typeof mostraErro === 'function') {
                        mostraErro('', 'Erro ao carregar dados da avaliação.')
                    }
                    throw error
                })
                .finally(() => {
                    setTimeout(() => {
                        this.preloadAvaliacao = false
                    }, 500)
                })
        },

        /**
         * Prepara formulário para adicionar nova avaliação
         * @param {Number} feedbackId - ID do feedback
         */
        prepararNovaAvaliacao(feedbackId) {
            this.formAvaliacao = _.cloneDeep(this.formDefaultAvaliacao)
            this.formAvaliacao.perguntas = _.cloneDeep(this.perguntasAvaliacao)
            this.formAvaliacao.gestor_imediato = ''
            this.formAvaliacao.observacao = ''
            this.formAvaliacao.feedback_id = feedbackId
            this.preloadSalvarAvaliacao = false

            // Reset de validações se as funções existirem
            if (typeof formReset === 'function') formReset()
            if (typeof setupCampo === 'function') setupCampo()
        },

        /**
         * Salva nova avaliação de experiência
         * @param {String} modalId - ID do modal para fechar após salvar
         * @returns {Promise}
         */
        salvarAvaliacao(modalId = 'janelaFormulario') {
            // Reset e validação de formulário
            if (typeof formReset === 'function') formReset()

            $(`#${modalId} :input:visible`).trigger('blur')

            if ($(`#${modalId} :input:visible.is-invalid`).length) {
                if (typeof mostraErro === 'function') {
                    mostraErro('', 'Verifique os erros no formulário.')
                }
                return Promise.reject('Validação falhou')
            }

            this.preloadSalvarAvaliacao = true

            return axios
                .post(`${this.URL_ADMIN_AVALIACAO}/historico/formulario-noventa-dias/${this.formAvaliacao.feedback_id}`, this.formAvaliacao)
                .then((response) => {
                    if (response.status === 201) {
                        if (typeof mostraSucesso === 'function') {
                            mostraSucesso('Avaliação de Experiência criada com sucesso.')
                        }
                        $(`#${modalId}`).modal('hide')
                        return this.carregarDadosAvaliacao(this.formAvaliacao.feedback_id)
                    }
                    return response
                })
                .catch((error) => {
                    console.error('Erro ao salvar avaliação:', error)
                    if (typeof mostraErro === 'function') {
                        mostraErro('', 'Erro ao salvar avaliação. Tente novamente.')
                    }
                    throw error
                })
                .finally(() => {
                    this.preloadSalvarAvaliacao = false
                })
        },

        /**
         * Gera PDF da avaliação
         * @param {Object} item - Item da avaliação com quantidade_avaliacao e feedback_id
         */
        gerarPdfAvaliacao(item) {
            const link = `${this.URL_ADMIN_AVALIACAO}/historico/formulario-noventa-dias/${item.quantidade_avaliacao}/${item.feedback_id}/pdf`
            window.open(link, '_blank')
        },

        /**
         * Verifica se pode adicionar nova avaliação (máximo 2)
         * @returns {Boolean}
         */
        podeAdicionarAvaliacao() {
            return this.tabelaNoventaAvaliacao.length < 2
        },

        /**
         * Retorna mensagem de status da avaliação
         * @returns {String}
         */
        getStatusAvaliacao() {
            const quantidade = this.tabelaNoventaAvaliacao.length
            if (quantidade === 0) return 'Nenhuma avaliação realizada'
            if (quantidade === 1) return '1ª avaliação realizada'
            if (quantidade === 2) return '2ª avaliação realizada (completo)'
            return 'Limite de avaliações atingido'
        }
    }
}

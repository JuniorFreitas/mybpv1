export default {
    methods: {
        /**
         * Formata valores decimais para exibição
         * @param {number|string|null|undefined} valor
         * @returns {string}
         */
        formatarDecimal(valor) {
            if (valor === null || valor === undefined || isNaN(valor)) {
                return '0.0'
            }
            return Number(valor).toFixed(1)
        },

        /**
         * Obtém a média formatada de um chart específico
         * @param {string} chartName
         * @param {Object} resultadoTopicoPai
         * @returns {string}
         */
        getMediaFormatada(chartName, resultadoTopicoPai = {}) {
            if (resultadoTopicoPai[chartName]?.media !== undefined) {
                return this.formatarDecimal(resultadoTopicoPai[chartName].media)
            }
            return '0.0'
        },

        /**
         * Obtém a média formatada de um tópico específico
         * @param {string|number} topicoId
         * @param {Object} resultTopico
         * @returns {string}
         */
        getMediaTopico(topicoId, resultTopico = {}) {
            if (resultTopico[topicoId]?.media !== undefined) {
                return this.formatarDecimal(resultTopico[topicoId].media)
            }
            return '0.0'
        },

        /**
         * Verifica se um array ou objeto possui dados válidos
         * @param {Array|Object} data
         * @returns {boolean}
         */
        hasValidData(data) {
            if (Array.isArray(data)) {
                return data.length > 0
            }
            if (typeof data === 'object' && data !== null) {
                return Object.keys(data).length > 0
            }
            return false
        },

        /**
         * Clona profundamente um objeto usando lodash
         * @param {any} obj
         * @returns {any}
         */
        deepClone(obj) {
            return _.cloneDeep(obj)
        },

        /**
         * Exibe mensagem de sucesso
         * @param {string} titulo
         * @param {string} mensagem
         */
        showSuccess(titulo = '', mensagem = 'Operação realizada com sucesso') {
            if (typeof mostraSucesso === 'function') {
                mostraSucesso(titulo, mensagem)
            } else {
                toastr.success(mensagem, titulo)
            }
        },

        /**
         * Exibe mensagem de erro
         * @param {string} titulo
         * @param {string} mensagem
         */
        showError(titulo = 'Erro!', mensagem = 'Ocorreu um erro na operação') {
            toastr.error(mensagem, titulo)
        },

        /**
         * Limpa formulários e validações
         */
        resetForm() {
            if (typeof formReset === 'function') {
                formReset()
            }
        },

        /**
         * Configura campos após carregamento
         */
        setupFields() {
            this.$nextTick(() => {
                if (typeof setupCampo === 'function') {
                    setupCampo()
                }
            })
        },

        /**
         * Valida campos obrigatórios
         * @returns {boolean}
         */
        validateRequiredFields() {
            if (typeof this.validaBlur === 'function') {
                this.validaBlur()
            }

            const errorCount = document.querySelectorAll('.is-invalid').length

            if (errorCount > 0) {
                this.showError('Atenção!', 'Verifique os campos obrigatórios')
                return false
            }

            return true
        },

        /**
         * Trata erros de requisições HTTP
         * @param {Error} error
         * @param {string} contexto
         */
        handleApiError(error, contexto = 'operação') {
            console.error(`Erro na ${contexto}:`, error)

            let mensagem = `Erro ao executar ${contexto}`

            if (error.response?.data?.message) {
                mensagem = error.response.data.message
            } else if (error.message) {
                mensagem = error.message
            }

            this.showError('Erro!', mensagem)
        },

        /**
         * Executa uma função de forma segura com tratamento de erro
         * @param {Function} fn
         * @param {string} contexto
         * @returns {Promise<any>}
         */
        async safeExecute(fn, contexto = 'operação') {
            try {
                return await fn()
            } catch (error) {
                this.handleApiError(error, contexto)
                throw error
            }
        },

        /**
         * Verifica se um item está em um estado específico
         * @param {Object} item
         * @param {string} status
         * @returns {boolean}
         */
        isItemInStatus(item, status) {
            return item?.status === status
        },

        /**
         * Verifica se um item pode ser avaliado
         * @param {Object} item
         * @returns {boolean}
         */
        canEvaluate(item) {
            return item?.status === 'Pendente' &&
                (item.fez_auto_avaliacao || item.avaliador_id === item.funcionario_id)
        },

        /**
         * Verifica se um item pode ter avaliação final
         * @param {Object} item
         * @returns {boolean}
         */
        canFinalEvaluate(item) {
            return item?.status === 'Avaliada' && item.fazer_avaliacao_final
        },

        /**
         * Abre modal de forma consistente
         * @param {string} modalId
         */
        openModal(modalId) {
            this.$nextTick(() => {
                $(`#${modalId}`).modal('show')
            })
        },

        /**
         * Fecha modal de forma consistente
         * @param {string} modalId
         */
        closeModal(modalId) {
            $(`#${modalId}`).modal('hide')
        }
    },

    /**
     * Filtros globais para formatação
     */
    filters: {
        casasDecimais(valor, casas = 1) {
            if (valor === null || valor === undefined || isNaN(valor)) {
                return '0.' + '0'.repeat(casas)
            }
            return Number(valor).toFixed(casas)
        }
    }
}

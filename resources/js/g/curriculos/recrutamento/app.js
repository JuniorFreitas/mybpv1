import endereco from '../../../components/Endereco'
import telefone from '../../../components/Telefones'
import datepicker from '../../../components/DatePicker'
import ExportacaoMixin from '../../../mixins/Exportacoes'

// ===== CONSTANTES E CONFIGURAÇÕES =====
const SELECOES = {
    NAO_SELECIONADO: 'nao',
    EMPTY: ''
}

const DELAYS = {
    BUSCA: 600,
    VALIDACAO: 100
}

const ESTADOS = {
    VISUALIZANDO: 'Visualizando Curriculo',
    EDITANDO: 'Editando Curriculo'
}

const MENSAGENS = {
    ERRO_VAGA_VAZIA: 'O Campo Vaga não pode ficar vazio',
    ERRO_TELEFONE_PRINCIPAL: 'Nenhum telefone foi marcado como principal',
    ERRO_CAMPOS_INVALIDOS: 'Verifique os campos',
    SUCESSO_FEEDBACK: 'Feedback realizado com sucesso!'
}

const app = new Vue({
    el: '#app',
    components: {
        endereco,
        datepicker,
        telefone
    },
    mixins: [ExportacaoMixin],

    data() {
        return {
            // ===== ESTADOS DA APLICAÇÃO =====
            tituloJanela: ESTADOS.VISUALIZANDO,
            preloadAjax: false,
            editando: false,
            apagado: false,
            feedback: false,
            cadastrado: false,
            atualizado: false,

            // ===== CONFIGURAÇÕES =====
            permite_envio_whatsapp: null,
            preloadExportacao: false,
            hash: `mastertag_${this.generateRandomId()}`,
            empresa: 0,
            urlExportacao: `${URL_ADMIN}/curriculos/recrutamentos/export`,

            // ===== LISTAS PARA SELECTS =====
            lista_sexos: [],
            lista_estados_civis: [],
            lista: [],
            ufs: [],
            vagas: [],

            // ===== FORMULÁRIOS =====
            form: this.createInitialForm(),
            formDefault: null,
            form_feedback: this.createInitialFeedback(),
            form_feedbackDefault: null,

            // ===== CAMPOS DE REFERÊNCIA =====
            campoNome: null,

            // ===== CONTROLES DE BUSCA E PAGINAÇÃO =====
            controle: {
                carregando: false,
                dados: {
                    caminho_autocomplete: 'autocomplete/todas-vagas-abertas-ativas',
                    caminho_cliente_autocomplete: 'autocomplete/todos-clientes-ativos',
                    autocomplete_label_anterior: '',
                    autocomplete_label: '',
                    pages: 20,
                    campoBusca: '',
                    campoVaga: '',
                    campoLido: '',
                    campoFiltro: '',
                    campoUf: '',
                    campoPcd: '',
                    campoCPF: '',
                    filtroPeriodo: false,
                    periodo: ''
                }
            }
        }
    },

    mounted() {
        this.inicializar()
    },

    methods: {
        // ===== MÉTODOS DE INICIALIZAÇÃO =====
        inicializar() {
            this.inicializarFormularios()
            this.carregarDadosIniciais()
        },

        inicializarFormularios() {
            this.formDefault = _.cloneDeep(this.form)
            this.form_feedbackDefault = _.cloneDeep(this.form_feedback)
        },

        async carregarDadosIniciais() {
            await Promise.all([this.atualizar(), this.listaVagas()])
        },

        createInitialForm() {
            return {
                id: '',
                bairro: '',
                cep: '',
                cnh: '',
                complemento: '',
                cpf: '',
                sexo: '',
                estado_civil: '',
                created_at: '',
                datalido: '',
                email: '',
                experiencias: [],
                feed_back: '',
                formacao: {},
                formacao_curso: '',
                formacao_instituicao: '',
                formacao_status: '',
                lido: '',
                logradouro: '',
                municipio: '',
                nascimento: '',
                nome: '',
                qualificacoes: [],
                telefones: [],
                telefonesDelete: [],
                uf: '',
                usuario: '',
                usuario_lido: '',
                vaga: {},
                vaga_pretendida: ''
            }
        },

        createInitialFeedback() {
            return {
                selecionado: '',
                autocomplete_label_vaga_modal: '',
                autocomplete_label_vaga_modal_anterior: '',
                vaga_id: '',
                vagas_abertas_id: '',
                contato_realizado: '',
                interesse: '',
                data_entrevista: '',
                local_entrevista: '',
                obs: '',
                autocomplete_label_cliente_modal: '',
                autocomplete_label_cliente_modal_anterior: '',
                cliente_id: '',
                telefone_id: '',
                envia_mail_desclassificacao: '',
                tem_provas: false,
                envia_mail_provas: '',
                envia_mail_proxima_etapa: '',
                envia_whatsapp: ''
            }
        },

        generateRandomId() {
            return parseInt(Math.random() * 999999)
        },

        // ===== MÉTODOS DE SELEÇÃO DE VAGAS =====
        selecionaVaga(obj) {
            if (!this.validarObjetoVaga(obj)) {
                return
            }

            this.atualizarDadosVaga(obj)
            this.controle.carregando = true
            this.executarBuscaComDelay()
        },

        validarObjetoVaga(obj) {
            return obj && obj.id && obj.label
        },

        atualizarDadosVaga(obj) {
            this.controle.dados.campoVaga = obj.id
            this.controle.dados.autocomplete_label = obj.label
            this.controle.dados.autocomplete_label_anterior = obj.label
        },

        executarBuscaComDelay() {
            setTimeout(() => {
                this.executarBusca()
            }, DELAYS.BUSCA)
        },

        executarBusca() {
            if (this.$refs.componente) {
                this.$refs.componente.buscar()
            }
        },

        resetaCampo() {
            if (this.campoFoiAlterado()) {
                this.limparCamposVaga()
                this.executarBusca()
            }
        },

        campoFoiAlterado() {
            return this.controle.dados.autocomplete_label_anterior !== this.controle.dados.autocomplete_label
        },

        limparCamposVaga() {
            this.controle.dados.autocomplete_label_anterior = ''
            this.controle.dados.autocomplete_label = ''
            this.controle.dados.campoVaga = ''
        },

        // ===== MÉTODOS DE MODAL DE VAGA =====
        selecionaVagaModal(obj) {
            if (!this.validarObjetoVaga(obj)) {
                return
            }
            this.atualizarDadosVagaModal(obj)
        },

        atualizarDadosVagaModal(obj) {
            this.form_feedback.vagas_abertas_id = obj.id
            this.form_feedback.vaga_id = obj.vaga_id
            this.form_feedback.autocomplete_label_vaga_modal = obj.label
            this.form_feedback.autocomplete_label_vaga_modal_anterior = obj.label
            this.form_feedback.tem_provas = obj.simulado_vaga?.length > 0 || false
        },

        resetaCampoVagaModal() {
            if (this.campoVagaModalFoiAlterado()) {
                this.limparCamposVagaModal()
                this.validarCampoVagaModal()
            }
        },

        campoVagaModalFoiAlterado() {
            return this.form_feedback.autocomplete_label_vaga_modal_anterior !== this.form_feedback.autocomplete_label_vaga_modal
        },

        limparCamposVagaModal() {
            this.form_feedback.autocomplete_label_vaga_modal_anterior = ''
            this.form_feedback.autocomplete_label_vaga_modal = ''
            this.form_feedback.vaga_id = ''
        },

        validarCampoVagaModal() {
            setTimeout(() => {
                if (this.form_feedback.vaga_id === SELECOES.EMPTY) {
                    this.mostrarErroVagaVazia()
                }
            }, DELAYS.VALIDACAO)
        },

        mostrarErroVagaVazia() {
            const campoId = `#vaga_modal_${this.hash}`
            valida_campo_vazio($(campoId), 1)
            $(`#janelaCadastrar ${campoId}`).focus().trigger('blur')
            mostraErro('Erro', MENSAGENS.ERRO_VAGA_VAZIA)
        },

        // ===== MÉTODOS DE FORMULÁRIO =====
        async formAlterar(id) {
            if (!this.validarId(id)) {
                return
            }

            this.resetarEstados()
            this.form.id = id
            this.preloadAjax = true

            try {
                const response = await this.carregarDadosFormulario(id)
                this.processarDadosFormulario(response.data)
            } catch (error) {
                this.tratarErroCarregamento(error)
            }
        },

        validarId(id) {
            return id && id !== ''
        },

        async carregarDadosFormulario(id) {
            return await axios.get(`${URL_ADMIN}/curriculos/recrutamentos/${id}/editar`)
        },

        tratarErroCarregamento(error) {
            console.error('Erro ao carregar dados:', error)
            this.preloadAjax = false
            this.mostrarErroGenerico('Erro ao carregar dados do formulário')
        },

        mostrarErroGenerico(mensagem) {
            mostraErro('Erro', mensagem)
        },

        resetarEstados() {
            this.feedback = false
            this.cadastrado = false
            this.atualizado = false
            this.editando = false
            Object.assign(this.form_feedback, this.form_feedbackDefault)
            this.form.telefonesDelete = []
            formReset()
        },

        processarDadosFormulario(data) {
            this.marcaLido(data)
            if (this.$refs.componente) {
                this.$refs.componente.buscar()
            }

            Object.assign(this.form, data)

            if (data.feed_back) {
                this.processarFeedback(data.feed_back)
            }

            this.finalizarCarregamentoFormulario(data)
        },

        processarFeedback(feedbackData) {
            Object.assign(this.form_feedback, feedbackData)
            this.feedback = true

            if (feedbackData.vaga_aberta?.vaga_selecionada) {
                this.configurarVagaSelecionada(feedbackData.vaga_aberta)
            } else {
                this.form_feedback.vaga_id = ''
                this.form_feedback.autocomplete_label_vaga_modal = ''
            }

            if (!this.form_feedback.contato_realizado) {
                this.form_feedback.envia_whatsapp = ''
            }

            if (feedbackData.cliente) {
                this.configurarClienteSelecionado(feedbackData.cliente)
            }
        },

        configurarVagaSelecionada(vagaAberta) {
            const vaga = vagaAberta.vaga_selecionada
            const municipio = vagaAberta.municipio

            this.form_feedback.autocomplete_label_vaga_modal = `${vaga.nome} - ${municipio.nome} - ${municipio.uf}`
            this.form_feedback.tem_provas = vaga.simulado_vaga?.length > 0 || false
        },

        configurarClienteSelecionado(cliente) {
            this.form_feedback.autocomplete_label_cliente_modal = cliente.tipo === 'Pessoa Jurídica' ? cliente.nome_fantasia : cliente.nome
        },

        finalizarCarregamentoFormulario(data) {
            this.tituloJanela = `Visualizando Curriculo - ${data.nome}`
            this.editando = true
            this.preloadAjax = false
            setupCampo()
        },

        async marcaLido(dados) {
            try {
                await axios.put(`${URL_ADMIN}/curriculos/recrutamentos/${dados.id}/lido`, dados)
            } catch (error) {
                console.error('Erro ao marcar como lido:', error)
            }
        },

        // ===== MÉTODOS DE VALIDAÇÃO E ALTERAÇÃO =====
        async alterar() {
            if (!this.validarFormulario()) {
                return false
            }

            this.prepararDadosAlteracao()
            this.preloadAjax = true

            try {
                const response = await this.enviarAlteracao()
                this.processarRespostaAlteracao(response)
            } catch (error) {
                this.tratarErroAlteracao(error)
            }
        },

        prepararDadosAlteracao() {
            this.form_feedback.curriculos = this.form
        },

        async enviarAlteracao() {
            return await axios.put(`${URL_ADMIN}/curriculos/recrutamentos/${this.form.id}`, this.form_feedback)
        },

        processarRespostaAlteracao(response) {
            if (response.status === 201) {
                this.processarSucessoAlteracao()
            }
        },

        tratarErroAlteracao(error) {
            this.preloadAjax = false
            console.error('Erro ao alterar:', error)
            this.mostrarErroGenerico('Erro ao processar alteração')
        },

        validarFormulario() {
            const validacoes = [() => this.validarTelefonePrincipal(), () => this.validarVagaSelecionada(), () => this.validarCamposObrigatorios()]

            return validacoes.every((validacao) => validacao())
        },

        validarTelefonePrincipal() {
            const telefonePrincipal = _.findIndex(this.form.telefones, { principal: true })

            if (telefonePrincipal <= -1) {
                mostraErro('', MENSAGENS.ERRO_TELEFONE_PRINCIPAL)
                return false
            }

            return true
        },

        validarVagaSelecionada() {
            const feedbackSelecionado = this.isFeedbackSelecionado()

            if (feedbackSelecionado && this.form_feedback.vaga_id === SELECOES.EMPTY) {
                this.mostrarErroVagaVazia()
                return false
            }

            return true
        },

        isFeedbackSelecionado() {
            return this.form_feedback.selecionado !== SELECOES.EMPTY && this.form_feedback.selecionado !== SELECOES.NAO_SELECIONADO
        },

        validarCamposObrigatorios() {
            $('#janelaCadastrar :input:visible').trigger('blur')

            if ($('#janelaCadastrar :input:visible.is-invalid').length) {
                mostraErro('', MENSAGENS.ERRO_CAMPOS_INVALIDOS)
                return false
            }

            return true
        },

        processarSucessoAlteracao() {
            this.preloadAjax = false
            this.atualizado = true
            this.atualizarLista()
            this.fecharModal()
            this.mostrarSucesso()
        },

        atualizarLista() {
            if (this.$refs.componente) {
                this.$refs.componente.buscar()
            }
        },

        fecharModal() {
            $('#janelaCadastrar').modal('hide')
        },

        mostrarSucesso() {
            mostraSucesso('', MENSAGENS.SUCESSO_FEEDBACK)
        },

        // ===== MÉTODOS DE EXCLUSÃO =====
        async apagar() {
            if (!this.validarId(this.form.id)) {
                return
            }

            this.limparErros()
            this.preloadAjax = true

            try {
                await this.enviarExclusao()
                this.processarSucessoExclusao()
            } catch (error) {
                this.tratarErroExclusao(error)
            }
        },

        limparErros() {
            this.erros = []
        },

        async enviarExclusao() {
            return await axios.delete(`${URL_ADMIN}/curriculos/recrutamentos/${this.form.id}`, this.form)
        },

        processarSucessoExclusao() {
            this.preloadAjax = false
            this.apagado = true
            this.atualizarLista()
        },

        tratarErroExclusao(error) {
            this.preloadAjax = false
            console.error('Erro ao apagar:', error)
            this.mostrarErroGenerico('Erro ao excluir registro')
        },

        janelaConfirmar(id) {
            if (this.validarId(id)) {
                this.form.id = id
                this.apagado = false
                this.preloadAjax = false
            }
        },

        // ===== MÉTODOS DE DADOS =====
        async listaVagas() {
            this.preloadAjax = true

            try {
                const response = await this.carregarVagas()
                this.processarVagas(response.data)
            } catch (error) {
                this.tratarErroCarregamentoVagas(error)
            } finally {
                this.preloadAjax = false
            }
        },

        async carregarVagas() {
            return await axios.get(`${URL_PUBLICO}/lista-vagas`)
        },

        processarVagas(data) {
            this.vagas = data.vagas
        },

        tratarErroCarregamentoVagas(error) {
            console.error('Erro ao carregar vagas:', error)
            this.mostrarErroGenerico('Erro ao carregar lista de vagas')
        },

        carregou(dados) {
            this.processarDadosCarregados(dados)
            this.controle.carregando = false
        },

        processarDadosCarregados(dados) {
            this.lista = dados.items
            this.lista_sexos = dados.lista_sexos
            this.lista_estados_civis = dados.lista_estados_civis
            this.permite_envio_whatsapp = dados.permite_envio_whatsapp
        },

        carregando() {
            this.controle.carregando = true
        },

        atualizar() {
            if (this.$refs.componente) {
                this.resetarPaginacao()
                this.executarBusca()
            }
        },

        resetarPaginacao() {
            this.$refs.componente.atual = 1
        }
    }
})

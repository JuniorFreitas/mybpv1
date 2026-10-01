import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import endereco from '../../../components/Endereco'
import telefone from '../../../components/Telefones'
import datepicker from '../../../components/DatePicker'
import DateRangeFilter from '../../../components/DateRangeFilter.vue'
import ComboboxAutoComplete from '../../../components/ComboboxAutoComplete.vue'
import FiltroListagem from '../../../components/ui/FiltroListagem.vue'
import MybpBoolCombobox from '../../../components/ui/MybpBoolCombobox.vue'
import MybpCardCampo from '../../../components/ui/MybpCardCampo.vue'
import MybpStatusBadge from '../../../components/ui/MybpStatusBadge.vue'
import ComboboxValidation from '../../../mixins/ComboboxValidation'
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

const app = createApp({
    components: {
        endereco,
        datepicker,
        telefone,
        DateRangeFilter,
        ComboboxAutoComplete,
        FiltroListagem,
        MybpBoolCombobox,
        MybpCardCampo,
        MybpStatusBadge
    },
    mixins: [ExportacaoMixin, ComboboxValidation],

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
            dropdownAbertoId: null,

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
            previewWhatsappAberto: false,
            previewWhatsappTipo: 'recrutamento_selecao',
            previewWhatsappContexto: {},

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
                    dataInicio: '',
                    dataFim: '',
                    periodo: ''
                }
            }
        }
    },

    mounted() {
        this.inicializar()
    },

    computed: {
        telefonePrincipal() {
            return _.find(this.form.telefones || [], { principal: true }) || null
        },
        telefonePrincipalNumero() {
            return this.telefonePrincipal ? this.telefonePrincipal.numero : ''
        },
        totalFiltrosAtivos() {
            const d = this.controle.dados
            let total = [d.campoBusca, d.campoCPF, d.campoVaga, d.campoUf, d.campoLido, d.campoPcd].filter(
                (v) => v !== '' && v !== null && v !== undefined
            ).length
            if (d.filtroPeriodo && d.dataInicio && d.dataFim) total++
            if (Number(d.pages) !== 20) total++
            return total
        },
        campoBuscaUnificada() {
            const d = this.controle.dados
            if (d.campoCPF) return d.campoCPF
            return d.campoBusca || ''
        },
        buscaUnificadaEhCpf() {
            return !!this.controle.dados.campoCPF
        },
        campoPagesCombo: {
            get() {
                return this.controle.dados.pages === '' || this.controle.dados.pages == null
                    ? 20
                    : Number(this.controle.dados.pages)
            },
            set(v) {
                this.controle.dados.pages = v === '' || v == null ? 20 : Number(v)
            }
        },
        filtroUfOpcoes() {
            const ufs = [
                'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA',
                'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN',
                'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'
            ]
            return [{ value: '', label: 'Todos os estados' }, ...ufs.map((uf) => ({ value: uf, label: uf }))]
        },
        filtroSimNaoOpcoes() {
            return [
                { value: '', label: 'Geral' },
                { value: 'true', label: 'Sim' },
                { value: 'false', label: 'Não' }
            ]
        },
        filtroPagesOpcoes() {
            return [20, 50, 100].map((n) => ({ value: n, label: String(n) }))
        },
        opcoesSexoModal() {
            return (this.lista_sexos || []).map((item) => ({ value: item, label: item }))
        },
        opcoesEstadoCivilModal() {
            return (this.lista_estados_civis || []).map((item) => ({ value: item, label: item }))
        },
        opcoesSelecionadoModal() {
            return [
                { value: 'sim', label: 'Sim' },
                { value: 'nao', label: 'Não' },
                { value: 'standby', label: 'Stand by' }
            ]
        },
        opcoesSimNaoModal() {
            return [
                { value: 'sim', label: 'Sim' },
                { value: 'nao', label: 'Não' }
            ]
        },
        viajarCombo: {
            get() {
                if (this.form.viajar === true || this.form.viajar === 'true' || this.form.viajar === 1) return 'sim'
                if (this.form.viajar === false || this.form.viajar === 'false' || this.form.viajar === 0) return 'nao'
                return ''
            },
            set() {
                /* somente leitura */
            }
        }
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
                this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
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
                this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
            }

            Object.assign(this.form, data)
            this.normalizarRelacoesFormulario()

            if (data.feed_back) {
                this.processarFeedback(data.feed_back)
            }

            this.finalizarCarregamentoFormulario(data)
        },

        normalizarRelacoesFormulario() {
            if (!this.form.formacao) {
                this.form.formacao = {}
            }
            if (!this.form.experiencias) {
                this.form.experiencias = []
            }
            if (!this.form.qualificacoes) {
                this.form.qualificacoes = []
            }
            if (!this.form.telefones) {
                this.form.telefones = []
            }
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
            this.$nextTick(() => this.$refs.janelaCadastrar?.abrirModal())
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
            if (
                !this.exigirCombobox(this.form_feedback.selecionado, 'rec-modal-selecionado', {
                    toastMsg: 'Selecione se o candidato foi selecionado'
                })
            ) {
                return false
            }

            if (this.form_feedback.selecionado === 'nao') {
                if (
                    this.form_feedback.envia_mail_desclassificacao !== true &&
                    this.form_feedback.envia_mail_desclassificacao !== false
                ) {
                    if (
                        !this.exigirCombobox(this.form_feedback.envia_mail_desclassificacao, 'rec-modal-mail-desclass', {
                            toastMsg: 'Informe se envia e-mail de desclassificação'
                        })
                    ) {
                        return false
                    }
                }
            }

            if (this.form_feedback.selecionado && this.form_feedback.selecionado !== 'nao') {
                if (this.form_feedback.contato_realizado !== true && this.form_feedback.contato_realizado !== false) {
                    if (
                        !this.exigirCombobox(this.form_feedback.contato_realizado, 'rec-modal-contato', {
                            toastMsg: 'Informe se o contato foi realizado'
                        })
                    ) {
                        return false
                    }
                }
            }

            $('#janelaCadastrar :input:visible:enabled').trigger('blur')

            if ($('#janelaCadastrar :input:visible:enabled.is-invalid').length) {
                mostraErro('', MENSAGENS.ERRO_CAMPOS_INVALIDOS)
                return false
            }

            return this.validarInputsAtivosVisiveis('janelaCadastrar', { preservarIds: [] })
        },

        fecharOutrosComboboxesModal() {
            /* Combobox do modal sem refs; opening só sinaliza — filtros usam fecharOutrosComboboxes */
        },

        onSelectFeedbackCombo(inputId) {
            this.limparComboboxInvalido(inputId)
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
                this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
            }
        },

        fecharModal() {
            this.$refs.janelaCadastrar?.fecharModal()
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
            this.sincronizarPeriodoBr()
            if (this.$refs.componente) {
                this.resetarPaginacao()
                this.executarBusca()
            }
        },

        sincronizarPeriodoBr() {
            const d = this.controle.dados
            if (d.filtroPeriodo && d.dataInicio && d.dataFim) {
                d.periodo = `${this.isoParaBr(d.dataInicio)} até ${this.isoParaBr(d.dataFim)}`
            } else {
                d.periodo = ''
            }
        },
        isoParaBr(iso) {
            if (!iso || !/^\d{4}-\d{2}-\d{2}$/.test(iso)) return ''
            const [y, m, day] = iso.split('-')
            return `${day}/${m}/${y}`
        },
        formatarCpfDigitos(valor) {
            const d = String(valor || '').replace(/\D/g, '').slice(0, 11)
            if (d.length <= 3) return d
            if (d.length <= 6) return `${d.slice(0, 3)}.${d.slice(3)}`
            if (d.length <= 9) return `${d.slice(0, 3)}.${d.slice(3, 6)}.${d.slice(6)}`
            return `${d.slice(0, 3)}.${d.slice(3, 6)}.${d.slice(6, 9)}-${d.slice(9)}`
        },
        parecePadraoCpf(valor) {
            const raw = String(valor || '').trim()
            if (!raw) return false
            if (!/^[\d.\-\s]+$/.test(raw)) return false
            const digitos = raw.replace(/\D/g, '')
            return digitos.length > 0 && digitos.length <= 11
        },
        onInputBuscaUnificada(event) {
            const valor = event && event.target ? event.target.value : ''
            const d = this.controle.dados
            if (!valor) {
                d.campoBusca = ''
                d.campoCPF = ''
                return
            }
            if (this.parecePadraoCpf(valor)) {
                const mascarado = this.formatarCpfDigitos(valor)
                d.campoCPF = mascarado
                d.campoBusca = ''
                if (event.target && event.target.value !== mascarado) {
                    event.target.value = mascarado
                }
                return
            }
            d.campoBusca = valor
            d.campoCPF = ''
        },
        onPeriodoChange() {
            this.atualizar()
        },
        onSelectFiltro() {
            this.atualizar()
        },
        fecharOutrosComboboxes(excetoId) {
            const mapa = {
                'rec-filtro-uf': 'comboFiltroUf',
                'rec-filtro-lido': 'comboFiltroLido',
                'rec-filtro-pcd': 'comboFiltroPcd',
                'rec-filtro-pages': 'comboFiltroPages'
            }
            Object.keys(mapa).forEach((id) => {
                if (id === excetoId) return
                const ref = this.$refs[mapa[id]]
                if (ref && typeof ref.fechar === 'function') ref.fechar()
                else if (ref && typeof ref.close === 'function') ref.close()
            })
        },
        limparFiltros() {
            const pages = this.controle.dados.pages || 20
            this.controle.dados = {
                ...this.controle.dados,
                autocomplete_label_anterior: '',
                autocomplete_label: '',
                campoBusca: '',
                campoCPF: '',
                campoVaga: '',
                campoUf: '',
                campoLido: '',
                campoPcd: '',
                campoFiltro: '',
                filtroPeriodo: false,
                dataInicio: '',
                dataFim: '',
                periodo: '',
                pages
            }
            this.atualizar()
        },

        chaveStatusRec(item) {
            const sel = item && item.feed_back ? item.feed_back.selecionado : ''
            if (sel === 'sim') return 'aprovado'
            if (sel === 'nao') return 'reprovado'
            if (sel === 'standby') return 'gestor'
            if (item && item.lido) return 'neutro'
            return 'pendente'
        },
        textoStatusRec(item) {
            const sel = item && item.feed_back ? item.feed_back.selecionado : ''
            if (sel === 'sim') return 'Selecionado'
            if (sel === 'nao') return 'Não selecionado'
            if (sel === 'standby') return 'Stand by'
            if (item && item.lido) return 'Lido'
            return 'Não lido'
        },
        textoSelecionadoRec(item) {
            const sel = item && item.feed_back ? item.feed_back.selecionado : ''
            if (!sel) return ''
            if (sel === 'sim') return 'Sim'
            if (sel === 'nao') return 'Não'
            if (sel === 'standby') return 'Stand by'
            return String(sel)
        },
        textoSimNaoFeed(item, key) {
            if (!item || !item.feed_back) return 'Não'
            return item.feed_back[key] ? 'Sim' : 'Não'
        },
        tomSimNaoFeed(item, key) {
            if (!item || !item.feed_back) return 'negativo'
            return item.feed_back[key] ? 'positivo' : 'negativo'
        },
        countRec(item, key) {
            const n = item ? Number(item[key] || 0) : 0
            return Number.isFinite(n) ? n : 0
        },
        textoTemCountRec(item, key) {
            const n = this.countRec(item, key)
            if (n <= 0) return 'Não'
            return n === 1 ? 'Sim (1)' : `Sim (${n})`
        },
        tomTemCountRec(item, key) {
            return this.countRec(item, key) > 0 ? 'positivo' : 'negativo'
        },
        textoInteresseRec(item) {
            if (!item || !item.feed_back) return ''
            if (item.feed_back.interesse === true || item.feed_back.interesse === false) {
                return item.feed_back.interesse ? 'Sim' : 'Não'
            }
            return ''
        },
        tomInteresseRec(item) {
            if (!item || !item.feed_back) return 'meta'
            if (item.feed_back.interesse === true) return 'positivo'
            if (item.feed_back.interesse === false) return 'negativo'
            return 'meta'
        },
        toggleDropdown(itemId) {
            this.dropdownAbertoId = this.dropdownAbertoId === itemId ? null : itemId
        },
        isDropdownOpen(itemId) {
            return this.dropdownAbertoId === itemId
        },
        fecharDropdown() {
            this.dropdownAbertoId = null
        },

        resetarPaginacao() {
            this.$refs && this && this && this.$refs && this.$refs.componente && (this.$refs.componente.atual = 1)
        },

        previewRecrutamentoWhatsapp() {
            const label = this.form_feedback.autocomplete_label_vaga_modal || ''
            const partes = label.split(' - ')
            this.previewWhatsappContexto = {
                nome_destinatario: this.form.nome,
                vaga_titulo: partes[0] || label,
                vaga_cidade: partes.length >= 3 ? `${partes[1]}/${partes[2]}` : (partes[1] || ''),
                data_entrevista: this.form_feedback.data_entrevista || '',
                local_entrevista: this.form_feedback.local_entrevista || '',
            }
            this.previewWhatsappTipo = 'recrutamento_selecao'
            this.previewWhatsappAberto = true
        }
    }
})

registerGlobals(app)
app.mount('#app')

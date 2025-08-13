import endereco from "../../../components/Endereco";
import telefone from "../../../components/Telefones";
import datepicker from "../../../components/DatePicker";
import ExportacaoMixin from "../../../mixins/Exportacoes";

// Constantes
const SELECOES = {
    NAO_SELECIONADO: "nao",
    EMPTY: ""
};

const DELAY_BUSCA = 600;
const DELAY_VALIDACAO = 100;

const app = new Vue({
    mixins: [ExportacaoMixin],
    el: "#app",

    components: {
        endereco,
        datepicker,
        telefone
    },

    data() {
        return {
            // Estados da aplicação
            tituloJanela: "Visualizando Curriculo",
            preloadAjax: false,
            editando: false,
            apagado: false,
            feedback: false,
            cadastrado: false,
            atualizado: false,

            // Configurações
            permite_envio_whatsapp: null,
            preloadExportacao: false,
            hash: `mastertag_${this.generateRandomId()}`,
            empresa: 0,
            urlExportacao: `${URL_ADMIN}/curriculos/recrutamentos/export`,

            // Listas para selects
            lista_sexos: [],
            lista_estados_civis: [],
            lista: [],
            ufs: [],
            vagas: [],

            // Formulário principal
            form: this.createInitialForm(),
            formDefault: null,

            // Formulário de feedback
            form_feedback: this.createInitialFeedback(),
            form_feedbackDefault: null,

            // Campo para referência
            campoNome: null,

            // Controles de busca e paginação
            controle: {
                carregando: false,
                dados: {
                    caminho_autocomplete: "autocomplete/todas-vagas-abertas-ativas",
                    caminho_cliente_autocomplete: "autocomplete/todos-clientes-ativos",
                    autocomplete_label_anterior: "",
                    autocomplete_label: "",
                    pages: 20,
                    campoBusca: "",
                    campoVaga: "",
                    campoLido: "",
                    campoFiltro: "",
                    campoUf: "",
                    campoPcd: "",
                    campoCPF: "",
                    filtroPeriodo: false,
                    periodo: ""
                }
            }
        };
    },

    mounted() {
        this.inicializar();
    },

    methods: {
        // ===== MÉTODOS DE INICIALIZAÇÃO =====
        inicializar() {
            this.formDefault = _.cloneDeep(this.form);
            this.form_feedbackDefault = _.cloneDeep(this.form_feedback);
            this.atualizar();
            this.listaVagas();
        },

        createInitialForm() {
            return {
                id: "",
                bairro: "",
                cep: "",
                cnh: "",
                complemento: "",
                cpf: "",
                sexo: "",
                estado_civil: "",
                created_at: "",
                datalido: "",
                email: "",
                experiencias: [],
                feed_back: "",
                formacao: {},
                formacao_curso: "",
                formacao_instituicao: "",
                formacao_status: "",
                lido: "",
                logradouro: "",
                municipio: "",
                nascimento: "",
                nome: "",
                qualificacoes: [],
                telefones: [],
                telefonesDelete: [],
                uf: "",
                usuario: "",
                usuario_lido: "",
                vaga: {},
                vaga_pretendida: ""
            };
        },

        createInitialFeedback() {
            return {
                selecionado: "",
                autocomplete_label_vaga_modal: "",
                autocomplete_label_vaga_modal_anterior: "",
                vaga_id: "",
                contato_realizado: "",
                interesse: "",
                data_entrevista: "",
                local_entrevista: "",
                obs: "",
                autocomplete_label_cliente_modal: "",
                autocomplete_label_cliente_modal_anterior: "",
                cliente_id: "",
                telefone_id: "",
                envia_mail_desclassificacao: "",
                tem_provas: false,
                envia_mail_provas: "",
                envia_mail_proxima_etapa: "",
                envia_whatsapp: ""
            };
        },

        generateRandomId() {
            return parseInt((Math.random() * 999999));
        },

        // ===== MÉTODOS DE SELEÇÃO DE VAGAS =====
        selecionaVaga(obj) {
            this.controle.dados.campoVaga = obj.id;
            this.controle.dados.autocomplete_label = obj.label;
            this.controle.dados.autocomplete_label_anterior = obj.label;
            this.controle.carregando = true;

            this.executarBuscaComDelay();
        },

        executarBuscaComDelay() {
            setTimeout(() => {
                if (this.$refs.componente) {
                    this.$refs.componente.buscar();
                }
            }, DELAY_BUSCA);
        },

        resetaCampo() {
            if (this.campoFoiAlterado(this.controle.dados.autocomplete_label_anterior, this.controle.dados.autocomplete_label)) {
                this.limparCamposVaga();
                if (this.$refs.componente) {
                    this.$refs.componente.buscar();
                }
            }
        },

        limparCamposVaga() {
            this.controle.dados.autocomplete_label_anterior = "";
            this.controle.dados.autocomplete_label = "";
            this.controle.dados.campoVaga = "";
        },

        campoFoiAlterado(anterior, atual) {
            return anterior !== atual;
        },

        // ===== MÉTODOS DE MODAL DE VAGA =====
        selecionaVagaModal(obj) {
            this.form_feedback.vaga_id = obj.id;
            this.form_feedback.autocomplete_label_vaga_modal = obj.label;
            this.form_feedback.autocomplete_label_vaga_modal_anterior = obj.label;
            this.form_feedback.tem_provas = obj.simulado_vaga?.length > 0 || false;
        },

        resetaCampoVagaModal() {
            if (this.campoFoiAlterado(
                this.form_feedback.autocomplete_label_vaga_modal_anterior,
                this.form_feedback.autocomplete_label_vaga_modal
            )) {
                this.limparCamposVagaModal();
                this.validarCampoVagaModal();
            }
        },

        limparCamposVagaModal() {
            this.form_feedback.autocomplete_label_vaga_modal_anterior = "";
            this.form_feedback.autocomplete_label_vaga_modal = "";
            this.form_feedback.vaga_id = "";
        },

        validarCampoVagaModal() {
            setTimeout(() => {
                if (this.form_feedback.vaga_id === SELECOES.EMPTY) {
                    this.mostrarErroVagaVazia();
                }
            }, DELAY_VALIDACAO);
        },

        mostrarErroVagaVazia() {
            const campoId = `#vaga_modal_${this.hash}`;
            valida_campo_vazio($(campoId), 1);
            $(`#janelaCadastrar ${campoId}`).focus().trigger("blur");
            mostraErro("Erro", "O Campo Vaga não pode ficar vazio");
        },

        // ===== MÉTODOS DE FORMULÁRIO =====
        async formAlterar(id) {
            this.resetarEstados();
            this.form.id = id;
            this.preloadAjax = true;

            try {
                const response = await axios.get(`${URL_ADMIN}/curriculos/recrutamentos/${id}/editar`);
                this.processarDadosFormulario(response.data);
            } catch (error) {
                console.error("Erro ao carregar dados:", error);
                this.preloadAjax = false;
            }
        },

        resetarEstados() {
            this.feedback = false;
            this.cadastrado = false;
            this.atualizado = false;
            this.editando = false;
            Object.assign(this.form_feedback, this.form_feedbackDefault);
            this.form.telefonesDelete = [];
            formReset();
        },

        processarDadosFormulario(data) {
            this.marcaLido(data);
            if (this.$refs.componente) {
                this.$refs.componente.buscar();
            }

            Object.assign(this.form, data);

            if (data.feed_back) {
                this.processarFeedback(data.feed_back);
            }

            this.finalizarCarregamentoFormulario(data);
        },

        processarFeedback(feedbackData) {
            Object.assign(this.form_feedback, feedbackData);
            this.feedback = true;

            if (feedbackData.vaga_aberta?.vaga_selecionada) {
                this.configurarVagaSelecionada(feedbackData.vaga_aberta);
            }

            if (feedbackData.cliente) {
                this.configurarClienteSelecionado(feedbackData.cliente);
            }
        },

        configurarVagaSelecionada(vagaAberta) {
            const vaga = vagaAberta.vaga_selecionada;
            const municipio = vagaAberta.municipio;

            this.form_feedback.autocomplete_label_vaga_modal =
                `${vaga.nome} - ${municipio.nome} - ${municipio.uf}`;
            this.form_feedback.tem_provas = vaga.simulado_vaga?.length > 0 || false;
        },

        configurarClienteSelecionado(cliente) {
            this.form_feedback.autocomplete_label_cliente_modal =
                cliente.tipo === "Pessoa Jurídica" ? cliente.nome_fantasia : cliente.nome;
        },

        finalizarCarregamentoFormulario(data) {
            this.tituloJanela = `Visualizando Curriculo - ${data.nome}`;
            this.editando = true;
            this.preloadAjax = false;
            setupCampo();
        },

        async marcaLido(dados) {
            try {
                await axios.put(`${URL_ADMIN}/curriculos/recrutamentos/${dados.id}/lido`, dados);
            } catch (error) {
                console.error("Erro ao marcar como lido:", error);
            }
        },

        // ===== MÉTODOS DE VALIDAÇÃO E ALTERAÇÃO =====
        async alterar() {
            if (!this.validarFormulario()) {
                return false;
            }

            this.form_feedback.curriculos = this.form;
            this.preloadAjax = true;

            try {
                const response = await axios.put(`${URL_ADMIN}/curriculos/recrutamentos/${this.form.id}`, this.form_feedback);

                if (response.status === 201) {
                    this.processarSucessoAlteracao();
                }
            } catch (error) {
                this.preloadAjax = false;
                console.error("Erro ao alterar:", error);
            }
        },

        validarFormulario() {
            if (!this.validarTelefonePrincipal()) {
                return false;
            }

            if (!this.validarVagaSelecionada()) {
                return false;
            }

            if (!this.validarCamposObrigatorios()) {
                return false;
            }

            return true;
        },

        validarTelefonePrincipal() {
            const buscaTel = _.findIndex(this.form.telefones, { "principal": true });

            if (buscaTel <= -1) {
                mostraErro("", "Nenhum telefone foi marcado como principal");
                return false;
            }

            return true;
        },

        validarVagaSelecionada() {
            const feedbackSelecionado = this.form_feedback.selecionado !== SELECOES.EMPTY &&
                this.form_feedback.selecionado !== SELECOES.NAO_SELECIONADO;

            if (feedbackSelecionado && this.form_feedback.vaga_id === SELECOES.EMPTY) {
                this.mostrarErroVagaVazia();
                return false;
            }

            return true;
        },

        validarCamposObrigatorios() {
            $("#janelaCadastrar :input:visible").trigger("blur");

            if ($("#janelaCadastrar :input:visible.is-invalid").length) {
                mostraErro("", "Verifique os campos");
                return false;
            }

            return true;
        },

        processarSucessoAlteracao() {
            this.preloadAjax = false;
            this.atualizado = true;

            if (this.$refs.componente) {
                this.$refs.componente.buscar();
            }

            $("#janelaCadastrar").modal("hide");
            mostraSucesso("", "Feedback realizado com sucesso!");
        },

        // ===== MÉTODOS DE EXCLUSÃO =====
        async apagar() {
            this.erros = [];
            this.preloadAjax = true;

            try {
                await axios.delete(`${URL_ADMIN}/curriculos/recrutamentos/${this.form.id}`, this.form);
                this.processarSucessoExclusao();
            } catch (error) {
                this.preloadAjax = false;
                console.error("Erro ao apagar:", error);
            }
        },

        processarSucessoExclusao() {
            this.preloadAjax = false;
            this.apagado = true;

            if (this.$refs.componente) {
                this.$refs.componente.buscar();
            }
        },

        janelaConfirmar(id) {
            this.form.id = id;
            this.apagado = false;
            this.preloadAjax = false;
        },

        // ===== MÉTODOS DE DADOS =====
        async listaVagas() {
            this.preloadAjax = true;

            try {
                const response = await axios.get(`${URL_PUBLICO}/lista-vagas`);
                this.vagas = response.data.vagas;
            } catch (error) {
                console.error("Erro ao carregar vagas:", error);
            } finally {
                this.preloadAjax = false;
            }
        },

        carregou(dados) {
            this.lista = dados.items;
            this.lista_sexos = dados.lista_sexos;
            this.lista_estados_civis = dados.lista_estados_civis;
            this.permite_envio_whatsapp = dados.permite_envio_whatsapp;
            this.controle.carregando = false;
        },

        carregando() {
            this.controle.carregando = true;
        },

        atualizar() {
            if (this.$refs.componente) {
                this.$refs.componente.atual = 1;
                this.$refs.componente.buscar();
            }
        }
    }
});

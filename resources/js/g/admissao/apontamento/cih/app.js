import autocomplete from "../../../../components/AutoComplete";
import DatePicker from "../../../../components/DatePicker";
import Upload from "../../../../components/Upload";
import ExportacaoMixin from "../../../../mixins/Exportacoes";
import Validacoes from "../../../../mixins/Validacoes";

const app = new Vue({
    mixins: [ExportacaoMixin, Validacoes],

    el: "#app",
    components: {
        autocomplete,
        DatePicker,
        Upload
    },
    data: {
        tituloJanela: "Cadastrando CIH",
        preloadAjax: false,
        editando: false,
        leitura: false,
        apagado: false,
        aprovando: false,
        preloadExportacao: false,

        colaborador_ativo: `autocomplete/colaboradorCih`,
        todos_municipios: `autocomplete/todos-municipios`,

        urlExportacao: `${URL_ADMIN}/apontamento/cih/export`,
        selecionados: [],

        hash: `mastertag_${parseInt((Math.random() * 999999))}`,
        cliente_id: 0,

        datarelatorio: "",
        tipoRelatorio: "pdf",
        cliente_relatorio: "",

        hoje: "",

        form: {
            tag_id: "",
            outra_tag: "",
            feedback_id: "",
            autocomplete_label_colaborador: "",
            autocomplete_label_colaborador_anterior: "",
            cliente_id: "",
            area_id: "",
            varios_colaboradores: false,
            colaboradores_avulso: "",
            outra_area: "",
            acao: "",
            user_lancamento_id: "",
            obs_lancamento: "",
            data_lancamento: "",
            user_aprovacao_id: "",
            obs_aprovacao: "",
            data_aprovacao: "",
            status: "",
            status_aprovacao: "",
            anexos: [],
            anexosDel: []
        },

        url_anexo: `${URL_ADMIN}/apontamento/cih/uploadAnexos`,
        anexoUploadAndamento: false,

        formDefault: null,

        campoNome: null,

        cadastrado: false,
        atualizado: false,

        lista: [],
        listaTags: [],
        listaAreas: [],
        listaClientes: [],

        controle: {
            carregando: false,
            dados: {
                campoBusca: "",
                campoStatus: "",
                campoTags: "",
                campoAreas: "",
                pages: 50
            }
        }
    },
    mounted() {
        this.formDefault = _.cloneDeep(this.form); //copia
        this.atualizar();
    },
    computed: {
        tudoMarcado() {
            let totalItens = this.comTeste.length;
            let totalEncontrado = 0;

            if (totalItens === 0) {
                return false;
            }

            this.comTeste.forEach(item => {
                let id = item.curriculo_id;
                if (this.selecionados.indexOf(id) >= 0) {
                    totalEncontrado++;
                    //faz nada
                } else {
                    return false;
                }
            });
            let resultado = totalItens === totalEncontrado;
            this.selecionaTudo = resultado;
            return resultado;
        }
    },
    methods: {
        selecionaColaborador(obj) {
            this.form.feedback_id = obj.id;
            this.form.cliente_id = obj.cliente_id;
            this.form.autocomplete_label_colaborador = obj.label;
            this.form.autocomplete_label_colaborador_anterior = obj.label;
        },
        resetaCampoColaborador() {
            if (this.form.autocomplete_label_colaborador_anterior !== this.form.autocomplete_label_colaborador) {
                this.form.autocomplete_label_colaborador_anterior = "";
                this.form.autocomplete_label_colaborador = "";
                this.form.feedback_id = "";
                this.form.cliente_id = "";

                setTimeout(() => {
                    if (this.form.feedback_id === "") {
                        valida_campo_vazio($(`#colaborador_${this.hash}`), 1);
                        // $('#janelaCadastrar #' + this.hash).focus().trigger('blur');
                        $(`#janelaCadastrar #colaborador_${this.hash}`).focus().trigger("blur");
                        mostraErro("Erro", "O Campo Vaga não pode ficar vazio");
                    }
                }, 100);
            }
        },

        formNovo() {
            this.cadastrado = false;
            this.atualizado = false;
            this.editando = false;
            this.aprovando = false;

            this.tituloJanela = "Cadastrando CIH";

            formReset();
            setupCampo();

            this.form = _.cloneDeep(this.formDefault); //copia
            this.form.status = "aberto";

        },
        cadastrar() {
            formReset();

            this.validaBlur();

            this.$nextTick(() => {
                $("#janelaCadastrar :input:enabled").trigger("blur");
                if ($("#janelaCadastrar :input:enabled.is-invalid").length) {
                    this.mostraErro("", "Existem campos obrigatórios não preenchidos");
                    return false;
                }
                if (!this.form.varios_colaboradores && this.form.feedback_id === "") {
                    valida_campo_vazio($(`#colaborador_${this.hash}`), 1);
                    $(`#janelaCadastrar #colaborador_${this.hash}`).focus().trigger("blur");
                    this.mostraErro("", "Selecione o colaborador");
                    return false;
                }

                let tag_selecionada = this.form.tag_id !== 0 ? this.listaTags.find(item => item.id === this.form.tag_id) : 0;
                if (tag_selecionada.anexo_obrigatorio) {
                    if (this.form.anexos.length === 0) {
                        this.mostraErro("", "O Campo Anexo não pode ficar vazio");
                        return false;
                    }
                }
                this.preloadAjax = true;
                this.form.status = "aberto";

                axios.post(`${URL_ADMIN}/apontamento/cih`, this.form)
                    .then(response => {
                        if (response.status === 201) {
                            $("#janelaCadastrar").modal("hide");
                            this.mostraSucesso("", "Ocorrência cadastrada com sucesso");
                            this.preloadAjax = false;
                            this.cadastrado = true;
                            this.atualizar();
                        }
                    }).catch(error => (this.preloadAjax = false));
            });
        },
        formAlterar(id) {
            this.cadastrado = false;
            this.atualizado = false;
            this.editando = false;
            this.aprovando = true;
            this.tituloJanela = `Alterando CIH #${id}`;
            this.preloadAjax = true;
            formReset();

            this.form = _.cloneDeep(this.formDefault); //copia
            this.leitura = true;

            axios.get(`${URL_ADMIN}/apontamento/cih/${id}/editar`)
                .then(response => {
                    Object.assign(this.form, response.data);
                    // this.form.status = this.form.status === "aberto" ? "" : this.form.status;
                    this.editando = true;
                    this.preloadAjax = false;
                    setupCampo();
                }).catch(
                error => (this.preloadAjax = false)
            );

        },
        selecionaTodos() {
            this.selecionaTudo = !this.selecionaTudo;
            if (this.selecionaTudo) {
                this.comTeste.map(item => {
                    let id = item.id;
                    if (this.selecionados.indexOf(id) === -1) {
                        this.selecionados.push(id);
                    }
                });
            } else {
                this.comTeste.map(item => {
                    let id = item.id;
                    let index = this.selecionados.indexOf(id);
                    if (index >= 0) {
                        this.selecionados.splice(index, 1);
                    }
                });
            }
        },

        alterar() {
            formReset();
            $("#janelaCadastrar :input:enabled").trigger("blur");
            if ($("#janelaCadastrar :input:enabled.is-invalid").length) {
                mostraErro("", "Verificar os erros");
                return false;
            }

            this.form._method = "PUT";
            this.preloadAjax = true;

            axios.put(`${URL_ADMIN}/apontamento/cih/${this.form.id}`, this.form).then(response => {
                $("#janelaCadastrar").modal("hide");
                mostraSucesso("", "Ocorrência alterada com sucesso!");
                this.preloadAjax = false;
                this.atualizado = true;
                this.atualizar();
            }).catch(error => (this.preloadAjax = false));

        },

        formAprovar(id) {
            this.cadastrado = false;
            this.atualizado = false;
            this.editando = false;
            this.aprovando = true;
            this.tituloJanela = `Aprovando CIH #${id}`;
            this.preloadAjax = true;
            formReset();

            this.form = _.cloneDeep(this.formDefault); //copia
            this.leitura = true;

            axios.get(`${URL_ADMIN}/apontamento/cih/${id}/editar`)
                .then(response => {
                    Object.assign(this.form, response.data);
                    this.form.status = this.form.status === "aberto" ? "" : this.form.status;
                    this.editando = true;
                    this.preloadAjax = false;
                    setupCampo();
                }).catch(
                error => (this.preloadAjax = false)
            );

        },

        aprovar() {
            formReset();
            $("#janelaCadastrar :input:enabled").trigger("blur");
            if ($("#janelaCadastrar :input:enabled.is-invalid").length) {
                mostraErro("", "Verificar os erros");
                return false;
            }

            this.form._method = "PUT";
            this.preloadAjax = true;
            this.form.status = "aprovado";

            axios.put(`${URL_ADMIN}/apontamento/cih/aprovar/${this.form.id}`, this.form).then(response => {
                $("#janelaCadastrar").modal("hide");
                mostraSucesso("", "Ocorrência alterada com sucesso!");
                this.preloadAjax = false;
                this.atualizado = true;
                this.atualizar();
            }).catch(error => (this.preloadAjax = false));

        },

        carregou(dados) {
            this.lista = dados.itens;
            this.listaTags = dados.tags;
            this.listaAreas = dados.areas;
            this.cliente_id = dados.cliente_id;
            this.datarelatorio = dados.intervalo;
            this.hoje = dados.hoje;
            this.listaClientes = dados.listaClientes;
            this.controle.carregando = false;
        },

        carregando() {
            this.controle.carregando = true;
        },

        atualizar() {
            this.$refs.componente.atual = 1;
            this.$refs.componente.buscar();
        }
    }
});

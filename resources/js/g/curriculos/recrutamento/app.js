import endereco from "../../../components/Endereco"
import telefone from "../../../components/Telefones"
import datepicker from "../../../components/DatePicker"

const app = new Vue({
        el: '#app',
        components: {
            endereco,
            datepicker,
            telefone
        },
        data: {
            tituloJanela: 'Visualizando Curriculo',
            preloadAjax: false,
            editando: false,
            apagado: false,

            empresa: 0,

            feedback: false,
            hash: `mastertag_${parseInt((Math.random() * 999999))}`,

            form: {
                id: "",
                bairro: "'",
                cep: "'",
                cnh: "",
                complemento: '',
                cpf: "",
                created_at: "",
                datalido: "",
                email: "",
                experiencias: [],
                feed_back: '',
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
                usuario: '',
                usuario_lido: '',
                vaga: {},
                vaga_pretendida: '',
            },

            formDefault: null,

            form_feedback: {
                selecionado: '',

                autocomplete_label_vaga_modal: '',
                autocomplete_label_vaga_modal_anterior: '',
                vaga_id: '',

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
                envia_whatsapp: '',
            },
            form_feedbackDefault: null,

            campoNome: null,

            cadastrado: false,
            atualizado: false,

            lista: [],
            ufs: [],
            vagas: [],

            controle: {
                carregando: false,
                dados: {
                    caminho_autocomplete: `autocomplete/todas-vagas-ativas`,
                    caminho_cliente_autocomplete: `autocomplete/todos-clientes-ativos`,
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
                    periodo: '',
                },
            },
        },
        mounted() {
            this.formDefault = _.cloneDeep(this.form) //copia
            this.form_feedbackDefault = _.cloneDeep(this.form_feedback) //copia
            this.atualizar();
            this.listaVagas();
        },
        methods: {
            selecionaVaga(obj) {
                this.controle.dados.campoVaga = obj.id;
                this.controle.dados.autocomplete_label = obj.label;
                this.controle.dados.autocomplete_label_anterior = obj.label;
            },
            resetaCampo() {
                if (this.controle.dados.autocomplete_label_anterior !== this.controle.dados.autocomplete_label) {
                    this.controle.dados.autocomplete_label_anterior = '';
                    this.controle.dados.autocomplete_label = '';
                    this.controle.dados.campoVaga = '';
                }
            },
            selecionaVagaModal(obj) {
                this.form_feedback.vaga_id = obj.id;
                this.form_feedback.autocomplete_label_vaga_modal = obj.label;
                this.form_feedback.autocomplete_label_vaga_modal_anterior = obj.label;

                this.form_feedback.tem_provas = obj.simulado_vaga.length > 0;

            },
            resetaCampoVagaModal() {
                if (this.form_feedback.autocomplete_label_vaga_modal_anterior !== this.form_feedback.autocomplete_label_vaga_modal) {
                    this.form_feedback.autocomplete_label_vaga_modal_anterior = '';
                    this.form_feedback.autocomplete_label_vaga_modal = '';
                    this.form_feedback.vaga_id = '';

                    setTimeout(() => {
                        if (this.form_feedback.vaga_id === '') {
                            valida_campo_vazio($('#vaga_modal_' + this.hash), 1);
                            $('#janelaCadastrar #vaga_modal_' + this.hash).focus().trigger('blur');
                            mostraErro('Erro', 'O Campo Vaga não pode ficar vazio');
                        }
                    }, 100);

                    // setTimeout(() => {
                    //     if (this.form_feedback.vaga_id == '') {
                    //         mostraErro('Erro', 'O Campo Vaga não pode ficar vazio');
                    //     }
                    // }, 100);
                }
            },
            selecionaClienteModal(obj) {
                this.form_feedback.cliente_id = obj.id;
                this.form_feedback.autocomplete_label_cliente_modal = obj.label;
                this.form_feedback.autocomplete_label_cliente_modal_anterior = obj.label;
            },
            resetaCampoClienteModal() {
                if (this.form_feedback.autocomplete_label_cliente_modal_anterior !== this.form_feedback.autocomplete_label_cliente_modal) {
                    this.form_feedback.autocomplete_label_cliente_modal_anterior = '';
                    this.form_feedback.autocomplete_label_cliente_modal = '';
                    this.form_feedback.cliente_id = '';

                    setTimeout(() => {
                        if (this.form_feedback.cliente_id === '') {
                            valida_campo_vazio($('#cliente_modal_' + this.hash), 1);
                            $('#janelaCadastrar #cliente_modal_' + this.hash).focus().trigger('blur');
                            mostraErro('Erro', 'O Campo Cliente não pode ficar vazio');
                        }
                    }, 100);

                }

            },

            formAlterar(id) {
                this.form.id = id;
                this.feedback = false;
                this.cadastrado = false;
                this.atualizado = false;
                this.editando = false;

                this.preloadAjax = true;
                Object.assign(this.form_feedback, this.form_feedbackDefault);
                this.form.telefonesDelete = [];

                formReset();
                axios.get(`${URL_ADMIN}/curriculos/recrutamentos/${id}/editar`)
                    .then((res) => {
                        let data = res.data;
                        this.marcaLido(data);
                        this.$refs.componente.buscar();
                        Object.assign(this.form, data);
                        if (data.feed_back) {
                            Object.assign(this.form_feedback, data.feed_back);
                            this.feedback = true;
                            if (data.feed_back.vaga_selecionada) {
                                this.form_feedback.autocomplete_label_vaga_modal = data.feed_back.vaga_selecionada.nome;
                                this.form_feedback.tem_provas = data.feed_back.vaga_selecionada.simulado_vaga.length > 0;
                            }
                            if (data.feed_back.cliente) {
                                if (data.feed_back.cliente.tipo === 'Pessoa Jurídica') {
                                    this.form_feedback.autocomplete_label_cliente_modal = data.feed_back.cliente.nome_fantasia;
                                } else {
                                    this.form_feedback.autocomplete_label_cliente_modal = data.feed_back.cliente.nome;
                                }
                            }
                        }

                        this.tituloJanela = `Visualizando Curriculo - ${data.nome}`;
                        this.editando = true;
                        this.preloadAjax = false;
                        setupCampo();

                    })
                    .catch((data) => {
                    });
            },

            marcaLido(dados) {
                axios.put(`${URL_ADMIN}/curriculos/recrutamentos/${dados.id}/lido`, dados)
                    .then((data) => {
                    })
                    .catch((data) => {
                    });
            },

            alterar() {
                let buscaTel = _.findIndex(this.form.telefones, {'principal': true});

                if (buscaTel <= -1) {
                    mostraErro('', 'Nenhum telefone foi marcado como principal');
                    return false;
                }

                if (this.form_feedback.selecionado !== '' && this.form_feedback.selecionado !== 'nao') {
                    if (this.form_feedback.vaga_id === '') {
                        valida_campo_vazio($('#vaga_modal_' + this.hash), 1);
                        $('#janelaCadastrar #vaga_modal_' + this.hash).focus().trigger('blur');
                        mostraErro('', 'O campo vaga não pode ficar vazio');
                        return false;
                    }
                }

                if (this.form_feedback.interesse) {
                    if (this.form_feedback.cliente_id === '') {
                        valida_campo_vazio($('#cliente_modal_' + this.hash), 1);
                        $('#janelaCadastrar #cliente_modal_' + this.hash).focus().trigger('blur');
                        mostraErro('', 'O campo cliente não pode ficar vazio');
                        return false;
                    }
                }


                $('#janelaCadastrar :input:visible').trigger('blur');
                if ($('#janelaCadastrar :input:visible.is-invalid').length) {
                    mostraErro('', 'Verifique os campos');
                    return false;
                }

                this.form_feedback.curriculos = this.form;
                this.preloadAjax = true;

                axios.put(`${URL_ADMIN}/curriculos/recrutamentos/${this.form.id}`, this.form_feedback)
                    .then((res) => {
                        if (res.status === 201) {
                            this.preloadAjax = false;
                            this.atualizado = true;
                            this.$refs.componente.buscar();
                            $('#janelaCadastrar').modal('hide');
                            mostraSucesso('', 'Feedback realizado com sucesso!')
                        }
                    })
                    .catch((data) => {
                        this.preloadAjax = false;
                    });

            },
            apagar() {
                this.erros = [];
                this.preloadAjax = true;

                axios.delete(`${URL_ADMIN}/curriculos/recrutamentos/${this.form.id}`, this.form)
                    .then((res) => {
                        this.preloadAjax = false;
                        this.apagado = true;
                        this.$refs.componente.buscar();
                    })
                    .catch((error) => {
                        this.preloadAjax = false;
                    });
            },

            listaVagas() {
                this.preloadAjax = true;
                axios.get(`${URL_PUBLICO}/lista-vagas`)
                    .then(response => {
                        let data = response.data;
                        this.preloadAjax = false;
                        this.vagas = data.vagas;

                    })
                    .catch(error => {
                        this.preloadAjax = false;
                    })
            },

            janelaConfirmar(id) {
                this.form.id = id;
                this.apagado = false;

                this.preloadAjax = false;
            },
            carregou(dados) {
                this.lista = dados;
                this.controle.carregando = false;

            },
            carregando() {
                this.controle.carregando = true;
            },
            atualizar() {
                this.$refs.componente.atual = 1;
                this.$refs.componente.buscar();
            },
        }
    })
;

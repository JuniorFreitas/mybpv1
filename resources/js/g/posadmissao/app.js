import datepicker from "../../components/DatePicker";

const app = new Vue({
    el: '#app',
    components: {
        datepicker
    },
    data: {
        tituloJanela: 'Carregando ...',
        tituloJanelaEntrevista: 'Carregando ...',
        preload: false,
        editando: false,
        apagado: false,
        cadastrado: false,
        cadastrando: false,
        atualizado: false,
        visualizar: false,
        avaliacao: false,
        desmobilizacao: false,
        entrevista: false,

        hash: `mastertag_${parseInt((Math.random() * 999999))}`,
        todos_municipios: `autocomplete/todos-municipios`,

        URL_ADMIN,

        selecionados: [],
        selecionaTudo: false,

        form: {
            tipo_form: "",
            data_desmobilizacao: "",
            avaliacao: "",
            obs_avaliacao: "",
            user_avaliacao: "",
            responsavel_feedback: "",
            data_avaliacao: "",
            motivo_rescisao: "",
            classificacao_rescisao: "",
            tipo_aviso: "",

            motivo: "",
            aviso: "",
            classificacao: "",

            //campos extras
            outromotivo: "",
            quem_classificou: "",
            observacoes: "",

            preenchido_por: "",

            alternativas: null,
            pendencia: "",
            pendencias_quais: "",
            outros: "",

            preenchido_por_rh: "",
            preenchido_por_adm: "",
            preenchido_por_ssma: "",

            entrevista_desligamento: {
                curriculo_id: '',
                superior_imediato: '',
                motivo: '',
                trabalharia_novamente: '',
                contr_melhoria: '',
                relacao_interpessoal: '',
                recursos_fisicos: '',
                valores_normas: '',
                planejamento: '',
                sob_superior_imediato: '',
                direcao_empresa: '',
                oportunidades: '',
                salario_beneficio: '',
                atividade: '',
                comentarios: '',
                parecer_entrevistador: '',
                pode_voltar: '',
                porque_pode_voltar: '',
                quem_entrevistou: '',
                data_entrevista: '',
                preenchido_por: '',
            },

        },

        formDefault: null,

        preloadEntrevista: false,
        atualizadoEntrevista: false,
        cadastrandoEntrevista: false,


        entrevista_desligamentoDefault: null,

        alternativasDefault: null,

        lista: [],
        listaMotivos: [],
        listaAvisos: [],
        listaClassificacoes: [],
        formulario: [],
        vagas: [],
        listaAreas: [],

        controle: {
            carregando: false,
            dados: {
                caminho_autocomplete: `autocomplete/todas-vagas-ativas`,
                autocomplete_label_anterior: '',
                autocomplete_label: '',
                pages: 20,

                campoBusca: '',
                campoArea: '',
                campoVaga: '',
                campoLido: '',
                campoFiltro: '',
                campoPcd: '',
                campoUf: '',
                campoFeedback: '',
            },
        },
    },
    computed: {
        comAvaliacao() {
            return this.lista.filter(item => {
                return item.avaliacao;
            });
        },
        tudoMarcado() {
            let totalItens = this.comAvaliacao.length;
            let totalEncontrado = 0;

            if (totalItens === 0) {
                return false;
            }
            this.comAvaliacao.forEach(item => {
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
    mounted() {
        this.formDefault = _.cloneDeep(this.form) //copia

        this.atualizar();
        this.listaVagas();
        this.listaAreasGeral();
    },
    methods: {
        selecionaTodos() {
            this.selecionaTudo = !this.selecionaTudo;
            if (this.selecionaTudo) {
                this.comAvaliacao.map(item => {
                    let id = item.curriculo_id;
                    if (this.selecionados.indexOf(id) === -1) {
                        this.selecionados.push(id)
                    }
                });
            } else {
                this.comAvaliacao.map(item => {
                    let id = item.curriculo_id;
                    let index = this.selecionados.indexOf(id);
                    if (index >= 0) {
                        this.selecionados.splice(index, 1)
                    }
                });
            }
        },
        //GERAL
        resetaCampo() {
            if (this.controle.dados.autocomplete_label_anterior !== this.controle.dados.autocomplete_label) {
                this.controle.dados.autocomplete_label_anterior = '';
                this.controle.dados.autocomplete_label = '';
                this.controle.dados.campoVaga = '';
            }
        },
        selecionaVaga(obj) {
            this.controle.dados.campoVaga = obj.id;
            this.controle.dados.autocomplete_label = obj.label;
            this.controle.dados.autocomplete_label_anterior = obj.label;
        },

        formVisualizar(id) {
            this.form.id = id;
            this.cadastrado = false;
            this.atualizado = false;
            this.editando = false;
            this.visualizar = true;

            this.preload = true;
            Object.assign(this.form, this.formDefault);

            formReset();
            axios.get(`${URL_ADMIN}/admissao/${id}/editar`)
                .then(response => {
                    let data = response.data;
                    Object.assign(this.form, data);
                    Object.assign(this.form, data['parecer_teste']);
                    this.tituloJanela = `Parecer Teste Prático - ${data.curriculo.nome}`;
                    this.preload = false;
                })
                .catch(error => {
                    this.preload = false;
                })

        },
        formAvaliar(curriculo_id) {
            this.tituloJanela = `Avaliando ${curriculo_id}`;
            this.cadastrando = true;
            this.atualizado = false;
            this.preload = true;

            this.avaliacao = true;
            this.desmobilizacao = false;
            this.entrevista = false;

            this.form = _.cloneDeep(this.formDefault)

            axios.get(`${URL_ADMIN}/posadmissao/${curriculo_id}/editar`)
                .then(response => {
                    let data = response.data;
                    this.tituloJanela = `Avaliando: ${data.feedback.curriculo.nome} - ${curriculo_id}`;
                    Object.assign(this.form, data);
                    this.form.avaliacao = data.avaliacao ? data.avaliacao : '';
                    if (data.alternativas) {

                    } else {
                        this.form.alternativas = _.cloneDeep(this.alternativasDefault);
                    }

                    this.preload = false;
                    // this.form.alternativas = !data.alternativas ? data.alternativas : Object.assign(this.form.alternativas, this.alternativasDefault);
                })
                .catch(error => {
                    this.preload = false;
                })
        },
        avaliar() {
            $('#janelaAvaliar :input:visible').trigger('blur');
            if ($('#janelaAvaliar :input:visible.is-invalid').length) {
                mostraErro('', 'Verifique os erros')
                return false;
            }

            this.form._method = 'PUT';
            this.preload = true;


            axios.post(`${URL_ADMIN}/posadmissao/${this.form.id}`, this.form)
                .then(response => {
                    let data = response.data;
                    this.cadastrando = false;
                    this.atualizado = true;
                    this.preload = false;
                    this.atualizar();
                })
                .catch(error => {
                    this.preload = false;
                })

        },
        formDesmobilizar(curriculo_id) {
            this.tituloJanela = `Desmobilizando ${curriculo_id}`;
            this.form.curriculo_id = curriculo_id;
            this.cadastrando = true;
            this.atualizado = false;
            this.preload = true;

            this.avaliacao = false;
            this.desmobilizacao = true;
            this.entrevista = false;

            this.form = _.cloneDeep(this.formDefault)
            this.form.alternativas = _.cloneDeep(this.alternativasDefault)

            axios.get(`${URL_ADMIN}/posadmissao/${curriculo_id}/editar`)
                .then(response => {
                    let data = response.data;
                    this.tituloJanela = `Desmobilizando: ${data.feedback.curriculo.nome} - ${curriculo_id}`;
                    Object.assign(this.form, data);
                    this.form.avaliacao = data.avaliacao ? data.avaliacao : '';
                    if (data.alternativas) {

                    } else {
                        this.form.alternativas = _.cloneDeep(this.alternativasDefault);
                    }

                    this.preload = false;
                    // this.form.alternativas = !data.alternativas ? data.alternativas : Object.assign(this.form.alternativas, this.alternativasDefault);
                })
                .catch(error => {
                    this.preload = false;
                })
        },
        desmobilizar() {
            $('#janelaAvaliar :input:visible').trigger('blur');
            if ($('#janelaAvaliar :input:visible.is-invalid').length) {
                mostraErro('', 'Verifique os erros')
                return false;
            }
            this.form._method = 'PUT';
            this.preload = true;
            axios.put(`${URL_ADMIN}/posadmissao/desmobilizar`, this.form)
                .then(response => {
                    let data = response.data;
                    this.cadastrando = false;
                    this.atualizado = true;
                    this.atualizar();
                    this.preload = false;
                })
                .catch(error => {
                    this.preload = false;
                })

        },
        formEntrevistar(curriculo_id) {
            this.tituloJanela = `Entrevista de desligamento ${curriculo_id}`;
            this.form.curriculo_id = curriculo_id;
            this.cadastrando = true;
            this.atualizado = false;
            this.preload = true;

            this.avaliacao = false;
            this.desmobilizacao = false;
            this.entrevista = true;

            // this.form = _.cloneDeep(this.formDefault)
            Object.assign(this.form, this.formDefault);

            axios.get(`${URL_ADMIN}/posadmissao/${curriculo_id}/editar`)
                .then(response => {
                    let data = response.data;
                    this.tituloJanela = `Entrevista de desligamento: ${data.feedback.curriculo.nome} - ${curriculo_id}`;
                    Object.assign(this.form, data);
                    if (!this.form.entrevista_desligamento) {
                        this.form.entrevista_desligamento = _.cloneDeep(this.formDefault.entrevista_desligamento)
                        // Object.assign(this.form.entrevista_desligamento, this.formDefault.entrevista_desligamento);
                    }
                    // this.form.entrevista_desligamento = !this.form.entrevista_desligamento ? Object.assign(this.form.entrevista_desligamento, this.formDefault.entrevista_desligamento) : Object.assign(this.form, data);
                    this.preload = false;
                })
                .catch(error => {
                    this.preload = false;
                })
        },
        entrevistar() {
            $('#janelaAvaliar :input:visible').trigger('blur');
            if ($('#janelaAvaliar :input:visible.is-invalid').length) {
                mostraErro('', 'Verifique os erros')
                return false;
            }
            // this.form._method = 'PUT';
            this.preload = true;

            if (!this.form.entrevista_desligamento.id) {
                axios.post(`${URL_ADMIN}/posadmissao/entrevistar`, this.form).then(response => {
                    let data = response.data;
                    this.cadastrando = false;
                    this.atualizado = true;
                    this.atualizar();
                    this.preload = false;
                })
                    .catch(error => {
                        this.preload = false;
                    })
            } else {
                // this.form.entrevista_desligamento._method = 'PUT';
                axios.put(`${URL_ADMIN}/posadmissao/entrevistar/${this.form.entrevista_desligamento.id}`, this.form.entrevista_desligamento)
                    .then(response => {
                        let data = response.data;
                        this.cadastrando = false;
                        this.atualizado = true;
                        this.atualizar();
                        this.preload = false;
                    })
                    .catch(error => {
                        this.preload = false;
                    })
            }
        },
        listaVagas() {
            this.preload = true;
            $.get(`${URL_PUBLICO}/lista-vagas`)
                .done((data) => {
                    this.preload = false;
                    this.vagas = data.vagas;
                })
                .fail((data) => {
                    this.preload = false;
                });
        },

        listaAreasGeral() {
            this.preload = true;
            $.get(`${URL_PUBLICO}/lista-areas`)
                .done((data) => {
                    this.preload = false;
                    this.listaAreas = data.areas;
                })
                .fail((data) => {
                    this.preload = false;
                });
        },
        janelaConfirmar(id) {
            this.form.id = id;
            this.apagado = false;

            this.preload = false;
        },
        carregou(dados) {
            this.lista = dados.items;
            this.listaMotivos = dados.motivos_rescisoes;
            this.listaAvisos = dados.tipos_rescisoes;
            this.listaClassificacoes = dados.classificacoes_rescisoes;
            this.formulario = dados.formulario;
            this.alternativasDefault = dados.form_limpo;
            this.selecionaTudo = this.tudoMarcado;
            this.form.alternativas = _.cloneDeep(this.alternativasDefault)
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
});

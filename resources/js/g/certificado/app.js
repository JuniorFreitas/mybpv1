import datepicker from "../../components/DatePicker";

const app = new Vue({
    el: '#app',
    components: {
        datepicker
    },
    data: {
        tituloJanela: 'Certificado',
        preload: false,
        editando: false,
        apagado: false,
        cadastrado: false,
        cadastrando: false,
        atualizado: false,
        visualizar: false,
        disabled: true,

        URL_ADMIN,
        cliente_id: '',

        hash: `mastertag_${parseInt((Math.random() * 999999))}`,

        todos_municipios: `autocomplete/todos-municipios`,

        selecionados: [],
        selecionaTudo: false,

        nr33: false,
        nr35: false,

        form: {
            _method: null,
            certificado: {
                curriculo_id: '',
                cliente_id: '',
                empresa_treinamento_trinta_tres_id: '',
                empresa_treinamento_trinta_cinco_id: '',
                instrutor_trinta_tres_id: '',
                instrutor_trinta_cinco_id: '',
                nacional: false,
            },
        },
        formDefault: null,


        formEnviar: {
            enviado: false,
            preload: false,
            titulo: 'Enviar Carteira e Etiqueta',
            nome: '',
            email: '',
            token: '',
        },

        formEnviarDefault: null,

        lista: [],
        vagas: [],
        listaAreas: [],

        inicial: '',
        controle: {
            carregando: false,
            dados: {
                caminho_autocomplete: `autocomplete/todas-vagas-ativas`,
                autocomplete_label_anterior: '',
                autocomplete_label: '',
                caminho_cliente_autocomplete: `autocomplete/todos-clientes-ativos`,
                autocomplete_label_cliente_anterior: '',
                autocomplete_label_cliente: '',
                pages: 20,
                cliente_custom: '',
                campoBusca: '',
                campoCPF: '',
                campoVaga: '',
                campoLido: '',
                campoFiltro: '',
                campoCliente: '',
                campoUf: '',
                campoArea: '',
                campoCargo: '',
                campoNr_trinta_tres: '',
                campoNr_trinta_cinco: '',
                campoInstrutor_nr_trinta_tres: '',
                campoInstrutor_nr_trinta_cinco: '',
                campoAdmitido: '',
                intervalo: '',
                filtroPeriodo: false
            },
        },
    },
    mounted() {
        this.formDefault = _.cloneDeep(this.form) //copia
        this.formEnviarDefault = _.cloneDeep(this.formEnviar) //copia
        this.cliente_id = $('#cliente_id').val();
        if (this.cliente_id) { //diferente de BPSE
            this.controle.dados.campoCliente = parseInt(this.cliente_id);
            this.controle.dados.cliente_custom = parseInt(this.cliente_id);
        }
        this.atualizar();
        // this.listaVagas();
        this.listaAreasGeral();
        this.inicial = this.controle.dados.intervalo;
    },
    computed: {
        comCertificado() {
            return this.lista.filter(item => {
                return item.certificado;
            });
        },
        tudoMarcado() {
            let totalCertificado = this.comCertificado.length;
            let totalEncontrado = 0;

            if (totalCertificado === 0) {
                return false;
            }

            this.comCertificado.forEach(item => {
                let id = item.certificado.feedback_id;
                // this.selecionados.indexOf(id) >= 0 ? totalEncontrado++ : false;
                if (this.selecionados.indexOf(id) >= 0) {
                    totalEncontrado++;
                    //faz nada
                } else {
                    return false;
                }
            });
            let resultado = totalCertificado === totalEncontrado;
            this.selecionaTudo = resultado;
            return resultado;
        }
    },
    methods: {
        selecionaTodos() {
            this.selecionaTudo = !this.selecionaTudo;
            if (this.selecionaTudo) {
                this.comCertificado.map(item => {
                    let id = item.certificado.feedback_id;
                    if (this.selecionados.indexOf(id) === -1) {
                        this.selecionados.push(id)
                    }
                });
            } else {
                this.comCertificado.map(item => {
                    let id = item.certificado.feedback_id;
                    let index = this.selecionados.indexOf(id);
                    if (index >= 0) {
                        this.selecionados.splice(index, 1)
                    }
                });
            }
        },

        gerarCarteiras() {
            axios.get(`${URL_ADMIN}/treinamento/carteiras`, {selecionados: this.selecionados})
                .then(response => {
                    let data = response.data;

                })
                .catch(error => {

                })
        },


        formAlterar(curriculo_id) {

            this.atualizado = false;
            this.cadastrando = false;
            this.visualizar = false;
            this.preload = true;
            this.cadastrado = false;
            this.form = _.cloneDeep(this.formDefault) //copia

            axios.get(`${URL_ADMIN}/certificado/${curriculo_id}/editar`)
                .then(response => {
                    let data = response.data;
                    Object.assign(this.form, data);
                    if (!data.certificado) {
                        this.form.certificado = _.cloneDeep(this.formDefault.certificado);
                    }
                    this.form.certificado.empresa_treinamento_trinta_tres_id = data.certificado.empresa_treinamento_trinta_tres_id ? data.certificado.empresa_treinamento_trinta_tres_id : "";
                    this.form.certificado.empresa_treinamento_trinta_cinco_id = data.certificado.empresa_treinamento_trinta_cinco_id ? data.certificado.empresa_treinamento_trinta_cinco_id : "";
                    this.form.certificado.instrutor_trinta_tres_id = data.certificado.instrutor_trinta_tres_id ? data.certificado.instrutor_trinta_tres_id : "";
                    this.form.certificado.instrutor_trinta_cinco_id = data.certificado.instrutor_trinta_cinco_id ? data.certificado.instrutor_trinta_cinco_id : "";

                    this.editando = true;
                    this.form.listaVencimentos = data.listaVencimentos;
                    this.preload = false;

                })
                .catch(error => (this.preload = false));
        },

        salvar() {
            formReset();
            $('#janelaTreinamento :input:visible').trigger('blur');
            if ($('#janelaTreinamento :input:visible.is-invalid').length) {
                mostraErro('', 'Verifique os erros')
                return false;
            }

            this.preload = true;

            axios.post(`${URL_ADMIN}/certificado`, this.form)
                .then(response => {
                    if (response.status === 201) {
                        this.preload = false;
                        this.cadastrado = true;
                        this.atualizar();
                    }
                }).catch(error => (this.preload = false));
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

        resetaCampoCliente() {
            if (this.controle.dados.autocomplete_label_cliente_anterior !== this.controle.dados.autocomplete_label_cliente) {
                this.controle.dados.autocomplete_label_cliente_anterior = '';
                this.controle.dados.autocomplete_label_cliente = '';
                this.controle.dados.campoCliente = '';
            }
        },

        selecionaCliente(obj) {
            this.controle.dados.campoCliente = obj.id;
            this.controle.dados.autocomplete_label_cliente = obj.label;
            this.controle.dados.autocomplete_label_cliente_anterior = obj.label;
        },

        validaData() {
            if (this.form.data_aso.length >= 10) {
                let dataCorreta = moment(this.form.data_aso, "DD/MM/YYYY");
                if (!dataCorreta.isValid()) {
                    mostraErro('', 'A data do ASO inserida é inválida');
                    this.form.data_aso = '';
                }
            }
        },

        listaVagas() {
            this.preload = true;
            axios.get(`${URL_PUBLICO}/lista-vagas`)
                .then((response) => {
                    this.preload = false;
                    this.vagas = response.data.vagas;
                })
                .catch((error) => {
                    this.preload = false;
                });
        },

        listaAreasGeral() {
            this.preload = true;
            axios.get(`${URL_PUBLICO}/lista-areas`)
                .then((response) => {
                    this.preload = false;
                    this.listaAreas = response.data.areas;
                })
                .catch((error) => {
                    this.preload = false;
                });
        },

        janelaConfirmar(id) {
            this.form.id = id;
            this.apagado = false;

            this.preload = false;
        },

        carregou(dados) {
            this.lista = dados.itens;
            this.selecionaTudo = this.tudoMarcado;
            // this.vencimentos = dados.vencimentos;
            this.controle.carregando = false;
            this.controle.dados.intervalo = dados.intervalo;
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

import datepicker from "../../../components/DatePicker";

const app = new Vue({
    el: '#app',
    components: {
        datepicker,
    },
    data: {
        tituloJanela: 'Planejamento - Requisição de Vaga',
        preload: false,
        editando: false,
        apagado: false,
        cadastrado: false,
        cadastrando: false,
        atualizado: false,
        visualizar: false,

        hash: `mastertag_${parseInt((Math.random() * 999999))}`,

        todos_municipios: `autocomplete/todos-municipios`,

        cliente_id: '',

        preloadForm: true,

        colunasTabela: {
            cliente: false,
        },

        URL_ADMIN,
        selecionados: [],
        selecionaTudo: false,

        form: {
            id: '',
            centro_custo_id: '',

            cliente_id: '',
            autocomplete_label_cliente_modal: '',
            autocomplete_label_cliente_modal_anterior: '',

            cargo_id: '',
            autocomplete_label_cargo_modal: '',
            autocomplete_label_cargo_modal_anterior: '',

            area_id: '',
            quantidade: '',
            tipo_contratacao: '',
            prioridade: '',
            imediata: false,
            previsao_inicio: '',
            solicitante: '',
            observacao: '',

            outras_informacoes: {
                posicao: '',
                processo: '',
                contrato: '',
                local_trabalho: '',
                horario: '',
                gestor: '',
                ppra: '',
            }
        },

        formDefault: null,

        lista: [],
        vagas: [],
        opened: [],
        areas_etiquetas: [],
        centro_custos: [],

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
                campoBusca: '',
                campoVaga: '',
                campoCliente: '',
                campoFiltro: '',
                campoStatus: '',

                cliente_custom: '',
                filtroPeriodo: false,
                periodo: '',
            },
        },
    },

    mounted() {
        this.formDefault = _.cloneDeep(this.form) //copia
        this.usuarioAutenticado();
        this.listaVagas();
        setTimeout(() => {
            this.atualizar();
        }, 200)
    },

    methods: {
        /***Campos de Filtros ****/
        resetaCampo() {
            if (this.controle.dados.autocomplete_label_anterior !== this.controle.dados.autocomplete_label) {
                this.controle.dados.autocomplete_label_anterior = '';
                this.controle.dados.autocomplete_label = '';
                this.controle.dados.campoVaga = '';
                this.$refs.componente.buscar();
            }
        },
        selecionaVaga(obj) {
            this.controle.dados.campoVaga = obj.id;
            this.controle.dados.autocomplete_label = obj.label;
            this.controle.dados.autocomplete_label_anterior = obj.label;
            this.controle.carregando = true;
            setTimeout(() => {
                this.$refs.componente.buscar();
            }, 600);
        },
        resetaCampoCliente() {
            if (this.controle.dados.autocomplete_label_cliente_anterior !== this.controle.dados.autocomplete_label_cliente) {
                this.controle.dados.autocomplete_label_cliente_anterior = '';
                this.controle.dados.autocomplete_label_cliente = '';
                this.controle.dados.campoCliente = '';
                this.$refs.componente.buscar();
            }
        },
        selecionaCliente(obj) {
            this.controle.dados.campoCliente = obj.id;
            this.controle.dados.autocomplete_label_cliente = obj.label;
            this.controle.dados.autocomplete_label_cliente_anterior = obj.label;
            this.controle.carregando = true;
            setTimeout(() => {
                this.$refs.componente.buscar();
            }, 600);

        },


        selecionaVagaModal(obj) {
            this.form.cargo_id = obj.id;
            this.form.autocomplete_label_cargo_modal = obj.label;
            this.form.autocomplete_label_cargo_modal_anterior = obj.label;
        },

        resetaCampoVagaModal() {
            if (this.form.autocomplete_label_cargo_modal_anterior !== this.form.autocomplete_label_cargo_modal) {
                this.form.autocomplete_label_cargo_modal_anterior = '';
                this.form.autocomplete_label_cargo_modal = '';
                this.form.cargo_id = '';

                setTimeout(() => {
                    if (this.form.cargo_id === '') {
                        valida_campo_vazio($('#vaga_modal_' + this.hash), 1);
                        $('#janelaCadastrar #vaga_modal_' + this.hash).focus().trigger('blur');
                        mostraErro('Erro', 'O Campo CARGO não pode ficar vazio');
                    }
                }, 100);
            }
        },
        selecionaClienteModal(obj) {
            this.form.cliente_id = obj.id;
            this.form.autocomplete_label_cliente_modal = obj.label;
            this.form.autocomplete_label_cliente_modal_anterior = obj.label;
            this.listaCentroCusto();
        },
        resetaCampoClienteModal() {
            if (this.form.autocomplete_label_cliente_modal_anterior !== this.form.autocomplete_label_cliente_modal) {
                this.form.autocomplete_label_cliente_modal_anterior = '';
                this.form.autocomplete_label_cliente_modal = '';
                this.form.cliente_id = '';
                this.listaCentroCusto();
                setTimeout(() => {
                    if (this.form.cliente_id === '') {
                        valida_campo_vazio($('#cliente_modal_' + this.hash), 1);
                        $('#janelaCadastrar #cliente_modal_' + this.hash).focus().trigger('blur');
                        mostraErro('Erro', 'O Campo Cliente não pode ficar vazio');
                    }
                }, 100);
            }
        },

        formNovo() {
            this.cadastrado = false;
            this.atualizado = false;
            this.editando = false;

            this.tituloJanela = "Solicitando Vaga";

            formReset();
            setupCampo();

            this.form = _.cloneDeep(this.formDefault) //copia
            this.leitura = false;
            this.form.cliente_id = this.cliente_id === 0 ? this.form.cliente_id : this.cliente_id;

            this.listaAreasEtiquetas();
            this.listaCentroCusto();

        },

        formOpen(id) {
            this.listaAreasEtiquetas();
            Object.assign(this.form, this.formDefault);
            this.form.id = id;
            this.cadastrado = false;
            this.atualizado = false;
            this.cadastrando = false;
            this.visualizar = false;
            this.editando = false;


            this.tituloJanela = `#${id}`;

            formReset();

            this.preload = true;

            axios.get(`${URL_ADMIN}/planejamento/requisicao-vaga/${id}/editar`)
                .then(response => {
                    let data = response.data;
                    Object.assign(this.form, data);

                    this.form.cliente_id = this.cliente_id === 0 ? this.form.cliente_id : this.cliente_id;
                    this.listaCentroCusto();

                    this.tituloJanela = `#${id} Planejamento - Requisição de vagas`;
                    this.cadastrando = true;
                    this.preload = false;
                })
                .catch(error => {
                    this.preload = false;
                })
        },

        cadastrar() {

            if (this.form.cargo_id === '') {
                valida_campo_vazio($('#vaga_modal_' + this.hash), 1);
                $('#janelaCadastrar #vaga_modal_' + this.hash).focus().trigger('blur');
                mostraErro('', 'Campo CARGO não pode ficar vazio');
                this.resetaCampoVagaModal();

                $('#janelaCadastrar :input:visible').trigger('blur');
                if ($('#janelaCadastrar :input:visible.is-invalid').length) {
                    mostraErro('', 'Verifique os campos marcados')
                    return false;
                }

                return false;
            }

            if (this.form.cliente_id === '') {
                valida_campo_vazio($('#cliente_' + this.hash), 1);
                mostraErro('', 'Campo CLIENTE não pode ficar vazio');
                this.resetaCampoClienteModal();
                return false;
            }

            $('#janelaCadastrar :input:visible').trigger('blur');
            if ($('#janelaCadastrar :input:visible.is-invalid').length) {
                mostraErro('', 'Verifique os campos marcados')
                return false;
            }

            this.preload = true;

            axios.post(`${URL_ADMIN}/planejamento/requisicao-vaga/`, this.form)
                .then(response => {
                    let data = response.data;
                    mostraSucesso('', 'Solicitação registrada com sucesso!');
                    $('#janelaCadastrar').modal('hide');
                    this.$refs.componente.buscar();
                    this.preload = false;
                })
                .catch(error => {
                    this.preload = false;
                })
        },

        alterar() {

            this.form.cliente_id = this.cliente_id === 0 ? this.form.cliente_id : this.cliente_id;

            if (this.form.cargo_id === '') {
                valida_campo_vazio($('#vaga_modal_' + this.hash), 1);
                $('#janelaCadastrar #vaga_modal_' + this.hash).focus().trigger('blur');
                mostraErro('', 'Campo CARGO não pode ficar vazio');
                this.resetaCampoVagaModal();

                $('#janelaCadastrar :input:visible').trigger('blur');
                if ($('#janelaCadastrar :input:visible.is-invalid').length) {
                    mostraErro('', 'Verifique os campos marcados')
                    return false;
                }

                return false;
            }

            if (this.form.cliente_id === '') {
                valida_campo_vazio($('#cliente_' + this.hash), 1);
                mostraErro('', 'Campo CLIENTE não pode ficar vazio');
                this.resetaCampoClienteModal();
                return false;
            }

            $('#janelaCadastrar :input:visible').trigger('blur');
            if ($('#janelaCadastrar :input:visible.is-invalid').length) {
                mostraErro('', 'Verifique os campos marcados')
                return false;
            }

            this.preload = true;

            axios.put(`${URL_ADMIN}/planejamento/requisicao-vaga/${this.form.id}`, this.form)
                .then(response => {
                    let data = response.data;
                    mostraSucesso('', 'Solicitação alterada com sucesso!');
                    $('#janelaCadastrar').modal('hide');
                    this.$refs.componente.buscar();
                    this.preload = false;
                })
                .catch(error => {
                    this.preload = false;
                })
        },

        listaVagas() {
            axios.get(`${URL_PUBLICO}/lista-vagas`)
                .then(res => {
                    this.vagas = res.data.vagas;
                })
                .catch(error => {
                    this.preload = false;
                });
        },

        listaAreasEtiquetas() {
            axios.get(`${URL_PUBLICO}/lista-areas`)
                .then(res => {
                    this.areas_etiquetas = res.data.areas;
                })
                .catch(error => {
                    // this.preload = false;
                });
        },

        listaCentroCusto() {
            axios.post(`${URL_PUBLICO}/centro-custos/`, {'cliente_id': this.form.cliente_id})
                .then(res => {
                    this.centro_custos = res.data.centro_custos;
                    this.form.centro_custo_id = '';
                })
                .catch(error => {
                    this.preload = false;
                });
        },

        janelaConfirmar(id) {
            this.form.id = id;
            this.apagado = false;

            this.preload = false;
        },
        usuarioAutenticado() {
            this.controle.carregando = true;
            axios.get(`${URL_ADMIN}/usuario/autenticado/`)
                .then(response => {
                    let data = response.data;

                    this.cliente_id = data.cliente_id;

                    this.colunasTabela.cliente = this.cliente_id === 0;
                    this.controle.dados.campoCliente = this.cliente_id !== 0 ? this.cliente_id : this.controle.dados.campoCliente;
                })
                .catch(error => {
                    this.preload = false;
                })
        },
        carregou(dados) {
            this.lista = dados.itens;
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

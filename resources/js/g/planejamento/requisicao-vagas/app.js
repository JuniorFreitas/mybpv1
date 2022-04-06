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
        aprovando: false,


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

            empresa_id: '',

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
            status_aprovacao: '',

            outras_informacoes: {
                posicao: '',
                processo: '',
                contrato: '',
                local_trabalho: '',
                horario: '',
                gestor: '',
                ppra: '',

                salario: '',
                salario_valor: '',
                salario_valor_format: '',
                beneficio: '',
                beneficio_excecao: '',
                treinamento: '',
                treinamento_excecao: '',
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
                caminho_autocomplete: `autocomplete/cargos_ativos`,
                autocomplete_label_anterior: '',
                autocomplete_label: '',
                pages: 20,
                campoBusca: '',
                campoVaga: '',
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

        formNovo() {
            this.cadastrado = false;
            this.cadastrando = true;
            this.atualizado = false;
            this.editando = false;
            this.visualizar = false;

            this.tituloJanela = "Solicitando Vaga";

            formReset();
            setupCampo();

            this.form = _.cloneDeep(this.formDefault) //copia
            this.leitura = false;

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

                    this.listaCentroCusto();

                    this.tituloJanela = `#${id} Planejamento - Requisição de vagas`;
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

            if (this.form.cargo_id === '') {
                valida_campo_vazio($('#vaga_modal_' + this.hash), 1);
                $('#janelaCadastrar #vaga_modal_' + this.hash).focus().trigger('blur');
                mostraErro('', 'Campo CARGO não pode ficar vazio');
                this.resetaCampoVagaModal();

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


        aprovar() {

            $('#janelaCadastrar :input:visible').trigger('blur');
            if ($('#janelaCadastrar :input:visible.is-invalid').length) {
                mostraErro('', 'Verifique os campos marcados')
                return false;
            }

            this.preload = true;

            axios.put(`${URL_ADMIN}/planejamento/requisicao-vaga/${this.form.id}/aprovar`, this.form)
                .then(response => {
                    let data = response.data;
                    mostraSucesso('', 'Registro salvo com sucesso!');
                    $('#janelaCadastrar').modal('hide');
                    this.$refs.componente.buscar();
                    this.preload = false;
                })
                .catch(error => {
                    this.preload = false;
                })
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
            axios.post(`${URL_PUBLICO}/centro-custos/`, {'empresa_id': this.form.empresa_id})
                .then(res => {
                    this.centro_custos = res.data.centro_custos;
                    // this.form.centro_custo_id = '';
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

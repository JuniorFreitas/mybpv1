import datepicker from '../../../components/DatePicker'
import DateRangeFilter from '../../../components/DateRangeFilter.vue'
import ExportacaoMixin from '../../../mixins/Exportacoes'

const app = new Vue({
    el: '#app',
    components: {
        datepicker,
        DateRangeFilter
    },
    mixins: [ExportacaoMixin],
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
        aprovandoExtra: false,
        aprovaGestor: false,
        aprovaExtra: false,
        temAprovacaoExtra: false,
        nomeAprovacaoExtra: 'Aprovação Extra',
        aprovandoRh: false,
        aprovaRh: false,
        preloadExportacao: false,

        urlExportacao: `${URL_ADMIN}/planejamento/requisicao-vaga/export`,

        hash: `mastertag_${parseInt(Math.random() * 999999)}`,

        todos_municipios: `autocomplete/todos-municipios`,

        cliente_id: '',

        preloadForm: true,

        colunasTabela: {
            cliente: false
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

            aprovacao_extra_id: '',
            aprovacao_extra_nome: '',
            obs_aprovacao_extra: '',
            status_aprovacao_extra: '',
            data_aprovacao_extra: '',

            rh_aprovacao_id: '',
            rh_aprovacao: '',
            obs_rh: '',
            status_aprovacao_rh: '',
            data_aprovacao_rh: '',

            outras_informacoes: {
                posicao: '',
                processo: '',
                contrato: '',
                local_trabalho: '',
                horario: '',
                gestor: '',
                gestor_id: '',
                autocomplete_label_gestor: '',
                autocomplete_label_gestor_anterior: '',
                ppra: '',

                salario: '',
                salario_valor: '',
                salario_valor_format: '',
                beneficio: '',
                beneficio_excecao: '',
                treinamento: '',
                treinamento_excecao: ''
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
                dataInicio: '',
                dataFim: '',
                ordenacao: 'created_at_desc'
            }
        }
    },

    computed: {
        paramsExport() {
            return {
                campoBusca: this.controle.dados.campoBusca,
                campoStatus: this.controle.dados.campoStatus,
                filtroPeriodo: this.controle.dados.filtroPeriodo,
                dataInicio: this.controle.dados.dataInicio,
                dataFim: this.controle.dados.dataFim,
                periodo: this.controle.dados.periodo,
                ordenacao: this.controle.dados.ordenacao
            }
        }
    },

    mounted() {
        this.formDefault = _.cloneDeep(this.form) //copia
        this.usuarioAutenticado()
        setTimeout(() => {
            this.atualizar()
        }, 200)
    },

    methods: {
        /***Campos de Filtros ****/
        resetaCampo() {
            if (this.controle.dados.autocomplete_label_anterior !== this.controle.dados.autocomplete_label) {
                this.controle.dados.autocomplete_label_anterior = ''
                this.controle.dados.autocomplete_label = ''
                this.controle.dados.campoVaga = ''
                this.$refs.componente.buscar()
            }
        },
        selecionaVaga(obj) {
            this.controle.dados.campoVaga = obj.id
            this.controle.dados.autocomplete_label = obj.label
            this.controle.dados.autocomplete_label_anterior = obj.label
            this.controle.carregando = true
            setTimeout(() => {
                this.$refs.componente.buscar()
            }, 600)
        },

        selecionaVagaModal(obj) {
            this.form.cargo_id = obj.id
            this.form.autocomplete_label_cargo_modal = obj.label
            this.form.autocomplete_label_cargo_modal_anterior = obj.label
        },

        resetaCampoVagaModal() {
            if (this.form.autocomplete_label_cargo_modal_anterior !== this.form.autocomplete_label_cargo_modal) {
                this.form.autocomplete_label_cargo_modal_anterior = ''
                this.form.autocomplete_label_cargo_modal = ''
                this.form.cargo_id = ''

                setTimeout(() => {
                    if (this.form.cargo_id === '') {
                        valida_campo_vazio($('#vaga_modal_' + this.hash), 1)
                        $('#janelaCadastrar #vaga_modal_' + this.hash)
                            .focus()
                            .trigger('blur')
                        mostraErro('Erro', 'O Campo CARGO não pode ficar vazio')
                    }
                }, 100)
            }
        },

        selecionaGestor(obj) {
            this.form.outras_informacoes.gestor_id = obj.id
            this.form.outras_informacoes.autocomplete_label_gestor = obj.label
            this.form.outras_informacoes.autocomplete_label_gestor_anterior = obj.label
        },

        resetaCampoGestor() {
            if (this.form.outras_informacoes.autocomplete_label_gestor_anterior !== this.form.outras_informacoes.autocomplete_label_gestor) {
                this.form.outras_informacoes.autocomplete_label_gestor_anterior = ''
                this.form.outras_informacoes.autocomplete_label_gestor = ''
                this.form.outras_informacoes.gestor_id = ''

                setTimeout(() => {
                    if (this.form.outras_informacoes.gestor_id === '') {
                        valida_campo_vazio($('#gestor_' + this.hash), 1)
                        $('#janelaCadastrar #gestor_' + this.hash)
                            .focus()
                            .trigger('blur')
                        mostraErro('Erro', 'O Campo GESTOR DA VAGA não pode ficar vazio')
                    }
                }, 100)
            }
        },

        formNovo() {
            // Nova solicitação: só cadastrando ativo; demais flags zeradas
            this.cadastrando = true
            this.aprovando = false
            this.aprovandoExtra = false
            this.aprovandoRh = false
            this.editando = false
            this.visualizar = false
            this.cadastrado = false
            this.atualizado = false

            this.tituloJanela = 'Solicitando Vaga'

            formReset()
            setupCampo()

            this.form = _.cloneDeep(this.formDefault) //copia
            this.leitura = false

            this.listaAreasEtiquetas()
            this.listaCentroCusto()
        },

        formOpen(id) {
            // Resetar sempre todas as flags da modal; o @click do dropdown define só a ação desejada
            this.cadastrando = false
            this.aprovando = false
            this.aprovandoExtra = false
            this.aprovandoRh = false
            this.editando = false
            this.visualizar = false
            this.cadastrado = false
            this.atualizado = false

            this.listaAreasEtiquetas()
            Object.assign(this.form, this.formDefault)
            this.form.id = id

            this.tituloJanela = `#${id}`

            formReset()

            this.preload = true

            axios
                .get(`${URL_ADMIN}/planejamento/requisicao-vaga/${id}/editar`)
                .then((response) => {
                    let data = response.data
                    Object.assign(this.form, data)

                    this.listaCentroCusto()

                    this.tituloJanela = `#${id} Planejamento - Requisição de vagas`
                    this.preload = false
                })
                .catch((error) => {
                    this.preload = false
                })
        },

        cadastrar() {
            if (this.form.cargo_id === '') {
                valida_campo_vazio($('#vaga_modal_' + this.hash), 1)
                $('#janelaCadastrar #vaga_modal_' + this.hash)
                    .focus()
                    .trigger('blur')
                mostraErro('', 'Campo CARGO não pode ficar vazio')
                this.resetaCampoVagaModal()
                return false
            }

            $('#janelaCadastrar :input:visible').trigger('blur')
            if ($('#janelaCadastrar :input:visible.is-invalid').length) {
                mostraErro('', 'Verifique os campos marcados')
                return false
            }

            this.preload = true

            axios
                .post(`${URL_ADMIN}/planejamento/requisicao-vaga/`, this.form)
                .then((response) => {
                    let data = response.data
                    mostraSucesso('', 'Solicitação registrada com sucesso!')
                    $('#janelaCadastrar').modal('hide')
                    this.$refs.componente.buscar()
                    this.preload = false
                })
                .catch((error) => {
                    this.preload = false
                })
        },

        alterar() {
            if (this.form.cargo_id === '') {
                valida_campo_vazio($('#vaga_modal_' + this.hash), 1)
                $('#janelaCadastrar #vaga_modal_' + this.hash)
                    .focus()
                    .trigger('blur')
                mostraErro('', 'Campo CARGO não pode ficar vazio')
                this.resetaCampoVagaModal()

                return false
            }

            $('#janelaCadastrar :input:visible').trigger('blur')
            if ($('#janelaCadastrar :input:visible.is-invalid').length) {
                mostraErro('', 'Verifique os campos marcados')
                return false
            }

            this.preload = true

            axios
                .put(`${URL_ADMIN}/planejamento/requisicao-vaga/${this.form.id}`, this.form)
                .then((response) => {
                    let data = response.data
                    mostraSucesso('', 'Solicitação alterada com sucesso!')
                    $('#janelaCadastrar').modal('hide')
                    this.$refs.componente.buscar()
                    this.preload = false
                })
                .catch((error) => {
                    this.preload = false
                })
        },

        aprovar() {
            $('#janelaCadastrar :input:visible').trigger('blur')
            if ($('#janelaCadastrar :input:visible.is-invalid').length) {
                mostraErro('', 'Verifique os campos marcados')
                return false
            }

            this.preload = true

            axios
                .put(`${URL_ADMIN}/planejamento/requisicao-vaga/${this.form.id}/aprovar`, this.form)
                .then((response) => {
                    let data = response.data
                    mostraSucesso('', 'Registro salvo com sucesso!')
                    $('#janelaCadastrar').modal('hide')
                    this.$refs.componente.buscar()
                    this.preload = false
                })
                .catch((error) => {
                    this.preload = false
                })
        },

        aprovarExtra() {
            $('#janelaCadastrar :input:visible').trigger('blur')
            if ($('#janelaCadastrar :input:visible.is-invalid').length) {
                mostraErro('', 'Verifique os campos marcados')
                return false
            }

            this.preload = true

            axios
                .put(`${URL_ADMIN}/planejamento/requisicao-vaga/${this.form.id}/aprovarextra`, this.form)
                .then((response) => {
                    let data = response.data
                    mostraSucesso('', 'Registro salvo com sucesso!')
                    $('#janelaCadastrar').modal('hide')
                    this.$refs.componente.buscar()
                    this.preload = false
                })
                .catch((error) => {
                    this.preload = false
                })
        },

        aprovarRh() {
            $('#janelaCadastrar :input:visible').trigger('blur')
            if ($('#janelaCadastrar :input:visible.is-invalid').length) {
                mostraErro('', 'Verifique os campos marcados')
                return false
            }

            this.preload = true

            const payload = {
                id: this.form.id,
                obs_rh: this.form.obs_rh || null,
                status_aprovacao_rh: this.form.status_aprovacao_rh || ''
            }
            axios
                .put(`${URL_ADMIN}/planejamento/requisicao-vaga/${this.form.id}/aprovarrh`, payload)
                .then((response) => {
                    mostraSucesso('', 'Registro salvo com sucesso!')
                    $('#janelaCadastrar').modal('hide')
                    if (this.$refs.componente && typeof this.$refs.componente.buscar === 'function') {
                        this.$refs.componente.buscar()
                    }
                    this.preload = false
                })
                .catch((error) => {
                    this.preload = false
                    const msg = (error.response && error.response.data && error.response.data.msg) ? error.response.data.msg : 'Houve um erro ao aprovar. Tente novamente.'
                    mostraErro('', msg)
                })
        },

        listaAreasEtiquetas() {
            axios
                .get(`${URL_PUBLICO}/lista-areas`)
                .then((res) => {
                    this.areas_etiquetas = res.data.areas
                })
                .catch((error) => {
                    // this.preload = false;
                })
        },

        listaCentroCusto() {
            axios
                .post(`${URL_PUBLICO}/centro-custos/`, { empresa_id: this.form.empresa_id })
                .then((res) => {
                    this.centro_custos = res.data.centro_custos
                    // this.form.centro_custo_id = '';
                })
                .catch((error) => {
                    this.preload = false
                })
        },

        janelaConfirmar(id) {
            this.form.id = id
            this.apagado = false

            this.preload = false
        },
        usuarioAutenticado() {
            this.controle.carregando = true
            axios
                .get(`${URL_ADMIN}/usuario/autenticado/`)
                .then((response) => {
                    let data = response.data

                    this.cliente_id = data.cliente_id

                    this.colunasTabela.cliente = this.cliente_id === 0
                    this.controle.dados.campoCliente = this.cliente_id !== 0 ? this.cliente_id : this.controle.dados.campoCliente
                })
                .catch((error) => {
                    this.preload = false
                })
        },
        carregou(dados) {
            this.lista = dados.itens
            this.aprovaGestor = dados.aprovar_por_gestor || false
            this.aprovaExtra = dados.pode_aprovar_extra || false
            this.temAprovacaoExtra = dados.tem_aprovacao_extra || false
            this.nomeAprovacaoExtra = dados.nome_aprovacao_extra || 'Aprovação Extra'
            this.aprovaRh = dados.aprovar_por_rh || false
            this.controle.carregando = false
        },
        carregando() {
            this.controle.carregando = true
        },
        atualizar() {
            this.$refs.componente.atual = 1
            this.$refs.componente.buscar()
        },
        formatarDataHoraBR(data) {
            if (!data) return ''
            const date = new Date(data)
            const dataBR = date.toLocaleDateString('pt-BR')
            const horaBR = date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
            return `${dataBR} ${horaBR}`
        }
    }
})

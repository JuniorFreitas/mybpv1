import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import endereco from '../../../components/Endereco'
import datepicker from '../../../components/DatePicker'
import DadosPessoais from '../../../components/entrevistas/DadosPessoaisTexto'
import ExportacaoMixin from '../../../mixins/Exportacoes'
import Utils from '../../../mixins/Utils'
const app = createApp({
    mixins: [ExportacaoMixin, Utils],
    components: {
        endereco,
        datepicker,
        DadosPessoais
    },
    data() {
        return {
            tituloJanela: 'Parecer Entrevista RH',
            preload: false,
            editando: false,
            apagado: false,
            cadastrado: false,
            cadastrando: false,
            atualizado: false,
            visualizar: false,
            preloadExportacao: false,

            urlExportacao: `${URL_ADMIN}/entrevistas/parecer-rota/export`,

            hash: `mastertag_${parseInt(Math.random() * 999999)}`,

            todos_municipios: `autocomplete/todos-municipios`,

            cliente_id: '',

            URL_ADMIN,
            selecionados: [],
            selecionaTudo: false,

            colunasTabela: {
                pcd: true,
                cliente: false,
                parecer_rh: true,
                tecnica_nota: false,
                teste_pratico_nota: false
            },

            form: {
                id: '',

                vaga_id: '',
                autocomplete_label_vaga_modal: '',
                autocomplete_label_vaga_modal_anterior: '',

                cliente_id: '',
                autocomplete_label_cliente_modal: '',
                autocomplete_label_cliente_modal_anterior: '',

                curriculo: {
                    nome: '',
                    nascimento: '',
                    municipio_id: '',
                    autocomplete_label_municipio_modal: '',
                    autocomplete_label_municipio_modal_anterior: ''
                },

                parecer_rota: {
                    feedback_id: '',
                    curriculo_id: '',
                    tem_rota: '',
                    qual: '',
                    pega_onibus: '',
                    pega_onibus_qual_ponto: '',
                    vale_transporte: '',
                    rota_disponivel_turno_a: '',
                    rota_disponivel_turno_b: '',
                    rota_disponivel_turno_c: '',
                    rota_disponivel_turno_o: '',
                    rota_disponivel_outros: '',
                    rota_atende: '',
                    rota_tipo: '',
                    quem_entrevistou: '',
                    bairro_rota: '',
                    ponto_referencia_rota: '',
                    bairro_residencia: '',
                    ponto_referencia_residencia: '',
                    observacao: ''
                }
            },

            formDefault: null,

            lista: [],
            vagas: [],
            opened: [],

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
                    campoLido: '',
                    campoFiltro: '',
                    campoPcd: '',
                    campoRota: '',
                    campoCliente: '',
                    campoUf: '',

                    filtroPeriodo: false,
                    periodo: ''
                }
            }
        }
    },
    computed: {
        comRota() {
            return this.lista.filter((item) => {
                return item.parecer_rota
            })
        },
        paramsExport() {
            let dados = this.controle.dados
            dados.selecionados = this.selecionados
            return dados
        },
        tudoMarcado() {
            let totalItens = this.comRota.length
            let totalEncontrado = 0

            if (totalItens === 0) {
                return false
            }

            this.comRota.forEach((item) => {
                let id = item.curriculo_id
                if (this.selecionados.indexOf(id) >= 0) {
                    totalEncontrado++
                    //faz nada
                } else {
                    return false
                }
            })
            let resultado = totalItens === totalEncontrado ? true : false
            this.selecionaTudo = resultado
            return resultado
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
                this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
            }
        },
        selecionaVaga(obj) {
            this.controle.dados.campoVaga = obj.id
            this.controle.dados.autocomplete_label = obj.label
            this.controle.dados.autocomplete_label_anterior = obj.label
            this.controle.carregando = true
            setTimeout(() => {
                this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
            }, 600)
        },
        resetaCampoCliente() {
            if (this.controle.dados.autocomplete_label_cliente_anterior !== this.controle.dados.autocomplete_label_cliente) {
                this.controle.dados.autocomplete_label_cliente_anterior = ''
                this.controle.dados.autocomplete_label_cliente = ''
                this.controle.dados.campoCliente = ''
                this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
            }
        },
        selecionaCliente(obj) {
            this.controle.dados.campoCliente = obj.id
            this.controle.dados.autocomplete_label_cliente = obj.label
            this.controle.dados.autocomplete_label_cliente_anterior = obj.label
            this.controle.carregando = true
            this.controle.carregando = true
            setTimeout(() => {
                this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
            }, 600)
        },
        selecionaTodos() {
            this.selecionaTudo = !this.selecionaTudo
            if (this.selecionaTudo) {
                this.comRota.map((item) => {
                    let id = item.id
                    if (this.selecionados.indexOf(id) === -1) {
                        this.selecionados.push(id)
                    }
                })
            } else {
                this.comRota.map((item) => {
                    let id = item.id
                    let index = this.selecionados.indexOf(id)
                    if (index >= 0) {
                        this.selecionados.splice(index, 1)
                    }
                })
            }
        },

        formEntrevistar(id) {
            this.cadastrado = false
            this.atualizado = false
            this.cadastrando = false
            this.visualizar = false
            this.editando = false

            this.preload = true
            this.form = _.cloneDeep(this.formDefault)
            this.form.id = id

            formReset()
            axios
                .get(`${URL_ADMIN}/entrevistas/parecer-rota/${id}/editar`)
                .then((response) => {
                    let data = response.data
                    Object.assign(this.form, data)

                    //Se não tiver parecer_rota
                    this.form.parecer_rota = data.parecer_rota ? data.parecer_rota : _.cloneDeep(this.formDefault.parecer_rota)
                    this.form.parecer_rota.rota_tipo = lower(data.parecer_rh.tipo_entrevista)

                    this.tituloJanela = `#${data.feedback.id} Entrevista - ${data.feedback.curriculo.nome}`
                    this.cadastrando = true
                    this.preload = false
                })
                .catch((error) => {
                    this.preload = false
                })
        },

        cadastrar() {
            $('#janelaParecerEntrevista :input:visible').trigger('blur')
            if ($('#janelaParecerEntrevista :input:visible.is-invalid').length) {
                mostraErro('', 'Verifique os campos marcados')
                return false
            }

            this.preload = true

            this.form.parecer_rota.feedback_id = this.form.id
            this.form.parecer_rota.curriculo_id = this.form.curriculo_id

            axios
                .post(`${URL_ADMIN}/entrevistas/parecer-rota/`, this.form.parecer_rota)
                .then((response) => {
                    let data = response.data
                    mostraSucesso('', 'Entrevista salva com sucesso!')
                    this.$refs.janelaParecerEntrevista?.fecharModal()
                    this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
                    this.preload = false
                })
                .catch((error) => {
                    this.preload = false
                })
        },

        alterar() {
            $('#janelaParecerEntrevista :input:visible').trigger('blur')
            if ($('#janelaParecerEntrevista :input:visible.is-invalid').length) {
                mostraErro('', 'Verifique os campos marcados')
                return false
            }

            this.preload = true

            axios
                .put(`${URL_ADMIN}/entrevistas/parecer-rota/${this.form.parecer_rota.id}`, this.form.parecer_rota)
                .then((response) => {
                    let data = response.data
                    mostraSucesso('', 'Entrevista salva com sucesso!')
                    this.$refs.janelaParecerEntrevista?.fecharModal()
                    this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
                    this.preload = false
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
            this.selecionaTudo = this.tudoMarcado
            this.controle.carregando = false
        },
        carregando() {
            this.controle.carregando = true
        },
        atualizar() {
            this.$refs && this && this && this.$refs && this.$refs.componente && (this.$refs.componente.atual = 1)
            this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
        }
    }
})

registerGlobals(app)
app.mount('#app')

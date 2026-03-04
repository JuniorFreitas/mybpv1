import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import endereco from '../../../components/Endereco'
import datepicker from '../../../components/DatePicker'
import DadosPessoais from '../../../components/entrevistas/DadosPessoaisTexto'
import ExportacaoMixin from '../../../mixins/Exportacoes'

const app = createApp({
    mixins: [ExportacaoMixin],
    components: {
        endereco,
        datepicker,
        DadosPessoais
    },
    data() {
        return {
            tituloJanela: 'Parecer Entrevista Teste Prático',
            preload: false,
            editando: false,
            apagado: false,
            cadastrado: false,
            cadastrando: false,
            atualizado: false,
            visualizar: false,
            preloadExportacao: false,

            urlExportacao: `${URL_ADMIN}/entrevistas/parecer-teste-pratico/export`,
            hash: `mastertag_${parseInt(Math.random() * 999999)}`,

            todos_municipios: `autocomplete/todos-municipios`,

            cliente_id: '',

            URL_ADMIN,
            selecionados: [],
            selecionaTudo: false,

            colunasTabela: {
                pcd: false,
                cliente: false,
                parecer_rh: true,
                parecer_rota: false,
                tecnica: false
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

                parecer_teste: {
                    feedback_id: '',
                    tipo_entrevista: 'Fixo',
                    curriculo_id: '',
                    fez_teste: '',
                    data_horario_realizacao: '',
                    responsavel_pelo_teste: '',
                    qual_teste: '',
                    resultado_teste: '',
                    desempenho: '',
                    nota_teste: '',
                    parecer_final_teste: '',
                    quem_entrevistou: ''
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
        comTeste() {
            return this.lista.filter((item) => {
                return item.parecer_teste
            })
        },
        tudoMarcado() {
            let totalItens = this.comTeste.length
            let totalEncontrado = 0

            if (totalItens === 0) {
                return false
            }

            this.comTeste.forEach((item) => {
                let id = item.curriculo_id
                if (this.selecionados.indexOf(id) >= 0) {
                    totalEncontrado++
                    //faz nada
                } else {
                    return false
                }
            })
            let resultado = totalItens === totalEncontrado
            this.selecionaTudo = resultado
            return resultado
        }
    },
    mounted() {
        this.formDefault = _.cloneDeep(this.form) //copia
        this.usuarioAutenticado()
        this.listaVagas()
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
                this.comTeste.map((item) => {
                    let id = item.id
                    if (this.selecionados.indexOf(id) === -1) {
                        this.selecionados.push(id)
                    }
                })
            } else {
                this.comTeste.map((item) => {
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
                .get(`${URL_ADMIN}/entrevistas/parecer-teste-pratico/${id}/editar`)
                .then((response) => {
                    let data = response.data
                    Object.assign(this.form, data)

                    //Se não tiver parecer_teste
                    this.form.parecer_teste = data.parecer_teste ? data.parecer_teste : _.cloneDeep(this.formDefault.parecer_teste)

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

            this.form.parecer_teste.feedback_id = this.form.id
            this.form.parecer_teste.curriculo_id = this.form.curriculo_id

            axios
                .post(`${URL_ADMIN}/entrevistas/parecer-teste-pratico/`, this.form.parecer_teste)
                .then((response) => {
                    let data = response.data
                    mostraSucesso('', 'Entrevista salva com sucesso!')
                    $('#janelaParecerEntrevista').modal('hide')
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
                .put(`${URL_ADMIN}/entrevistas/parecer-teste-pratico/${this.form.parecer_teste.id}`, this.form.parecer_teste)
                .then((response) => {
                    let data = response.data
                    mostraSucesso('', 'Entrevista salva com sucesso!')
                    $('#janelaParecerEntrevista').modal('hide')
                    this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
                    this.preload = false
                })
                .catch((error) => {
                    this.preload = false
                })
        },

        listaVagas() {
            this.preload = true
            axios
                .get(`${URL_PUBLICO}/lista-vagas`)
                .then((res) => {
                    this.preload = false
                    this.vagas = res.data.vagas
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

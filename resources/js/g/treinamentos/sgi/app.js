import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import datepicker from '../../../components/DatePicker'
import Upload from '../../../components/Upload'

const app = createApp({
    components: {
        datepicker,
        Upload
    },
    data() {
        return {
            tituloJanela: 'Treinamentos',
            preload: false,
            editando: false,
            apagado: false,
            cadastrado: false,
            cadastrando: false,
            atualizado: false,
            visualizar: false,
            disabled: true,
            cliente_id: '',

            URL_ADMIN,

            hash: `mastertag_${parseInt(Math.random() * 999999)}`,

            selecionados: [],
            selecionaTudo: false,

            form: {
                cliente_id: '',
                treinamento_sgi_id: '',
                empresa_treinamento_id: '',
                data_inicio: '',
                data_fim: '',
                pessoas_evento: [],
                pessoas_eventoDelete: [],
                instrutores_evento: [],
                instrutores_eventoDelete: [],
                anexos: [],
                anexosDel: []
            },
            formDefault: null,

            url_anexo: `${URL_ADMIN}/storage/uploadAnexos`,
            anexoUploadAndamento: false,

            lista: [],
            listaTreinamentos: [],
            listaEmpresaTreinamentos: [],
            listaInstrutores: [],
            listaClientes: [],

            controle: {
                carregando: false,
                dados: {
                    caminho_autocomplete: `autocomplete/todas-vagas-ativas`,
                    pages: 20,
                    campo_dataInicio: '',
                    campo_dataFim: ''
                }
            }
        }
    },
    mounted() {
        this.formDefault = _.cloneDeep(this.form) //copia
        this.formEnviarDefault = _.cloneDeep(this.formEnviar) //copia
        this.atualizar()
    },
    computed: {
        treinamentoSelecionado() {
            return _.find(this.listaTreinamentos || [], { id: this.form.treinamento_sgi_id }) || null
        }
    },
    methods: {
        addLIInstrutor() {
            const obj = {}
            obj.novo = true
            obj.instrutor_id = ''

            this.form.instrutores_evento.push(obj)
        },
        removerLIInstrutor(index) {
            if (this.editando) {
                this.form.instrutores_eventoDelete.push(this.form.instrutores_evento[index].id)
            }
            this.form.instrutores_evento.splice(index, 1)
        },
        addLIPessoa() {
            const obj = {}
            obj.novo = true
            obj.cliente_id = ''
            obj.nome = ''
            obj.cpf = ''
            obj.email = ''
            obj.telefone = ''
            obj.nota = ''

            this.form.pessoas_evento.push(obj)
        },
        removerLIPessoa(index) {
            if (this.editando) {
                this.form.pessoas_eventoDelete.push(this.form.pessoas_evento[index].id)
            }
            this.form.pessoas_evento.splice(index, 1)
        },
        buscaCPF(cpf, index) {
            if (cpf.length === 14) {
                axios
                    .post(`${URL_ADMIN}/autocomplete/treinamento/buscaCPF`, { cpf: cpf })
                    .then((response) => {
                        let data = response.data
                        if (data !== 'zero') {
                            this.form.pessoas_evento[index].nome = data.nome
                            this.form.pessoas_evento[index].email = data.email
                            this.form.pessoas_evento[index].telefone = data.telefone
                        }
                    })
                    .catch((error) => {})
            }
        },

        formCadastrar() {
            this.form = _.cloneDeep(this.formDefault) //copia
            formReset()
            setupCampo()
        },
        formAlterar(id) {
            this.form.id = id

            this.atualizado = false
            this.cadastrando = false
            this.visualizar = false
            this.preload = true
            this.cadastrado = false
            this.form = _.cloneDeep(this.formDefault) //copia

            axios
                .get(`${URL_ADMIN}/1/treinamento/${id}/editar`)
                .then((response) => {
                    let data = response.data
                    Object.assign(this.form, response.data)
                    this.preload = false
                })
                .catch((error) => (this.preload = false))
        },

        salvar() {
            formReset()
            $('#janelaTreinamento :input:visible').trigger('blur')
            if ($('#janelaTreinamento :input:visible.is-invalid').length) {
                mostraErro('', 'Verifique os erros')
                return false
            }

            this.preload = true

            if (this.form.id) {
                //alterar
                axios
                    .put(`${URL_ADMIN}/1/treinamento/${this.form.id}`, this.form)
                    .then((response) => {
                        if (response.status === 201) {
                            this.preload = false
                            this.cadastrado = true
                            this.atualizar()
                        }
                    })
                    .catch((error) => (this.preload = false))
            } else {
                //criar
                axios
                    .post(`${URL_ADMIN}/1/treinamento`, this.form)
                    .then((response) => {
                        if (response.status === 201) {
                            this.preload = false
                            this.cadastrado = true
                            this.atualizar()
                        }
                    })
                    .catch((error) => (this.preload = false))
            }
        },

        //GERAL
        resetaCampo() {
            if (this.controle.dados.autocomplete_label_anterior != this.controle.dados.autocomplete_label) {
                this.controle.dados.autocomplete_label_anterior = ''
                this.controle.dados.autocomplete_label = ''
                this.controle.dados.campoVaga = ''
            }
        },

        selecionaVaga(obj) {
            this.controle.dados.campoVaga = obj.id
            this.controle.dados.autocomplete_label = obj.label
            this.controle.dados.autocomplete_label_anterior = obj.label
        },

        resetaCampoCliente() {
            if (this.controle.dados.autocomplete_label_cliente_anterior != this.controle.dados.autocomplete_label_cliente) {
                this.controle.dados.autocomplete_label_cliente_anterior = ''
                this.controle.dados.autocomplete_label_cliente = ''
                this.controle.dados.campoCliente = ''
            }
        },

        selecionaCliente(obj) {
            this.controle.dados.campoCliente = obj.id
            this.controle.dados.autocomplete_label_cliente = obj.label
            this.controle.dados.autocomplete_label_cliente_anterior = obj.label
        },

        carregou(dados) {
            this.lista = dados.itens
            this.cliente_id = dados.cliente_id
            this.listaTreinamentos = dados.listaTreinamentos
            this.listaEmpresaTreinamentos = dados.listaEmpresasTreinamentos
            this.listaInstrutores = dados.listaInstrutores
            this.listaClientes = dados.listaClientes
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

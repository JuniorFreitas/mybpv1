import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import preload from '../../../components/preload'
import Driver from 'driver.js' // import driver.js
import optionDriver from '../../../components/optionDriver'

const app = createApp({
    data() {
        return {
            tituloJanela: 'Cadastrando',
            preloadAjax: false,
            editando: false,

            cadastrado: false,
            atualizado: false,
            apagado: false,
            campoDesc: null,
            descricao: '',
            ativo: true,
            rubricagrupo: '',

            listaAtual: [],
            listaAnterior: [],

            lista: [],
            controle: {
                carregando: false,
                dados: {}
            }
        }
    },
    computed: {},
    components: {
        preload
    },
    mounted() {
        this.atualizar()
        this.guide()
    },
    methods: {
        guide() {
            const driver = new Driver(optionDriver)
            // Define os passos de introduções
            driver.defineSteps([
                {
                    element: '#campoBusca',
                    popover: {
                        title: 'Campo de busca',
                        description: 'Digitando qualquer aqui...',
                        position: 'left'
                    }
                },
                {
                    element: '#btCadastrar',
                    popover: {
                        title: 'Cadastrar',
                        description: 'Exibe formulário para cadastramento de uma nova classificação',
                        position: 'right'
                    }
                }
            ])
            // Inicia Introdução
            driver.start()
        },

        formNovo: function () {
            $('#form')[0].reset()
            this.cadastrado = false
            this.atualizado = false
            this.editando = false
            this.campoDesc = null
            this.descricao = ''
            this.ativo = true
            this.listaAtual = []
            this.listaAnterior = []
            this.tituloJanela = 'Cadastrando'
            formReset()
            setupCampo()
            this.$nextTick(() => this.$refs.janelaCadastrar?.abrirModal())
        },
        cadastrar: function () {
            $('#janelaCadastrar :input:visible:enabled').trigger('blur')
            if ($('#janelaCadastrar :input:visible:enabled.is-invalid').length) {
                alert('Verificar os erros')
                return false
            }
            let dados = {}
            dados.descricao = this.descricao
            dados.ativo = this.ativo

            this.preloadAjax = true
            axios
                .post(`${URL_ADMIN}/classificacao-plano-conta/`, dados)
                .then((response) => {
                    this.preloadAjax = false
                    this.cadastrado = true
                    this.atualizar()
                })
                .catch((error) => {
                    this.preloadAjax = false
                })
        },
        formAlterar: function (id) {
            app.id = id
            this.cadastrado = false
            this.atualizado = false
            this.editando = false
            this.tituloJanela = 'Alterando'

            this.preloadAjax = true
            formReset()

            axios
                .get(`${URL_ADMIN}/classificacao-plano-conta/${id}/editar`)
                .then((response) => {
                    this.descricao = response.data.descricao
                    this.ativo = response.data.ativo
                    this.editando = true
                    this.preloadAjax = false
                    setupCampo()
                    this.$nextTick(() => this.$refs.janelaCadastrar?.abrirModal())
                })
                .catch((data) => {
                    this.preloadAjax = false
                })
        },
        alterar: function () {
            $('#janelaCadastrar :input:visible:enabled').trigger('blur')
            if ($('#janelaCadastrar :input:visible:enabled.is-invalid').length) {
                alert('Verificar os erros')
                return false
            }

            let dados = {}
            dados.descricao = this.descricao
            dados.ativo = this.ativo
            this.preloadAjax = true

            axios
                .put(`${URL_ADMIN}/classificacao-plano-conta/${this.id}`, dados)
                .then((data) => {
                    this.preloadAjax = false
                    this.atualizado = true
                    this.atualizar()
                })
                .catch((data) => {
                    this.preloadAjax = false
                })
        },
        janelaConfirmar: function (id) {
            this.id = id
            this.apagado = false

            this.preloadAjax = false
            this.$nextTick(() => this.$refs.janelaConfirmar?.abrirModal())
        },
        apagar: function () {
            this.preloadAjax = true

            axios
                .delete(`${URL_ADMIN}/classificacao-plano-conta/${this.id}`, null)
                .then((data) => {
                    this.preloadAjax = false
                    this.apagado = true
                    this.atualizar()
                })
                .catch((data) => {
                    this.preloadAjax = false
                })
        },

        carregou: function (dados) {
            this.lista = dados
            this.controle.carregando = false
        },
        carregando: function () {
            this.controle.carregando = true
        },
        atualizar() {
            this.$refs.paginacao.atual = 1
            this.$refs.paginacao.buscar()
        }
    }
})

registerGlobals(app)
app.mount('#app')

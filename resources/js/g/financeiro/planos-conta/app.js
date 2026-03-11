import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import preload from '../../../components/preload'

const app = createApp({
    data() {
        return {
            tituloJanela: 'Cadastrando plano de conta',
            preloadAjax: false,
            editando: false,

            campoDesc: null,
            descricao: '',
            categoria_plano_id: 0,
            operacao: '',
            ativo: true,

            cadastrado: false,
            atualizado: false,
            apagado: false,

            lista: [],

            controle: {
                carregando: false,
                dados: {
                    campoBusca: ''
                }
            }
        }
    },
    components: {
        preload
    },
    mounted() {
        this.atualizar()
    },
    methods: {
        formNovo: function () {
            $('#form')[0].reset()
            this.cadastrado = false
            this.atualizado = false
            this.editando = false
            this.tituloJanela = 'Cadastrando plano de conta'
            this.campoDesc = null
            this.descricao = ''
            this.categoria_plano_id = 0
            this.operacao = ''
            this.ativo = true
            $('#categorias').val(0).trigger('change')
            formReset()
            setupCampo()
        },
        cadastrar: function () {
            $('#janelaCadastrar :input:visible:enabled').trigger('blur')
            if ($('#janelaCadastrar :input:visible:enabled.is-invalid').length) {
                alert('Verificar os erros')
                return false
            }
            let dados = {}
            dados.categoria_plano_id = this.categoria_plano_id
            dados.descricao = this.descricao
            dados.operacao = this.operacao
            dados.ativo = this.ativo

            this.preloadAjax = true
            axios
                .post(URL_ADMIN + '/plano-conta', dados)
                .then((data) => {
                    this.preloadAjax = false
                    this.cadastrado = true
                    this.atualizar()
                })
                .catch((data) => {
                    this.preloadAjax = false
                })
        },
        formAlterar: function (id) {
            this.id = id

            this.cadastrado = false
            this.atualizado = false
            this.editando = false
            this.tituloJanela = 'Alterando plano de conta'
            $('#categorias').val(0).trigger('change')

            this.preloadAjax = true

            formReset()
            axios
                .get(`${URL_ADMIN}/plano-conta/${id}/editar`)
                .then((response) => {
                    let data = response.data
                    $('#categorias').trigger('change')
                    this.categoria_plano_id = data.categoria_plano_id
                    this.descricao = data.descricao
                    this.ativo = data.ativo
                    this.operacao = data.operacao

                    this.editando = true
                    this.preloadAjax = false
                    setupCampo()
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
            dados.categoria_plano_id = this.categoria_plano_id
            dados.descricao = this.descricao
            dados.operacao = this.operacao
            dados.ativo = this.ativo

            this.preloadAjax = true

            axios
                .put(`${URL_ADMIN}/plano-conta/${this.id}`, dados)
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
        },
        apagar: function () {
            this.preloadAjax = true

            axios
                .delete(`${URL_ADMIN}/plano-conta/${this.id}`, null)
                .then((response) => {
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

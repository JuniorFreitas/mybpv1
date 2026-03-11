import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'

const app = createApp({
    data() {
        return {
            tituloJanela: 'Cadastrando encargo',
            preloadAjax: false,
            editando: false,
            id: 0,

            cadastrado: false,
            atualizado: false,
            urlAjax: '',
            apagado: false,

            erros: [],

            lista: [],
            dados: {},
            controle: {
                carregando: false,
                dados: {}
            }
        }
    },
    methods: {
        formNovo: function () {
            $('#form')[0].reset()
            this.cadastrado = false
            this.atualizado = false
            this.editando = false
            this.tituloJanela = 'Cadastrando encargo'
            formReset()
        },
        cadastrar: function () {
            $('#janelaCadastrar :input:visible').trigger('blur')
            if ($('#janelaCadastrar :input:visible.is-invalid').length) {
                alert('Verificar os erros')
                return false
            }
            this.erros = []
            var dados = {}
            dados.nome = $('#nome').val()
            dados.descricao = $('#descricao').val()
            dados.periodicidade = $('#periodicidade').val()
            dados.tipo = $('#tipo_form').val()
            dados.ativo = $('#ativo').val()

            this.preloadAjax = true

            $.post(URL_ADMIN + '/encargos', dados)
                .done((data) => {
                    app.preloadAjax = false
                    app.cadastrado = true
                    $('#controle button:eq(0)').click()
                })
                .fail((data) => {
                    app.preloadAjax = false
                    //app.erros = data.responseJSON.erros;
                    //alert(data.responseJSON.msg);
                    mostraErro(data.responseJSON)
                })
        },
        formAlterar: function (id) {
            app.id = id

            this.cadastrado = false
            this.atualizado = false
            this.editando = false
            this.tituloJanela = 'Alterando encargo'

            this.erros = []
            this.preloadAjax = true
            formReset()

            $.get(URL_ADMIN + '/encargos/' + id + '/editar')
                .done((data) => {
                    $('#nome').val(data.nome)
                    $('#descricao').val(data.descricao)
                    $('#periodicidade').val(data.periodicidade)
                    $('#tipo_form').val(data.tipo)
                    $('#ativo').val(data.ativo.toString())
                    app.editando = true
                    app.preloadAjax = false
                })
                .fail((data) => {
                    app.preloadAjax = false
                    app.erros = data.responseJSON.erros
                    mostraErro(data.responseJSON)
                })
        },
        alterar: function () {
            $('#janelaCadastrar :input:visible').trigger('blur')
            if ($('#janelaCadastrar :input:visible.is-invalid').length) {
                alert('Verificar os erros')
                return false
            }

            this.erros = []
            var dados = {}
            dados.nome = $('#nome').val()
            dados.descricao = $('#descricao').val()
            dados.periodicidade = $('#periodicidade').val()
            dados.tipo = $('#tipo_form').val()
            dados.ativo = $('#ativo').val()
            dados._method = 'PUT'
            this.preloadAjax = true

            $.post(URL_ADMIN + '/encargos/' + this.id, dados)
                .done((data) => {
                    app.preloadAjax = false
                    app.atualizado = true
                    $('#controle button:eq(0)').click()
                })
                .fail((data) => {
                    app.preloadAjax = false
                    //app.erros = data.responseJSON.erros;
                    //alert(data.responseJSON.msg);
                    mostraErro(data.responseJSON)
                })
        },
        ativaDesativa: function (encargo) {
            encargo.preload = true
            var dados = {}
            dados._method = 'PUT'

            $.post(`${URL_ADMIN}/encargos/${encargo.id}/ativa-desativa`, dados)
                .done((data) => {
                    encargo.preload = false
                    encargo.ativo = data.ativo ? true : false
                })
                .fail((data) => {
                    encargo.preload = false
                    //app.erros = data.responseJSON.erros;
                    //alert(data.responseJSON.msg);
                    mostraErro(data.responseJSON)
                })
        },
        carregou: function (dados) {
            this.lista = dados
            this.controle.carregando = false
        },
        carregando: function () {
            this.controle.carregando = true
        }
    }
})

registerGlobals(app)
app.mount('#app')

$().ready(function () {
    $('#janelaCadastrar').on('shown.bs.modal', function () {
        $('#nome').focus() // ja foca no nome quando a janela abrir
    })
    $('#btnAtualizar').on('click', atualizar)
    atualizar()

    $('#formBusca').on('submit', function (e) {
        e.preventDefault()
        app.controle.dados.campoBusca = $('#campoBusca').val()
        atualizar()
    })
})

function atualizar() {
    app.$refs.componente.atual = 1
    app.this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
}

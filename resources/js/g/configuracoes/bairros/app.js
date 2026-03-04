import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'

const app = createApp({
    data() {
        return {
            tituloJanela: 'Cadastrando bairro',
            preloadAjax: false,
            editando: false,

            cadastrado: false,
            atualizado: false,
            apagado: false,
            data: '',

            lista: [],
            dados: {},
            controle: {
                carregando: false,
                dados: {}
            }
        }
    },
    mounted: function () {},
    methods: {
        formNovo: function () {
            $('#form')[0].reset()
            this.cadastrado = false
            this.atualizado = false
            this.editando = false
            this.tituloJanela = 'Cadastrando bairro'
            $('#municipio_id').val('').trigger('change')
            formReset()
        },
        cadastrar: function () {
            $('#janelaCadastrar :input:visible:enabled').trigger('blur')
            if ($('#janelaCadastrar :input:visible:enabled.is-invalid').length) {
                alert('Verificar os erros')
                return false
            }

            var dados = {}
            dados.nome = $('#nome').val()
            dados.municipio_id = $('#municipio_id').val()

            this.preloadAjax = true
            $.post(URL_ADMIN + '/bairros', dados)
                .done((data) => {
                    app.preloadAjax = false
                    app.cadastrado = true
                    $('#controle button:eq(0)').click()
                })
                .fail((data) => {
                    app.preloadAjax = false
                })
        },
        formAlterar: function (id) {
            app.id = id

            this.cadastrado = false
            this.atualizado = false
            this.editando = false
            this.tituloJanela = 'Alterando bairro'

            this.preloadAjax = true
            formReset()

            $.get(URL_ADMIN + '/bairros/' + id + '/editar')
                .done((data) => {
                    $('#nome').val(data.nome)
                    var newOption = new Option(`${data.municipio.nome} - ${data.municipio.uf}`, data.municipio_id, true, true)
                    $('#municipio_id').append(newOption).trigger('change')
                    app.editando = true
                    app.preloadAjax = false
                })
                .fail((data) => {
                    app.preloadAjax = false
                })
        },
        alterar: function () {
            $('#janelaCadastrar :input:visible').trigger('blur')
            if ($('#janelaCadastrar :input:visible.is-invalid').length) {
                alert('Verificar os erros')
                return false
            }

            var dados = {}
            dados.nome = $('#nome').val()
            dados.municipio_id = $('#municipio_id').val()
            dados._method = 'PUT'
            this.preloadAjax = true

            $.post(URL_ADMIN + '/bairros/' + this.id, dados)
                .done((data) => {
                    app.preloadAjax = false
                    app.atualizado = true
                    $('#controle button:eq(0)').click()
                })
                .fail((data) => {
                    app.preloadAjax = false
                })
        },
        janelaConfirmar: function (id) {
            app.id = id
            this.apagado = false

            this.preloadAjax = false
        },
        apagar: function () {
            var dados = {}
            dados._method = 'DELETE'
            this.preloadAjax = true

            $.post(URL_ADMIN + '/bairros/' + this.id, dados)
                .done((data) => {
                    app.preloadAjax = false
                    app.apagado = true
                    $('#controle button:eq(0)').click()
                })
                .fail((data) => {
                    app.preloadAjax = false
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
        $('#conteudo').fadeIn()
        atualizar()
    })

    $('#municipio_id').select2({
        dropdownParent: $('#janelaCadastrar'),
        placeholder: 'Nome do município',
        language: 'pt-BR',
        templateSelection: function (data, container) {
            return data.text
        },
        ajax: {
            url: `${URL_ADMIN}/bairros/buscar`,
            data: function (params) {
                var query = {
                    busca: params.term
                }
                return query
            },
            delay: 250,
            cache: false,
            dataType: 'json'
        }
    })

    /*$('#municipio_id').on('select2:select', function (e) {
        var dados = e.params.data;
        //console.log(dados);

        //$('#municipio_id').val(dados.id);
        //$('#select2-municipio_id-container').text(dados.text);

    });*/
})

function atualizar() {
    app.$refs.componente.atual = 1
    app.this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
}

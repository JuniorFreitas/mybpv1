const app = new Vue({
    el: '#app',
    data: {
        tituloJanela: 'Cadastrando feriado',
        preloadAjax: false,
        editando: false,

        cadastrado: false,
        atualizado: false,
        apagado: false,
        data:'',

        erros: [],

        lista: [],
        dados: {},
        controle: {
            carregando: false,
            dados: {},
        }
    },
    methods: {
        formNovo: function () {
            $('#form')[0].reset();
            this.cadastrado = false;
            this.atualizado = false;
            this.editando = false;
            this.tituloJanela = "Cadastrando feriado";
            formReset();
            setupCampo();

        },
        cadastrar: function () {

            $('#janelaCadastrar :input:visible:enabled').trigger('blur');
            if ($('#janelaCadastrar :input:visible:enabled.is-invalid').length) {
                alert('Verificar os erros');
                return false;
            }
            this.erros = [];
            var dados = {};
            dados.descricao = $('#descricao').val();
            dados.data = $('#data').val();
            dados.ativo = $('#ativo').val();

            this.preloadAjax = true;
            $.post(URL_ADMIN + '/feriados', dados)
                .done((data) => {
                    app.preloadAjax = false;
                    app.cadastrado = true;
                    $('#controle button:eq(0)').click();
                })
                .fail((data) => {
                    app.preloadAjax = false;
                    //app.erros = data.responseJSON.erros;
                    //alert(data.responseJSON.msg);
                    mostraErro(data.responseJSON)
                });
        },
        formAlterar: function (id) {
            app.id = id;

            this.cadastrado = false;
            this.atualizado = false;
            this.editando = false;
            this.tituloJanela = "Alterando feriado";

            this.erros = [];
            this.preloadAjax = true;
            formReset();

            $.get(URL_ADMIN + '/feriados/' + id + "/editar")
                .done((data) => {
                    $('#descricao').val(data.descricao);
                    $('#data').val(data.data);
                    $('#ativo').val(data.ativo.toString());
                    app.editando = true;
                    app.preloadAjax = false;
                    setupCampo();
                })
                .fail((data) => {
                    app.preloadAjax = false;
                    app.erros = data.responseJSON.erros;
                    mostraErro(data.responseJSON);
                });


        },
        alterar: function () {

            $('#janelaCadastrar :input:visible:enabled').trigger('blur');
            if ($('#janelaCadastrar :input:visible.is-invalid').length) {
                alert('Verificar os erros');
                return false;
            }

            this.erros = [];
            var dados = {};
            dados.descricao = $('#descricao').val();
            dados.data = $('#data').val();
            dados.ativo = $('#ativo').val();
            dados._method = 'PUT';
            this.preloadAjax = true;

            $.post(URL_ADMIN + '/feriados/' + this.id, dados)
                .done((data) => {
                    app.preloadAjax = false;
                    app.atualizado = true;
                    $('#controle button:eq(0)').click();
                })
                .fail((data) => {
                    app.preloadAjax = false;
                    //app.erros = data.responseJSON.erros;
                    //alert(data.responseJSON.msg);

                });

        },
        janelaConfirmar: function (id) {
            app.id = id;
            this.apagado = false;

            this.erros = [];
            this.preloadAjax = false;
        },
        apagar: function () {
            this.erros = [];
            var dados = {};
            dados._method = 'DELETE';
            this.preloadAjax = true;

            $.post(URL_ADMIN+'/feriados/' + this.id, dados)
                .done((data) => {
                    app.preloadAjax = false;
                    app.apagado = true;
                    $('#controle button:eq(0)').click();
                })
                .fail((data) => {
                    app.preloadAjax = false;
                    app.erros = data.erros;
                    mostraErro(data.responseJSON);
                });
        },

        carregou: function (dados) {

            this.lista = dados;
            this.controle.carregando = false;

        },
        carregando: function () {
            this.controle.carregando = true;
        }

    }
});


$().ready(function () {

    $('#janelaCadastrar').on('shown.bs.modal', function () {
        $('#descricao').focus(); // ja foca no descricao quando a janela abrir
    });
    $('#btnAtualizar').on('click', atualizar);
    atualizar();

    $('#formBusca').on('submit', function (e) {
        e.preventDefault();
        app.controle.dados.campoBusca = $('#campoBusca').val();
        atualizar();
    });


});

function atualizar() {
    app.$refs.componente.atual = 1;
    app.$refs.componente.buscar();
}

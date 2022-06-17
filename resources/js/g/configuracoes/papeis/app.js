const app = new Vue({
    el: '#app',
    data: {
        tituloJanela: '',
        preloadAjax: false,
        editando: false,
        id: 0,//id_curso

        cadastrado: false,
        atualizado: false,
        urlAjax: '',
        apagado: false,

        form: {
            id: '',
            nome: '',
            descricao: '',
            email: '',
            ativo: '',
            empresa_id: '',
            listaDeHabilidades: '',
        },

        lista: [],
        listaDeHabilidades: [],
        todasHabilidades: true,

        dados: {},
        controle: {
            carregando: false,
            dados: {},
        }
    },
    methods: {
        selecionarTodas: function () {
            this.todasHabilidades = !this.todasHabilidades;
            var valor = this.todasHabilidades;
            _.forEach(this.listaDeHabilidades, function (habilidade) {
                habilidade.acesso = valor;
            });
        },
        formNovo() {
            this.tituloJanela = 'Cadastrando papeis'
            formReset();

            this.preloadAjax = true;
            this.cadastrado = false;
            this.atualizado = false;
            this.editando = false;
            formReset();

            $.get(`${URL_ADMIN}/papeis/novo`)
                .done((data) => {
                    this.listaDeHabilidades = data;
                    this.preloadAjax = false;
                });
        },
        cadastrar() {

            $('#janelaCadastrar :input:visible:enabled').trigger('blur');
            if ($('#janelaCadastrar :input:visible:enabled.is-invalid').length) {
                alert('Verificar os erros');
                return false;
            }

            this.form.listaDeHabilidades = this.listaDeHabilidades;
            this.preloadAjax = true;

            axios.post(`${URL_ADMIN}/papeis`, this.form)
                .then(response => {
                    if (response.status === 201) {
                        this.preloadAjax = false;
                        mostraSucesso('', 'Papel cadastrado com sucesso!');
                        $('#janelaCadastrar').modal('hide');
                        this.$refs.componente.buscar();
                        // $('#controle button:eq(0)').click();
                    }
                }).catch(error => {
                this.preloadAjax = false;
            });
        },
        formAlterar(id) {
            this.tituloJanela = 'Alterando papeis'
            formReset();
            this.cadastrado = false;
            this.atualizado = false;
            this.editando = false;

            this.preloadAjax = true;

            axios.get(`${URL_ADMIN}/papeis/${id}/editar`)
                .then(response => {

                    if (response.status === 201) {
                        this.preloadAjax = false;
                        let data = response.data;
                        Object.assign(this.form, data.papel);

                        this.listaDeHabilidades = data.listaDeHabilidade;
                        // this.listaDeHabilidades = data.papel.habilidades;

                        //ligando os botoes
                        // var habilidades_papel = data.listaDeHabilidade;
                        var habilidades_papel = data.papel.habilidades;
                        _.forEach(this.listaDeHabilidades, function (habilidade) {
                            var achou = _.find(habilidades_papel, {'id': habilidade.id});
                            if (achou) {
                                habilidade.acesso = true;
                            }
                        });
                        this.editando = true;
                    }
                }).catch(error => {
                this.preloadAjax = false;
            });
        },
        alterar() {
            $('#janelaCadastrar :input:visible:enabled').trigger('blur');
            if ($('#janelaCadastrar :input:visible:enabled.is-invalid').length) {
                alert('Verificar os erros');
                return false;
            }

            this.form.listaDeHabilidades = this.listaDeHabilidades;
            this.preloadAjax = true;

            axios.put(`${URL_ADMIN}/papeis/${this.form.id}`, this.form)
                .then(response => {
                    if (response.status === 201) {
                        this.preloadAjax = false;
                        this.atualizado = true;
                        mostraSucesso('', 'Papel alterado com sucesso!');
                        $('#janelaCadastrar').modal('hide');
                        this.$refs.componente.buscar();
                    }

                }).catch(error => {
                this.preloadAjax = false;
            });
        },
        janelaConfirmar: function (id) {
            app.id = id;
            this.apagado = false;
            this.preloadAjax = false;
        },
        apagar: function () {
            this.erros = [];
            var dados = {};
            dados._method = 'DELETE';
            this.preloadAjax = true;

            $.post(`${URL_ADMIN}/papeis/${this.id}`, dados)
                .done((data) => {

                    app.preloadAjax = false;
                    app.apagado = true;
                    $('#controle button:eq(0)').click();

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
        $('#nome').focus(); // ja foca no nome quando a janela abrir
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

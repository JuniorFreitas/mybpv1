Vue.component('endereco', require('../../../components/Endereco.vue'));

const app = new Vue({
    el: '#app',
    data: {
        tituloJanela: 'Cadastrando Cliente',
        preloadAjax: false,
        editando: false,
        apagado: false,

        empresa: 0,

        pages: 10,

        form: {
            id: '',
            tipo: 'pessoa_juridica',
            logradouro: '',
            complemento: '',
            bairro: '',
            municipio: '',
            uf: 'MA',
            cep: '',
            telefone: '',
            celular: '',
            email: '',
            site: '',
            datafaturamento: 1,
            ValorPrestacaoFormat: '0.00',
            ativo: true,
            clientepf: {},
            clientepj: {},
            servicos: [],

            servicosDelete: [],
            graduacao: '',

        },
        formDefault: null,
        campoNome: null,

        cadastrado: false,
        atualizado: false,

        lista: [],

        controle: {
            carregando: false,
            dados: {
                empresa_id: "",
                servico_id: ""
            },
        }
    },
    mounted: function () {
        this.formDefault = _.cloneDeep(this.form) //copia
    },
    methods: {
        filtroEmpresa: function () {
            this.controle.dados.servico_id = ""
            setTimeout(atualizar, 300);
        },
        filtroServico: function () {
            this.controle.dados.empresa_id = ""
            setTimeout(atualizar, 300);
        },
        addLIServico: function () {
            let obj = {};
            obj.nova = true;
            obj.empresa_id = 1;
            obj.servico_id = 1;
            obj.ValorPrestacaoFormat = '0,00';
            obj.ano = '';
            obj.ativo = false;
            this.form.servicos.push(obj);
        },
        removerLIServico: function (index) {
            if (this.editando) {
                this.form.servicosDelete.push(this.form.servicos[index].id);
            }
            this.form.servicos.splice(index, 1);
        },
        formNovo: function () {
            this.cadastrado = false;
            this.atualizado = false;
            this.editando = false;

            this.tituloJanela = "Cadastrando Cliente";

            formReset();
            setupCampo();
            this.form = _.cloneDeep(this.formDefault) //copia
        },
        cadastrar: function () {
            $('#janelaCadastrar :input:visible').trigger('blur');
            if ($('#janelaCadastrar :input:visible.is-invalid').length) {
                alert('Verificar os erros');
                return false;
            }
            this.preloadAjax = true;
            $.post(`${URL_ADMIN}/clientes`, this.form)
                .done((data) => {
                    // console.log(data);
                    app.preloadAjax = false;
                    app.cadastrado = true;
                    $('#controle button:eq(0)').click();
                })
                .fail((data) => {
                    app.preloadAjax = false;
                });
        },
        formAlterar: function (id) {
            this.form.id = id;

            this.cadastrado = false;
            this.atualizado = false;
            this.editando = false;
            this.tituloJanela = "Alterando Cliente";

            this.preloadAjax = true;

            formReset();
            $.get(`${URL_ADMIN}/clientes/${id}/editar`)
                .done((data) => {
                    Object.assign(app.form, data);
                    app.editando = true;
                    app.preloadAjax = false;
                    setupCampo();
                })
                .fail((data) => {
                    app.preloadAjax = false;
                });
        },

        alterar: function () {
            $('#janelaCadastrar :input:visible').trigger('blur');
            if ($('#janelaCadastrar :input:visible.is-invalid').length) {
                alert('Verificar os erros');
                return false;
            }
            this.form._method = 'PUT';
            this.preloadAjax = true;

            $.post(`${URL_ADMIN}/clientes/${this.form.id}`, this.form)
                .done((data) => {
                    app.preloadAjax = false;
                    app.atualizado = true;
                    $('#controle button:eq(0)').click();
                })
                .fail((data) => {
                    app.preloadAjax = false;
                });

        },
        apagar: function () {
            this.erros = [];
            this.form._method = 'DELETE';
            this.preloadAjax = true;

            $.post(`${URL_ADMIN}/clientes/${this.form.id}`, this.form)
                .done((data) => {
                    console.log(data);
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

        janelaConfirmar: function (id) {
            app.form.id = id;
            this.apagado = false;

            this.preloadAjax = false;
        },
        carregou: function (dados) {
            this.lista = dados;
            this.controle.carregando = false;

        },
        carregando: function () {
            this.controle.carregando = true;
        },
        // verificaCpf: function () {
        //     if (!this.editando) {
        //         $.get(`${URL_ADMIN}/beneficiarios/buscar-cpf?cpf=${this.form.cpf}`)
        //             .done((data) => {
        //             })
        //             .fail((data) => {
        //             });
        //     }
        // },
        // verificaCnpj: function () {
        //     if (!this.editando) {
        //         $.get(`${URL_ADMIN}/beneficiarios/buscar-cnpj?cnpj=${this.form.cnpj}`)
        //             .done((data) => {
        //             })
        //             .fail((data) => {
        //             });
        //     }
        // }
    }
});

$().ready(function () {

    $('#janelaCadastrar').on('shown.bs.modal', function () {
        $('#cnpj').focus(); // ja foca no descricao quando a janela abrir
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


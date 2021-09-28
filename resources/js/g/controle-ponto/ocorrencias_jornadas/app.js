import preload from '../../../components/preload';

const app = new Vue({
    el: '#app',
    data: {
        tituloJanela: 'Cadastrando',
        preloadAjax: false,
        editando: false,

        cadastrado: false,
        atualizado: false,
        apagado: false,
        campoDesc: null,
        descricao: '',
        trabalhado: false,
        conta_horas: false,
        ativo: true,

        listaAtual: [],
        listaAnterior: [],

        lista: [],
        controle: {
            carregando: false,
            dados: {},
        }
    },
    computed: {

    },
    components: {
        preload
    },
    mounted() {
        this.atualizar();
    },
    methods: {


        formNovo: function () {
            $('#form')[0].reset();
            this.cadastrado = false;
            this.atualizado = false;
            this.editando = false;
            this.campoDesc = null;
            this.descricao = '';
            this.trabalhado = false;
            this.conta_horas = false;
            this.ativo = true;
            this.listaAtual = [];
            this.listaAnterior = [];
            this.tituloJanela = "Cadastrando";
            formReset();
            setupCampo();

        },
        cadastrar: function () {

            $('#janelaCadastrar :input:visible:enabled').trigger('blur');
            if ($('#janelaCadastrar :input:visible:enabled.is-invalid').length) {
                alert('Verificar os erros');
                return false;
            }
            let dados = {};
            dados.descricao = this.descricao;
            dados.trabalhado = this.trabalhado;
            dados.conta_horas = this.conta_horas;
            dados.ativo = this.ativo;

            this.preloadAjax = true;
            axios.post(`${URL_ADMIN}/controle-ponto/ocorrencias_jornadas/`, dados)
                .then(response => {
                    this.preloadAjax = false;
                    this.cadastrado = true;
                    this.atualizar();
                }).catch(error => {
                this.preloadAjax = false;
            });
        },
        formAlterar: function (id) {
            app.id = id;
            this.cadastrado = false;
            this.atualizado = false;
            this.editando = false;
            this.tituloJanela = "Alterando";

            this.preloadAjax = true;
            formReset();

            axios.get(`${URL_ADMIN}/controle-ponto/ocorrencias_jornadas/${id}/editar`)
                .then((response) => {
                    this.descricao = response.data.descricao;
                    this.trabalhado = response.data.trabalhado;
                    this.conta_horas = response.data.conta_horas;
                    this.ativo = response.data.ativo;
                    this.editando = true;
                    this.preloadAjax = false;
                    setupCampo();
                })
                .catch((data) => {
                    this.preloadAjax = false;
                });


        },
        alterar: function () {

            $('#janelaCadastrar :input:visible:enabled').trigger('blur');
            if ($('#janelaCadastrar :input:visible:enabled.is-invalid').length) {
                alert('Verificar os erros');
                return false;
            }

            let dados = {};
            dados.descricao = this.descricao;
            dados.trabalhado = this.trabalhado;
            dados.conta_horas = this.conta_horas;
            dados.ativo = this.ativo;
            this.preloadAjax = true;

            axios.put(`${URL_ADMIN}/controle-ponto/ocorrencias_jornadas/${this.id}`, dados)
                .then((data) => {
                    this.preloadAjax = false;
                    this.atualizado = true;
                    this.atualizar()
                })
                .catch((data) => {
                    this.preloadAjax = false;
                });

        },
        janelaConfirmar: function (id) {
            this.id = id;
            this.apagado = false;

            this.preloadAjax = false;
        },
        apagar: function () {

            this.preloadAjax = true;

            axios.delete(`${URL_ADMIN}/controle-ponto/ocorrencias_jornadas/${this.id}`, null)
                .then((data) => {
                    this.preloadAjax = false;
                    this.apagado = true;
                    this.atualizar();
                })
                .catch((data) => {
                    this.preloadAjax = false;
                });
        },

        carregou: function (dados) {

            this.lista = dados;
            this.controle.carregando = false;

        },
        carregando: function () {
            this.controle.carregando = true;
        },
        atualizar() {
            this.$refs.paginacao.atual = 1;
            this.$refs.paginacao.buscar();
        }

    }
});

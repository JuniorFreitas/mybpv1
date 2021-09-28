const app = new Vue({
    el: '#app',
    data: {
        tituloJanela: 'Cadastrando Vaga',
        preloadAjax: false,
        editando: false,
        apagado: false,

        pages: 10,

        form: {
            nome: '',
            ativo: true
        },

        formDefault: null,
        campoNome: null,

        cadastrado: false,
        atualizado: false,

        lista: [],

        controle: {
            carregando: false,
            dados: {
                campoBusca: "",
                campoStatus: "",
            },
        }
    },
    mounted() {
        this.formDefault = _.cloneDeep(this.form) //copia
        this.atualizar();
    },
    methods: {
        formNovo() {
            this.cadastrado = false;
            this.atualizado = false;
            this.editando = false;

            this.tituloJanela = "Cadastrando Vaga";

            formReset();
            setupCampo();

            this.form = _.cloneDeep(this.formDefault) //copia
            this.leitura = false;

        },
        cadastrar() {
            formReset();

            $('#janelaCadastrar :input:enabled').trigger('blur');

            if ($('#janelaCadastrar :input:enabled.is-invalid').length) {
                mostraErro('', 'Verificar os erros');
                return false;
            }

            this.preloadAjax = true;
            axios.post(`${URL_ADMIN}/cadastro/vagas`, this.form)
                .then(response => {
                    if (response.status === 201) {
                        this.preloadAjax = false;
                        this.cadastrado = true;
                        this.atualizar();
                    }
                }).catch(error => (this.preloadAjax = false));
        },
        formAlterar(id) {
            this.cadastrado = false;
            this.atualizado = false;
            this.editando = false;
            this.tituloJanela = "Alterando Vaga";
            this.preloadAjax = true;
            formReset();

            this.form = _.cloneDeep(this.formDefault) //copia
            this.leitura = true;

            axios.get(`${URL_ADMIN}/cadastro/vagas/${id}/editar`)
                .then(response => {
                    Object.assign(this.form, response.data);
                    this.editando = true;
                    this.preloadAjax = false;
                    setupCampo();
                }).catch(
                error => (this.preloadAjax = false)
            );

        },

        alterar() {
            formReset();
            $('#janelaCadastrar :input:enabled').trigger('blur');

            if ($('#janelaCadastrar :input:enabled.is-invalid').length) {
                mostraErro('', 'Verificar os erros');
                return false;
            }

            this.form._method = 'PUT';
            this.preloadAjax = true;

            axios.put(`${URL_ADMIN}/cadastro/vagas/${this.form.id}`, this.form).then(response => {
                this.preloadAjax = false;
                this.atualizado = true;
                this.atualizar();
            }).catch(error => (this.preloadAjax = false));

        },

        carregou(dados) {
            this.lista = dados;
            this.controle.carregando = false;
        },

        carregando() {
            this.controle.carregando = true;
        },

        atualizar() {
            this.$refs.componente.atual = 1;
            this.$refs.componente.buscar();
        },
    }
});

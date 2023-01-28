const app = new Vue({
    el: '#app',
    data: {
        tituloJanela: '',
        empresa_id: '',
        preloadAjax: false,
        editando: false,

        cadastrado: false,
        atualizado: false,
        urlAjax: '',
        apagado: false,

        form: {
            id: '',
            nome: '',
            descricao: '',
            email: '',
            ativo: true,
            empresa_id: '',
            listaDeHabilidades: '',
        },

        formDefault: null,

        lista: [],
        listaDeHabilidades: [],
        todasHabilidades: true,

        dados: {},
        controle: {
            carregando: false,
            dados: {
                pages: 20,
                campoBusca: "",
            }
        }
    },
    mounted() {
        this.atualizar();
        this.formDefault = _.cloneDeep(this.form);
    },
    methods: {
        selecionarTodas() {
            this.todasHabilidades = !this.todasHabilidades;
            var valor = this.todasHabilidades;
            _.forEach(this.listaDeHabilidades, function (habilidade) {
                habilidade.acesso = valor;
            });
        },
        formNovo() {
            this.tituloJanela = 'Cadastrando papeis'
            $("#aba-identificacao-tab").tab("show");
            Object.assign(this.form, this.formDefault);
            this.preloadAjax = true;
            this.cadastrado = false;
            this.atualizado = false;
            this.editando = false;
            formReset();

            axios.get(`${URL_ADMIN}/papeis/novo`)
                .then(({data}) => {
                    this.listaDeHabilidades = data;
                    this.preloadAjax = false;
                }).catch((error) => {
                    console.log(error);
            })
        },
        cadastrar() {
            $('#janelaCadastrar :input:visible:enabled').trigger('blur');
            if ($('#janelaCadastrar :input:visible:enabled.is-invalid').length) {
                alert('Verificar os erros');
                return false;
            }

            this.form.listaDeHabilidades = this.listaDeHabilidades;
            this.preloadAjax = true;
            this.form.empresa_id = this.empresa_id;

            axios.post(`${URL_ADMIN}/papeis`, this.form)
                .then(response => {
                    if (response.status === 201) {
                        this.preloadAjax = false;
                        mostraSucesso('', 'Papel cadastrado com sucesso!');
                        $('#janelaCadastrar').modal('hide');
                        this.$refs.componente.buscar();
                    }
                }).catch(error => {
                this.preloadAjax = false;
            });
        },
        formAlterar(id) {
            this.tituloJanela = 'Alterando papeis'
            $("#aba-identificacao-tab").tab("show");
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
            this.form.empresa_id = this.empresa_id;

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
        atualizar() {
            this.$refs.componente.atual = 1;
            this.$refs.componente.buscar();
        },

        carregou(dados) {
            this.lista = dados.itens;
            this.empresa_id = dados.empresa_id;
            this.controle.carregando = false;
        },
        carregando() {
            this.controle.carregando = true;
        }

    }
});

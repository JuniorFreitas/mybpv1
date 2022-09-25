const app = new Vue({
    el: "#app",
    data: {
        tituloJanela: "Cadastrando usuário",
        preloadAjax: false,
        editando: false,
        cadastrado: false,
        atualizado: false,
        urlAjax: "",
        apagado: false,
        grupoempresa: false,
        user_recebe_emailDefault: null,

        form: {
            id: "",
            nome: "",
            login: "",
            grupo_id: "",
            tipo: "",
            grupo_cloud_id: "",
            empresa_id: "",
            ativo: true,
            gestor: false,
            user_recebe_email: []
        },
        empresa_id: "",
        formDefault: null,
        listaPapeis: [],
        listaCloud: [],
        listaTipoEmail: [],
        lista_tipos: [],
        lista: [],
        dados: {},
        controle: {
            carregando: false,
            dados: {
                campoBusca: "",
                por_pagina: 50,
                campoEmpresa: "",
                campoTipo: ""
            }
        }
    },
    mounted() {
        this.formDefault = _.cloneDeep(this.form); //copia
        this.atualizar();
        // Object.assign(this.form, this.formDefault);

    },
    methods: {
        formNovo() {

            this.selecionaEmpresa(this.empresa_id);

            this.cadastrado = false;
            this.atualizado = false;
            this.editando = false;
            this.tituloJanela = "Cadastrando usuário";
            formReset();
            this.form = _.cloneDeep(this.formDefault); //copia
            if (this.empresa_id !== 100) {
                this.form.empresa_id = this.empresa_id;
            }
        },

        cadastrar() {
            $("#janelaCadastrar :input:visible:enabled").trigger("blur");
            if ($("#janelaCadastrar :input:visible:enabled.is-invalid").length) {
                alert("Verificar os erros");
                return false;
            }

            this.preloadAjax = true;
            axios.post(`${URL_ADMIN}/usuarios`, this.form)
                .then(response => {
                    this.preloadAjax = false;
                    this.cadastrado = true;
                    this.atualizar();
                })
                .catch(error => {
                    this.preloadAjax = false;
                });
        },

        formAlterar(id) {
            formReset();
            this.form = _.cloneDeep(this.formDefault); //copia

            this.selecionaEmpresa(this.empresa_id);
            if (this.empresa_id !== 100) {
                this.form.empresa_id = this.empresa_id;
            }

            this.form.id = id;

            this.cadastrado = false;
            this.atualizado = false;
            this.editando = false;
            this.tituloJanela = "Alterando usuário";

            this.preloadAjax = true;
            formReset();
            this.form = _.cloneDeep(this.formDefault); //copia

            axios.get(`${URL_ADMIN}/usuarios/${id}/editar`)
                .then(response => {
                    Object.assign(this.form, response.data.usuario);
                    this.listaPapeis = response.data.papeis;
                    this.listaCloud = response.data.cloud;
                    this.form.user_recebe_email = response.data.formulario_vazio;
                    this.editando = true;
                    this.preloadAjax = false;
                })
                .catch(error => {
                    this.preloadAjax = false;
                });
        },

        alterar() {
            $("#janelaCadastrar :input:visible:enabled").trigger("blur");
            if ($("#janelaCadastrar :input:visible:enabled.is-invalid").length) {
                alert("Verificar os erros");
                return false;
            }

            this.preloadAjax = true;
            axios.put(`${URL_ADMIN}/usuarios/${this.form.id}`, this.form)
                .then(response => {
                    if (response.status === 201) {
                        this.preloadAjax = false;
                        this.atualizado = true;
                        this.atualizar();
                    }
                }).catch(error => {
                this.preloadAjax = false;
            });
        },

        simularUsuario(user_id) {

            this.preloadAjax = true;
            axios.put(`${URL_ADMIN}/usuarios/simularUsuario`, { user_id: user_id })
                .then(response => {
                    if (response.data.simulacao) {
                        window.location.href = `${URL_ADMIN}/dashboard`;
                    }
                }).catch(error => {
                this.preloadAjax = false;
            });
        },

        selecionaEmpresa(id) {
            this.grupoempresa = false;
            axios.get(`${URL_ADMIN}/usuario/busca-grupo-empresa/${id}`)
                .then(response => {
                    if (response.status === 200) {
                        let data = response.data;
                        this.listaPapeis = data.papeis;
                        this.listaCloud = data.cloud;
                        this.grupoempresa = true;
                    }
                });
        },

        carregou(dados) {
            this.lista = dados.resultado;
            this.empresa_id = dados.empresa;
            this.listaTipoEmail = dados.tipo_email;
            this.user_recebe_emailDefault = dados.formulario_vazio;
            this.lista_tipos = dados.lista_tipos;
            this.form.user_recebe_email = _.cloneDeep(this.user_recebe_emailDefault);
            this.controle.carregando = false;
        },
        carregando() {
            this.controle.carregando = true;
        },
        atualizar() {
            this.$refs.componente.atual = 1;
            this.$refs.componente.buscar();
        }
    }
});

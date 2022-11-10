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
        tipos_usuarios_gerenciais: [],
        lista: [],
        dados: {},
        controle: {
            carregando: false,
            showCampoGrupo: false,
            dados: {
                campoBusca: "",
                campoLogin: "",
                por_pagina: 50,
                campoEmpresa: "",
                campoGrupo: "",
                campoTipo: "",
                listaPapeis: [],
                campoStatus: "",
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

                    if(response.data.usuario.grupo_id == null){
                        this.form.grupo_id = '';
                    }
                    if(response.data.usuario.gestor == null){
                        this.form.gestor = false;
                    }

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
            this.listaPapeis = [];
            this.form.grupo_id = "";
            if(id != '' && id != 100){
                axios.get(`${URL_ADMIN}/usuario/busca-grupo-empresa/${id}`)
                    .then(response => {
                        if (response.status === 200) {
                            let data = response.data;
                            this.listaPapeis = data.papeis;
                            this.listaCloud = data.cloud;
                            this.grupoempresa = true;
                        }
                    });
            }
        },

        buscarGruposEmpresa(id) {
            this.controle.showCampoGrupo = false;
            this.controle.dados.listaPapeis = [];
            this.controle.dados.campoGrupo = '';
            if(id != ''){
                axios.get(`${URL_ADMIN}/usuario/busca-grupo-empresa/${id}`)
                    .then(response => {
                        if (response.status === 200) {
                            let data = response.data;
                            this.controle.dados.listaPapeis = response.data.papeis;
                            this.controle.showCampoGrupo = true;
                        }
                    });
            }
        },

        carregou(dados) {
            this.lista = dados.resultado;
            this.empresa_id = dados.empresa;
            this.listaTipoEmail = dados.tipo_email;
            this.tipos_usuarios_gerenciais = dados.tipos_usuarios_gerenciais;
            this.user_recebe_emailDefault = dados.formulario_vazio;
            this.lista_tipos = dados.lista_tipos;
            this.empresa_id != 100 ? this.controle.dados.listaPapeis = dados.lista_grupos : [];
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

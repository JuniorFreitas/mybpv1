const app = new Vue({
    el: "#app",
    data: {
        tituloJanela: "Cadastrando Grupo",
        preloadAjax: false,
        editando: false,
        cadastrado: false,
        atualizado: false,
        apagado: false,

        form: {
            nome: "",
            empresa_id: "",
            descricao: "",
            ativo: true,
            habilidades: [],
            todasHabilidades: false,
            usuarios: [],
            usuariosDelete: [],
            colaborador_id: "",
            autocomplete_label_colaborador: ""
        },

        formDefault: null,
        hash: `mastertag_${parseInt((Math.random() * 999999))}`,

        lista: [],
        listaDeHabilidades: [],

        controle: {
            carregando: false,
            dados: {}
        }
    },
    mounted() {
        this.formDefault = _.cloneDeep(this.form);//copia
        this.atualizar();
    },
    methods: {
        removerLIColaborador(index) {
            if (this.editando && !this.form.usuarios[index].novo) {
                this.form.usuariosDelete.push(this.form.usuarios[index].id);
            }
            this.form.usuarios.splice(index, 1);
        },
        selecionaColaborador(obj) {
            const usuario = {};
            usuario.novo = true;
            usuario.id = obj.id;
            usuario.nome = obj.nome;

            let atual = this.form.usuarios.findIndex(val => val.id === usuario.id);

            if (atual < 0) {//Se não existir ainda no array
                if (obj.grupo_cloud_id) {
                    mostraErro('', `O colaborador(a) ${usuario.nome} ja está vinculado ao grupo ${obj.grupo_cloud.nome}.`);
                    this.form.autocomplete_label_colaborador = "";
                    return false;
                }
                this.form.usuarios.push(usuario);
            } else {
                mostraErro("", `O colaborador(a) ${usuario.nome} já está na lista.`);
            }

            this.form.autocomplete_label_colaborador = "";
        },

        resetaCampoColaborador() {
            if (this.form.autocomplete_label_colaborador_anterior !== this.form.autocomplete_label_colaborador) {
                this.form.autocomplete_label_colaborador_anterior = "";
                this.form.autocomplete_label_colaborador = "";
                this.form.colaborador_id = "";

                setTimeout(() => {
                    if (this.form.colaborador_id === "") {
                        valida_campo_vazio($(`#colaborador_${this.hash}`), 1);
                        $(`#${this.hash} #colaborador_${this.hash}`).focus().trigger("blur");
                        mostraErro("Erro", "O Campo Colaborador não pode ficar vazio");
                    }
                }, 100);
            }
        },

        verificaHabilitados(habilidade) {
            habilidade.acesso = !habilidade.acesso;
            let ativos = _.filter(this.form.habilidades, "acesso");

            if (this.form.habilidades.length < ativos.length || ativos.length === 0) {
                this.form.todasHabilidades = false;
            }

            if (this.form.habilidades.length === ativos.length) {
                this.form.todasHabilidades = true;
            }
        },
        selecionarTodas() {
            this.form.todasHabilidades = !this.form.todasHabilidades;
            var valor = this.form.todasHabilidades;
            _.forEach(this.form.habilidades, function(habilidade) {
                habilidade.acesso = valor;
            });
        },

        formNovo() {
            this.cadastrado = false;
            this.atualizado = false;
            this.editando = false;

            this.tituloJanela = "Cadastrando Grupo";

            formReset();
            this.form = _.cloneDeep(this.formDefault); //copia
            this.form.habilidades = _.cloneDeep(this.listaDeHabilidades);
        },

        cadastrar() {
            $("#janelaCadastrar :input:visible").trigger("blur");
            if ($("#janelaCadastrar :input:visible.is-invalid").length) {
                alert("Verificar os erros");
                return false;
            }

            this.preloadAjax = true;
            axios.post(`${URL_ADMIN}/clouds/configuracoes`, this.form)
                .then(response => {
                    let data = response.data;
                    this.preloadAjax = false;
                    this.cadastrado = true;
                    this.atualizar();
                }).catch(error => {
                this.preloadAjax = false;
            });
        },

        formAlterar(id) {
            this.form = _.cloneDeep(this.formDefault); //copia
            this.form.id = id;

            $("[href=\"#abaIdentificacao\"]").tab("show");

            this.cadastrado = false;
            this.atualizado = false;
            this.editando = false;
            this.tituloJanela = "Alterando Grupo";

            this.preloadAjax = true;

            formReset();
            axios.get(`${URL_ADMIN}/clouds/configuracoes/${id}/editar`)
                .then(response => {
                    this.editando = true;
                    let data = response.data;
                    Object.assign(this.form, data);
                    this.form.habilidades = _.cloneDeep(this.listaDeHabilidades);

                    // ligando os botoes
                    let habilidade_grupo = data.habilidades;
                    _.forEach(this.form.habilidades, function(habilidade) {
                        var achou = _.find(habilidade_grupo, { "id": habilidade.id });
                        if (achou) {
                            habilidade.acesso = true;
                        }
                    });

                    let ativos = _.filter(this.form.habilidades, "acesso");
                    if (this.form.habilidades.length < ativos.length || ativos.length === 0) {
                        this.form.todasHabilidades = false;
                    }
                    if (this.form.habilidades.length === ativos.length) {
                        this.form.todasHabilidades = true;
                    }
                    this.preloadAjax = false;
                }).catch(error => {
                this.preloadAjax = false;
            });
        },

        alterar() {
            $("#janelaCadastrar :input:visible").trigger("blur");
            if ($("#janelaCadastrar :input:visible.is-invalid").length) {
                alert("Verificar os erros");
                return false;
            }

            this.preloadAjax = true;
            this.form._method = "PUT";

            axios.put(`${URL_ADMIN}/clouds/configuracoes/${this.form.id}`, this.form)
                .then(response => {
                    let data = response.data;
                    this.preloadAjax = false;
                    this.atualizado = true;
                    this.atualizar();
                }).catch(error => {
                this.preloadAjax = false;
            });

        },

        janelaConfirmar(id) {
            this.form.id = id;
            this.apagado = false;
            this.preloadAjax = false;
        },

        apagar() {
            this.erros = [];
            this.form._method = "DELETE";
            this.preloadAjax = true;

            axios.delete(`${URL_ADMIN}/clouds/configuracoes/${this.form.id}`, this.form)
                .then(response => {
                    let data = response.data;
                    this.preloadAjax = false;
                    this.apagado = true;
                    this.atualizar();
                }).catch(error => {
                this.preloadAjax = false;
            });
        },

        carregou(dados) {
            this.lista = dados.lista;
            this.listaDeHabilidades = dados.listaHabilidades;
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

const app = new Vue({
    el: "#app",
    data: {
        tituloJanela: "Cadastrando Projeto",
        preloadAjax: false,
        editando: false,
        apagado: false,

        pages: 10,

        form: {
            nome: "",
            qnt_total: 1,
            vagas_projeto: [],
            vagas_projetoDelete: [],
            autocomplete_label_vaga_aberta: ""
        },

        hash: `mastertag_${parseInt((Math.random() * 999999))}`,

        formDefault: null,
        campoNome: null,

        cadastrado: false,
        atualizado: false,

        lista: [],

        controle: {
            carregando: false,
            dados: {
                campoBusca: "",
                campoStatus: ""
            }
        }
    },
    computed: {
        totalRestanteVagas() {
            let totalProjeto = this.form.qnt_total;
            let totalVagas = this.form.vagas_projeto.reduce((total, vaga) => {
                return total + parseFloat(vaga.qnt_total);
            }, 0);

            let somatorio = totalProjeto - totalVagas;

            if (somatorio < 0) {
                mostraErro("", "A soma das vagas não pode ser maior que a quantidade total do projeto.");
            }

            return somatorio;
        }
    },
    mounted() {
        this.formDefault = _.cloneDeep(this.form); //copia
        this.atualizar();
    },
    methods: {
        // removerLIColaborador(index) {
        //     if (this.editando && !this.form.vagas_projeto[index].novo) {
        //         this.form.vagas_projetoDelete.push(this.form.vagas_projeto[index].id);
        //     }
        //     this.form.vagas_projeto.splice(index, 1);
        // },
        selecionaVaga(obj) {
            console.log(obj);
            const vagas_projeto = {};
            vagas_projeto.novo = true;
            vagas_projeto.vaga_aberta_id = obj.id;
            vagas_projeto.empresa_id = obj.empresa_id;
            vagas_projeto.projeto_id = null;
            vagas_projeto.qnt_total = this.totalRestanteVagas;
            vagas_projeto.qnt_preenchida = 0;
            vagas_projeto.vaga_aberta = obj;

            let atual = this.form.vagas_projeto.findIndex(val => val.vaga_aberta_id === vagas_projeto.vaga_aberta_id);

            if (atual < 0) {//Se não existir ainda no array
                this.form.vagas_projeto.push(vagas_projeto);
            } else {
                mostraErro("", `A vaga ${vagas_projeto.vaga_aberta.titulo} já está na lista.`);
            }

            this.form.autocomplete_label_vaga_aberta = "";
        },

        formNovo() {
            this.cadastrado = false;
            this.atualizado = false;
            this.editando = false;

            this.tituloJanela = "Cadastrando Projeto";

            formReset();
            setupCampo();

            this.form = _.cloneDeep(this.formDefault); //copia
            this.leitura = false;

        },
        cadastrar() {
            formReset();

            $("#janelaCadastrar :input:enabled").trigger("blur");

            if ($("#janelaCadastrar :input:enabled.is-invalid").length) {
                mostraErro("", "Verificar os erros");
                return false;
            }

            this.preloadAjax = true;
            axios.post(`${URL_ADMIN}/cadastro/projetos`, this.form)
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
            this.tituloJanela = "Alterando Projeto";
            this.preloadAjax = true;
            formReset();

            this.form = _.cloneDeep(this.formDefault); //copia
            this.leitura = true;

            axios.get(`${URL_ADMIN}/cadastro/projetos/${id}/editar`)
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
            $("#janelaCadastrar :input:enabled").trigger("blur");

            if ($("#janelaCadastrar :input:enabled.is-invalid").length) {
                mostraErro("", "Verificar os erros");
                return false;
            }

            this.form._method = "PUT";
            this.preloadAjax = true;

            axios.put(`${URL_ADMIN}/cadastro/projetos/${this.form.id}`, this.form).then(response => {
                this.preloadAjax = false;
                this.atualizado = true;
                this.atualizar();
            }).catch(error => (this.preloadAjax = false));

        },

        carregou(dados) {
            this.lista = dados.itens;
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

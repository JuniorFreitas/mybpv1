import { createApp } from 'vue'
import { registerGlobals } from '../../../../registerGlobals'
import preload from '../../../../components/preload'

const app = createApp({
    components: {
        preload
    },
    data() {
        return {
            URL_ADMIN,
            preload: true,
            editando: false,
            update: false,
            janelaTitulo: 'Avaliador',
            hash: `mybp_${parseInt(Math.random() * 999999)}`,

            form: {
                autocomplete_label_avaliador: '',
                autocomplete_label_avaliador_anterior: '',
                avaliador_id: '',
                avaliadores: []
            },

            funcionariosSelecionados: [],
            todosFuncionariosSelecionados: false,
            listaFuncionarios: [],

            urlPaginacao: `${URL_ADMIN}/cadastro/avaliacoes/avaliadores/atualizar`,
            controle: {
                carregando: false,
                dados: {
                    campoBusca: ''
                }
            }
        }
    },
    computed: {},
    mounted() {
        this.formPerimetroDefault = _.cloneDeep(this.formPerimetro)
        this.atualizar()
        // this.atualizarListaFuncionarios();
        // this.atualizarListaPeriemetros();
    },
    methods: {
        removerLIColaborador(index) {
            if (this.editando && !this.form.usuarios[index].novo) {
                this.form.usuariosDelete.push(this.form.usuarios[index].id)
            }
            this.form.usuarios.splice(index, 1)
        },
        selecionaAvaliador(obj) {
            const avaliador = {
                novo: true,
                id: obj.id,
                nome: obj.nome
            }

            let atual = this.form.avaliadores.findIndex((val) => val.id === avaliador.id)

            if (atual < 0) {
                //Se não existir ainda no array
                this.form.avaliadores.push(avaliador)
            } else {
                mostraErro('', `Avaliador(a) ${avaliador.nome} já está na lista.`)
                this.form.autocomplete_label_avaliador = ''
                return false
            }
            this.form.autocomplete_label_avaliador = ''
        },

        resetaCampo() {
            if (this.form.autocomplete_label_avaliador_anterior !== this.form.autocomplete_label_avaliador) {
                this.form.autocomplete_label_avaliador_anterior = ''
                this.form.autocomplete_label_avaliador = ''
                this.form.avaliador_id = ''
            }
        },

        //Configurações ------------------------------------
        getPermissoes() {
            this.preload = true
            axios
                .get(`${URL_ADMIN}/controle-ponto/configuracoes/getPermissoes/`)
                .then((response) => {
                    this.preload = false
                    this.perimetros_insert = response.data.perimetros_insert
                    this.perimetros_update = response.data.perimetros_update
                    this.perimetros_delete = response.data.perimetros_delete
                    this.perimetros_funcionarios = response.data.perimetros_funcionarios
                    this.config_empresa = response.data.config_empresa
                })
                .catch((error) => {
                    this.preload = false
                })
        },
        salvarConfiguracoes() {
            $('#config_frequencia :input:visible:enabled').trigger('blur')
            if ($('#config_frequencia :input:visible:enabled.is-invalid').length) {
                alert('Verificar os erros')
                return false
            }

            this.preloadConfig = true
            axios
                .put(`${URL_ADMIN}/controle-ponto/configuracoes/${this.EMPRESA_ID}`, this.formConfig)
                .then((response) => {
                    this.preloadConfig = false
                    mostraSucesso('', 'Configuração salva')
                })
                .catch((error) => {
                    this.preloadConfig = false
                })
        },

        formNovoPerimetro() {
            this.formPerimetro = _.cloneDeep(this.formPerimetroDefault)
            if (!this.preloadGoogleMaps) {
                this.initMap()
            }
        },
        formEditarPerimetro(perimetro) {
            this.formPerimetro = _.cloneDeep(this.formPerimetroDefault)
            this.formPerimetro.editando = true
            this.formPerimetro.titulo = 'Editar perimetro'
            this.formPerimetro.preload = true

            axios
                .get(`${URL_ADMIN}/controle-ponto/perimetros/${perimetro.id}/editar`)
                .then((response) => {
                    this.formPerimetro.preload = false
                    Object.assign(this.formPerimetro, response.data)
                    if (!this.preloadGoogleMaps) {
                        this.initMap()
                    }
                })
                .catch((error) => {
                    this.formPerimetro.preload = false
                })
        },
        salvarPerimetro() {
            $('#janelaFormPerimetro :input:visible:enabled').trigger('blur')
            if ($('#janelaFormPerimetro :input:visible:enabled.is-invalid').length) {
                alert('Verificar os erros')
                return false
            }

            this.formPerimetro.preload = true
            if (this.formPerimetro.editando) {
                axios
                    .put(`${URL_ADMIN}/controle-ponto/perimetros/${this.formPerimetro.id}`, this.formPerimetro)
                    .then((response) => {
                        this.formPerimetro.preload = false
                        this.formPerimetro.save = true
                        this.atualizarListaPeriemetros()
                        this.atualizarListaFuncionarios()
                    })
                    .catch((error) => {
                        this.formPerimetro.preload = false
                        this.atualizarListaPeriemetros()
                        this.atualizarListaFuncionarios()
                    })
            } else {
                axios
                    .post(`${URL_ADMIN}/controle-ponto/perimetros`, this.formPerimetro)
                    .then((response) => {
                        this.formPerimetro.preload = false
                        this.formPerimetro.save = true
                        this.atualizarListaPeriemetros()
                        this.atualizarListaFuncionarios()
                    })
                    .catch((error) => {
                        this.formPerimetro.preload = false
                        this.atualizarListaPeriemetros()
                        this.atualizarListaFuncionarios()
                    })
            }
        },
        formApagarPerimetro(id) {
            this.formPerimetro.id = id
            this.formPerimetro.save = false
        },
        apagarPerimetro: function () {
            this.formPerimetro.preload = true

            axios
                .delete(`${URL_ADMIN}/controle-ponto/perimetros/${this.formPerimetro.id}`, null)
                .then((data) => {
                    this.formPerimetro.preload = false
                    this.formPerimetro.save = true
                    this.atualizarListaPeriemetros()
                    this.atualizarListaFuncionarios()
                })
                .catch((data) => {
                    this.formPerimetro.preload = false
                    this.atualizarListaPeriemetros()
                    this.atualizarListaFuncionarios()
                })
        },
        //-----Perimetros a funcionarios------------

        carregou(dados) {
            this.listaFuncionarios = dados
            this.controle.carregando = false
        },

        carregando() {
            this.controle.carregando = true
        },

        atualizar() {
            this.$refs && this && this && this.$refs && this.$refs.componente && (this.$refs.componente.atual = 1)
            this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
        },

        // atualizarListaFuncionarios() {
        //     this.$refs.paginacaoFuncionarios.atual = 1;
        //     this.$refs.paginacaoFuncionarios.buscar();
        // },

        selecionarTodosFuncionarios() {
            if (this.todosFuncionariosSelecionados) {
                this.listaFuncionarios.forEach((user) => {
                    if (!this.funcionariosSelecionados.includes(user.id)) {
                        this.funcionariosSelecionados.push(user.id)
                    }
                })
            } else {
                this.listaFuncionarios.forEach((user) => {
                    let index = this.funcionariosSelecionados.indexOf(user.id)
                    if (index !== -1) {
                        this.funcionariosSelecionados.splice(index, 1)
                    }
                })
            }
        },
        selecionarFuncionario(user) {
            if (!this.funcionariosSelecionados.includes(user.id)) {
                this.funcionariosSelecionados.push(user.id)
            } else {
                let index = this.funcionariosSelecionados.indexOf(user.id)
                if (index !== -1) {
                    this.funcionariosSelecionados.splice(index, 1)
                }
            }
            this.checarMarcarTodosFuncionarios()
        },

        selecionarPerimetro(perimetro) {
            if (!this.formPerimetroFuncionarios.perimetrosSelecionados.includes(perimetro.id)) {
                this.formPerimetroFuncionarios.perimetrosSelecionados.push(perimetro.id)
            } else {
                let index = this.formPerimetroFuncionarios.perimetrosSelecionados.indexOf(perimetro.id)
                if (index !== -1) {
                    this.formPerimetroFuncionarios.perimetrosSelecionados.splice(index, 1)
                }
            }
            this.checarMarcarTodosFuncionarios()
            this.formPerimetroFuncionarios.perimetrosSelecionados.length === 0
                ? (this.formPerimetroFuncionarios.perimetro_id = 0)
                : (this.formPerimetroFuncionarios.perimetro_id = null)
        },

        checarMarcarTodosFuncionarios() {
            let quantidade = this.listaFuncionarios.length
            let marcados = this.listaFuncionarios.filter((funcionario) => this.funcionariosSelecionados.includes(funcionario.id)).length
            this.todosFuncionariosSelecionados = quantidade === marcados
        },

        formAssociarAvaliador() {
            this.editando = true
            this.preload = false
            this.form.autocomplete_label_avaliador = ''
            this.form.avaliadores = []
            //Get para pegar os Avaliadores qnd for 1
            if (this.funcionariosSelecionados.length === 1) {
                // axios.get(`${URL_ADMIN}/cadastro/avaliacoes/avaliadores/`).then(({data}) =>{
                //     this.form.avaliadores = data;
                // }).catch((error)=>{
                //
                // });
            }

            if (this.funcionariosSelecionados.length > 1) {
                this.form.avaliadores = []
            }

            // this.listaPerimetros = _.cloneDeep(this.listaPerimetrosDefault);
            // this.formPerimetroFuncionarios.perimetro_id=0;
            // this.formPerimetroFuncionarios.perimetrosSelecionados=[];
            //
            // if(this.funcionariosSelecionados.length === 1){
            //     let funcionarioId = this.formPerimetroFuncionarios.funcionariosSelecionados[0];
            //     let perimetros = _.filter(this.listaFuncionarios, {'id':funcionarioId})[0].perimetros_funcionario;
            //
            //     console.log(perimetros);
            //
            //     this.listaPerimetros.forEach((item) => {
            //         perimetros.forEach((perimetro) => {
            //             if (item.id === perimetro.id){
            //                 this.formPerimetroFuncionarios.perimetrosSelecionados.push(perimetro.id);
            //                 item.selecionado = true;
            //             }
            //         });
            //     });
            //     this.formPerimetroFuncionarios.perimetro_id=null;
            // }
            //
            // this.formPerimetroFuncionarios.update=false;
        },
        assosicarAvaliadores() {
            this.formPerimetroFuncionarios.preload = true
            axios
                .put(`${URL_ADMIN}/controle-ponto/perimetros/assosicarPerimetro`, this.formPerimetroFuncionarios)
                .then((response) => {
                    this.formPerimetroFuncionarios.preload = false
                    this.formPerimetroFuncionarios.update = true
                    this.atualizarListaFuncionarios()
                    this.checarMarcarTodosFuncionarios()
                })
                .catch((error) => {
                    this.formPerimetroFuncionarios.preload = false
                    this.atualizarListaFuncionarios()
                })
        },

        resetFuncionariosSelecionados() {
            if (this.formPerimetroFuncionarios.update) {
                this.formPerimetroFuncionarios.funcionariosSelecionados = []
                this.todosFuncionariosSelecionados = false
            }
        }
    }
})

registerGlobals(app)
app.mount('#app')

<template>
    <div>
        <modal id="janelaAssociarAvaliador" titulo="Associar avaliadores" :fechar="!preload"
               size="g">
            <template slot="conteudo">
                <fieldset v-if="editando">
                    <legend>Avaliadores</legend>
                    <div class="form-group">
                        <label>Avaliador</label>
                        <autocomplete :caminho="`autocomplete/buscaAvaliadoresAtivos`"
                                      :formsm="true"
                                      v-model="form.autocomplete_label_avaliador"
                                      placeholder="Selecione um(a) avaliador(a)"
                                      :id="`avaliador_${hash}`"
                                      metodo="post"
                                      :disabled="form.avaliadores.length >=4"
                                      :dados="{ funcionariosSelecionados: funcionariosSelecionados }"
                                      @onselect="selecionaAvaliador"
                                      @onblur="resetaCampo"
                        ></autocomplete>
                        <div class="alert alert-warning mt-3" v-show="form.avaliadores.length >=4">
                            <i class="fa fa-exclamation-triangle"></i> O número máximo de avaliadores foi atingido.
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-condensed bg-white"
                               v-if="form.avaliadores.length > 0">
                            <thead>
                            <tr class="bg-default">
                                <th class="text-center">Nome</th>
                                <th class="text-center">Remover</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(user, index) in form.avaliadores" :key="user.avaliador.id">
                                <td class="text-center">
                                    {{ user.avaliador.nome }}<br><span class="badge badge-success ml-1 p-1" v-if="index === 0">Principal</span>
                                </td>
                                <td class="text-center">
                                    <a href="javascript://" class="btn btn-sm btn-danger"
                                       @click.prevent="removerLIAvaliador(index)">
                                        <i class="fa fa-times" aria-hidden="true"></i>
                                    </a>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </fieldset>
            </template>
            <template slot="rodape">
                <button :disabled="listaFuncionarios.length === 0" v-if="!preload && !update"
                        class="btn btn-sm btn-success"
                        type="button" @click="associarAvaliadores">
                    <i class="fas fa-link"></i> Associar
                </button>
            </template>
        </modal>

        <div class="row">
            <div class="col-12 py-2">
                <h4 v-show="!controle.carregando && listaFuncionarios.length===0" class="text-center mt-3"> Sem
                    colaboradores cadastrados</h4>
                <form @submit.prevent="atualizar">
                    <div class="form-row align-items-center">
                        <div class="col-sm-3 my-1">
                            <label class="sr-only">Buscar</label>
                            <div class="input-group mb-2">
                                <input type="text" class="form-control" placeholder="Nome colaborador"
                                       v-model="controle.dados.campoBusca"
                                       @keyup="controle.dados.campoBusca=== '' ? atualizar() : false">
                                <div class="input-group-append">
                                    <div class="input-group-text"><i class="fas fa-search"></i></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto mb-2">
                            <button type="button" class="btn btn-secondary"
                                    :disabled="funcionariosSelecionados.length===0"
                                    data-toggle="modal" data-target="#janelaAssociarAvaliador"
                                    @click="formAssociarAvaliador">
                                <i class="fas fa-link"></i> Associar Avaliadores
                            </button>
                        </div>
                    </div>
                </form>
                <preload v-if="controle.carregando"></preload>
                <table class="tabela"
                       v-if="!controle.carregando && listaFuncionarios.length > 0">
                    <thead>
                    <tr class="bg-default">
                        <th>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" v-model="todosFuncionariosSelecionados"
                                       @change="selecionarTodosFuncionarios">
                                <label class="form-check-label" style="visibility: hidden"></label>
                            </div>
                        </th>
                        <th>Nome</th>
                        <th>Avaliadores</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr class="pointer" v-for="funcionario in listaFuncionarios"
                        @click="selecionarFuncionario(funcionario)">
                        <td data-label="id" class="text-center" width="10%">
                            <div class="form-check">
                                <input type="checkbox" :value="funcionario.id" class="form-check-input"
                                       v-model="funcionariosSelecionados">
                                <label class="form-check-label" style="visibility: hidden"></label>
                            </div>
                        </td>
                        <td data-label="nome">{{ funcionario.curriculo.nome }}</td>
                        <td data-label="avaliadores">
                                <span class="badge badge-secondary ml-1 p-1" v-if="funcionario.avaliadores.length"
                                      v-for="avaliadores in funcionario.avaliadores">
                                    {{ avaliadores.avaliador.nome }}
                                </span>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <controle-paginacao
                    class="d-flex justify-content-center"
                    id="controle"
                    ref="componente"
                    :url="urlPaginacao"
                    por-pagina="100"
                    :dados="controle.dados"
                    v-on:carregou="carregou"
                    v-on:carregando="carregando">
                </controle-paginacao>

            </div>

        </div>
    </div>
</template>

<script>
export default {
    name: "vinculaAvaliador",
    props: {
        obj: {
            type: Object,
            required: true,
            default: () => {
                return {}
            }
        }
    },
    data() {
        return {
            URL_ADMIN,
            preload: true,
            editando: false,
            update: false,
            janelaTitulo: 'Avaliador',
            hash: `mybp_${parseInt((Math.random() * 999999))}`,

            form: {
                autocomplete_label_avaliador: '',
                autocomplete_label_avaliador_anterior: '',
                avaliador_id: '',
                avaliadores: [],
                avaliadoresDelete: [],
                feedbacks: [],
                avaliacao_id: '',
                id: '',
            },
            formDefault: null,

            funcionariosSelecionados: [],
            todosFuncionariosSelecionados: false,
            listaFuncionarios: [],

            urlPaginacao: `${URL_ADMIN}/cadastro/avaliacoes/avaliadores/atualizar`,
            controle: {
                carregando: false,
                dados: {
                    campoBusca: "",
                    avaliacao_id: "",
                }
            }
        }
    },
    mounted() {
        this.controle.dados.avaliacao_id = this.obj.id;
        this.form.avaliacao_id = this.obj.id;
        this.formDefault = _.cloneDeep(this.form);
        this.atualizar();
    },
    methods: {
        removerLIAvaliador(index) {
            if (this.editando && !this.form.avaliadores[index].novo) {
                this.form.avaliadoresDelete.push(this.form.avaliadores[index].id);
            }
            this.form.avaliadores.splice(index, 1);
        },
        selecionaAvaliador(obj) {
            const user = {
                avaliacao_id: this.form.avaliacao_id,
                feedback_id: this.form.id,
                avaliador:{
                    novo: true,
                    id: obj.id,
                    nome: obj.nome,
                }
            }

            let atual = this.form.avaliadores.findIndex(val => val.id === user.avaliador.id);

            if (atual < 0) {//Se não existir ainda no array
                this.form.avaliadores.push(user);
            } else {
                mostraErro("", `Avaliador(a) ${user.avaliador.nome} já está na lista.`);
                this.form.autocomplete_label_avaliador = "";
                return false;
            }
            this.form.autocomplete_label_avaliador = "";
        },

        resetaCampo() {
            if (this.form.autocomplete_label_avaliador_anterior !== this.form.autocomplete_label_avaliador) {
                this.form.autocomplete_label_avaliador_anterior = "";
                this.form.autocomplete_label_avaliador = "";
                this.form.avaliador_id = "";
            }
        },

        selecionarTodosFuncionarios() {
            if (this.todosFuncionariosSelecionados) {
                this.listaFuncionarios.forEach((user) => {
                    if (!this.funcionariosSelecionados.includes(user.id)) {
                        this.funcionariosSelecionados.push(user.id);
                    }
                });
            } else {
                this.listaFuncionarios.forEach((user) => {
                    let index = this.funcionariosSelecionados.indexOf(user.id);
                    if (index !== -1) {
                        this.funcionariosSelecionados.splice(index, 1);
                    }
                });
            }
        },
        selecionarFuncionario(user) {
            if (!this.funcionariosSelecionados.includes(user.id)) {
                this.funcionariosSelecionados.push(user.id);
            } else {
                let index = this.funcionariosSelecionados.indexOf(user.id);
                if (index !== -1) {
                    this.funcionariosSelecionados.splice(index, 1);
                }
            }
            this.checarMarcarTodosFuncionarios();
        },

        checarMarcarTodosFuncionarios() {
            let quantidade = this.listaFuncionarios.length;
            let marcados = this.listaFuncionarios.filter((funcionario => this.funcionariosSelecionados.includes(funcionario.id))).length
            this.todosFuncionariosSelecionados = quantidade === marcados;
        },

        formAssociarAvaliador() {
            this.editando = true;
            this.preload = false;
            this.form.autocomplete_label_avaliador = '';
            this.form.avaliadores = [];
            this.form.feedbacks = this.funcionariosSelecionados;
            if (this.form.feedbacks.length === 1) {
                axios.post(`${URL_ADMIN}/cadastro/avaliacoes/avaliadores/avaliador-associado/`,{
                    feedback_id: this.form.feedbacks[0],
                    avaliacao_id: this.form.avaliacao_id,
                }).then(({data}) =>{
                    this.form.avaliadores = data;
                }).catch((error)=>{
                });
            }

            if (this.form.feedbacks.length > 1) {
                this.form.avaliadores = [];
            }

        },
        associarAvaliadores() {
            this.form.feedbacks = this.funcionariosSelecionados;
            console.log(this.form);
            // this.preload = true;
            // axios.post(`${URL_ADMIN}/cadastro/avaliacoes/avaliadores/associar/`,{
            //     feedback_id: this.form.feedbacks[0],
            //     avaliacao_id: this.form.avaliacao_id,
            // }).then(({data}) =>{
            //     this.form.avaliadores = data;
            // }).catch((error)=>{
            // });
            // axios.put(`${URL_ADMIN}/controle-ponto/perimetros/assosicarPerimetro`, this.form)
            //     .then(response => {
            //         this.preload = false;
            //         this.update = true;
            //         this.atualizar();
            //         this.checarMarcarTodosFuncionarios();
            //     }).catch(error => {
            //     this.preload = false;
            //     this.atualizar();
            // });
        },

        resetFuncionariosSelecionados() {
            if (this.update) {
                this.form.funcionariosSelecionados = [];
                this.todosFuncionariosSelecionados = false;
            }
        },

        carregou(dados) {
            this.listaFuncionarios = dados;
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
}
</script>

<style scoped>

</style>

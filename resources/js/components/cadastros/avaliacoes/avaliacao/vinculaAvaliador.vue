<template>
    <div>
        <modal id="janelaAssociarAvaliador" titulo="Associar avaliadores" :fechar="!preload"
               size="g">
            <template slot="conteudo">
                <preload v-if="preload" :label="update ? 'Associando aguarde' : 'Carregando'"></preload>
                <div v-if="!preload">
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
                                        {{ user.avaliador.nome }}<br>
                                        <span class="badge badge-success ml-1 p-1" v-if="index === 0">Principal</span>
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
                </div>
            </template>
            <template slot="rodape">
                <button :disabled="preload" v-if="!preload && !update"
                        class="btn btn-sm btn-primary"
                        type="button" @click="associarAvaliadores">
                    <i class="fa fa-save"></i> Salvar
                </button>
            </template>
        </modal>

        <div class="alert alert-warning text-center" v-if="!podeAssociar">
            <i class="fa fa-exclamation-triangle"></i>
            Só é permitido associar avaliadores para avaliações com status de <strong>Aguardando Inicio</strong>
        </div>

        <div class="row">
            <div class="col-12 py-2">
                <form @submit.prevent="atualizar">
                    <div class="form-row align-items-center">
                        <div class="col-sm-3 my-1">
                            <label class="sr-only">Buscar</label>
                            <div class="input-group mb-2">
                                <input type="text" class="form-control form-control-sm" placeholder="Nome colaborador"
                                       v-model="controle.dados.campoBusca"
                                       @keyup="controle.dados.campoBusca=== '' ? atualizar() : false">
                                <div class="input-group-append" style="height: 26.5px">
                                    <div class="input-group-text" style="cursor: pointer" @click.prevent="atualizar"><i
                                        class="fas fa-search"></i></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto mb-2">
                            <button type="button" class="btn btn-primary btn-sm"
                                    :disabled="funcionariosSelecionados.length===0"
                                    data-toggle="modal" data-target="#janelaAssociarAvaliador"
                                    @click="formAssociarAvaliador">
                                <i class="fas fa-link"></i> Associar Avaliadores
                            </button>
                        </div>
                    </div>
                </form>
                <preload v-if="controle.carregando"></preload>

                <div v-show="!controle.carregando && listaFuncionarios.length===0"
                     class="alert alert-warning text-center mt-3">
                    <i class="fa fa-exclamation-triangle"></i> Registro não encontrado
                </div>

                <table class="tabela"
                       v-if="!controle.carregando && listaFuncionarios.length > 0">
                    <thead>
                    <tr class="bg-default">
                        <th class="text-center" style="width: 7%">
                            <div class="form-check" v-if="podeAssociar">
                                <input type="checkbox" class="form-check-input" v-model="todosFuncionariosSelecionados"
                                       @change="selecionarTodosFuncionarios">
                                <label class="form-check-label" style="visibility: hidden"></label>
                            </div>
                        </th>
                        <th class="text-left">Nome</th>
                        <th class="text-left">Avaliadores</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr class="pointer" v-for="funcionario in listaFuncionarios"
                        @click="selecionarFuncionario(funcionario)">
                        <td data-label="id" class="text-center">
                            <div class="form-check" v-if="podeAssociar">
                                <input type="checkbox" :value="funcionario.id" class="form-check-input"
                                       v-model="funcionariosSelecionados">
                                <label class="form-check-label" style="visibility: hidden"></label>
                            </div>
                        </td>
                        <td data-label="nome" class="text-left">{{ funcionario.nome }}</td>
                        <td data-label="avaliadores" class="text-left">
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
                funcionarios: [],
                avaliacao_id: '',
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
    computed: {
        podeAssociar() {
            return this.obj.status === 'Aguardando Inicio';
        }
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
                novo: true,
                avaliador: {
                    id: obj.id,
                    nome: obj.nome,
                }
            }

            let atual = this.form.avaliadores.findIndex(val => val.avaliador.id === user.avaliador.id);

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
            if (this.podeAssociar) {
                if (!this.funcionariosSelecionados.includes(user.id)) {
                    this.funcionariosSelecionados.push(user.id);
                } else {
                    let index = this.funcionariosSelecionados.indexOf(user.id);
                    if (index !== -1) {
                        this.funcionariosSelecionados.splice(index, 1);
                    }
                }
                this.checarMarcarTodosFuncionarios();
            }
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
            this.form.avaliadoresDelete = [];
            this.form.funcionarios = this.funcionariosSelecionados;
            if (this.form.funcionarios.length === 1) {
                axios.post(`${URL_ADMIN}/cadastro/avaliacoes/avaliadores/avaliador-associado/`, {
                    funcionario_id: this.form.funcionarios[0],
                    avaliacao_id: this.form.avaliacao_id,
                }).then(({data}) => {
                    this.form.avaliadores = data;
                }).catch((error) => {
                });
            }

            if (this.form.funcionarios.length > 1) {
                this.form.avaliadores = [];
            }

        },
        associarAvaliadores() {
            this.form.funcionarios = this.funcionariosSelecionados;
            this.preload = true;

            if (this.form.avaliadores.length >= 1) {
                this.form.avaliadores.forEach((avaliador, index) => {
                    avaliador.principal = index === 0;
                });
            }
            axios.post(`${URL_ADMIN}/cadastro/avaliacoes/avaliadores/associar/`, this.form)
                .then(({data}) => {
                    $(`#janelaAssociarAvaliador`).modal('hide');
                    this.form = _.cloneDeep(this.formDefault);
                    this.funcionariosSelecionados = [];
                    mostraSucesso("", "Avaliadores associados com sucesso.");
                    this.update = false;
                    this.atualizar();
                    this.preload = false;
                }).catch((error) => {
                this.preload = false;
                this.update = false;
            });
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

<template>
    <div>
        <modal id="janelaAssociarAvaliador" titulo="Associar avaliadores" :fechar="!preload"
               size="g">
            <template v-slot:conteudo>
                <preload v-if="preload" :label="update ? 'Associando aguarde' : 'Carregando'"></preload>
                <div v-if="!preload">
                    <fieldset style="margin-top: -7px">
                        <legend>Fluxo de avaliação</legend>
                        <ul class="fluxo_ul alert-link">
                            <li class="fluxo-item_li" v-for="f in fluxo" :key="f.id">
                                {{ f.label }}
                            </li>
                        </ul>
                    </fieldset>
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
                                    <th class="text-center">Avaliar como</th>
                                    <th class="text-center">Remover</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr class="table-warning" v-if="fluxo[0].label === 'Auto Avaliação'">
                                    <td class="text-center bold">Auto Avaliação</td>
                                    <td class="text-center bold">Auto Avaliação</td>
                                    <td class="text-center bold"></td>
                                </tr>

                                <tr v-for="(user, index) in form.avaliadores" :key="user.avaliador.id">
                                    <td class="text-center">
                                        {{ user.avaliador.nome }}<br>
                                        <!--                                        <span class="badge badge-success ml-1 p-1" v-if="index === 0">Principal</span>-->
                                    </td>
                                    <td class="text-center">
                                        <select class="form-control form-control-sm"
                                                v-model="user.avaliador.tipo_avaliador_id"
                                                onchange="valida_campo_vazio(this,1)"
                                                onblur="valida_campo_vazio(this,1)"
                                                @change.prevent="changeAvaliaComo(index, $event.target.value)"
                                        >
                                            <option value="">Selecione</option>
                                            <option v-for="item in obj.fluxo" :value="item.id" :key="item.id">
                                                {{ item.label }} {{ item.principal ? '- (Avaliador Final)' : '' }}
                                            </option>
                                        </select>
                                        <!--                                        {{ user.avaliador.nome }}<br>-->
                                        <!--                                        <span class="badge badge-success ml-1 p-1" v-if="index === 0">Principal</span>-->
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
            <template v-slot:rodape>
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
            <div class="col-12">
                <fieldset style="margin-top: -7px">
                    <legend>Fluxo de avaliação</legend>
                    <ul class="fluxo_ul alert-link">
                        <li class="fluxo-item_li" v-for="f in fluxo" :key="f.id">
                            {{ f.label }}
                        </li>
                    </ul>
                </fieldset>

                <form @submit.prevent="atualizar">
                    <div class="form-row align-items-center">
                        <div class="col-sm-3 my-1">
                            <label class="sr-only">Buscar</label>
                            <div class="input-group mb-2">
                                <input type="text" class="form-control form-control-sm" placeholder="Nome colaborador"
                                       v-model="controle.dados.campoBusca"
                                       @keyup="controle.dados.campoBusca === '' ? atualizar() : false">
                                <div class="input-group-append" style="height: 26.5px">
                                    <div class="input-group-text" style="cursor: pointer" @click.prevent="atualizar"><i
                                        class="fas fa-search"></i></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-3 my-1">
                            <label class="sr-only">Vinculados</label>
                            <div class="input-group mb-2">
                                <select class="form-control form-control-sm" v-model="controle.dados.campoVinculados"
                                        @change="atualizar">
                                    <option value="">Todos</option>
                                    <option :value="true">Vinculados</option>
                                    <option :value="false">Não vinculados</option>
                                </select>
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
                                <span class="badge  ml-1 p-1"
                                      :class="avaliadores.tipo_avaliador_principal ? 'badge-success' : 'badge-secondary'"
                                      v-if="funcionario.avaliadores.length"
                                      :key="avaliadores.avaliador.id"
                                      v-for="avaliadores in funcionario.avaliadores">
                                         {{ avaliadores.avaliador.nome }} - {{ avaliadores.tipo_avaliador_label }}
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
    name: 'vinculaAvaliador',
    props: {
        tipo_pj: {
            type: Boolean,
            required: false,
            default: false
        },
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
            // _,

            form: {
                autocomplete_label_avaliador: '',
                autocomplete_label_avaliador_anterior: '',
                avaliador_id: '',
                avaliadores: [],
                avaliadoresDelete: [],
                funcionarios: [],
                avaliacao_id: ''
            },
            formDefault: null,

            funcionariosSelecionados: [],
            todosFuncionariosSelecionados: false,
            listaFuncionarios: [],
            fluxo: [],

            urlPaginacao: `${URL_ADMIN}/cadastro/avaliacoes/avaliadores/atualizar`,
            controle: {
                carregando: false,
                dados: {
                    campoBusca: '',
                    avaliacao_id: '',
                    campoVinculados: '',
                    tipo_pj: false
                }
            }
        }
    },
    mounted() {
        this.controle.dados.avaliacao_id = this.obj.id
        this.form.avaliacao_id = this.obj.id
        this.formDefault = _.cloneDeep(this.form)
        this.atualizar()
    },
    computed: {
        podeAssociar() {
            return this.obj.status === 'Aguardando Inicio'
        },
        fluxoSemAutoAvaliacao() {
            return this.fluxo.filter(val => val.label !== 'Auto Avaliação')
        },
        avaliadorFluxo() {
            return this.checkSeTipoAvaliadorIdEstaNoFluxoSemAutoAvaliacao()
        }
    },
    methods: {
        changeAvaliaComo(index) {
            let avaliador_id_selecionado = this.form.avaliadores[index].avaliador.tipo_avaliador_id
            this.form.avaliadores[index].avaliador.tipo_avaliador_id = ''
            this.form.avaliadores[index].avaliador.tipo_avaliador_label = ''
            this.form.avaliadores[index].avaliador.tipo_avaliador_principal = false

            if (avaliador_id_selecionado !== '') {
                let fluxoSelecionado = this.obj.fluxo.find(val => val.id === avaliador_id_selecionado)

                this.form.avaliadores[index].avaliador.tipo_avaliador_id = fluxoSelecionado.id
                this.form.avaliadores[index].avaliador.tipo_avaliador_label = fluxoSelecionado.label
                this.form.avaliadores[index].avaliador.tipo_avaliador_principal = fluxoSelecionado.principal
            }

        },
        removerLIAvaliador(index) {
            if (this.editando && !this.form.avaliadores[index].novo) {
                this.form.avaliadoresDelete.push(this.form.avaliadores[index].id)
            }
            this.form.avaliadores.splice(index, 1)
        },
        selecionaAvaliador(obj) {
            const user = {
                avaliacao_id: this.form.avaliacao_id,
                novo: true,
                avaliador: {
                    id: obj.id,
                    nome: obj.nome,
                    tipo_avaliador_id: '',
                    tipo_avaliador_label: '',
                    tipo_avaliador_principal: false
                }
            }

            let atual = this.form.avaliadores.findIndex(val => val.avaliador.id === user.avaliador.id)

            if (atual < 0) {//Se não existir ainda no array
                this.form.avaliadores.push(user)
            } else {
                mostraErro('', `Avaliador(a) ${user.avaliador.nome} já está na lista.`)
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
            if (this.podeAssociar) {
                if (!this.funcionariosSelecionados.includes(user.id)) {
                    this.funcionariosSelecionados.push(user.id)
                } else {
                    let index = this.funcionariosSelecionados.indexOf(user.id)
                    if (index !== -1) {
                        this.funcionariosSelecionados.splice(index, 1)
                    }
                }
                this.checarMarcarTodosFuncionarios()
            }
        },

        checarMarcarTodosFuncionarios() {
            let quantidade = this.listaFuncionarios.length
            let marcados = this.listaFuncionarios.filter((funcionario => this.funcionariosSelecionados.includes(funcionario.id))).length
            this.todosFuncionariosSelecionados = quantidade === marcados
        },

        formAssociarAvaliador() {
            this.editando = true
            this.preload = false
            this.form.autocomplete_label_avaliador = ''
            this.form.avaliadores = []
            this.form.avaliadoresDelete = []
            this.form.funcionarios = this.funcionariosSelecionados
            if (this.form.funcionarios.length === 1) {
                axios.post(`${URL_ADMIN}/cadastro/avaliacoes/avaliadores/avaliador-associado/`, {
                    funcionario_id: this.form.funcionarios[0],
                    avaliacao_id: this.form.avaliacao_id
                }).then(({ data }) => {
                    this.form.avaliadores = data.avaliadores
                    this.fluxo = data.fluxo
                }).catch((error) => {
                })
            }

            if (this.form.funcionarios.length > 1) {
                this.form.avaliadores = []
            }

        },

        checkSePossuiSomenteUmAvaliadorPrincipal() {
            let avaliadoresPrincipais = this.form.avaliadores.filter(val => val.avaliador.tipo_avaliador_principal)
            return avaliadoresPrincipais.length > 1
        },

        checkSeNaoPossuiAvaliadorPrincipal() {
            let avaliadoresPrincipais = this.form.avaliadores.filter(val => val.avaliador.tipo_avaliador_principal)
            return avaliadoresPrincipais.length === 0
        },

        checkSeTipoAvaliadorIdEstaNoFluxoSemAutoAvaliacao() {
            // return this.fluxoSemAutoAvaliacao.map(val => val.id).includes(this.form.avaliadores[0].avaliador.tipo_avaliador_id);
            _.forEach(this.form.avaliadores, (avaliador) => {
                if (!this.fluxoSemAutoAvaliacao.map(val => val.id).includes(avaliador.avaliador.tipo_avaliador_id)) {
                    mostraErro('', 'Verifique o fluxo de avaliação.')
                    return false
                }
            })
        },

        verificarFluxoSemAutoAvaliacao() {
            for (const item of this.fluxoSemAutoAvaliacao) {
                for (const avaliador of this.form.avaliadores) {
                    if (avaliador.avaliador.tipo_avaliador_id === item.id) {
                        return true // Se encontrar, retorna true
                    }
                }
            }
            mostraErro('', 'Verifique o fluxo de avaliação.')
            return false // Se não encontrar, retorna false
        },

        associarAvaliadores() {
            this.form.funcionarios = this.funcionariosSelecionados

            $('#janelaAssociarAvaliador :input:visible').trigger('blur')
            if ($('#janelaAssociarAvaliador :input:visible.is-invalid').length) {
                mostraErro('', 'Verificar os erros')
                return false
            }

            if (this.form.avaliadores.length >= 1) {
                if (this.checkSePossuiSomenteUmAvaliadorPrincipal()) {
                    mostraErro('', 'Deve haver somente um avaliador principal.')
                    this.preload = false
                    return false
                }

                if (this.checkSeNaoPossuiAvaliadorPrincipal()) {
                    mostraErro('', 'Deve haver um avaliador principal.')
                    this.preload = false
                    return false
                }
            }

            this.preload = true
            axios.post(`${URL_ADMIN}/cadastro/avaliacoes/avaliadores/associar/`, this.form)
                .then(({ data }) => {
                    $(`#janelaAssociarAvaliador`).modal('hide')
                    this.form = _.cloneDeep(this.formDefault)
                    this.funcionariosSelecionados = []
                    mostraSucesso('', 'Avaliadores associados com sucesso.')
                    this.update = false
                    this.atualizar()
                    this.preload = false
                }).catch((error) => {
                this.preload = false
                this.update = false
            })
        },

        resetFuncionariosSelecionados() {
            if (this.update) {
                this.form.funcionariosSelecionados = []
                this.todosFuncionariosSelecionados = false
            }
        },

        carregou(dados) {
            this.listaFuncionarios = dados.funcionarios
            this.fluxo = dados.fluxo
            this.controle.carregando = false
        },

        carregando() {
            this.controle.carregando = true
        },

        atualizar() {
            this.$refs.componente.atual = 1
            this.$refs.componente.buscar()
        }
    }
}
</script>

<style scoped>
.fluxo_ul {
    display: flex;
    align-items: center;
    list-style: none;
    padding: 0;
}

.fluxo-item_li {
    display: flex;
    align-items: center;
}

.fluxo-item_li:not(:last-child)::after {
    content: '-->';
    letter-spacing: -2px;
    color: #417f9d;
    margin: 0 10px;
}

.fluxo-item_li:last-child::after {
    content: '';
}

.fluxo-link {
    text-decoration: none;
    color: black;
}
</style>

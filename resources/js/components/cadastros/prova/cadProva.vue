<template>
    <div :id="hash">
        <modal id="janelaCadastrar" :titulo="titulo_janela" :fechar="!preload" :size="90">
            <template #conteudo>
                <preload v-show="preload"></preload>
                <div v-if="!preload && !cadastrado">
                    <fieldset>
                        <legend>Informações</legend>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Titulo</label>
                                    <input
                                        v-model="form.titulo"
                                        class="form-control"
                                        type="text"
                                        placeholder="Informe o Titulo"
                                        onblur="valida_campo_vazio(this, 1)"
                                    />
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Tipo da prova</label>
                                    <select
                                        class="form-control"
                                        onblur="valida_campo_vazio(this, 1)"
                                        onchange="valida_campo_vazio(this, 1)"
                                        v-model="form.tipo_prova"
                                    >
                                        <option :value="''">Selecione</option>
                                        <option :value="'objetiva'">Objetiva</option>
                                        <option :value="'subjetiva'">Subjetiva</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label>Ativo</label>
                                    <select
                                        class="form-control"
                                        onblur="valida_campo_vazio(this, 1)"
                                        onchange="valida_campo_vazio(this, 1)"
                                        v-model="form.ativo"
                                    >
                                        <option :value="''">Selecione</option>
                                        <option :value="true">Sim</option>
                                        <option :value="false">Não</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset v-if="form.tipo_prova === 'objetiva'">
                        <legend>Questões Objetivas</legend>
                        <button class="btn btn-sm btn-primary mb-2" @click="addLiQuestao($event.target)">Adicionar Questão</button>
                        <div class="accordion" id="prova">
                            <div class="card mb-3 border" v-for="(objperg, ind) in form.perguntas" v-show="form.perguntas.length > 0">
                                <div class="card-header" style="background: #072433; color: white" :id="objperg.id">
                                    <h2 class="mb-0">
                                        <a
                                            class="btn btn-link btn-block text-left"
                                            href="javascript://"
                                            data-toggle="collapse"
                                            :data-target="`#collapse${objperg.id}`"
                                            aria-expanded="true"
                                            :aria-controls="`collapse${objperg.id}`"
                                        >
                                            Questão - {{ ind + 1 }}
                                        </a>
                                    </h2>
                                </div>

                                <div :id="`collapse${objperg.id}`" class="collapse show" :aria-labelledby="objperg.id" data-parent="#prova">
                                    <div class="card-body">
                                        <a class="btn btn-sm btn-danger" href="javascript://" @click="removerLIQuestao(ind)">
                                            <i class="fa fa-trash"></i> Apagar questão {{ ind + 1 }}
                                        </a>

                                        <div class="col-12 mt-2">
                                            <label>Enunciado</label>
                                            <editor :api-key="tinyProva.key" v-model="objperg.enunciado" :init="tinyProva"></editor>
                                        </div>

                                        <div class="col-12 mt-3 mb-3">
                                            <button class="btn btn-sm btn-primary" @click="addLIResposta(ind, $event.target)">Adicionar opção</button>
                                        </div>

                                        <div
                                            class="col-12 mt-2 mb-2"
                                            v-show="form.perguntas[ind].respostas.length > 0"
                                            v-for="(obj, index) in form.perguntas[ind].respostas"
                                            :key="obj.id"
                                        >
                                            <fieldset>
                                                <legend>Opção {{ index + 1 }}</legend>

                                                <editor :api-key="tinyProva.key" v-model="obj.resposta" :init="tinyProva"></editor>

                                                <div class="row">
                                                    <div class="form-group col-12 mt-3 mb-2">
                                                        <div class="input-group">
                                                            <span class="input-group-prepend">
                                                                <span class="input-group-text" id="basic-addon1">Resposta</span>
                                                            </span>
                                                            <select class="custom-select" v-model="obj.correto">
                                                                <option :value="true">Sim</option>
                                                                <option :value="false">Não</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="form-group col-12 mt-3 mb-2">
                                                        <button class="btn btn-sm btn-danger mt-2 mb-2" @click="removerLIResposta(ind, index)">
                                                            Remover opção
                                                        </button>

                                                        <button class="btn btn-sm btn-primary mt-2 mb-2 ml-2" @click="addLIResposta(ind, $event.target)">
                                                            Adicionar outra opção
                                                        </button>
                                                    </div>
                                                </div>
                                            </fieldset>
                                        </div>
                                    </div>

                                    <a class="btn btn-sm btn-danger" href="javascript://" @click="removerLIQuestao(ind)">
                                        <i class="fa fa-trash"></i> Apagar questão {{ ind + 1 }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset v-if="form.tipo_prova === 'subjetiva'">
                        <legend>Questões Subjetivas</legend>
                        <button class="btn btn-sm btn-primary mb-2" @click="addLiQuestao($event.target)">Adicionar Questão</button>
                        <div class="accordion" id="provaSub">
                            <div class="card mb-3 border" v-for="(objperg, ind) in form.perguntas" v-show="form.perguntas.length > 0">
                                <div class="card-header" style="background: #072433; color: white" :id="objperg.id">
                                    <h2 class="mb-0">
                                        <a
                                            class="btn btn-link btn-block text-left"
                                            href="javascript://"
                                            data-toggle="collapse"
                                            :data-target="`#collapse${objperg.id}`"
                                            aria-expanded="true"
                                            :aria-controls="`collapse${objperg.id}`"
                                        >
                                            Questão - {{ ind + 1 }}
                                        </a>
                                    </h2>
                                </div>

                                <div :id="`collapse${objperg.id}`" class="collapse show" :aria-labelledby="objperg.id" data-parent="#provaSub">
                                    <div class="card-body">
                                        <a class="btn btn-sm btn-danger" href="javascript://" @click="removerLIQuestao(ind)">
                                            <i class="fa fa-trash"></i> Apagar questão {{ ind + 1 }}
                                        </a>

                                        <div class="col-12 mt-2">
                                            <label>Enunciado</label>
                                            <editor :api-key="tinyProva.key" v-model="objperg.enunciado" :init="tinyProva"></editor>
                                        </div>

                                        <div class="col-12 mt-2">
                                            <label>Quantidade de linhas para resposta</label>
                                            <input
                                                class="form-control"
                                                v-model="objperg.qnt_linhas"
                                                type="text"
                                                placeholder="Informe o número de linhas"
                                                onblur="valida_campo_vazio(this, 1)"
                                            />
                                        </div>
                                    </div>

                                    <a class="btn btn-sm btn-danger" href="javascript://" @click="removerLIQuestao(ind)">
                                        <i class="fa fa-trash"></i> Apagar questão {{ ind + 1 }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </template>
            <template #rodape>
                <button type="button" class="btn btn-sm btn-primary" v-show="editando && !preload" @click="alterar()">Alterar</button>
                <button type="button" class="btn btn-sm btn-primary" v-show="!editando && !preload" @click="cadastrar()">Cadastrar</button>
            </template>
        </modal>

        <!-- Filtro -->
        <fieldset>
            <legend>Filtro</legend>
            <form class="row" @submit.prevent="this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null">
                <div class="col-12 col-md-4">
                    <div class="form-group">
                        <label>Buscar</label>
                        <input
                            type="text"
                            placeholder="Buscar por título"
                            autocomplete="off"
                            class="form-control form-control-sm"
                            :disabled="controle.carregando"
                            v-model="controle.dados.campoBusca"
                        />
                    </div>
                </div>

                <div class="col-12 col-md-12">
                    <button type="button" class="btn btn-sm btn-success" :disabled="controle.carregando" @click="atualizar">
                        <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                        Atualizar
                    </button>

                    <button
                        type="button"
                        class="btn btn-sm btn-primary"
                        :disabled="controle.carregando"
                        @click="formNovo"
                        data-toggle="modal"
                        data-target="#janelaCadastrar"
                    >
                        <i class="fa fa-plus"></i> Cadastrar
                    </button>
                </div>
            </form>
        </fieldset>

        <div id="conteudo">
            <p class="mt-2 text-center" v-if="controle.carregando">
                <preload></preload>
            </p>

            <div class="alert alert-warning text-center" v-show="!controle.carregando && lista.length === 0">
                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
            </div>

            <div class="table-responsive" v-show="!controle.carregando && lista.length > 0">
                <table class="tabela">
                    <thead>
                        <tr class="bg-default">
                            <td class="text-center">Nº</td>
                            <td class="text-center">Titulo</td>
                            <td class="text-center">Qnt Quesões</td>
                            <td class="text-center">Ativo</td>
                            <td class="text-center">Ação</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in lista">
                            <td class="text-center">{{ item.id }}</td>
                            <td class="text-center">{{ item.titulo }}</td>
                            <td class="text-center">{{ item.qnt_questoes }}</td>
                            <td class="text-center">
                                <bt-ativo :rota="`cadastro/provas/${item.id}/ativa-desativa`" :model="item"></bt-ativo>
                            </td>
                            <td class="text-center">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-primary mb-1"
                                    data-toggle="modal"
                                    data-target="#janelaCadastrar"
                                    @click="alterarForm(item)"
                                >
                                    <i class="fa fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <controle-paginacao
                class="d-flex justify-content-center"
                id="controle"
                ref="componente"
                :url="urlPaginacao"
                :por-pagina="qntPag"
                :dados="controle.dados"
                v-on:carregou="carregou"
                v-on:carregando="carregando"
            ></controle-paginacao>
        </div>
    </div>
</template>

<script>
import controlePaginacao from '../../ControlePaginacao'
import editor from '@tinymce/tinymce-vue'
import modal from '../../Modal'
import { tinyProva } from '../../../utils'

export default {
    components: {
        modal,
        controlePaginacao,
        editor
    },
    props: {
        qntPag: {
            type: Number,
            required: false,
            default: 20
        },
        filtro: {
            type: Boolean,
            required: false,
            default: true
        },
        modal: {
            // modal Pai
            type: String,
            required: false,
            default: ''
        }
    },
    mounted() {
        this.atualizar()
        this.formDefault = _.cloneDeep(this.form)
    },
    data() {
        return {
            hash: String(Math.random()).substr(2),
            titulo_janela: '',
            tinyProva,

            preload: false,
            editando: false,
            cadastrado: false,

            form: {
                titulo: '',
                ativo: true,
                tipo_prova: '',
                perguntas: [],
                perguntasDelete: [],
                respostasDelete: []
            },

            formDefault: null,

            lista: [],

            urlPaginacao: `${URL_ADMIN}/cadastro/provas/atualizar`,
            controle: {
                carregando: false,
                dados: {
                    campoBusca: ''
                }
            }
        }
    },
    methods: {
        addLiQuestao() {
            let objperg = {}
            objperg.nova = true
            objperg.enunciado = ''
            objperg.qnt_linhas = ''
            objperg.respostas = []
            objperg.respostasDelete = []
            this.form.perguntas.push(objperg)
        },
        removerLIQuestao(index) {
            if (this.editando) {
                this.form.perguntasDelete.push(this.form.perguntas[index].id)
            }
            this.form.perguntas.splice(index, 1)
        },
        addLIResposta(index) {
            let obj = {}
            obj.nova = true
            obj.pergunta_id = ''
            obj.resposta = ''
            obj.correto = false
            this.form.perguntas[index].respostas.push(obj)
        },
        removerLIResposta(ind, index) {
            if (this.editando) {
                this.form.respostasDelete.push(this.form.perguntas[ind].respostas[index].id)
            }
            this.form.perguntas[ind].respostas.splice(index, 1)
        },

        formNovo() {
            this.form = _.cloneDeep(this.formDefault) //copia
            this.titulo_janela = 'Montagem da Prova'
            this.editando = false
            this.cadastrado = false
            this.preload = false
            formReset()
            setupCampo()
        },

        cadastrar() {
            $('#janelaCadastrar :input:visible').trigger('blur')
            if ($('#janelaCadastrar :input:visible.is-invalid').length) {
                mostraErro('', 'Verificar os erros')
                return false
            }
            this.preload = true
            axios
                .post(`${URL_ADMIN}/cadastro/provas`, this.form)
                .then((res) => {
                    if (res.status === 201) {
                        $('#janelaCadastrar').modal('hide')
                        mostraSucesso('', 'Prova cadastrada com sucesso')
                        this.cadastrado = true
                        this.preload = false
                        this.atualizar()
                    }
                })
                .catch((error) => {
                    this.cadastrado = false
                    this.preload = false
                })
        },

        alterarForm(simulado) {
            this.cadastrado = false
            this.editando = true
            this.titulo_janela = `Alterando Prova ${simulado.id}`
            this.preload = true

            this.form = _.cloneDeep(this.formDefault) //copia
            formReset()

            axios
                .get(`${URL_ADMIN}/cadastro/provas/${simulado.id}/editar`)
                .then((response) => {
                    Object.assign(this.form, response.data)
                    this.editando = true
                    setupCampo()
                    this.preload = false
                })
                .catch((error) => (this.preloadAjax = false))
        },

        alterar() {
            formReset()
            $('#janelaCadastrar :input:enabled').trigger('blur')

            if ($('#janelaCadastrar :input:enabled.is-invalid').length) {
                mostraErro('', 'Verificar os erros')
                return false
            }

            this.preload = true

            axios
                .put(`${URL_ADMIN}/cadastro/provas/${this.form.id}`, this.form)
                .then((response) => {
                    $('#janelaCadastrar').modal('hide')
                    mostraSucesso('', 'Prova alterada com sucesso')
                    this.preload = false
                    this.atualizado = true
                    this.atualizar()
                })
                .catch((error) => (this.preload = false))
        },
        carregou(dados) {
            this.lista = dados.itens
            this.controle.carregando = false
        },
        carregando() {
            this.controle.carregando = true
        },
        atualizar() {
            this.$refs && this && this && this.$refs && this.$refs.componente && (this.$refs.componente.atual = 1)
            this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
        }
    }
}
</script>

<style scoped>
.card-header {
    background-color: white;
}

.btn-link {
    font-weight: 400;
    color: white;
    text-decoration: none;
}

.btn-link:hover {
    color: #dddddd;
}
</style>

<template>
    <div id="componentePlanejamentoDiario">
        <modal ref="modalPlanejamentoDiario" :modal-pai="modal" :titulo="titulo_janela_form_planejamentodiario" id="janelaFormPlanejamentoDiario" :size="65">
            <template #conteudo>
                <p class="mt-2 text-center" v-if="preload"><i class="fa fa-spinner fa-pulse"></i>Carregando...</p>
                <div class="alert alert-success alert-dismissible" v-show="cadastrado">
                    <h6 class="text-center"><i class="icon fa fa-check"></i> Cadastrado com sucesso!</h6>
                </div>
                <div v-if="!preload && !cadastrado">
                    <fieldset>
                        <legend>Informações</legend>
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <date-picker label="Início" v-model="form.data" :disabled="editando"></date-picker>
                                </div>
                            </div>
                            <div class="col-12">
                                <label>Tarefas Agendadas</label>
                                <textarea class="form-control" :disabled="editando" v-model="form.tarefas_agendadas" type="text" cols="3"></textarea>
                            </div>
                            <div class="col-12">
                                <label>Importante</label>
                                <textarea class="form-control" :disabled="editando" v-model="form.importante" type="text" cols="3"></textarea>
                            </div>
                        </div>
                    </fieldset>

                    <button class="btn btn-sm mr-1 btn-primary mb-3" :disabled="editando" @click="addLITarefas"><i class="fa fa-plus"></i> Adicionar Tarefa</button>

                    <template v-if="form.tarefas.length > 0" >
                        <fieldset class="mb-2" v-for="(obj, index) in form.tarefas" :key="index + 1">
                            <legend>#Tarefa {{ index + 1 }}</legend>
                            <div class="row">
                                <div class="col-12">
                                    <label>Tarefa</label>
                                    <input v-model="obj.tarefa" :disabled="!obj.novo" onblur="valida_campo_vazio(this, 1)" class="form-control" />
                                </div>
                                <div class="col-12">
                                    <label>Status</label>
                                    <select class="form-control" onblur="valida_campo_vazio(this, 1)" onchange="valida_campo_vazio(this, 1)" v-model="obj.status">
                                        <option :value="''">Selecione</option>
                                        <option :value="'pendente'">Pendente</option>
                                        <option :value="'cancelado'">Cancelado</option>
                                        <option :value="'concluido'">Concluído</option>
                                    </select>
                                </div>
                                <div class="col-12 mt-3" v-show="obj.novo">
                                    <button class="btn btn-sm mr-1 btn-danger" @click="removerLITarefas(index)"><i class="fa fa-times"></i> Remover</button>

                                    <button class="btn btn-sm mr-1 btn-primary mt" @click="addLITarefas" v-show="index >= 1">
                                        <i class="fa fa-plus"></i> Adicionar
                                    </button>
                                </div>
                            </div>
                        </fieldset>
                    </template>
                </div>
            </template>
            <template #rodape>
                <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="!editando" @click="cadastrar()">Cadastrar</button>
                <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="editando" @click="alterarformPlanejamentoDiario()">Alterar</button>
            </template>
        </modal>

        <!-- Filtro -->
        <fieldset>
            <legend>Filtro</legend>
            <form class="row" @submit.prevent="$refs.componente && $refs.componente.buscar ? $refs.componente.buscar() : null">
                <div class="col-12 col-md-3">
                    <div class="form-group">
                        <label>Buscar</label>
                        <input
                            type="text"
                            placeholder="Buscar por conteudo"
                            autocomplete="off"
                            class="form-control"
                            :disabled="controle.carregando"
                            v-model="controle.dados.campoBusca"
                        />
                    </div>
                </div>

                <div class="col-12 col-md-12">
                    <button type="button" class="btn btn-sm mr-1 btn-success" :disabled="controle.carregando" @click="atualizar">
                        <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                        Atualizar
                    </button>

                    <button type="button" class="btn btn-sm mr-1 btn-primary mb-1" :disabled="controle.carregando" @click="formNovo">
                        <i class="fa fa-plus"></i> Cadastrar Planejamento Diário
                    </button>
                </div>
            </form>
        </fieldset>

        <div id="conteudo">
            <p class="mt-2 text-center" v-if="controle.carregando"><i class="fa fa-spinner fa-pulse"></i> Carregando...</p>

            <div class="alert alert-warning text-center" v-show="!controle.carregando && lista.length === 0">
                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
            </div>

            <div class="table-responsive" v-show="!controle.carregando && lista.length > 0">
                <table class="tabela">
                    <thead>
                        <tr class="bg-default">
                            <td class="text-center">Nº</td>
                            <td class="text-center">Data</td>
                            <td class="text-center">Opções</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(planejamentodiario, index) in lista" :key="planejamentodiario.id || index">
                            <td class="text-center">{{ planejamentodiario.id }}</td>
                            <td class="text-center">{{ planejamentodiario.data }}</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm mr-1 btn-primary" @click="alterarPlanejamentoDiario(planejamentodiario.id)">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <!--                            <button class="btn btn-sm mr-1 btn-outline-default" @click="gerarPdf(planejamentodiario.id)"><i-->
                                <!--                                class="fas fa-file-pdf"></i> GERAR PDF-->
                                <!--                            </button>-->
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
import modal from '../../Modal'
import editor from '@tinymce/tinymce-vue'
import DatePicker from '../../DatePicker'

export default {
    components: {
        DatePicker,
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

        status: {
            type: Boolean,
            required: false,
            default: true
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
    data() {
        return {
            hash: String(Math.random()).substr(2),
            titulo_janela_form_planejamentodiario: 'Planejamento Diário',

            preload: false,
            editando: false,
            cadastrado: false,
            atualizado: false,

            form: {
                data: '',
                tarefas_agendadas: '',
                importante: '',
                tarefas: [],
                tarefasDelete: []
            },
            formDefault: null,

            lista: [],

            urlPaginacao: `${URL_ADMIN}/administracao/planejamentodiario/atualizar`,
            controle: {
                carregando: false,
                dados: {
                    campoBusca: '',
                    campoFiltro: ''
                }
            }
        }
    },
    mounted() {
        this.atualizar()
        this.formDefault = _.cloneDeep(this.form)
    },
    methods: {
        abrirModalPlanejamentoDiario() {
            if (this.$refs && this.$refs.modalPlanejamentoDiario && typeof this.$refs.modalPlanejamentoDiario.abrirModal === 'function') {
                this.$refs.modalPlanejamentoDiario.abrirModal()
            }
        },
        fecharModalPlanejamentoDiario() {
            if (this.$refs && this.$refs.modalPlanejamentoDiario && typeof this.$refs.modalPlanejamentoDiario.fecharModal === 'function') {
                this.$refs.modalPlanejamentoDiario.fecharModal()
            }
        },
        addLITarefas() {
            const obj = {}
            obj.novo = true
            obj.tarefa = ''
            obj.status = ''

            this.form.tarefas.push(obj)
        },
        removerLITarefas(index) {
            if (this.editando) {
                this.form.tarefasDelete.push(this.form.tarefas[index].id)
            }
            this.form.tarefas.splice(index, 1)
        },
        formNovo() {
            this.form = _.cloneDeep(this.formDefault) //copia
            this.titulo_janela_form_planejamentodiario = 'Cadastro de Planejamento Diário'
            this.cadastrado = false
            this.finalizado = false
            this.atualizado = false
            this.editando = false

            formReset()
            setupCampo()
            this.abrirModalPlanejamentoDiario()
        },
        cadastrar() {
            $('#janelaFormPlanejamentoDiario :input:visible').trigger('blur')
            if ($('#janelaFormPlanejamentoDiario :input:visible.is-invalid').length) {
                mostraErro('', 'Verificar os erros')
                return false
            }
            this.preload = true
            axios
                .post(`${URL_ADMIN}/administracao/planejamentodiario`, this.form)
                .then((res) => {
                    if (res.status === 201) {
                        this.fecharModalPlanejamentoDiario()
                        mostraSucesso('', 'Planejamento Diário Cadastrado com sucesso')
                        this.preload = false
                        this.cadastrado = true
                        this.atualizar()
                    } else {
                        this.cadastrado = false
                        this.preload = false
                    }
                })
                .catch((error) => {
                    this.cadastrado = false
                    this.preload = false
                })
        },
        alterarPlanejamentoDiario(planejamentodiario) {
            this.cadastrado = false
            this.editando = true
            this.titulo_janela_form_planejamentodiario = 'Alterando Planejamento Diário'
            formReset()

            this.form = _.cloneDeep(this.formDefault) //copia
            this.abrirModalPlanejamentoDiario()

            axios
                .get(`${URL_ADMIN}/administracao/planejamentodiario/${planejamentodiario}/editar`)
                .then((response) => {
                    Object.assign(this.form, response.data)
                    this.editando = true
                    setupCampo()
                })
                .catch((error) => (this.preloadAjax = false))
        },
        alterarformPlanejamentoDiario() {
            formReset()
            $('#janelaFormPlanejamentoDiario :input:enabled').trigger('blur')

            if ($('#janelaFormPlanejamentoDiario :input:enabled.is-invalid').length) {
                mostraErro('', 'Verificar os erros')
                return false
            }

            this.preloadAjax = true

            axios
                .put(`${URL_ADMIN}/administracao/planejamentodiario/${this.form.id}`, this.form)
                .then((response) => {
                    this.fecharModalPlanejamentoDiario()
                    mostraSucesso('', 'Planejamento Diário Editado com sucesso')
                    this.preloadAjax = false
                    this.controle.carregando = true
                    this.atualizado = true
                    this.atualizar()
                })
                .catch((error) => (this.preloadAjax = false))
        },
        gerarPdf(item) {
            let link = `${URL_ADMIN}/administracao/planejamentodiario/pdf/${item}`
            open(link, 'blank')
        },
        carregou(dados) {
            this.lista = dados.items
            this.controle.carregando = false
        },
        carregando() {
            this.controle.carregando = true
        },
        atualizar() {
            this.$refs && this.$refs && this.$refs.componente && (this.$refs.componente.atual = 1)
            this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
        }
    }
}
</script>

<style scoped>
.card {
    border: none;
    background: transparent;
}

ul.timeline {
    list-style-type: none;
    position: relative;
}

ul.timeline:before {
    content: ' ';
    background: #d4d9df;
    display: inline-block;
    position: absolute;
    left: 29px;
    width: 2px;
    height: 100%;
    z-index: 400;
}

ul.timeline > li {
    margin: 20px 0;
    padding-left: 20px;
}

ul.timeline > li:before {
    content: ' ';
    background: white;
    display: inline-block;
    position: absolute;
    border-radius: 50%;
    border: 3px solid #184056;
    left: 20px;
    width: 20px;
    height: 20px;
    z-index: 400;
}

.trackind {
    padding: 0.5rem 0.8rem;
    background-color: #f4f4f4;
    border-radius: 0.5rem;
}
</style>

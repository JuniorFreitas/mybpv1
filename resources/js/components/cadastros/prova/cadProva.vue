<template>
    <div :id="hash">
        <modal id="janelaCadastrar" :titulo="titulo_janela" :fechar="!preload" :size="90" ref="modal_janelaCadastrar">
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
                        <button class="btn btn-sm mr-1 btn-primary mb-2" @click="addLiQuestao($event.target)">Adicionar Questão</button>
                        <div class="accordion" id="prova">
                            <div class="card mb-3 border" v-for="(objperg, ind) in form.perguntas" :key="objperg.id || ind" v-show="form.perguntas.length > 0">
                                <div class="card-header" style="background: #072433; color: white" :id="objperg.id">
                                    <h2 class="mb-0">
                                        <a
                                            class="btn btn-link btn-block text-left"
                                            href="javascript://"
                                            data-toggle="collapse"
                                            aria-expanded="true"
                                            :aria-controls="`collapse${objperg.id}`"
                                         @click="$refs[`collapse${objperg.id}`] && $refs[`collapse${objperg.id}`].abrirModal()">
                                            Questão - {{ ind + 1 }}
                                        </a>
                                    </h2>
                                </div>

                                <div :id="`collapse${objperg.id}`" class="collapse show" :aria-labelledby="objperg.id" data-parent="#prova">
                                    <div class="card-body">
                                        <a class="btn btn-sm mr-1 btn-danger" href="javascript://" @click="removerLIQuestao(ind)">
                                            <i class="fa fa-trash"></i> Apagar questão {{ ind + 1 }}
                                        </a>

                                        <div class="col-12 mt-2">
                                            <label>Enunciado</label>
                                            <tiny-mce-editor v-model="objperg.enunciado" preset="prova"></tiny-mce-editor>
                                        </div>

                                        <div class="col-12 mt-3 mb-3">
                                            <button class="btn btn-sm mr-1 btn-primary" @click="addLIResposta(ind, $event.target)">Adicionar opção</button>
                                        </div>

                                        <div
                                            class="col-12 mt-2 mb-2"
                                            v-show="form.perguntas[ind].respostas.length > 0"
                                            v-for="(obj, index) in form.perguntas[ind].respostas"
                                            :key="obj.id"
                                        >
                                            <fieldset>
                                                <legend>Opção {{ index + 1 }}</legend>

                                                <tiny-mce-editor v-model="obj.resposta" preset="prova"></tiny-mce-editor>

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
                                                        <button class="btn btn-sm mr-1 btn-danger mt-2 mb-2" @click="removerLIResposta(ind, index)">
                                                            Remover opção
                                                        </button>

                                                        <button class="btn btn-sm mr-1 btn-primary mt-2 mb-2 ml-2" @click="addLIResposta(ind, $event.target)">
                                                            Adicionar outra opção
                                                        </button>
                                                    </div>
                                                </div>
                                            </fieldset>
                                        </div>
                                    </div>

                                    <a class="btn btn-sm mr-1 btn-danger" href="javascript://" @click="removerLIQuestao(ind)">
                                        <i class="fa fa-trash"></i> Apagar questão {{ ind + 1 }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset v-if="form.tipo_prova === 'subjetiva'">
                        <legend>Questões Subjetivas</legend>
                        <button class="btn btn-sm mr-1 btn-primary mb-2" @click="addLiQuestao($event.target)">Adicionar Questão</button>
                        <div class="accordion" id="provaSub">
                            <div class="card mb-3 border" v-for="(objperg, ind) in form.perguntas" :key="objperg.id || ind" v-show="form.perguntas.length > 0">
                                <div class="card-header" style="background: #072433; color: white" :id="objperg.id">
                                    <h2 class="mb-0">
                                        <a
                                            class="btn btn-link btn-block text-left"
                                            href="javascript://"
                                            data-toggle="collapse"
                                            aria-expanded="true"
                                            :aria-controls="`collapse${objperg.id}`"
                                         @click="$refs[`collapse${objperg.id}`] && $refs[`collapse${objperg.id}`].abrirModal()">
                                            Questão - {{ ind + 1 }}
                                        </a>
                                    </h2>
                                </div>

                                <div :id="`collapse${objperg.id}`" class="collapse show" :aria-labelledby="objperg.id" data-parent="#provaSub">
                                    <div class="card-body">
                                        <a class="btn btn-sm mr-1 btn-danger" href="javascript://" @click="removerLIQuestao(ind)">
                                            <i class="fa fa-trash"></i> Apagar questão {{ ind + 1 }}
                                        </a>

                                        <div class="col-12 mt-2">
                                            <label>Enunciado</label>
                                            <tiny-mce-editor v-model="objperg.enunciado" preset="prova"></tiny-mce-editor>
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

                                    <a class="btn btn-sm mr-1 btn-danger" href="javascript://" @click="removerLIQuestao(ind)">
                                        <i class="fa fa-trash"></i> Apagar questão {{ ind + 1 }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </template>
            <template #rodape>
                <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="editando && !preload" @click="alterar()">Alterar</button>
                <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="!editando && !preload" @click="cadastrar()">Cadastrar</button>
            </template>
        </modal>

        <FiltroListagem
            class="mt-2 mybp-filtros-compactos"
            @submit="atualizar"
            :mostrar-limpar-filtros="temFiltrosAtivos"
            :desabilitado="controle.carregando"
            @limpar="limparFiltros"
        >
            <template #filtros>
                <div class="col-12 col-md-6">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="prova-filtro-busca">Buscar</label>
                        <input
                            id="prova-filtro-busca"
                            type="text"
                            placeholder="Buscar por título ou ID"
                            autocomplete="off"
                            class="form-control form-control-sm"
                            :disabled="controle.carregando"
                            v-model="controle.dados.campoBusca"
                        />
                    </div>
                </div>
            </template>
            <template #acoes>
                <button type="submit" class="btn btn-sm btn-success" :disabled="controle.carregando">
                    <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                    Atualizar
                </button>
                <button type="button" class="btn btn-sm btn-secondary" :disabled="controle.carregando" @click="formNovo(); $refs.modal_janelaCadastrar && $refs.modal_janelaCadastrar.abrirModal()">
                    <i class="fa fa-plus"></i> Cadastrar
                </button>
            </template>
        </FiltroListagem>
        <div class="d-flex justify-content-end my-2">
            <mybp-visualizacao-listagem v-model="visualizacao" tela="cadastro-provas"></mybp-visualizacao-listagem>
        </div>

        <div id="conteudo">
            <p class="mt-2 text-center" v-if="controle.carregando">
                <preload></preload>
            </p>

            <div class="alert alert-warning text-center" v-show="!controle.carregando && lista.length === 0">
                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
            </div>

            <div class="mybp-cards-lista" v-show="!controle.carregando && lista.length > 0 && visualizacao === 'lista'">
                <div class="mybp-card" v-for="item in lista" :key="'card-' + item.id">
                    <div class="mybp-card-header-row">
                        <div class="mybp-card-left">
                            <span class="mybp-badge-id">#{{ item.id }}</span>
                            <div class="mybp-card-titulo"><strong>{{ item.titulo || 'Não informado' }}</strong></div>
                        </div>
                        <div class="mybp-card-right">
                            <bt-ativo :rota="`cadastro/provas/${item.id}/ativa-desativa`" :model="item"></bt-ativo>
                            <div class="dropdown" :class="{ show: isDropdownOpen(item.id) }">
                                <a class="mybp-btn-acoes-compact" href="#" role="button" @click.prevent.stop="toggleDropdown(item.id)"><i class="fas fa-ellipsis-v"></i></a>
                                <div class="dropdown-menu mybp-dropdown-menu dropdown-menu-right" :class="{ show: isDropdownOpen(item.id) }">
                                    <a class="dropdown-item" href="javascript://" @click.prevent="fecharDropdown(); alterarForm(item); $refs.modal_janelaCadastrar && $refs.modal_janelaCadastrar.abrirModal()"><i class="fa fa-edit mr-1"></i> Editar</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mybp-card-details-row">
                        <div class="mybp-detail-item"><span class="mybp-detail-label">Questões</span><span class="mybp-detail-value">{{ item.qnt_questoes }}</span></div>
                    </div>
                </div>
            </div>
            <div class="table-responsive mybp-tabela-wrap" v-show="!controle.carregando && lista.length > 0 && visualizacao === 'tabela'">
                <table class="tabela mybp-tabela">
                    <thead>
                        <tr>
                            <th>Cód</th>
                            <th>Título</th>
                            <th>Questões</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in lista" :key="index">
                            <td class="text-center">{{ item.id }}</td>
                            <td class="text-center">{{ item.titulo }}</td>
                            <td class="text-center">{{ item.qnt_questoes }}</td>
                            <td class="text-center">
                                <bt-ativo :rota="`cadastro/provas/${item.id}/ativa-desativa`" :model="item"></bt-ativo>
                            </td>
                            <td data-label="Ações">
                                <div class="dropdown d-inline-block" :class="{ show: isDropdownOpen(item.id) }">
                                    <a class="mybp-btn-acoes-compact" href="#" role="button" @click.prevent.stop="toggleDropdown(item.id); posicionarMenuAcoes($event)"><i class="fas fa-ellipsis-v"></i></a>
                                    <div class="dropdown-menu mybp-dropdown-menu dropdown-menu-right" :class="{ show: isDropdownOpen(item.id) }" @click="fecharDropdown">
                                        <a class="dropdown-item" href="javascript://" @click.prevent="fecharDropdown(); alterarForm(item); $refs.modal_janelaCadastrar && $refs.modal_janelaCadastrar.abrirModal()"><i class="fa fa-edit mr-1"></i> Editar</a>
                                    </div>
                                </div>
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
import FiltroListagem from '../../ui/FiltroListagem'
import { lerVisualizacaoListagem } from '../../../utils/visualizacaoListagem'
import { posicionarMenuAcoes } from '../../../utils/posicionarMenuAcoes'

export default {
    components: {
        modal,
        controlePaginacao,
        FiltroListagem
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
        document.addEventListener('click', this.onClickOutside)
    },
    beforeUnmount() {
        document.removeEventListener('click', this.onClickOutside)
    },
    computed: {
        temFiltrosAtivos() {
            return !!this.controle.dados.campoBusca
        }
    },
    data() {
        return {
            hash: String(Math.random()).substr(2),
            titulo_janela: 'Montagem da Prova',
            visualizacao: lerVisualizacaoListagem('cadastro-provas', 'lista'),
            dropdownAbertoKey: null,

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
        posicionarMenuAcoes,
        limparFiltros() {
            this.controle.dados.campoBusca = ''
            this.atualizar()
        },
        onClickOutside(event) {
            if (event?.target?.closest?.('.dropdown')) return
            this.dropdownAbertoKey = null
        },
        toggleDropdown(id) {
            const key = `prova:${id}`
            this.dropdownAbertoKey = this.dropdownAbertoKey === key ? null : key
        },
        isDropdownOpen(id) {
            return this.dropdownAbertoKey === `prova:${id}`
        },
        fecharDropdown() {
            this.dropdownAbertoKey = null
        },
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
                        this.$refs.modal_janelaCadastrar && this.$refs.modal_janelaCadastrar.fecharModal()
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
                    this.$refs.modal_janelaCadastrar && this.$refs.modal_janelaCadastrar.fecharModal()
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
            this.$refs && this.$refs && this.$refs.componente && (this.$refs.componente.atual = 1)
            this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
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

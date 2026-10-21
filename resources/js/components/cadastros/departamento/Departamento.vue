<template>
    <div id="componenteDepartamento">
        <modal ref="modalDepartamento" id="janelaCadastrar" :titulo="titulo_janela" :fechar="!preload">
            <template #conteudo>
                <preload v-show="preload"></preload>
                <div v-if="!preload && !cadastrado">
                    <fieldset>
                        <legend>Informações</legend>
                        <div class="row">
                            <div class="col-12 col-md-12">
                                <div class="form-group">
                                    <label>Nome</label>
                                    <input v-model="form.label" class="form-control form-control-sm" type="text" onblur="valida_campo_vazio(this, 1)" />
                                </div>
                            </div>
                            <div class="col-12 mt-2">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" v-model="form.ativo" class="custom-control-input" id="ativo" />
                                    <label class="custom-control-label" for="ativo">{{ form.ativo ? 'Ativo' : 'Inativo' }}</label>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </template>
            <template #rodape>
                <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="editando" @click="alterarformDepartamento()">Salvar</button>
                <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="!editando" @click="cadastrar()">Cadastrar</button>
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
                        <label class="mybp-label" for="departamento-filtro-busca">Buscar</label>
                        <input
                            id="departamento-filtro-busca"
                            type="text"
                            placeholder="Buscar por nome ou ID"
                            autocomplete="off"
                            class="form-control form-control-sm"
                            :disabled="controle.carregando"
                            v-model="controle.dados.campoBusca"
                        />
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="departamento-filtro-status">Status</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroStatus"
                                instance-id="filtro-status-departamento"
                                v-model="controle.dados.campoStatus"
                                :options="filtroStatusOpcoes"
                                :disabled="controle.carregando"
                                input-id="departamento-filtro-status"
                                placeholder-blur="Todos os status"
                                empty-message="Nenhuma opção encontrada."
                                :max-results="10"
                                @select="atualizar"
                            ></combobox-auto-complete>
                        </div>
                    </div>
                </div>
            </template>
            <template #acoes>
                <button type="submit" class="btn btn-sm btn-success" :disabled="controle.carregando">
                    <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                    Atualizar
                </button>
                <button type="button" class="btn btn-sm btn-secondary" :disabled="controle.carregando" @click="formNovo">
                    <i class="fa fa-plus"></i> Departamento
                </button>
            </template>
        </FiltroListagem>
        <div class="d-flex justify-content-end my-2">
            <mybp-visualizacao-listagem v-model="visualizacao" tela="cadastro-departamentos"></mybp-visualizacao-listagem>
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
                            <div class="mybp-card-titulo"><strong>{{ item.label || 'Não informado' }}</strong></div>
                        </div>
                        <div class="mybp-card-right">
                            <div class="dropdown" :class="{ show: isDropdownOpen(item.id) }">
                                <a class="mybp-btn-acoes-compact" href="#" role="button" @click.prevent.stop="toggleDropdown(item.id)">
                                    <i class="fas fa-ellipsis-v"></i>
                                </a>
                                <div class="dropdown-menu mybp-dropdown-menu dropdown-menu-right" :class="{ show: isDropdownOpen(item.id) }">
                                    <a class="dropdown-item" href="javascript://" @click.prevent="fecharDropdown(); alterarDepartamento(item.id)"><i class="fa fa-edit mr-1"></i> Editar</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mybp-card-details-row">
                        <div class="mybp-detail-item">
                            <span class="mybp-detail-label">Status</span>
                            <span class="mybp-detail-value">{{ item.ativo === true ? 'Ativo' : 'Inativo' }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="table-responsive mybp-tabela-wrap" v-show="!controle.carregando && lista.length > 0 && visualizacao === 'tabela'">
                <table class="tabela mybp-tabela">
                    <thead>
                        <tr>
                            <th>Cód</th>
                            <th>Nome</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in lista" :key="index">
                            <td class="text-center">{{ item.id }}</td>
                            <td class="text-center">{{ item.label }}</td>
                            <td class="text-center">{{ item.ativo === true ? 'Ativo' : 'Inativo' }}</td>
                            <td class="text-center" data-label="Ações">
                                <div class="dropdown d-inline-block" :class="{ show: isDropdownOpen(item.id) }">
                                    <a class="mybp-btn-acoes-compact" href="#" role="button" @click.prevent.stop="toggleDropdown(item.id); posicionarMenuAcoes($event)">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </a>
                                    <div class="dropdown-menu mybp-dropdown-menu dropdown-menu-right" :class="{ show: isDropdownOpen(item.id) }">
                                        <a class="dropdown-item" href="javascript://" @click.prevent="fecharDropdown(); alterarDepartamento(item.id)"><i class="fa fa-edit mr-1"></i> Editar</a>
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
import ComboboxAutoComplete from '../../ComboboxAutoComplete'
import { lerVisualizacaoListagem } from '../../../utils/visualizacaoListagem'
import { posicionarMenuAcoes } from '../../../utils/posicionarMenuAcoes'

export default {
    components: {
        modal,
        controlePaginacao,
        FiltroListagem,
        ComboboxAutoComplete
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
            return !!(this.controle.dados.campoBusca || this.controle.dados.campoStatus !== '')
        },
        filtroStatusOpcoes() {
            return [
                { value: '', label: 'Todos os status' },
                { value: 'true', label: 'Apenas ativos' },
                { value: 'false', label: 'Apenas inativos' }
            ]
        }
    },
    data() {
        return {
            hash: String(Math.random()).substr(2),
            titulo_janela: 'Departamento',
            visualizacao: lerVisualizacaoListagem('cadastro-departamentos', 'lista'),
            dropdownAbertoKey: null,

            preload: false,
            editando: false,
            cadastrado: false,

            form: {
                label: '',
                cliente_id: '',
                ativo: true
            },

            formDefault: null,

            lista: [],
            listaClientes: [],

            urlPaginacao: `${URL_ADMIN}/cadastro/departamento/atualizar`,
            controle: {
                carregando: false,
                dados: {
                    campoBusca: '',
                    campoStatus: ''
                }
            }
        }
    },
    methods: {
        posicionarMenuAcoes,
        limparFiltros() {
            this.controle.dados.campoBusca = ''
            this.controle.dados.campoStatus = ''
            if (this.$refs.comboFiltroStatus?.close) this.$refs.comboFiltroStatus.close()
            this.atualizar()
        },
        onClickOutside(event) {
            if (event?.target?.closest?.('.dropdown')) return
            if (event?.target?.closest?.('.mybp-combobox-wrap')) return
            this.dropdownAbertoKey = null
        },
        toggleDropdown(id) {
            const key = `departamento:${id}`
            this.dropdownAbertoKey = this.dropdownAbertoKey === key ? null : key
        },
        isDropdownOpen(id) {
            return this.dropdownAbertoKey === `departamento:${id}`
        },
        fecharDropdown() {
            this.dropdownAbertoKey = null
        },
        abrirModalDepartamento() {
            if (this.$refs && this.$refs.modalDepartamento && typeof this.$refs.modalDepartamento.abrirModal === 'function') {
                this.$refs.modalDepartamento.abrirModal()
            }
        },
        fecharModalDepartamento() {
            if (this.$refs && this.$refs.modalDepartamento && typeof this.$refs.modalDepartamento.fecharModal === 'function') {
                this.$refs.modalDepartamento.fecharModal()
            }
        },
        formNovo() {
            this.form = _.cloneDeep(this.formDefault) //copia
            this.titulo_janela = 'Departamento'
            this.editando = false
            this.cadastrado = false
            this.preload = false
            formReset()
            setupCampo()
            this.abrirModalDepartamento()
        },

        cadastrar() {
            $('#janelaCadastrar :input:visible').trigger('blur')
            if ($('#janelaCadastrar :input:visible.is-invalid').length) {
                mostraErro('', 'Verificar os erros')
                return false
            }
            this.preload = true
            axios
                .post(`${URL_ADMIN}/cadastro/departamento`, this.form)
                .then((res) => {
                    if (res.status === 201) {
                        this.fecharModalDepartamento()
                        mostraSucesso('', 'Departamento cadastrado com sucesso')
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
        alterarDepartamento(departamento) {
            this.cadastrado = false
            this.editando = true
            this.titulo_janela = 'Alterando Departamento'
            formReset()

            this.form = _.cloneDeep(this.formDefault) //copia
            this.abrirModalDepartamento()

            axios
                .get(`${URL_ADMIN}/cadastro/departamento/${departamento}/editar`)
                .then((response) => {
                    Object.assign(this.form, response.data)
                    this.editando = true
                    setupCampo()
                })
                .catch((error) => (this.preloadAjax = false))
        },
        alterarformDepartamento() {
            formReset()
            $('#janelaCadastrar :input:enabled').trigger('blur')

            if ($('#janelaCadastrar :input:enabled.is-invalid').length) {
                mostraErro('', 'Verificar os erros')
                return false
            }

            this.preload = true

            axios
                .put(`${URL_ADMIN}/cadastro/departamento/${this.form.id}`, this.form)
                .then((response) => {
                    this.fecharModalDepartamento()
                    mostraSucesso('', 'Departamento atualizado com sucesso')
                    this.preload = false
                    this.atualizado = true
                    this.atualizar()
                })
                .catch((error) => (this.preload = false))
        },
        carregou(dados) {
            this.lista = dados.items
            this.listaClientes = dados.clientes
            this.controle.carregando = false
        },
        carregando() {
            this.controle.carregando = true
        },
        atualizar() {
            if (this.$refs && this.$refs.componente) {
                this.$refs.componente.atual = 1
                if (this.$refs.componente.buscar) {
                    this.$refs.componente.buscar()
                }
            }
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

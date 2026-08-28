<template>
    <div id="componente">
        <modal ref="modalForm" :modal-pai="modal" :titulo="titulo_janela_form" :fechar="!preload" id="janelaForm">
            <template #conteudo>
                <p class="mt-2 text-center" v-if="preload"><i class="fa fa-spinner fa-pulse"></i>Carregando...</p>
                <fieldset v-if="!preload">
                    <legend>Cadastro de Área</legend>
                    <p class="mybp-campo-obrigatorio-legenda mb-3">Campos com <span class="text-danger">*</span> são obrigatórios.</p>
                    <div class="row">
                        <div class="col-12">
                            <label class="mybp-label" for="area-form-nome">Nome <span class="text-danger">*</span></label>
                            <input
                                id="area-form-nome"
                                class="form-control form-control-sm"
                                type="text"
                                placeholder="Informe o nome da área"
                                onblur="valida_campo_vazio(this, 1)"
                                v-model="form.label"
                            />
                        </div>

                        <div class="col-12 mt-2 mb-2">
                            <label class="mybp-label" for="area-form-supervisor">Contato supervisor para etiqueta</label>
                            <input
                                id="area-form-supervisor"
                                class="form-control form-control-sm"
                                type="text"
                                placeholder="Informe o telefone do supervisor"
                                onblur="valida_telefone(this)"
                                v-mascara:telefone
                                v-model="form.numero_supervisor"
                            />
                        </div>

                        <gestor label="Gestor responsável" :model="form" :verifica="false" :hash="hash"></gestor>

                        <div class="col-12">
                            <div class="form-group">
                                <label class="mybp-label" for="area-form-centro-custo">Centro de Custo</label>
                                <select id="area-form-centro-custo" v-model="form.centro_custo_id" class="form-control form-control-sm">
                                    <option value="">Selecione</option>
                                    <option v-for="item in centro_custos" :value="item.id" :key="item.id">
                                        {{ item.label }}
                                    </option>
                                </select>
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
            </template>
            <template #rodape>
                <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="!cadastrado && !preload" @click="cadastra">
                    <i class="fa fa-save"></i>Cadastrar
                </button>

                <button v-show="cadastrado" type="button" class="btn btn-sm mr-1 btn-primary" @click="alterarForm"><i class="fa fa-save"></i> Alterar</button>
            </template>
        </modal>

        <filtro-listagem @submit="onSubmitFiltro">
            <template #filtros>
                <div class="col-12 col-lg-6">
                    <div class="form-group mb-2 mb-lg-0">
                        <label class="mybp-label" for="area-filtro-busca">Buscar</label>
                        <input
                            id="area-filtro-busca"
                            type="text"
                            placeholder="Buscar por nome ou ID"
                            autocomplete="off"
                            class="form-control form-control-sm"
                            :disabled="controle.carregando"
                            v-model="controle.dados.campoBusca"
                        />
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="form-group mb-2 mb-lg-0">
                        <label class="mybp-label" for="area-filtro-status">Status</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroStatus"
                                instance-id="filtro-status"
                                v-model="controle.dados.campoStatus"
                                :options="filtroStatusOpcoes"
                                :disabled="controle.carregando"
                                input-id="area-filtro-status"
                                placeholder-blur="Todos os status"
                                empty-message="Nenhuma opção encontrada."
                                :max-results="10"
                                @opening="fecharOutrosComboboxes('filtro-status')"
                                @select="onSelectFiltro"
                            ></combobox-auto-complete>
                        </div>
                    </div>
                </div>
            </template>

            <template #acoes>
                <button type="button" class="btn btn-sm btn-success" :disabled="controle.carregando" @click="atualizar">
                    <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                    Atualizar
                </button>

                <button type="button" class="btn btn-sm btn-secondary" :disabled="controle.carregando" @click="formNovo">
                    <i class="fa fa-plus"></i> Cadastrar Área
                </button>
            </template>
        </filtro-listagem>

        <div id="conteudo">
            <preload class="mt-2 text-center" v-if="controle.carregando"></preload>

            <div class="alert alert-warning text-center" v-show="!controle.carregando && lista.length === 0">
                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
            </div>

            <div class="mybp-cards-lista" v-show="!controle.carregando && lista.length > 0">
                <div class="mybp-card" v-for="area in lista" :key="area.id">
                    <div class="mybp-card-header-row">
                        <div class="mybp-card-left">
                            <span class="mybp-badge-id">#{{ area.id }}</span>
                            <div class="mybp-card-titulo">
                                <strong>{{ area.label }}</strong>
                            </div>
                        </div>
                        <div class="mybp-card-right">
                            <bt-ativo
                                :rota="`cadastro/areas/${area.id}/ativa-desativa`"
                                :model="area"
                                @atualizou="atualizar()"
                            ></bt-ativo>
                            <div class="dropdown" :class="{ show: isDropdownOpen(area.id) }">
                                <a
                                    class="mybp-btn-acoes-compact"
                                    href="#"
                                    role="button"
                                    aria-haspopup="true"
                                    :aria-expanded="isDropdownOpen(area.id) ? 'true' : 'false'"
                                    @click.prevent.stop="toggleDropdown(area.id)"
                                >
                                    <i class="fas fa-ellipsis-v"></i>
                                </a>
                                <div
                                    class="dropdown-menu mybp-dropdown-menu dropdown-menu-right"
                                    :class="{ show: isDropdownOpen(area.id) }"
                                    @click="fecharDropdown"
                                >
                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        title="Editar"
                                        @click.prevent="abrirEdicaoArea(area.id)"
                                    >
                                        <i class="fa fa-edit mr-1"></i> Editar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mybp-card-conteudo">
                        <div class="mybp-card-secoes-row">
                            <div class="mybp-card-destaque mybp-card-destaque--primary">
                                <i class="fas fa-user-tie text-primary"></i>
                                <div class="mybp-card-destaque-info">
                                    <small class="mybp-card-destaque-etapa">Gestor responsável</small>
                                    <strong class="mybp-card-destaque-valor mybp-card-destaque-valor--wrap">
                                        {{ resumoGestor(area) }}
                                    </strong>
                                </div>
                            </div>

                            <div class="mybp-card-destaque mybp-card-destaque--info">
                                <i class="fas fa-building text-info"></i>
                                <div class="mybp-card-destaque-info">
                                    <small class="mybp-card-destaque-etapa">Centro de Custo</small>
                                    <strong class="mybp-card-destaque-valor mybp-card-destaque-valor--wrap">
                                        {{ resumoCentroCusto(area) }}
                                    </strong>
                                </div>
                            </div>

                            <div class="mybp-card-destaque">
                                <i class="fas fa-phone text-muted"></i>
                                <div class="mybp-card-destaque-info">
                                    <small class="mybp-card-destaque-etapa">Supervisor (etiqueta)</small>
                                    <strong class="mybp-card-destaque-valor mybp-card-destaque-valor--wrap">
                                        {{ resumoSupervisor(area) }}
                                    </strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <controle-paginacao
                class="d-flex justify-content-center mt-3"
                id="controle"
                ref="componente"
                :url="urlPaginacao"
                :por-pagina="controle.dados.pages"
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
import gestor from '../../GestorAprovacao.vue'
import FiltroListagem from '../../ui/FiltroListagem.vue'
import ComboboxAutoComplete from '../../ComboboxAutoComplete.vue'
import {
    lerFiltrosDaUrl,
    lerPaginacaoDaUrl,
    sincronizarFiltrosNaUrl,
    criarWatchQueryParams,
    montarExtrasPaginacao,
    aplicarPaginaInicialListagem,
    buscarListagem
} from '../../../utils/listagemQueryParams'

const CAMPOS_FILTRO_URL = ['campoBusca', 'campoStatus']
const PAGES_DEFAULT = 20

export default {
    components: {
        modal,
        controlePaginacao,
        gestor,
        FiltroListagem,
        ComboboxAutoComplete
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
            type: String,
            required: false,
            default: ''
        }
    },

    mounted() {
        this.urlParamGetFiltros()
        const paginaInicial = lerPaginacaoDaUrl(this.controle.dados, { pagesDefault: this.qntPag })
        this.formDefault = _.cloneDeep(this.form)
        this.$nextTick(() => {
            aplicarPaginaInicialListagem(this, paginaInicial)
            buscarListagem(this, { resetPagina: false })
        })
        document.addEventListener('click', this.onClickOutside)
    },
    beforeUnmount() {
        document.removeEventListener('click', this.onClickOutside)
    },
    watch: {
        'controle.dados': criarWatchQueryParams(CAMPOS_FILTRO_URL, { pagesDefault: PAGES_DEFAULT })
    },
    computed: {
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
            titulo_janela_form: 'Áreas',
            dropdownAbertoKey: null,

            preload: false,
            editando: false,
            cadastrado: false,
            atualizado: false,

            empresa_id: '',

            form: {
                gestor_id: '',
                autocomplete_label_gestor_modal: '',
                autocomplete_label_gestor_modal_anterior: '',

                centro_custo_id: '',

                label: '',
                numero_supervisor: '',
                ativo: true
            },
            formDefault: null,

            lista: [],
            centro_custos: [],

            urlPaginacao: `${URL_ADMIN}/cadastro/areas/atualizar`,
            controle: {
                carregando: false,
                dados: {
                    campoBusca: '',
                    campoStatus: '',
                    pages: PAGES_DEFAULT
                }
            }
        }
    },
    methods: {
        urlParamGetFiltros() {
            lerFiltrosDaUrl(this.controle.dados, CAMPOS_FILTRO_URL)
        },
        syncUrlFiltros() {
            sincronizarFiltrosNaUrl(
                this.controle.dados,
                CAMPOS_FILTRO_URL,
                montarExtrasPaginacao(this, { pagesDefault: this.qntPag })
            )
        },
        onSubmitFiltro() {
            this.atualizar()
        },
        onSelectFiltro() {
            this.atualizar()
        },
        fecharOutrosComboboxes(manter) {
            const combos = [['filtro-status', 'comboFiltroStatus']]

            combos.forEach(([id, refName]) => {
                if (id !== manter && this.$refs[refName]?.close) {
                    this.$refs[refName].close()
                }
            })
        },
        onClickOutside(event) {
            if (event?.target?.closest?.('.dropdown')) return
            if (event?.target?.closest?.('.mybp-combobox-wrap')) return
            this.dropdownAbertoKey = null
            this.fecharOutrosComboboxes(null)
        },
        toggleDropdown(areaId) {
            if (!areaId) return
            const key = `area:${areaId}`
            this.dropdownAbertoKey = this.dropdownAbertoKey === key ? null : key
        },
        isDropdownOpen(areaId) {
            return this.dropdownAbertoKey === `area:${areaId}`
        },
        fecharDropdown() {
            this.dropdownAbertoKey = null
        },
        abrirEdicaoArea(areaId) {
            this.fecharDropdown()
            this.alterar(areaId)
        },
        resumoGestor(area) {
            return area.gestor_nome || area.gestor?.nome || area.Gestor?.nome || 'Não informado'
        },
        resumoCentroCusto(area) {
            return area.centro_custo_label || area.centro_custo?.label || area.CentroCusto?.label || 'Não informado'
        },
        resumoSupervisor(area) {
            return area.numero_supervisor || 'Não informado'
        },
        formNovo() {
            this.titulo_janela_form = 'Cadastro Áreas'
            this.preload = false
            this.cadastrado = false
            this.atualizado = false
            this.form = _.cloneDeep(this.formDefault)
            formReset()
            if (this.$refs && this.$refs.modalForm && typeof this.$refs.modalForm.abrirModal === 'function') {
                this.$refs.modalForm.abrirModal()
            }
        },
        cadastra() {
            $('#janelaForm :input:visible').trigger('blur')
            if ($('#janelaForm :input:visible.is-invalid').length) {
                mostraErro('', 'Verificar os erros')
                return false
            }
            this.preload = true
            axios
                .post(`${URL_ADMIN}/cadastro/areas`, this.form)
                .then((res) => {
                    if (this.$refs && this.$refs.modalForm && typeof this.$refs.modalForm.fecharModal === 'function') {
                        this.$refs.modalForm.fecharModal()
                    }
                    mostraSucesso('', 'Área cadastrada com sucesso')
                    this.cadastrado = true
                    this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
                    this.preload = false
                })
                .catch((error) => {
                    this.cadastrado = false
                    this.preload = false
                })
        },

        alterar(area) {
            this.cadastrado = true
            this.editando = true
            this.titulo_janela_form = 'Alterando Área'
            formReset()

            this.form = _.cloneDeep(this.formDefault)

            this.preload = true

            if (this.$refs && this.$refs.modalForm && typeof this.$refs.modalForm.abrirModal === 'function') {
                this.$refs.modalForm.abrirModal()
            }

            axios
                .get(`${URL_ADMIN}/cadastro/areas/${area}/editar`)
                .then((response) => {
                    Object.assign(this.form, response.data)
                    this.editando = true
                    setupCampo()
                    this.preload = false
                })
                .catch((error) => (this.preloadAjax = false))
        },
        alterarForm() {
            $('#janelaForm :input:visible').trigger('blur')
            if ($('#janelaForm :input:visible.is-invalid').length) {
                mostraErro('', 'Verificar os erros')
                return false
            }
            this.preload = true
            axios
                .put(`${URL_ADMIN}/cadastro/areas/${this.form.id}`, this.form)
                .then((res) => {
                    if (this.$refs && this.$refs.modalForm && typeof this.$refs.modalForm.fecharModal === 'function') {
                        this.$refs.modalForm.fecharModal()
                    }
                    mostraSucesso('', 'Área Alterada com sucesso')
                    this.cadastrado = true
                    this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
                    this.preload = false
                })
                .catch((error) => {
                    this.cadastrado = false
                    this.preload = false
                })
        },
        carregou(dados) {
            this.lista = dados.items
            this.controle.carregando = false
            this.$nextTick(() => this.syncUrlFiltros())
            axios
                .post(`${URL_PUBLICO}/centro-custos/`, { empresa_id: dados.empresa_id })
                .then((response) => {
                    this.centro_custos = response.data.centro_custos
                })
                .catch((error) => {
                    this.preload = false
                })
        },
        carregando() {
            this.controle.carregando = true
        },
        atualizar() {
            buscarListagem(this, { resetPagina: true })
        }
    }
}
</script>

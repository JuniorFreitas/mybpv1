<template>
    <div>
        <ModalComponent
            ref="modal_janelaForm"
            id="janelaForm"
            :modal-pai="modal"
            :fechar="!carregandoModal"
            :mostrar-botao-fechar-no-rodape="false"
            size="g"
            :titulo="titulo_janela_form"
        >
            <template #conteudo>
                <preload class="text-center" v-if="carregandoModal"></preload>
                <fieldset class="mt-0" v-if="!carregandoModal">
                    <legend>Dados do Centro de Custo</legend>
                    <p class="mybp-campo-obrigatorio-legenda mb-3">
                        Campos com <span class="text-danger">*</span> são obrigatórios.
                    </p>
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="mybp-label" for="cc-form-nome">Nome <span class="text-danger">*</span></label>
                            <input
                                id="cc-form-nome"
                                class="form-control form-control-sm validacampo"
                                type="text"
                                placeholder="Informe o nome"
                                onblur="valida_campo_vazio(this, 1)"
                                v-model="form.label"
                            />
                        </div>

                        <gestor label="Gestor responsável" :model="form" :verifica="false" :hash="hash"></gestor>

                        <div class="col-12">
                            <div class="form-group">
                                <label class="mybp-label" for="cc-form-gestor-substituto">Gestor substituto</label>
                                <autocomplete
                                    :caminho="`autocomplete/todos-gestores-ativos/`"
                                    :formsm="true"
                                    :valido="form.gestor_substituto_id !== ''"
                                    v-model="form.autocomplete_label_gestor_substituto_modal"
                                    placeholder="Digite o nome do gestor substituto"
                                    :id="`gestor_substituto_${hash}`"
                                    @onselect="selecionaGestorSubstituto"
                                    @onblur="resetaGestorSubstituto"
                                    @change="resetaGestorSubstituto"
                                ></autocomplete>
                                <small class="form-text text-muted">Assume a aprovação quando o gestor principal estiver indisponível.</small>
                            </div>
                        </div>

                        <div class="col-12 mt-2">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="ativo" v-model="form.ativo" />
                                <label class="custom-control-label" for="ativo">{{ form.ativo ? 'Ativo' : 'Inativo' }}</label>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <fieldset v-if="!carregandoModal && temFilial && listaCcs?.cnpjs">
                    <legend>CNPJ</legend>
                    <div class="row">
                        <div class="col-12 mybp-combobox-wrap">
                            <label class="mybp-label" for="cc-form-cnpj-input">CNPJ <span class="text-danger">*</span></label>
                            <combobox-auto-complete
                                ref="comboFormCnpj"
                                instance-id="form-cnpj"
                                v-model="form.campoCnpj"
                                :options="cnpjComboboxOpcoes"
                                :disabled="carregandoModal || !cnpjComboboxOpcoes.length"
                                input-id="cc-form-cnpj-input"
                                empty-message="Nenhum CNPJ encontrado."
                                :max-results="50"
                                @opening="fecharOutrosComboboxes('form-cnpj')"
                            />
                            <small class="form-text text-muted">
                                Selecione a matriz ou a filial à qual este centro de custo pertence.
                            </small>
                        </div>
                    </div>
                </fieldset>
            </template>
            <template #rodape>
                <button type="button" class="btn btn-sm mr-1 btn-secondary" @click="fecharModal"><i class="fa fa-times"></i> Cancelar</button>
                <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="!cadastrado && !carregandoModal" @click="cadastra" :disabled="carregandoModal">
                    <span v-if="carregandoModal">
                        <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                        Salvando...
                    </span>
                    <span v-else><i class="fa fa-save"></i> Salvar</span>
                </button>
                <button v-show="cadastrado && !carregandoModal" type="button" class="btn btn-sm mr-1 btn-primary" @click="alterarForm" :disabled="carregandoModal">
                    <span v-if="carregandoModal">
                        <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                        Salvando...
                    </span>
                    <span v-else><i class="fa fa-save"></i> Salvar</span>
                </button>
            </template>
        </ModalComponent>

        <div id="componente">
            <FiltroListagem
                @submit="onSubmitFiltro"
                :mostrar-limpar-filtros="temFiltrosAtivos"
                :desabilitado="controle.carregando"
                @limpar="limparFiltros"
            >
                <template #filtros>
                    <div class="col-12" :class="temFilial ? 'col-lg-4' : 'col-lg-6'">
                        <div class="form-group mb-2 mb-lg-0">
                            <label class="mybp-label" for="cc-filtro-busca">Buscar</label>
                            <input
                                id="cc-filtro-busca"
                                type="text"
                                placeholder="Buscar por nome ou ID"
                                autocomplete="off"
                                class="form-control form-control-sm"
                                :disabled="controle.carregando"
                                v-model="controle.dados.campoBusca"
                            />
                        </div>
                    </div>

                    <div class="col-12" :class="temFilial ? 'col-lg-3' : 'col-lg-6'">
                        <div class="form-group mb-2 mb-lg-0">
                            <label class="mybp-label" for="cc-filtro-status">Status</label>
                            <div class="mybp-combobox-wrap">
                                <combobox-auto-complete
                                    ref="comboFiltroStatus"
                                    instance-id="filtro-status"
                                    v-model="controle.dados.campoStatus"
                                    :options="filtroStatusOpcoes"
                                    :disabled="controle.carregando"
                                    input-id="cc-filtro-status"
                                    placeholder-blur="Todos os status"
                                    empty-message="Nenhuma opção encontrada."
                                    :max-results="10"
                                    @opening="fecharOutrosComboboxes('filtro-status')"
                                    @select="onSelectFiltro"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-5 mybp-combobox-wrap" v-if="temFilial && listaCcs?.cnpjs">
                        <div class="form-group mb-2 mb-lg-0">
                            <label class="mybp-label" for="cc-filtro-cnpj-input">Por Cnpj</label>
                            <combobox-auto-complete
                                ref="comboFiltroCnpj"
                                instance-id="filtro-cnpj"
                                v-model="controle.dados.campoCnpj"
                                :options="cnpjComboboxOpcoesFiltro"
                                :disabled="controle.carregando || !cnpjComboboxOpcoes.length"
                                input-id="cc-filtro-cnpj-input"
                                empty-message="Nenhum CNPJ encontrado."
                                :max-results="50"
                                @opening="fecharOutrosComboboxes('filtro-cnpj')"
                                @select="onSelectFiltro"
                            />
                        </div>
                    </div>
                </template>

                <template #acoes>
                    <button type="button" class="btn btn-sm btn-success" :disabled="controle.carregando" @click="atualizar">
                        <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i> Atualizar
                    </button>
                    <button type="button" class="btn btn-sm btn-secondary" @click="abrirModalFormNovo">
                        <i class="fa fa-plus"></i> Cadastrar
                    </button>
                </template>
            </FiltroListagem>

            <div class="alert alert-info border-0 py-2" role="alert">
                <i class="fa fa-info-circle me-2"></i>
                <strong>Importante:</strong> O gestor principal e o substituto definidos aqui são usados automaticamente no fluxo de transferência prevista.
            </div>

            <preload class="text-center" v-if="controle.carregando"></preload>

            <div class="alert alert-warning text-center" v-show="!controle.carregando && lista.length === 0">
                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
            </div>

            <div class="mybp-cards-lista" v-show="!controle.carregando && lista.length > 0">
                <div class="mybp-card" v-for="centrocusto in lista" :key="centrocusto.id">
                    <div class="mybp-card-header-row">
                        <div class="mybp-card-left">
                            <span class="mybp-badge-id">#{{ centrocusto.id }}</span>
                            <div class="mybp-card-titulo">
                                <strong>{{ centrocusto.label }}</strong>
                            </div>
                        </div>
                        <div class="mybp-card-right">
                            <bt-ativo
                                :rota="`cadastro/centrocusto/${centrocusto.id}/ativa-desativa`"
                                :model="centrocusto"
                                @atualizou="atualizar()"
                            ></bt-ativo>
                            <div class="dropdown" :class="{ show: isDropdownOpen(centrocusto.id) }">
                                <a
                                    class="mybp-btn-acoes-compact"
                                    href="#"
                                    role="button"
                                    aria-haspopup="true"
                                    :aria-expanded="isDropdownOpen(centrocusto.id) ? 'true' : 'false'"
                                    @click.prevent.stop="toggleDropdown(centrocusto.id)"
                                >
                                    <i class="fas fa-ellipsis-v"></i>
                                </a>
                                <div
                                    class="dropdown-menu mybp-dropdown-menu dropdown-menu-right"
                                    :class="{ show: isDropdownOpen(centrocusto.id) }"
                                    @click="fecharDropdown"
                                >
                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        title="Editar"
                                        @click.prevent="abrirModalAlterar(centrocusto.id)"
                                    >
                                        <i class="fa fa-edit mr-1"></i> Editar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mybp-card-details-row" v-if="temFilial">
                        <div class="mybp-detail-item">
                            <i class="fas fa-map-marker-alt text-muted"></i>
                            <span class="mybp-detail-label">Lotação</span>
                            <span class="mybp-detail-value">{{ lotacaoCentroCusto(centrocusto) }}</span>
                        </div>
                    </div>

                    <div class="mybp-card-secoes-row">
                        <div class="mybp-card-destaque mybp-card-destaque--primary">
                            <i class="fas fa-user-tie text-primary"></i>
                            <div class="mybp-card-destaque-info">
                                <small class="mybp-card-destaque-etapa">Gestor Principal</small>
                                <strong class="mybp-card-destaque-valor">{{ nomeGestorPrincipal(centrocusto) }}</strong>
                            </div>
                        </div>

                        <div class="mybp-card-destaque mybp-card-destaque--info">
                            <i
                                class="fas fa-user-clock"
                                :class="nomeGestorSubstituto(centrocusto) !== 'Não informado' ? 'text-info' : 'text-muted'"
                            ></i>
                            <div class="mybp-card-destaque-info">
                                <small class="mybp-card-destaque-etapa">Gestor Substituto</small>
                                <strong
                                    class="mybp-card-destaque-valor"
                                    :class="{ 'text-muted': nomeGestorSubstituto(centrocusto) === 'Não informado' }"
                                >
                                    {{ nomeGestorSubstituto(centrocusto) }}
                                </strong>
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

<script setup>
import { ref, reactive, computed, onMounted, onBeforeUnmount, watch, nextTick } from 'vue'
import gestor from '../../GestorAprovacao'
import autocomplete from '../../AutoComplete'
import controlePaginacao from '../../ControlePaginacao'
import ModalComponent from '../../Modal'
import ComboboxAutoComplete from '../../ComboboxAutoComplete'
import FiltroListagem from '../../ui/FiltroListagem'
import {
    lerFiltrosDaUrl,
    lerPaginacaoDaUrl,
    sincronizarFiltrosNaUrl,
    montarExtrasPaginacao,
    aplicarPaginaInicialListagem,
    temFiltrosPreenchidos,
    limparFiltrosListagem
} from '../../../utils/listagemQueryParams'

const CAMPOS_FILTRO_URL = ['campoBusca', 'campoStatus', 'campoCnpj']

const props = defineProps({
    qntPag: {
        type: Number,
        default: 20
    },
    status: {
        type: Boolean,
        default: true
    },
    filtro: {
        type: Boolean,
        default: true
    },
    modal: {
        type: String,
        default: ''
    }
})

const hash = ref(String(Math.random()).substr(2))
const titulo_janela_form = ref('Centro de Custos')
const carregandoModal = ref(false)
const editando = ref(false)
const cadastrado = ref(false)
const atualizado = ref(false)
const cliente_id = ref('')
const authconfiguracao = ref(null)
const formDefault = ref(null)
const lista = ref([])
const listaCcs = ref(null)
const clientes = ref([])
const modal_janelaForm = ref(null)
const componente = ref(null)
const dropdownAbertoKey = ref(null)
const comboFiltroStatus = ref(null)
const comboFiltroCnpj = ref(null)
const comboFormCnpj = ref(null)
let syncUrlTimer = null

const form = reactive({
    gestor_id: '',
    autocomplete_label_gestor_modal: '',
    autocomplete_label_gestor_modal_anterior: '',
    gestor_substituto_id: '',
    autocomplete_label_gestor_substituto_modal: '',
    autocomplete_label_gestor_substituto_modal_anterior: '',
    label: '',
    campoCnpj: '',
    ativo: true
})

const urlPaginacao = `${URL_ADMIN}/cadastro/centrocusto/atualizar`
const controle = reactive({
    carregando: false,
    dados: {
        campoBusca: '',
        campoStatus: '',
        campoCnpj: '',
        pages: props.qntPag
    }
})

const temFilial = computed(() => authconfiguracao.value?.temFilial ?? false)

const cnpjComboboxOpcoes = computed(() => {
    const cnpjs = listaCcs.value?.cnpjs ?? {}

    return Object.entries(cnpjs).map(([key, item]) => ({
        value: key,
        label: `${item.nome_fantasia || item.razao_social || 'CNPJ'} - ${item.cnpj}`,
        meta: item.matriz ? 'Matriz' : 'Filial',
        raw: item
    }))
})

const cnpjComboboxOpcoesFiltro = computed(() => [{ value: '', label: 'Todos os CNPJs' }, ...cnpjComboboxOpcoes.value])

const filtroStatusOpcoes = computed(() => [
    { value: '', label: 'Todos os status' },
    { value: 'true', label: 'Apenas ativos' },
    { value: 'false', label: 'Apenas inativos' }
])

const temFiltrosAtivos = computed(() => temFiltrosPreenchidos(controle.dados, CAMPOS_FILTRO_URL))

function getPaginacaoVm() {
    return {
        controle,
        $refs: { componente: componente.value }
    }
}

function urlParamGetFiltros() {
    lerFiltrosDaUrl(controle.dados, CAMPOS_FILTRO_URL)
}

function syncUrlFiltros() {
    sincronizarFiltrosNaUrl(
        controle.dados,
        CAMPOS_FILTRO_URL,
        montarExtrasPaginacao(getPaginacaoVm(), { pagesDefault: props.qntPag })
    )
}

function buscarListagemLocal(resetPagina = true) {
    if (resetPagina && componente.value) {
        componente.value.atual = 1
    }

    componente.value?.buscar?.()
}

function fecharOutrosComboboxes(manter) {
    if (manter !== 'filtro-status' && comboFiltroStatus.value?.close) {
        comboFiltroStatus.value.close()
    }
    if (manter !== 'filtro-cnpj' && comboFiltroCnpj.value?.close) {
        comboFiltroCnpj.value.close()
    }
    if (manter !== 'form-cnpj' && comboFormCnpj.value?.close) {
        comboFormCnpj.value.close()
    }
}

function onSelectFiltro() {
    atualizar()
}

function limparFiltros() {
    limparFiltrosListagem(controle.dados, CAMPOS_FILTRO_URL)
    fecharOutrosComboboxes(null)
    atualizar()
}

function cnpjKeyMatriz() {
    if (!listaCcs.value?.cnpjs) return ''

    const matriz = Object.entries(listaCcs.value.cnpjs).find(([, info]) => info.matriz)
    if (matriz) return matriz[0]

    return Object.keys(listaCcs.value.cnpjs)[0] ?? ''
}

function validarCnpjFormulario() {
    if (temFilial.value && !form.campoCnpj) {
        mostraErro('', 'Selecione o CNPJ do centro de custo')
        return false
    }

    return true
}

function nomeGestorPrincipal(item) {
    return item.gestor?.nome ?? item.Gestor?.nome ?? 'Não informado'
}

function nomeGestorSubstituto(item) {
    return item.gestor_substituto?.usuario?.nome ?? item.GestorSubstituto?.Usuario?.nome ?? 'Não informado'
}

function lotacaoCentroCusto(item) {
    if (listaCcs.value?.centros_custos) {
        for (const [cnpjKey, centros] of Object.entries(listaCcs.value.centros_custos)) {
            const encontrado = (centros || []).find((centro) => centro.id === item.id)
            if (!encontrado) continue

            const info = listaCcs.value.cnpjs?.[cnpjKey] ?? {}
            const tipo = encontrado.matriz ? 'Matriz' : 'Filial'
            const nome = info.nome_fantasia || info.razao_social || encontrado.nome_fantasia || encontrado.razao_social
            const cnpj = info.cnpj || encontrado.cnpj_format

            if (nome && cnpj) return `${nome} - ${cnpj} (${tipo})`
            if (nome) return `${nome} (${tipo})`
            return tipo
        }
    }

    const filiais = item.filiais ?? item.Filiais ?? []
    if (filiais.length) {
        const dadosFilial = filiais[0].filial?.dados ?? filiais[0].Filial?.dados ?? {}
        const nome = dadosFilial.nome_fantasia || dadosFilial.razao_social
        const cnpj = dadosFilial.cnpj
        if (nome && cnpj) return `${nome} - ${cnpj} (Filial)`
        if (nome) return `${nome} (Filial)`
        return 'Filial'
    }

    return 'Matriz'
}

function fecharModal() {
    modal_janelaForm.value?.fecharModal?.()
}

function toggleDropdown(centroCustoId) {
    if (!centroCustoId) return
    const key = `cc:${centroCustoId}`
    dropdownAbertoKey.value = dropdownAbertoKey.value === key ? null : key
}

function isDropdownOpen(centroCustoId) {
    return dropdownAbertoKey.value === `cc:${centroCustoId}`
}

function fecharDropdown() {
    dropdownAbertoKey.value = null
}

function onClickOutside(event) {
    if (event?.target?.closest?.('.dropdown')) return
    if (comboFiltroStatus.value?.containsTarget?.(event.target)) return
    if (comboFiltroCnpj.value?.containsTarget?.(event.target)) return
    if (comboFormCnpj.value?.containsTarget?.(event.target)) return
    dropdownAbertoKey.value = null
    fecharOutrosComboboxes(null)
}

function formNovo() {
    titulo_janela_form.value = 'Novo Centro de Custo'
    carregandoModal.value = false
    cadastrado.value = false
    atualizado.value = false
    Object.assign(form, _.cloneDeep(formDefault.value))
    form.campoCnpj = cnpjKeyMatriz()
    formReset()
}

async function cadastra() {
    form.cliente_id = cliente_id.value === 0 ? form.cliente_id : cliente_id.value
    if (!validarCnpjFormulario()) return false
    $('#janelaForm :input:visible').trigger('blur')
    if ($('#janelaForm :input:visible.is-invalid').length) {
        mostraErro('', 'Verificar os erros')
        return false
    }
    carregandoModal.value = true
    try {
        await axios.post(`${URL_ADMIN}/cadastro/centrocusto`, form)
        fecharModal()
        mostraSucesso('', 'Centro de Custo cadastrado com sucesso')
        cadastrado.value = true
        componente.value?.buscar?.()
    } catch (error) {
        cadastrado.value = false
    } finally {
        carregandoModal.value = false
    }
}

async function alterar(centrocustoId) {
    form.cliente_id = cliente_id.value === 0 ? form.cliente_id : cliente_id.value
    cadastrado.value = true
    editando.value = true
    carregandoModal.value = true
    titulo_janela_form.value = 'Editar Centro de Custo'
    formReset()
    Object.assign(form, _.cloneDeep(formDefault.value))
    form.campoCnpj = cnpjKeyMatriz()

    try {
        const response = await axios.get(`${URL_ADMIN}/cadastro/centrocusto/${centrocustoId}/editar`)
        const data = response.data
        Object.assign(form, data)
        form.campoCnpj = data.campo_cnpj ?? cnpjKeyMatriz()

        editando.value = true
        setupCampo()
    } catch (error) {
        // silencioso
    } finally {
        carregandoModal.value = false
    }
}

async function alterarForm() {
    form.cliente_id = cliente_id.value === 0 ? form.cliente_id : cliente_id.value
    if (!validarCnpjFormulario()) return false
    $('#janelaForm :input:visible').trigger('blur')
    if ($('#janelaForm :input:visible.is-invalid').length) {
        mostraErro('', 'Verificar os erros')
        return false
    }
    carregandoModal.value = true
    try {
        await axios.put(`${URL_ADMIN}/cadastro/centrocusto/${form.id}`, form)
        fecharModal()
        mostraSucesso('', 'Centro de Custo alterado com sucesso')
        componente.value?.buscar?.()
    } catch (error) {
        cadastrado.value = false
    } finally {
        carregandoModal.value = false
    }
}

function carregou(dados) {
    lista.value = dados.items
    clientes.value = dados.clientes ?? []
    listaCcs.value = dados.lista_ccs ?? null
    controle.carregando = false
    nextTick(() => syncUrlFiltros())
}

function carregando() {
    controle.carregando = true
}

function atualizar() {
    buscarListagemLocal(true)
}

function abrirModalFormNovo() {
    formNovo()
    modal_janelaForm.value?.abrirModal?.()
}

async function abrirModalAlterar(centrocustoId) {
    fecharDropdown()
    await alterar(centrocustoId)
    modal_janelaForm.value?.abrirModal?.()
}

function selecionaGestorSubstituto(obj) {
    form.gestor_substituto_id = obj.id
    form.autocomplete_label_gestor_substituto_modal = obj.label
    form.autocomplete_label_gestor_substituto_modal_anterior = obj.label
}

function resetaGestorSubstituto() {
    if (form.autocomplete_label_gestor_substituto_modal_anterior !== form.autocomplete_label_gestor_substituto_modal) {
        form.autocomplete_label_gestor_substituto_modal_anterior = ''
        form.autocomplete_label_gestor_substituto_modal = ''
        form.gestor_substituto_id = ''
    }
}

function onSubmitFiltro() {
    atualizar()
}

watch(
    () => controle.dados,
    () => {
        if (syncUrlTimer) {
            clearTimeout(syncUrlTimer)
        }

        syncUrlTimer = setTimeout(() => syncUrlFiltros(), 400)
    },
    { deep: true }
)

onMounted(async () => {
    try {
        const { data } = await axios.get(`${URL_ADMIN}/usuario/autenticado/`)
        authconfiguracao.value = data
    } catch (error) {
        // silencioso
    }
    formDefault.value = _.cloneDeep(form)
    urlParamGetFiltros()
    const paginaInicial = lerPaginacaoDaUrl(controle.dados, { pagesDefault: props.qntPag })
    document.addEventListener('click', onClickOutside)
    await nextTick()
    aplicarPaginaInicialListagem(getPaginacaoVm(), paginaInicial)
    buscarListagemLocal(false)
})

onBeforeUnmount(() => {
    document.removeEventListener('click', onClickOutside)
})
</script>

<style scoped>
/* Estilos globais em resources/sass/_mybp-listagem-ui.scss — ver docs/PADRAO_UX_LISTAGEM_CADASTROS.md */
</style>

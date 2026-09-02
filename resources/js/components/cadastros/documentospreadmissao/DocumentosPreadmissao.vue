<template>
    <div id="componenteDocumentosPreadmissao">
        <ModalComponent
            id="janelaCadastrar"
            :titulo="titulo_janela"
            :fechar="!carregandoModal && !salvando"
            :mostrar-botao-fechar-no-rodape="false"
            :size="90"
            ref="modal_janelaCadastrar"
            @abriu="aoAbrirModal"
            @fechou="aoFecharModal"
        >
            <template #conteudo>
                <preload class="text-center" v-if="carregandoModal"></preload>
                <fieldset class="mt-0" v-if="!carregandoModal">
                    <legend>Informações</legend>
                    <p class="mybp-campo-obrigatorio-legenda mb-3">
                        Campos com <span class="text-danger">*</span> são obrigatórios.
                    </p>
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="mybp-label" for="doc-preadm-form-label">
                                    Nome <span class="text-danger">*</span>
                                </label>
                                <input
                                    id="doc-preadm-form-label"
                                    v-model="form.label"
                                    class="form-control form-control-sm"
                                    type="text"
                                    placeholder="Informe o nome exibido na pré-admissão"
                                    onblur="valida_campo_vazio(this, 1)"
                                />
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label class="mybp-label" for="doc-preadm-form-categoria">
                                    Categoria <span class="text-danger">*</span>
                                </label>
                                <div class="mybp-combobox-wrap">
                                    <combobox-auto-complete
                                        ref="comboFormCategoria"
                                        instance-id="form-categoria"
                                        v-model="form.categoria_id"
                                        :options="formCategoriaOpcoes"
                                        :disabled="salvando"
                                        input-id="doc-preadm-form-categoria"
                                        placeholder-blur="Selecione a categoria"
                                        empty-message="Nenhuma categoria encontrada."
                                        :max-results="50"
                                        @opening="fecharOutrosComboboxes('form-categoria')"
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label class="mybp-label" for="doc-preadm-form-categoria-nova">Nova categoria</label>
                                <input
                                    id="doc-preadm-form-categoria-nova"
                                    v-model="form.categoria_nova"
                                    class="form-control form-control-sm"
                                    type="text"
                                    placeholder="Preencha para criar uma categoria"
                                />
                                <small class="text-muted">Se preencher, ignora a categoria selecionada e cria (ou reusa) este nome.</small>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label class="mybp-label" for="doc-preadm-form-ordem">Ordem</label>
                                <input
                                    id="doc-preadm-form-ordem"
                                    v-model="form.ordem"
                                    class="form-control form-control-sm"
                                    type="number"
                                    min="0"
                                    placeholder="Ex: 1"
                                />
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label class="mybp-label">Descrição</label>
                                <tiny-mce-editor
                                    v-if="modalAberto && !carregandoModal"
                                    :key="'doc-preadm-descricao-' + editorDescricaoKey"
                                    v-model="form.descricao"
                                    preset="simples"
                                    :init="tinyDescricaoInit"
                                />
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label class="mybp-label" for="doc-preadm-form-arquivo">Tipo de arquivo aceito</label>
                                <div class="mybp-combobox-wrap">
                                    <combobox-auto-complete
                                        ref="comboFormArquivo"
                                        instance-id="form-arquivo"
                                        v-model="tipoArquivoAceito"
                                        :options="tipoArquivoOpcoes"
                                        :disabled="salvando"
                                        input-id="doc-preadm-form-arquivo"
                                        placeholder-blur="Qualquer formato"
                                        empty-message="Nenhuma opção encontrada."
                                        :max-results="10"
                                        @opening="fecharOutrosComboboxes('form-arquivo')"
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-3" v-if="form.configuracoes.multiple">
                            <div class="form-group">
                                <label class="mybp-label" for="doc-preadm-form-min">Mínimo</label>
                                <input
                                    id="doc-preadm-form-min"
                                    v-model.number="form.configuracoes.min"
                                    class="form-control form-control-sm"
                                    type="number"
                                    min="1"
                                />
                            </div>
                        </div>
                        <div class="col-12 col-md-3" v-if="form.configuracoes.multiple">
                            <div class="form-group">
                                <label class="mybp-label" for="doc-preadm-form-max">Máximo</label>
                                <input
                                    id="doc-preadm-form-max"
                                    v-model.number="form.configuracoes.max"
                                    class="form-control form-control-sm"
                                    type="number"
                                    min="1"
                                />
                            </div>
                        </div>
                        <div class="col-12 mt-2">
                            <div class="custom-control custom-switch mb-2">
                                <input type="checkbox" v-model="form.configuracoes.obrigatorio" class="custom-control-input" id="doc-preadm-obrigatorio" />
                                <label class="custom-control-label" for="doc-preadm-obrigatorio">Obrigatório</label>
                            </div>
                            <div class="custom-control custom-switch mb-2">
                                <input type="checkbox" v-model="form.configuracoes.multiple" class="custom-control-input" id="doc-preadm-multiple" />
                                <label class="custom-control-label" for="doc-preadm-multiple">Permitir vários arquivos</label>
                            </div>
                            <div class="custom-control custom-switch mb-2">
                                <input type="checkbox" v-model="form.configuracoes.sogestao" class="custom-control-input" id="doc-preadm-sogestao" />
                                <label class="custom-control-label" for="doc-preadm-sogestao">Só gestão (oculto para o candidato)</label>
                            </div>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" v-model="form.ativo" class="custom-control-input" id="doc-preadm-ativo" />
                                <label class="custom-control-label" for="doc-preadm-ativo">{{ form.ativo ? 'Ativo' : 'Inativo' }}</label>
                            </div>
                        </div>
                    </div>
                </fieldset>
            </template>
            <template #rodape>
                <button type="button" class="btn btn-sm mr-1 btn-secondary" :disabled="carregandoModal || salvando" @click="fecharModal">
                    <i class="fa fa-times"></i> Cancelar
                </button>
                <button
                    type="button"
                    class="btn btn-sm mr-1 btn-primary"
                    v-if="!cadastrado && canInsert"
                    :disabled="carregandoModal || salvando"
                    @click="cadastrar()"
                >
                    <span v-if="salvando">
                        <span class="spinner-border spinner-border-sm mr-1" role="status"></span>
                        Salvando...
                    </span>
                    <span v-else><i class="fa fa-save"></i> Cadastrar</span>
                </button>
                <button
                    type="button"
                    class="btn btn-sm mr-1 btn-primary"
                    v-if="cadastrado && canUpdate"
                    :disabled="carregandoModal || salvando"
                    @click="alterarForm()"
                >
                    <span v-if="salvando">
                        <span class="spinner-border spinner-border-sm mr-1" role="status"></span>
                        Salvando...
                    </span>
                    <span v-else><i class="fa fa-save"></i> Salvar</span>
                </button>
            </template>
        </ModalComponent>

        <FiltroListagem
            @submit="onSubmitFiltro"
            :mostrar-limpar-filtros="temFiltrosAtivos"
            :desabilitado="controle.carregando"
            @limpar="limparFiltros"
        >
            <template #filtros>
                <div class="col-12 col-lg-4">
                    <div class="form-group mb-2 mb-lg-0">
                        <label class="mybp-label" for="doc-preadm-filtro-busca">Buscar</label>
                        <input
                            id="doc-preadm-filtro-busca"
                            type="text"
                            placeholder="Buscar por nome, tipo ou ID"
                            autocomplete="off"
                            class="form-control form-control-sm"
                            :disabled="controle.carregando"
                            v-model="controle.dados.campoBusca"
                        />
                    </div>
                </div>
                <div class="col-12 col-lg-4">
                    <div class="form-group mb-2 mb-lg-0">
                        <label class="mybp-label" for="doc-preadm-filtro-status">Status</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroStatus"
                                instance-id="filtro-status"
                                v-model="controle.dados.campoStatus"
                                :options="filtroStatusOpcoes"
                                :disabled="controle.carregando"
                                input-id="doc-preadm-filtro-status"
                                placeholder-blur="Todos os status"
                                empty-message="Nenhuma opção encontrada."
                                :max-results="10"
                                @opening="fecharOutrosComboboxes('filtro-status')"
                                @select="onSelectFiltro"
                            />
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-4">
                    <div class="form-group mb-2 mb-lg-0">
                        <label class="mybp-label" for="doc-preadm-filtro-categoria">Categoria</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroCategoria"
                                instance-id="filtro-categoria"
                                v-model="controle.dados.campoCategoria"
                                :options="filtroCategoriaOpcoes"
                                :disabled="controle.carregando"
                                input-id="doc-preadm-filtro-categoria"
                                placeholder-blur="Todas as categorias"
                                empty-message="Nenhuma opção encontrada."
                                :max-results="50"
                                @opening="fecharOutrosComboboxes('filtro-categoria')"
                                @select="onSelectFiltro"
                            />
                        </div>
                    </div>
                </div>
            </template>
            <template #acoes>
                <button type="submit" class="btn btn-sm btn-success" :disabled="controle.carregando">
                    <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i> Atualizar
                </button>
                <button v-if="canInsert" type="button" class="btn btn-sm btn-secondary" :disabled="controle.carregando" @click="abrirModalNovo">
                    <i class="fa fa-plus"></i> Cadastrar
                </button>
            </template>
        </FiltroListagem>

        <div id="conteudo">
            <p class="mt-2 text-center" v-if="controle.carregando">
                <preload></preload>
            </p>

            <div class="alert alert-warning text-center" v-show="!controle.carregando && lista.length === 0">
                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
            </div>

            <div class="mybp-cards-lista" v-show="!controle.carregando && lista.length > 0">
                <div class="mybp-card" v-for="item in lista" :key="item.id">
                    <div class="mybp-card-header-row">
                        <div class="mybp-card-left">
                            <span class="mybp-badge-id">#{{ item.id }}</span>
                            <div class="mybp-card-titulo">
                                <strong>{{ item.label }}</strong>
                                <span class="badge badge-info ml-2" v-if="item.padrao_sistema">Padrão do sistema</span>
                            </div>
                        </div>
                        <div class="mybp-card-right">
                            <bt-ativo
                                v-if="canUpdate && !item.padrao_sistema"
                                :rota="`cadastro/documentos-preadmissao/${item.id}/ativa-desativa`"
                                :model="item"
                                @atualizou="atualizar()"
                            ></bt-ativo>
                            <div class="dropdown" v-if="canUpdate && !item.padrao_sistema" :class="{ show: isDropdownOpen(item.id) }">
                                <a
                                    class="mybp-btn-acoes-compact"
                                    href="#"
                                    role="button"
                                    aria-haspopup="true"
                                    :aria-expanded="isDropdownOpen(item.id) ? 'true' : 'false'"
                                    @click.prevent.stop="toggleDropdown(item.id)"
                                >
                                    <i class="fas fa-ellipsis-v"></i>
                                </a>
                                <div
                                    class="dropdown-menu mybp-dropdown-menu dropdown-menu-right"
                                    :class="{ show: isDropdownOpen(item.id) }"
                                    @click="fecharDropdown"
                                >
                                    <a class="dropdown-item" href="javascript://" title="Editar" @click.prevent="abrirModalAlterar(item.id)">
                                        <i class="fa fa-edit mr-1"></i> Editar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mybp-card-details-row">
                        <div class="mybp-detail-item">
                            <i class="fas fa-layer-group text-muted"></i>
                            <span class="mybp-detail-label">Categoria</span>
                            <span class="mybp-detail-value">{{ valorOuNaoInformado(item.categoria_label || item.categoria?.label) }}</span>
                        </div>
                        <div class="mybp-detail-item">
                            <i class="fas fa-fingerprint text-muted"></i>
                            <span class="mybp-detail-label">Tipo</span>
                            <span class="mybp-detail-value">{{ valorOuNaoInformado(item.tipo) }}</span>
                        </div>
                        <div class="mybp-detail-item">
                            <i class="fas fa-sort-numeric-up text-muted"></i>
                            <span class="mybp-detail-label">Ordem</span>
                            <span class="mybp-detail-value">{{ valorOuNaoInformado(item.ordem) }}</span>
                        </div>
                    </div>
                    <div class="mybp-card-secoes-row mt-2">
                        <div class="mybp-card-destaque mybp-card-destaque--primary">
                            <i class="fas fa-file-alt text-primary"></i>
                            <div class="mybp-card-destaque-info">
                                <small class="mybp-card-destaque-etapa">Arquivo</small>
                                <strong class="mybp-card-destaque-valor">{{ resumoArquivo(item.configuracoes) }}</strong>
                            </div>
                        </div>
                        <div class="mybp-card-destaque mybp-card-destaque--info">
                            <i class="fas fa-clipboard-check text-info"></i>
                            <div class="mybp-card-destaque-info">
                                <small class="mybp-card-destaque-etapa">Regras</small>
                                <strong class="mybp-card-destaque-valor">{{ resumoRegras(item.configuracoes) }}</strong>
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

const CAMPOS_FILTRO_URL = ['campoBusca', 'campoStatus', 'campoCategoria']
const CONFIG_DEFAULT = () => ({
    obrigatorio: false,
    apenas_img: false,
    apenas_pdf: false,
    apenas_pdf_img: false,
    multiple: false,
    min: 1,
    max: 1,
    sogestao: false
})

const props = defineProps({
    qntPag: {
        type: Number,
        required: false,
        default: 20
    },
    canInsert: {
        type: Boolean,
        default: false
    },
    canUpdate: {
        type: Boolean,
        default: false
    }
})

const titulo_janela = ref('Documento da pré-admissão')
const dropdownAbertoKey = ref(null)
const carregandoModal = ref(false)
const modalAberto = ref(false)
const editorDescricaoKey = ref(0)
const salvando = ref(false)
const cadastrado = ref(false)
const formDefault = ref(null)
const lista = ref([])
const categorias = ref([])
const modal_janelaCadastrar = ref(null)
const componente = ref(null)
const comboFiltroStatus = ref(null)
const comboFiltroCategoria = ref(null)
const comboFormCategoria = ref(null)
const comboFormArquivo = ref(null)
let syncUrlTimer = null

const tinyDescricaoInit = {
    height: 220,
    plugins: 'paste link',
    toolbar: 'undo redo | bold italic underline | link'
}

const form = reactive({
    label: '',
    descricao: '',
    categoria_id: '',
    categoria_nova: '',
    ordem: 1,
    ativo: true,
    configuracoes: CONFIG_DEFAULT()
})

const urlPaginacao = `${URL_ADMIN}/cadastro/documentos-preadmissao/atualizar`
const controle = reactive({
    carregando: false,
    dados: {
        campoBusca: '',
        campoStatus: '',
        campoCategoria: '',
        pages: props.qntPag
    }
})

const temFiltrosAtivos = computed(() => temFiltrosPreenchidos(controle.dados, CAMPOS_FILTRO_URL))

const filtroStatusOpcoes = computed(() => [
    { value: '', label: 'Todos os status' },
    { value: 'true', label: 'Apenas ativos' },
    { value: 'false', label: 'Apenas inativos' }
])

const filtroCategoriaOpcoes = computed(() => {
    const opcoes = [{ value: '', label: 'Todas as categorias' }]
    categorias.value.forEach((cat) => {
        opcoes.push({ value: String(cat.id), label: cat.label })
    })
    return opcoes
})

const formCategoriaOpcoes = computed(() => {
    return categorias.value.map((cat) => ({ value: cat.id, label: cat.label }))
})

const tipoArquivoOpcoes = computed(() => [
    { value: '', label: 'Qualquer formato' },
    { value: 'apenas_img', label: 'Apenas imagens' },
    { value: 'apenas_pdf', label: 'Apenas PDF' },
    { value: 'apenas_pdf_img', label: 'PDF ou imagens' }
])

const tipoArquivoAceito = computed({
    get() {
        const cfg = form.configuracoes || {}
        if (cfg.apenas_img) return 'apenas_img'
        if (cfg.apenas_pdf) return 'apenas_pdf'
        if (cfg.apenas_pdf_img) return 'apenas_pdf_img'
        return ''
    },
    set(valor) {
        form.configuracoes.apenas_img = valor === 'apenas_img'
        form.configuracoes.apenas_pdf = valor === 'apenas_pdf'
        form.configuracoes.apenas_pdf_img = valor === 'apenas_pdf_img'
    }
})

function getPaginacaoVm() {
    return {
        controle,
        $refs: { componente: componente.value }
    }
}

function resetarFormulario() {
    Object.keys(form).forEach((key) => {
        delete form[key]
    })
    Object.assign(form, _.cloneDeep(formDefault.value))
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

function onSubmitFiltro() {
    atualizar()
}

function onSelectFiltro() {
    atualizar()
}

function limparFiltros() {
    limparFiltrosListagem(controle.dados, CAMPOS_FILTRO_URL)
    fecharOutrosComboboxes(null)
    atualizar()
}

function fecharOutrosComboboxes(manter) {
    const combos = [
        ['filtro-status', comboFiltroStatus],
        ['filtro-categoria', comboFiltroCategoria],
        ['form-categoria', comboFormCategoria],
        ['form-arquivo', comboFormArquivo]
    ]
    combos.forEach(([id, comboRef]) => {
        if (id !== manter && comboRef.value?.close) {
            comboRef.value.close()
        }
    })
}

function onClickOutside(event) {
    if (event?.target?.closest?.('.dropdown')) return
    if (event?.target?.closest?.('.mybp-combobox-wrap')) return
    dropdownAbertoKey.value = null
    fecharOutrosComboboxes(null)
}

function toggleDropdown(id) {
    if (!id) return
    const key = `preadm:${id}`
    dropdownAbertoKey.value = dropdownAbertoKey.value === key ? null : key
}

function isDropdownOpen(id) {
    return dropdownAbertoKey.value === `preadm:${id}`
}

function fecharDropdown() {
    dropdownAbertoKey.value = null
}

function valorOuNaoInformado(valor) {
    if (valor === 0) return '0'
    return valor || 'Não informado'
}

function resumoArquivo(config) {
    const cfg = config || {}
    if (cfg.apenas_img) return 'Apenas imagens'
    if (cfg.apenas_pdf) return 'Apenas PDF'
    if (cfg.apenas_pdf_img) return 'PDF ou imagens'
    return 'Qualquer formato'
}

function resumoRegras(config) {
    const cfg = config || {}
    const partes = []
    partes.push(cfg.obrigatorio ? 'Obrigatório' : 'Opcional')
    if (cfg.multiple) partes.push(`Até ${cfg.max || 1} arquivos`)
    if (cfg.sogestao) partes.push('Só gestão')
    return partes.join(' · ')
}

function aoAbrirModal() {
    modalAberto.value = true
    editorDescricaoKey.value += 1
}

function aoFecharModal() {
    modalAberto.value = false
}

function abrirModalNovo() {
    titulo_janela.value = 'Cadastrar documento'
    carregandoModal.value = false
    salvando.value = false
    cadastrado.value = false
    resetarFormulario()
    if (typeof formReset === 'function') formReset()
    modal_janelaCadastrar.value?.abrirModal?.()
}

function fecharModal() {
    modal_janelaCadastrar.value?.fecharModal?.()
}

function validarFormulario() {
    $('#janelaCadastrar :input:visible').trigger('blur')
    if ($('#janelaCadastrar :input:visible.is-invalid').length) {
        mostraErro('', 'Verificar os erros')
        return false
    }
    const temCategoria = !!(form.categoria_nova && String(form.categoria_nova).trim()) || !!form.categoria_id
    if (!temCategoria) {
        mostraErro('', 'Informe ou cadastre uma categoria.')
        return false
    }
    return true
}

function payloadFormulario() {
    const cfg = { ...CONFIG_DEFAULT(), ...(form.configuracoes || {}) }
    if (!cfg.multiple) {
        cfg.min = 1
        cfg.max = 1
    }
    return {
        label: form.label,
        descricao: form.descricao || '',
        categoria_id: form.categoria_id || null,
        categoria_nova: form.categoria_nova || '',
        ordem: form.ordem,
        ativo: !!form.ativo,
        configuracoes: cfg
    }
}

async function cadastrar() {
    if (!validarFormulario()) return
    salvando.value = true
    try {
        await axios.post(`${URL_ADMIN}/cadastro/documentos-preadmissao`, payloadFormulario())
        fecharModal()
        mostraSucesso('', 'Documento cadastrado com sucesso')
        componente.value?.buscar?.()
    } finally {
        salvando.value = false
    }
}

async function abrirModalAlterar(id) {
    fecharDropdown()
    cadastrado.value = true
    titulo_janela.value = 'Alterar documento'
    resetarFormulario()
    if (typeof formReset === 'function') formReset()
    carregandoModal.value = true
    modal_janelaCadastrar.value?.abrirModal?.()
    try {
        const response = await axios.get(`${URL_ADMIN}/cadastro/documentos-preadmissao/${id}/editar`)
        Object.assign(form, response.data)
        form.configuracoes = { ...CONFIG_DEFAULT(), ...(response.data.configuracoes || {}) }
        form.categoria_nova = ''
        if (typeof setupCampo === 'function') setupCampo()
    } finally {
        carregandoModal.value = false
    }
}

async function alterarForm() {
    if (!validarFormulario()) return
    salvando.value = true
    try {
        await axios.put(`${URL_ADMIN}/cadastro/documentos-preadmissao/${form.id}`, payloadFormulario())
        fecharModal()
        mostraSucesso('', 'Documento alterado com sucesso')
        componente.value?.buscar?.()
    } finally {
        salvando.value = false
    }
}

function carregou(dados) {
    lista.value = dados.items || []
    if (Array.isArray(dados.categorias)) {
        categorias.value = dados.categorias
    }
    controle.carregando = false
    nextTick(() => syncUrlFiltros())
}

function carregando() {
    controle.carregando = true
}

function atualizar() {
    buscarListagemLocal(true)
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

onMounted(() => {
    urlParamGetFiltros()
    const paginaInicial = lerPaginacaoDaUrl(controle.dados, { pagesDefault: props.qntPag })
    formDefault.value = _.cloneDeep(form)
    document.addEventListener('click', onClickOutside)
    nextTick(() => {
        aplicarPaginaInicialListagem(getPaginacaoVm(), paginaInicial)
        buscarListagemLocal(false)
    })
})

onBeforeUnmount(() => {
    document.removeEventListener('click', onClickOutside)
    if (syncUrlTimer) {
        clearTimeout(syncUrlTimer)
    }
})
</script>

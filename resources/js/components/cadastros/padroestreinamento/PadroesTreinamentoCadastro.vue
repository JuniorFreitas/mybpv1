<template>
    <div>
        <modal id="modalPadrao" ref="modal_modalPadrao" :titulo="tituloModal" :fechar="true" size="g">
            <template #conteudo>
                <div class="mybp-modal-form mybp-filtros-compactos padrao-treinamento-form">
                    <fieldset class="mybp-modal-secao">
                        <legend>Identificação</legend>
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" for="pad_nome">Nome <span class="text-danger">*</span></label>
                                    <input id="pad_nome" v-model="form.nome" class="form-control form-control-sm" type="text" placeholder="Ex.: Padrão ALUMAR Matriz">
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" for="pad_slug">Slug</label>
                                    <input id="pad_slug" v-model="form.slug" class="form-control form-control-sm" type="text" placeholder="Gerado se vazio">
                                </div>
                            </div>
                            <div class="col-12 col-md-2 d-flex align-items-end">
                                <div class="form-group mb-0 pb-1">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" v-model="form.ativo" class="custom-control-input" id="pad_ativo">
                                        <label class="custom-control-label" for="pad_ativo">{{ form.ativo ? 'Ativo' : 'Inativo' }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="mybp-modal-secao">
                        <legend>Documentos</legend>
                        <p class="mybp-modal-legenda mb-2">Defina se o padrão gera carteira e/ou etiqueta de bloqueio.</p>
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="padrao-doc-bloco">
                                    <div class="custom-control custom-switch mb-2">
                                        <input type="checkbox" v-model="form.tem_carteira" class="custom-control-input" id="pad_cart" @change="onToggleCarteira">
                                        <label class="custom-control-label" for="pad_cart">Usa carteira</label>
                                    </div>
                                    <div class="form-group mybp-filtro-campo mb-0" v-if="form.tem_carteira">
                                        <label class="mybp-label" for="pad_carteira">Modelo de carteira <span class="text-danger">*</span></label>
                                        <div class="mybp-combobox-wrap">
                                            <combobox-auto-complete
                                                ref="comboFormCarteira"
                                                instance-id="form-carteira"
                                                v-model="formCarteiraId"
                                                :options="carteiraComboboxOpcoes"
                                                input-id="pad_carteira"
                                                placeholder-blur="Selecione a carteira"
                                                empty-message="Nenhuma carteira encontrada."
                                                :max-results="30"
                                                @opening="fecharOutrosComboboxes('form-carteira')"
                                                @select="onSelectCarteira"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="padrao-doc-bloco">
                                    <div class="custom-control custom-switch mb-2">
                                        <input type="checkbox" v-model="form.tem_etiqueta_bloqueio" class="custom-control-input" id="pad_etq" @change="onToggleEtiqueta">
                                        <label class="custom-control-label" for="pad_etq">Usa etiqueta de bloqueio</label>
                                    </div>
                                    <div class="form-group mybp-filtro-campo mb-0" v-if="form.tem_etiqueta_bloqueio">
                                        <label class="mybp-label" for="pad_etiqueta">Modelo de etiqueta <span class="text-danger">*</span></label>
                                        <div class="mybp-combobox-wrap">
                                            <combobox-auto-complete
                                                ref="comboFormEtiqueta"
                                                instance-id="form-etiqueta"
                                                v-model="formEtiquetaId"
                                                :options="etiquetaComboboxOpcoes"
                                                input-id="pad_etiqueta"
                                                placeholder-blur="Selecione a etiqueta"
                                                empty-message="Nenhuma etiqueta encontrada."
                                                :max-results="30"
                                                @opening="fecharOutrosComboboxes('form-etiqueta')"
                                                @select="onSelectEtiqueta"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="mybp-modal-secao">
                        <legend>Válido para <span class="text-danger">*</span></legend>
                        <p class="mybp-modal-legenda mb-2">Selecione os CNPJs onde este padrão pode ser usado. A escolha é adicionada na hora.</p>
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label" for="pad_lotacao">Buscar CNPJ</label>
                            <div class="mybp-combobox-wrap">
                                <combobox-auto-complete
                                    ref="comboFormLotacao"
                                    instance-id="form-lotacao"
                                    v-model="lotacaoSelecionada"
                                    :options="lotacaoComboboxOpcoes"
                                    input-id="pad_lotacao"
                                    placeholder-blur="Digite nome ou CNPJ e selecione"
                                    empty-message="Nenhum CNPJ disponível ou todos já foram adicionados."
                                    :max-results="30"
                                    @opening="fecharOutrosComboboxes('form-lotacao')"
                                    @select="onSelectLotacaoCombo"
                                />
                            </div>
                        </div>

                        <div class="padrao-chip-lista" v-if="form.lotacoes.length">
                            <div class="padrao-chip" v-for="(l, idx) in form.lotacoes" :key="l.key">
                                <span class="padrao-chip-texto" :title="l.label">{{ l.label }}</span>
                                <span v-if="l.meta" class="padrao-chip-meta">{{ l.meta }}</span>
                                <button
                                    type="button"
                                    class="padrao-chip-remove"
                                    :aria-label="`Remover ${l.label}`"
                                    @click="removerLotacao(idx)"
                                >
                                    <i class="fa fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="padrao-empty-hint" v-else>
                            <i class="fas fa-building text-muted mr-1"></i>
                            Nenhum CNPJ selecionado. Busque e escolha ao menos um.
                        </div>
                        <div class="mt-2" v-if="opcoesLotacaoDisponiveis.length > 1">
                            <button type="button" class="btn btn-sm btn-link p-0" @click="adicionarTodasLotacoes">
                                Adicionar todos os CNPJs restantes ({{ opcoesLotacaoDisponiveis.length }})
                            </button>
                        </div>
                    </fieldset>

                    <fieldset class="mybp-modal-secao">
                        <legend>Treinamentos</legend>
                        <p class="mybp-modal-legenda mb-2">
                            Marque os treinamentos deste padrão.
                            <span v-if="vencimentos.length">
                                <strong>{{ qtdVencimentosSelecionados }}</strong> de {{ vencimentos.length }} selecionado(s).
                            </span>
                        </p>

                        <div class="padrao-treino-toolbar">
                            <div class="padrao-treino-busca">
                                <i class="fas fa-search padrao-treino-busca-icon" aria-hidden="true"></i>
                                <input
                                    id="pad_busca_venc"
                                    v-model="buscaVencimento"
                                    type="search"
                                    class="form-control form-control-sm"
                                    placeholder="Filtrar por nome do treinamento"
                                    autocomplete="off"
                                >
                                <button
                                    v-if="buscaVencimento"
                                    type="button"
                                    class="padrao-treino-busca-limpar"
                                    aria-label="Limpar busca"
                                    @click="buscaVencimento = ''"
                                >
                                    <i class="fa fa-times"></i>
                                </button>
                            </div>
                            <div class="padrao-treino-acoes">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary"
                                    :disabled="!vencimentosFiltrados.length"
                                    @click="selecionarTodosVencimentos"
                                >
                                    Selecionar{{ buscaVencimento ? ' filtrados' : ' todos' }}
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-secondary"
                                    :disabled="!form.vencimento_ids.length"
                                    @click="desmarcarTodosVencimentos"
                                >
                                    Desmarcar{{ buscaVencimento ? ' filtrados' : '' }}
                                </button>
                            </div>
                        </div>

                        <div class="padrao-treino-lista" role="group" aria-labelledby="pad_busca_venc">
                            <label
                                class="padrao-treino-item"
                                v-for="v in vencimentosFiltrados"
                                :key="v.id"
                                :for="'venc_'+v.id"
                            >
                                <input
                                    type="checkbox"
                                    class="padrao-treino-check"
                                    :id="'venc_'+v.id"
                                    :value="v.id"
                                    v-model="form.vencimento_ids"
                                >
                                <span class="padrao-treino-label">{{ v.label }}</span>
                            </label>
                            <div class="padrao-empty-hint" v-if="!vencimentos.length">
                                Nenhum treinamento cadastrado na empresa.
                            </div>
                            <div class="padrao-empty-hint" v-else-if="!vencimentosFiltrados.length">
                                Nenhum resultado para “{{ buscaVencimento }}”.
                                <button type="button" class="btn btn-link btn-sm p-0 ml-1" @click="buscaVencimento = ''">Limpar filtro</button>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </template>
            <template #rodape>
                <button type="button" class="btn btn-sm btn-primary" @click="salvar">
                    {{ editando ? 'Salvar' : 'Cadastrar' }}
                </button>
            </template>
        </modal>

        <FiltroListagem
            @submit="onSubmitFiltro"
            :mostrar-limpar-filtros="temFiltrosAtivos"
            :desabilitado="controle.carregando"
            @limpar="limparFiltros"
        >
            <template #filtros>
                <div class="col-12 col-lg-6">
                    <div class="form-group mb-2 mb-lg-0">
                        <label class="mybp-label" for="padrao-filtro-busca">Buscar</label>
                        <input
                            id="padrao-filtro-busca"
                            type="text"
                            placeholder="Buscar por nome, slug ou ID"
                            autocomplete="off"
                            class="form-control form-control-sm"
                            :disabled="controle.carregando"
                            v-model="controle.dados.campoBusca"
                        />
                    </div>
                </div>
                <div class="col-12 col-lg-6">
                    <div class="form-group mb-2 mb-lg-0">
                        <label class="mybp-label" for="padrao-filtro-status">Status</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroStatus"
                                instance-id="filtro-status"
                                v-model="controle.dados.campoStatus"
                                :options="filtroStatusOpcoes"
                                :disabled="controle.carregando"
                                input-id="padrao-filtro-status"
                                placeholder-blur="Todos os status"
                                empty-message="Nenhuma opção encontrada."
                                :max-results="10"
                                @opening="fecharOutrosComboboxes('filtro-status')"
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
                <button type="button" class="btn btn-sm btn-secondary" :disabled="controle.carregando" @click="abrirModalNovo">
                    <i class="fa fa-plus"></i> Cadastrar
                </button>
            </template>
        </FiltroListagem>

        <div id="conteudo">
            <p class="mt-2 text-center" v-if="controle.carregando">
                <i class="fa fa-spinner fa-pulse fa-2x text-muted"></i>
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
                                <strong>{{ item.nome }}</strong>
                            </div>
                            <span class="badge badge-pill ml-2" :class="item.ativo ? 'badge-success' : 'badge-secondary'">
                                {{ item.ativo ? 'Ativo' : 'Inativo' }}
                            </span>
                        </div>
                        <div class="mybp-card-right">
                            <div class="dropdown" :class="{ show: isDropdownOpen(item.id) }">
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
                                    <a class="dropdown-item" href="javascript://" title="Editar" @click.prevent="abrirModalAlterar(item)">
                                        <i class="fa fa-edit mr-1"></i> Editar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mybp-card-details-row">
                        <div class="mybp-detail-item">
                            <i class="fas fa-code text-muted"></i>
                            <span class="mybp-detail-label">Slug</span>
                            <span class="mybp-detail-value"><code>{{ item.slug || '—' }}</code></span>
                        </div>
                        <div class="mybp-detail-item">
                            <i class="fas fa-id-card text-muted"></i>
                            <span class="mybp-detail-label">Carteira</span>
                            <span class="mybp-detail-value">{{ nomeCarteira(item) }}</span>
                        </div>
                        <div class="mybp-detail-item">
                            <i class="fas fa-ban text-muted"></i>
                            <span class="mybp-detail-label">Etiqueta</span>
                            <span class="mybp-detail-value">{{ nomeEtiqueta(item) }}</span>
                        </div>
                        <div class="mybp-detail-item">
                            <i class="fas fa-graduation-cap text-muted"></i>
                            <span class="mybp-detail-label">Treinamentos</span>
                            <span class="mybp-detail-value">{{ (item.vencimentos && item.vencimentos.length) || 0 }}</span>
                        </div>
                        <div class="mybp-detail-item mybp-detail-item--full">
                            <i class="fas fa-building text-muted"></i>
                            <span class="mybp-detail-label">CNPJs</span>
                            <span class="mybp-detail-value">{{ resumoLotacoes(item) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import modal from '../../Modal'
import ComboboxAutoComplete from '../../ComboboxAutoComplete'
import FiltroListagem from '../../ui/FiltroListagem'
import {
    lerFiltrosDaUrl,
    sincronizarFiltrosNaUrl,
    temFiltrosPreenchidos,
    limparFiltrosListagem
} from '../../../utils/listagemQueryParams'

const CAMPOS_FILTRO_URL = ['campoBusca', 'campoStatus']

export default {
    components: { modal, ComboboxAutoComplete, FiltroListagem },
    data() {
        return {
            lista: [],
            editando: false,
            carteiras: [],
            etiquetas: [],
            vencimentos: [],
            opcoesLotacao: [],
            lotacaoSelecionada: '',
            formCarteiraId: '',
            formEtiquetaId: '',
            buscaVencimento: '',
            form: this.formDefault(),
            dropdownAbertoKey: null,
            syncUrlTimer: null,
            controle: {
                carregando: false,
                dados: {
                    campoBusca: '',
                    campoStatus: '',
                },
            },
        }
    },
    computed: {
        tituloModal() {
            return this.editando ? 'Editar padrão' : 'Novo padrão'
        },
        opcoesLotacaoDisponiveis() {
            const usadas = new Set(this.form.lotacoes.map((l) => l.key))
            return this.opcoesLotacao.filter((o) => !usadas.has(o.key))
        },
        lotacaoComboboxOpcoes() {
            return this.opcoesLotacaoDisponiveis.map((o) => ({
                value: o.key,
                label: o.label,
                meta: o.meta || (o.is_matriz ? 'Matriz' : 'Filial'),
                raw: o,
            }))
        },
        carteiraComboboxOpcoes() {
            return (this.carteiras || []).map((c) => ({
                value: String(c.id),
                label: c.nome,
                meta: c.empresa_id ? 'Empresa' : 'Global',
                raw: c,
            }))
        },
        etiquetaComboboxOpcoes() {
            return (this.etiquetas || []).map((e) => ({
                value: String(e.id),
                label: e.nome,
                meta: e.empresa_id ? 'Empresa' : 'Global',
                raw: e,
            }))
        },
        vencimentosFiltrados() {
            const termo = String(this.buscaVencimento || '').trim().toLowerCase()
            if (!termo) return this.vencimentos
            return this.vencimentos.filter((v) => String(v.label || '').toLowerCase().includes(termo))
        },
        qtdVencimentosSelecionados() {
            return (this.form.vencimento_ids || []).length
        },
        filtroStatusOpcoes() {
            return [
                { value: '', label: 'Todos os status' },
                { value: 'true', label: 'Apenas ativos' },
                { value: 'false', label: 'Apenas inativos' },
            ]
        },
        temFiltrosAtivos() {
            return temFiltrosPreenchidos(this.controle.dados, CAMPOS_FILTRO_URL)
        },
    },
    watch: {
        'controle.dados': {
            handler() {
                if (this.syncUrlTimer) clearTimeout(this.syncUrlTimer)
                this.syncUrlTimer = setTimeout(() => this.syncUrlFiltros(), 400)
            },
            deep: true,
        },
    },
    mounted() {
        lerFiltrosDaUrl(this.controle.dados, CAMPOS_FILTRO_URL)
        document.addEventListener('click', this.onClickOutside)
        this.carregarOpcoes()
        this.$nextTick(() => this.atualizar())
    },
    beforeUnmount() {
        document.removeEventListener('click', this.onClickOutside)
        if (this.syncUrlTimer) clearTimeout(this.syncUrlTimer)
    },
    methods: {
        formDefault() {
            return {
                id: null,
                nome: '',
                slug: '',
                ativo: true,
                tem_carteira: false,
                carteira_treinamento_id: null,
                tem_etiqueta_bloqueio: false,
                etiqueta_bloqueio_id: null,
                lotacoes: [],
                vencimento_ids: [],
            }
        },
        onSubmitFiltro() {
            this.atualizar()
        },
        onSelectFiltro() {
            this.atualizar()
        },
        fecharOutrosComboboxes(manter) {
            const combos = [
                ['filtro-status', 'comboFiltroStatus'],
                ['form-lotacao', 'comboFormLotacao'],
                ['form-carteira', 'comboFormCarteira'],
                ['form-etiqueta', 'comboFormEtiqueta'],
            ]
            combos.forEach(([id, refName]) => {
                if (id !== manter && this.$refs[refName]?.close) {
                    this.$refs[refName].close()
                }
            })
        },
        limparFiltros() {
            limparFiltrosListagem(this.controle.dados, CAMPOS_FILTRO_URL)
            this.fecharOutrosComboboxes(null)
            this.atualizar()
        },
        syncUrlFiltros() {
            sincronizarFiltrosNaUrl(this.controle.dados, CAMPOS_FILTRO_URL)
        },
        onClickOutside(event) {
            if (event?.target?.closest?.('.dropdown')) return
            if (event?.target?.closest?.('.mybp-combobox-wrap')) return
            this.dropdownAbertoKey = null
            this.fecharOutrosComboboxes(null)
        },
        toggleDropdown(id) {
            const key = `padrao:${id}`
            this.dropdownAbertoKey = this.dropdownAbertoKey === key ? null : key
        },
        isDropdownOpen(id) {
            return this.dropdownAbertoKey === `padrao:${id}`
        },
        fecharDropdown() {
            this.dropdownAbertoKey = null
        },
        abrirModal() {
            this.$refs.modal_modalPadrao?.abrirModal?.()
        },
        abrirModalNovo() {
            this.editando = false
            this.form = this.formDefault()
            this.lotacaoSelecionada = ''
            this.formCarteiraId = ''
            this.formEtiquetaId = ''
            this.buscaVencimento = ''
            this.abrirModal()
        },
        abrirModalAlterar(item) {
            this.fecharDropdown()
            this.editar(item)
            this.abrirModal()
        },
        carregarOpcoes() {
            axios.get(`${URL_ADMIN}/cadastro/carteirastreinamento/lista`).then((r) => { this.carteiras = r.data || [] })
            axios.get(`${URL_ADMIN}/cadastro/etiquetasbloqueio/lista`).then((r) => { this.etiquetas = r.data || [] })
            this.carregarOpcoesFormulario()
        },
        carregarOpcoesFormulario() {
            axios.get(`${URL_ADMIN}/cadastro/padroestreinamento/opcoes-formulario`)
                .then((res) => {
                    const data = res.data || {}
                    this.opcoesLotacao = Array.isArray(data.lotacoes) ? data.lotacoes : []
                    this.vencimentos = Array.isArray(data.vencimentos) ? data.vencimentos : []
                })
                .catch(() => {
                    this.opcoesLotacao = []
                    this.vencimentos = []
                })
        },
        atualizar() {
            this.controle.carregando = true
            axios.post(`${URL_ADMIN}/cadastro/padroestreinamento/atualizar`, {
                pages: 1,
                per_page: 100,
                campoBusca: this.controle.dados.campoBusca || '',
                campoStatus: this.controle.dados.campoStatus || '',
            })
                .then((res) => {
                    this.lista = (res.data.dados && res.data.dados.items) ? res.data.dados.items : []
                })
                .finally(() => { this.controle.carregando = false })
        },
        labelLotacaoDeRegistro(l) {
            const key = l.is_matriz ? 'matriz' : `f:${l.cliente_filial_id}`
            const opt = this.opcoesLotacao.find((o) => o.key === key)
            if (opt) {
                return {
                    key,
                    label: opt.label,
                    meta: opt.meta || (opt.is_matriz ? 'Matriz' : 'Filial'),
                    is_matriz: !!opt.is_matriz,
                    cliente_filial_id: opt.cliente_filial_id,
                }
            }
            if (l.is_matriz) {
                return { key: 'matriz', label: 'Não informado', meta: 'Matriz', is_matriz: true, cliente_filial_id: null }
            }
            const dados = (l.cliente_filial && l.cliente_filial.dados) ? l.cliente_filial.dados : {}
            const nome = dados.nome_fantasia || dados.nome_fntasia || dados.razao_social || `Filial #${l.cliente_filial_id}`
            const cnpj = dados.cnpj || ''
            const label = nome && cnpj ? `${nome} - ${cnpj}` : (nome || cnpj || `Filial #${l.cliente_filial_id}`)
            return {
                key: `f:${l.cliente_filial_id}`,
                label,
                meta: 'Filial',
                is_matriz: false,
                cliente_filial_id: l.cliente_filial_id,
            }
        },
        editar(item) {
            this.editando = true
            this.lotacaoSelecionada = ''
            this.buscaVencimento = ''
            const lotacoes = (item.lotacoes || []).map((l) => this.labelLotacaoDeRegistro(l))
            const vencimentoIds = (item.vencimentos || []).map((v) => v.id)
            ;(item.vencimentos || []).forEach((v) => {
                if (!this.vencimentos.some((opt) => Number(opt.id) === Number(v.id))) {
                    this.vencimentos.push({
                        id: v.id,
                        label: (v.label || `#${v.id}`) + (v.ativo === false ? ' (inativo)' : ''),
                        ativo: v.ativo !== false,
                    })
                }
            })
            this.form = {
                id: item.id,
                nome: item.nome,
                slug: item.slug,
                ativo: item.ativo,
                tem_carteira: !!item.tem_carteira,
                carteira_treinamento_id: item.carteira_treinamento_id,
                tem_etiqueta_bloqueio: !!item.tem_etiqueta_bloqueio,
                etiqueta_bloqueio_id: item.etiqueta_bloqueio_id,
                lotacoes,
                vencimento_ids: vencimentoIds,
            }
            this.formCarteiraId = item.carteira_treinamento_id ? String(item.carteira_treinamento_id) : ''
            this.formEtiquetaId = item.etiqueta_bloqueio_id ? String(item.etiqueta_bloqueio_id) : ''
        },
        onToggleCarteira() {
            if (!this.form.tem_carteira) {
                this.form.carteira_treinamento_id = null
                this.formCarteiraId = ''
            }
        },
        onToggleEtiqueta() {
            if (!this.form.tem_etiqueta_bloqueio) {
                this.form.etiqueta_bloqueio_id = null
                this.formEtiquetaId = ''
            }
        },
        onSelectCarteira() {
            this.form.carteira_treinamento_id = this.formCarteiraId ? Number(this.formCarteiraId) : null
        },
        onSelectEtiqueta() {
            this.form.etiqueta_bloqueio_id = this.formEtiquetaId ? Number(this.formEtiquetaId) : null
        },
        onSelectLotacaoCombo() {
            this.$nextTick(() => this.adicionarLotacao())
        },
        adicionarLotacao() {
            const opt = this.opcoesLotacao.find((o) => o.key === this.lotacaoSelecionada)
            if (!opt) return
            if (this.form.lotacoes.some((l) => l.key === opt.key)) {
                this.lotacaoSelecionada = ''
                return
            }
            this.form.lotacoes.push({
                key: opt.key,
                label: opt.label,
                meta: opt.meta || (opt.is_matriz ? 'Matriz' : 'Filial'),
                is_matriz: opt.is_matriz,
                cliente_filial_id: opt.cliente_filial_id,
            })
            this.lotacaoSelecionada = ''
        },
        adicionarTodasLotacoes() {
            this.opcoesLotacaoDisponiveis.forEach((opt) => {
                if (this.form.lotacoes.some((l) => l.key === opt.key)) return
                this.form.lotacoes.push({
                    key: opt.key,
                    label: opt.label,
                    meta: opt.meta || (opt.is_matriz ? 'Matriz' : 'Filial'),
                    is_matriz: opt.is_matriz,
                    cliente_filial_id: opt.cliente_filial_id,
                })
            })
            this.lotacaoSelecionada = ''
        },
        removerLotacao(idx) {
            this.form.lotacoes.splice(idx, 1)
        },
        selecionarTodosVencimentos() {
            const ids = new Set(this.form.vencimento_ids.map((id) => Number(id)))
            this.vencimentosFiltrados.forEach((v) => ids.add(Number(v.id)))
            this.form.vencimento_ids = Array.from(ids)
        },
        desmarcarTodosVencimentos() {
            if (!this.buscaVencimento.trim()) {
                this.form.vencimento_ids = []
                return
            }
            const filtrados = new Set(this.vencimentosFiltrados.map((v) => Number(v.id)))
            this.form.vencimento_ids = this.form.vencimento_ids.filter((id) => !filtrados.has(Number(id)))
        },
        nomeCarteira(item) {
            if (!item.tem_carteira) return 'Não'
            return (item.carteira_treinamento && item.carteira_treinamento.nome) || 'Sim'
        },
        nomeEtiqueta(item) {
            if (!item.tem_etiqueta_bloqueio) return 'Não'
            return (item.etiqueta_bloqueio && item.etiqueta_bloqueio.nome) || 'Sim'
        },
        resumoLotacoes(item) {
            const lots = item.lotacoes || []
            if (!lots.length) return '—'
            return lots.map((l) => this.labelLotacaoDeRegistro(l).label).join(', ')
        },
        salvar() {
            if (this.form.tem_carteira) {
                this.form.carteira_treinamento_id = this.formCarteiraId ? Number(this.formCarteiraId) : null
            }
            if (this.form.tem_etiqueta_bloqueio) {
                this.form.etiqueta_bloqueio_id = this.formEtiquetaId ? Number(this.formEtiquetaId) : null
            }
            if (!this.form.nome) {
                if (typeof mostraErro === 'function') mostraErro('', 'Informe o nome')
                return
            }
            if (!this.form.lotacoes.length) {
                if (typeof mostraErro === 'function') mostraErro('', 'Selecione ao menos um CNPJ')
                return
            }
            if (this.form.tem_carteira && !this.form.carteira_treinamento_id) {
                if (typeof mostraErro === 'function') mostraErro('', 'Selecione o modelo de carteira')
                return
            }
            if (this.form.tem_etiqueta_bloqueio && !this.form.etiqueta_bloqueio_id) {
                if (typeof mostraErro === 'function') mostraErro('', 'Selecione o modelo de etiqueta')
                return
            }
            const payload = {
                nome: this.form.nome,
                slug: this.form.slug || null,
                ativo: Boolean(this.form.ativo),
                tem_carteira: Boolean(this.form.tem_carteira),
                carteira_treinamento_id: this.form.tem_carteira ? this.form.carteira_treinamento_id : null,
                tem_etiqueta_bloqueio: Boolean(this.form.tem_etiqueta_bloqueio),
                etiqueta_bloqueio_id: this.form.tem_etiqueta_bloqueio ? this.form.etiqueta_bloqueio_id : null,
                lotacoes: this.form.lotacoes.map((l) => ({
                    is_matriz: l.is_matriz,
                    cliente_filial_id: l.cliente_filial_id,
                })),
                vencimento_ids: this.form.vencimento_ids,
            }
            const req = this.editando
                ? axios.put(`${URL_ADMIN}/cadastro/padroestreinamento/${this.form.id}`, payload)
                : axios.post(`${URL_ADMIN}/cadastro/padroestreinamento`, payload)
            req.then(() => {
                this.$refs.modal_modalPadrao?.fecharModal?.()
                if (typeof mostraSucesso === 'function') mostraSucesso('', 'Padrão salvo')
                this.atualizar()
            }).catch((err) => {
                const data = err.response && err.response.data
                let msg = (data && (data.message || data.msg)) || 'Erro ao salvar'
                if (data && data.errors) {
                    msg = Object.values(data.errors).flat().join(' ')
                }
                if (typeof mostraErro === 'function') mostraErro('', msg)
            })
        },
    },
}
</script>

<style scoped>
.padrao-doc-bloco {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 0.75rem 0.85rem;
    min-height: 4.5rem;
}

.padrao-chip-lista {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
    margin-top: 0.35rem;
}

.padrao-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    max-width: 100%;
    padding: 0.28rem 0.45rem 0.28rem 0.55rem;
    background: #fff;
    border: 1px solid #d7dde3;
    border-radius: 999px;
    font-size: 0.75rem;
    line-height: 1.2;
}

.padrao-chip-texto {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 18rem;
}

.padrao-chip-meta {
    color: #6c757d;
    font-size: 0.68rem;
    white-space: nowrap;
}

.padrao-chip-remove {
    border: 0;
    background: transparent;
    color: #6c757d;
    padding: 0 0.15rem;
    line-height: 1;
    cursor: pointer;
}

.padrao-chip-remove:hover {
    color: #c82333;
}

.padrao-empty-hint {
    color: #6c757d;
    font-size: 0.78rem;
    padding: 0.55rem 0.15rem;
}

.padrao-treino-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}

.padrao-treino-busca {
    position: relative;
    flex: 1 1 14rem;
    min-width: 12rem;
}

.padrao-treino-busca-icon {
    position: absolute;
    left: 0.55rem;
    top: 50%;
    transform: translateY(-50%);
    color: #98a2b3;
    font-size: 0.75rem;
    pointer-events: none;
}

.padrao-treino-busca .form-control {
    padding-left: 1.7rem;
    padding-right: 1.7rem;
}

.padrao-treino-busca-limpar {
    position: absolute;
    right: 0.35rem;
    top: 50%;
    transform: translateY(-50%);
    border: 0;
    background: transparent;
    color: #6c757d;
    padding: 0.15rem 0.35rem;
    cursor: pointer;
}

.padrao-treino-acoes {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
}

.padrao-treino-lista {
    max-height: 240px;
    overflow: auto;
    border: 1px solid #e3e7ec;
    border-radius: 8px;
    background: #fff;
}

.padrao-treino-item {
    display: flex;
    align-items: flex-start;
    gap: 0.55rem;
    margin: 0;
    padding: 0.45rem 0.7rem;
    border-bottom: 1px solid #f0f2f4;
    cursor: pointer;
    font-size: 0.8rem;
    font-weight: 400;
}

.padrao-treino-item:last-child {
    border-bottom: 0;
}

.padrao-treino-item:hover {
    background: #f8fafc;
}

.padrao-treino-check {
    margin-top: 0.15rem;
    flex: 0 0 auto;
}

.padrao-treino-label {
    flex: 1 1 auto;
    line-height: 1.3;
}
</style>

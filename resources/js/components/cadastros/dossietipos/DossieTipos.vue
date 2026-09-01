<template>
    <div id="componenteDossieTipos">
        <modal
            id="janelaCadastrar"
            :titulo="titulo_janela"
            :fechar="!preload && !salvando"
            :mostrar-botao-fechar-no-rodape="false"
            :size="90"
            ref="modal_janelaCadastrar"
        >
            <template #conteudo>
                <preload class="text-center" v-if="preload"></preload>
                <fieldset class="mt-0" v-if="!preload">
                    <legend>Informações</legend>
                    <p class="mybp-campo-obrigatorio-legenda mb-3">
                        Campos com <span class="text-danger">*</span> são obrigatórios.
                    </p>
                    <div class="alert alert-info" v-if="editandoRegistroPadrao">
                        Este documento pertence ao catálogo padrão. Ao salvar, o ajuste vale somente para a sua empresa.
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="mybp-label" for="dossie-tipo-form-label">
                                    Nome <span class="text-danger">*</span>
                                </label>
                                <input
                                    id="dossie-tipo-form-label"
                                    v-model="form.label"
                                    class="form-control form-control-sm"
                                    type="text"
                                    placeholder="Informe o nome exibido no dossiê"
                                    onblur="valida_campo_vazio(this, 1)"
                                />
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label class="mybp-label" for="dossie-tipo-form-ordem">Ordem</label>
                                <input
                                    id="dossie-tipo-form-ordem"
                                    v-model="form.ordem"
                                    class="form-control form-control-sm"
                                    type="number"
                                    min="0"
                                    placeholder="Ex: 1"
                                />
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group mb-2">
                                <label class="mybp-label">Modelo do documento (PDF)</label>
                                <p class="text-muted small mb-2">
                                    Arquivo usado no botão <strong>Baixar Modelo</strong> no dossiê do colaborador.
                                    Tipos padrão do sistema já têm modelo interno; envie um PDF para substituir só na sua empresa.
                                </p>
                                <div class="alert alert-info py-2" v-if="form.tem_modelo_sistema && !(form.modelo && form.modelo.length)">
                                    Este tipo usa o modelo padrão do sistema. Envie um PDF para personalizar.
                                </div>
                                <upload
                                    label="Selecionar PDF do modelo"
                                    :model="form.modelo"
                                    :model-delete="form.modeloDel"
                                    :url="urlModeloUpload"
                                    :quantidade="1"
                                    @onprogresso="anexoUploadAndamento = true"
                                    @onfinalizado="anexoUploadAndamento = false"
                                ></upload>
                            </div>
                        </div>
                        <div class="col-12 mb-2" v-if="assinaturaDigitalHabilitada">
                            <div class="custom-control custom-switch">
                                <input
                                    type="checkbox"
                                    v-model="form.permite_assinatura"
                                    class="custom-control-input"
                                    id="dossie-tipo-permite-assinatura"
                                />
                                <label class="custom-control-label" for="dossie-tipo-permite-assinatura">
                                    Enviar para assinatura digital
                                </label>
                            </div>
                            <small class="text-muted d-block">
                                Quando ativo, o dossiê do colaborador mostra o botão para enviar este documento por e-mail
                                para assinatura digital. É necessário ter um modelo (PDF cadastrado ou modelo padrão do sistema).
                            </small>
                        </div>
                        <div class="col-12 mt-2">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" v-model="form.ativo" class="custom-control-input" id="dossie-tipo-ativo" />
                                <label class="custom-control-label" for="dossie-tipo-ativo">{{ form.ativo ? 'Ativo' : 'Inativo' }}</label>
                            </div>
                        </div>
                    </div>
                </fieldset>
            </template>
            <template #rodape>
                <button type="button" class="btn btn-sm mr-1 btn-secondary" :disabled="preload || salvando" @click="fecharModal">
                    <i class="fa fa-times"></i> Cancelar
                </button>
                <button
                    type="button"
                    class="btn btn-sm mr-1 btn-primary"
                    v-show="!cadastrado"
                    :disabled="preload || salvando || anexoUploadAndamento"
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
                    v-show="cadastrado"
                    :disabled="preload || salvando || anexoUploadAndamento"
                    @click="alterarForm()"
                >
                    <span v-if="salvando">
                        <span class="spinner-border spinner-border-sm mr-1" role="status"></span>
                        Salvando...
                    </span>
                    <span v-else><i class="fa fa-save"></i> Salvar</span>
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
                <div class="col-12 col-lg-4">
                    <div class="form-group mb-2 mb-lg-0">
                        <label class="mybp-label" for="dossie-tipo-filtro-busca">Buscar</label>
                        <input
                            id="dossie-tipo-filtro-busca"
                            type="text"
                            placeholder="Buscar por nome, tipo, chave ou ID"
                            autocomplete="off"
                            class="form-control form-control-sm"
                            :disabled="controle.carregando"
                            v-model="controle.dados.campoBusca"
                        />
                    </div>
                </div>
                <div class="col-12 col-lg-4">
                    <div class="form-group mb-2 mb-lg-0">
                        <label class="mybp-label" for="dossie-tipo-filtro-status">Status</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroStatus"
                                instance-id="filtro-status"
                                v-model="controle.dados.campoStatus"
                                :options="filtroStatusOpcoes"
                                :disabled="controle.carregando"
                                input-id="dossie-tipo-filtro-status"
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
                        <label class="mybp-label" for="dossie-tipo-filtro-escopo">Escopo</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroEscopo"
                                instance-id="filtro-escopo"
                                v-model="controle.dados.campoEscopo"
                                :options="filtroEscopoOpcoes"
                                :disabled="controle.carregando"
                                input-id="dossie-tipo-filtro-escopo"
                                placeholder-blur="Todos os escopos"
                                empty-message="Nenhuma opção encontrada."
                                :max-results="10"
                                @opening="fecharOutrosComboboxes('filtro-escopo')"
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
                            </div>
                        </div>
                        <div class="mybp-card-right">
                            <bt-ativo
                                :rota="`cadastro/dossietipos/${item.id}/ativa-desativa`"
                                :model="item"
                                @atualizou="atualizar()"
                            ></bt-ativo>
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
                                    <a class="dropdown-item" href="javascript://" title="Editar" @click.prevent="abrirModalAlterar(item.id)">
                                        <i class="fa fa-edit mr-1"></i> Editar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mybp-card-details-row">
                        <div class="mybp-detail-item">
                            <i class="fas fa-fingerprint text-muted"></i>
                            <span class="mybp-detail-label">Tipo</span>
                            <span class="mybp-detail-value">{{ valorOuNaoInformado(item.tipo) }}</span>
                        </div>
                        <div class="mybp-detail-item">
                            <i class="fas fa-key text-muted"></i>
                            <span class="mybp-detail-label">Chave</span>
                            <span class="mybp-detail-value">{{ valorOuNaoInformado(item.chave) }}</span>
                        </div>
                        <div class="mybp-detail-item">
                            <i class="fas fa-layer-group text-muted"></i>
                            <span class="mybp-detail-label">Escopo</span>
                            <span class="mybp-detail-value">{{ valorOuNaoInformado(item.escopo_label) }}</span>
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
                                <small class="mybp-card-destaque-etapa">Modelo</small>
                                <strong class="mybp-card-destaque-valor">{{ item.modelo_origem_label || 'Não informado' }}</strong>
                            </div>
                        </div>
                        <div class="mybp-card-destaque mybp-card-destaque--info" v-if="assinaturaDigitalHabilitada">
                            <i class="fas fa-signature text-info"></i>
                            <div class="mybp-card-destaque-info">
                                <small class="mybp-card-destaque-etapa">Assinatura digital</small>
                                <strong class="mybp-card-destaque-valor">{{ item.permite_assinatura ? 'Sim' : 'Não' }}</strong>
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
import ComboboxAutoComplete from '../../ComboboxAutoComplete'
import FiltroListagem from '../../ui/FiltroListagem'
import Upload from '../../Upload'
import {
    lerFiltrosDaUrl,
    lerPaginacaoDaUrl,
    sincronizarFiltrosNaUrl,
    criarWatchQueryParams,
    montarExtrasPaginacao,
    aplicarPaginaInicialListagem,
    buscarListagem,
    temFiltrosPreenchidos,
    limparFiltrosListagem
} from '../../../utils/listagemQueryParams'

const CAMPOS_FILTRO_URL = ['campoBusca', 'campoStatus', 'campoEscopo']
const PAGES_DEFAULT = 20

export default {
    components: {
        modal,
        controlePaginacao,
        ComboboxAutoComplete,
        FiltroListagem,
        Upload
    },
    props: {
        qntPag: {
            type: Number,
            required: false,
            default: 20
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
        document.addEventListener('click', this.onClickOutside)
        this.$nextTick(() => {
            aplicarPaginaInicialListagem(this, paginaInicial)
            buscarListagem(this, { resetPagina: false })
        })
    },
    beforeUnmount() {
        document.removeEventListener('click', this.onClickOutside)
    },
    watch: {
        'controle.dados': criarWatchQueryParams(CAMPOS_FILTRO_URL, { pagesDefault: PAGES_DEFAULT })
    },
    computed: {
        temFiltrosAtivos() {
            return temFiltrosPreenchidos(this.controle.dados, CAMPOS_FILTRO_URL)
        },
        filtroStatusOpcoes() {
            return [
                { value: '', label: 'Todos os status' },
                { value: 'true', label: 'Apenas ativos' },
                { value: 'false', label: 'Apenas inativos' }
            ]
        },
        filtroEscopoOpcoes() {
            return [
                { value: '', label: 'Todos os escopos' },
                { value: 'global', label: 'Padrão do sistema' },
                { value: 'empresa', label: 'Da empresa' }
            ]
        },
        editandoRegistroPadrao() {
            return this.cadastrado && this.form.empresa_id == null
        }
    },
    data() {
        return {
            titulo_janela: 'Tipo de Dossiê',
            dropdownAbertoKey: null,
            anexoUploadAndamento: false,
            preload: false,
            salvando: false,
            editando: false,
            cadastrado: false,
            assinaturaDigitalHabilitada: typeof window !== 'undefined' ? !!window.MYBP_ASSINATURA_DIGITAL_HABILITADA : false,
            form: {
                label: '',
                tipo: '',
                chave: '',
                tipo_modelo: '',
                tipo_documento: '',
                tem_modelo: false,
                tem_modelo_sistema: false,
                permite_assinatura: false,
                ordem: 1,
                ativo: true,
                empresa_id: null,
                modelo: [],
                modeloDel: []
            },
            formDefault: null,
            lista: [],
            urlPaginacao: `${URL_ADMIN}/cadastro/dossietipos/atualizar`,
            urlModeloUpload: `${URL_ADMIN}/cadastro/dossietipos/uploadAnexos`,
            controle: {
                carregando: false,
                dados: {
                    campoBusca: '',
                    campoStatus: '',
                    campoEscopo: '',
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
        limparFiltros() {
            limparFiltrosListagem(this.controle.dados, CAMPOS_FILTRO_URL)
            this.fecharOutrosComboboxes(null)
            this.atualizar()
        },
        fecharOutrosComboboxes(manter) {
            const combos = [
                ['filtro-status', 'comboFiltroStatus'],
                ['filtro-escopo', 'comboFiltroEscopo']
            ]
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
        toggleDropdown(id) {
            if (!id) return
            const key = `dossie:${id}`
            this.dropdownAbertoKey = this.dropdownAbertoKey === key ? null : key
        },
        isDropdownOpen(id) {
            return this.dropdownAbertoKey === `dossie:${id}`
        },
        fecharDropdown() {
            this.dropdownAbertoKey = null
        },
        valorOuNaoInformado(valor) {
            if (valor === 0) return '0'
            return valor || 'Não informado'
        },
        abrirModalNovo() {
            this.titulo_janela = 'Cadastrar tipo de dossiê'
            this.preload = false
            this.salvando = false
            this.cadastrado = false
            this.editando = false
            this.form = _.cloneDeep(this.formDefault)
            if (typeof formReset === 'function') formReset()
            this.$refs.modal_janelaCadastrar?.abrirModal()
        },
        fecharModal() {
            this.$refs.modal_janelaCadastrar?.fecharModal()
        },
        validarFormulario() {
            $('#janelaCadastrar :input:visible').trigger('blur')
            if ($('#janelaCadastrar :input:visible.is-invalid').length) {
                mostraErro('', 'Verificar os erros')
                return false
            }
            return true
        },
        cadastrar() {
            if (!this.validarFormulario()) return
            this.salvando = true
            axios
                .post(`${URL_ADMIN}/cadastro/dossietipos`, this.form)
                .then(() => {
                    this.fecharModal()
                    mostraSucesso('', 'Tipo de dossiê cadastrado com sucesso')
                    this.$refs.componente?.buscar?.()
                })
                .finally(() => {
                    this.salvando = false
                })
        },
        abrirModalAlterar(id) {
            this.fecharDropdown()
            this.cadastrado = true
            this.editando = true
            this.titulo_janela = 'Alterar tipo de dossiê'
            this.form = _.cloneDeep(this.formDefault)
            if (typeof formReset === 'function') formReset()
            this.preload = true
            this.$refs.modal_janelaCadastrar?.abrirModal()
            axios
                .get(`${URL_ADMIN}/cadastro/dossietipos/${id}/editar`)
                .then((response) => {
                    Object.assign(this.form, response.data)
                    if (!Array.isArray(this.form.modelo)) {
                        this.form.modelo = []
                    }
                    if (!Array.isArray(this.form.modeloDel)) {
                        this.form.modeloDel = []
                    }
                    if (typeof setupCampo === 'function') setupCampo()
                })
                .finally(() => {
                    this.preload = false
                })
        },
        alterarForm() {
            if (!this.validarFormulario()) return
            this.salvando = true
            axios
                .put(`${URL_ADMIN}/cadastro/dossietipos/${this.form.id}`, this.form)
                .then(() => {
                    this.fecharModal()
                    mostraSucesso('', 'Tipo de dossiê alterado com sucesso')
                    this.$refs.componente?.buscar?.()
                })
                .finally(() => {
                    this.salvando = false
                })
        },
        carregou(dados) {
            this.lista = dados.items || []
            if (dados.assinatura_digital_habilitada !== undefined) {
                this.assinaturaDigitalHabilitada = !!dados.assinatura_digital_habilitada
            }
            this.controle.carregando = false
            this.$nextTick(() => this.syncUrlFiltros())
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

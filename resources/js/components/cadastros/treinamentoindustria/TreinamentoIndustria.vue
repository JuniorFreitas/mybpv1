<template>
    <div id="componenteTreinamentoIndustria">
        <modal :modal-pai="modal" :titulo="titulo_janela_assinatura" :size="90" id="janelaAssinatura" ref="modal_janelaAssinatura">
            <template #conteudo>
                <assinatura-carteira modal="janelaAssinatura"></assinatura-carteira>
            </template>
        </modal>

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
                    <div class="row">
                        <div class="col-12 col-md-12">
                            <div class="form-group">
                                <label class="mybp-label" for="ti-form-nome">Nome <span class="text-danger">*</span></label>
                                <input
                                    id="ti-form-nome"
                                    v-model="form.label"
                                    class="form-control form-control-sm"
                                    type="text"
                                    placeholder="Informe o nome"
                                    onblur="valida_campo_vazio(this, 1)"
                                />
                            </div>
                        </div>
                        <div class="col-12 mb-2">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" v-model="form.exibir_na_carteira" class="custom-control-input" id="exibir_na_carteira" />
                                <label class="custom-control-label" for="exibir_na_carteira">Exibir Carteira Treinamento</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-12" v-if="form.exibir_na_carteira">
                            <div class="form-group">
                                <label class="mybp-label" for="ti-form-nome-reduzido">
                                    Nome Reduzido <span class="text-danger">*</span>
                                </label>
                                <input
                                    id="ti-form-nome-reduzido"
                                    v-model="form.label_reduzida"
                                    class="form-control form-control-sm"
                                    type="text"
                                    placeholder="Informe o nome reduzido"
                                    onblur="valida_campo_vazio(this, 1)"
                                />
                            </div>
                        </div>
                        <div class="col-12 col-md-12">
                            <div class="form-group">
                                <label class="mybp-label" for="ti-form-descricao">A quem se destina <span class="text-danger">*</span></label>
                                <input
                                    id="ti-form-descricao"
                                    v-model="form.descricao"
                                    class="form-control form-control-sm"
                                    type="text"
                                    placeholder="Informe para quem se destina"
                                    onblur="valida_campo_vazio(this, 1)"
                                />
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label class="mybp-label" for="ti-form-prazo-fixo">Prazo fixo (dias para vencimento)</label>
                                <input
                                    id="ti-form-prazo-fixo"
                                    v-model="form.prazo_fixo"
                                    class="form-control form-control-sm"
                                    type="number"
                                    placeholder="Ex: 365"
                                    min="1"
                                />
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label class="mybp-label" for="ti-form-ordem">Ordem</label>
                                <input id="ti-form-ordem" v-model="form.ordem" class="form-control form-control-sm" type="number" />
                            </div>
                        </div>
                        <div class="col-12 col-md-12 mybp-combobox-wrap">
                            <div class="form-group">
                                <label class="mybp-label" for="ti-form-segmento-input">Segmento / Padrão de treinamento</label>
                                <combobox-auto-complete
                                    ref="comboFormSegmento"
                                    instance-id="form-segmento"
                                    v-model="form.segmento_treinamento_id"
                                    :options="segmentoComboboxOpcoesForm"
                                    :disabled="preload || !segmentos.length"
                                    input-id="ti-form-segmento-input"
                                    placeholder-blur="Selecione o segmento"
                                    empty-message="Nenhum segmento encontrado."
                                    :max-results="50"
                                    @opening="fecharOutrosComboboxes('form-segmento')"
                                />
                            </div>
                        </div>
                        <div class="col-12 mb-2">
                            <div class="custom-control custom-switch">
                                <input
                                    type="checkbox"
                                    v-model="form.vinculo_todos_cargos"
                                    class="custom-control-input"
                                    id="vinculo_todos_cargos"
                                    @change="onToggleVinculoTodosCargos"
                                />
                                <label class="custom-control-label" for="vinculo_todos_cargos">Vincular a todos os cargos</label>
                            </div>
                            <small v-if="form.vinculo_todos_cargos" class="text-muted d-block">
                                Quando ativo, o treinamento vale para qualquer cargo, independentemente da lista abaixo.
                            </small>
                        </div>
                        <div class="col-12 col-md-12" v-show="!form.vinculo_todos_cargos">
                            <fieldset>
                                <legend>Cargos vinculados</legend>
                                <p class="text-muted small mb-2">
                                    O mesmo vínculo é exibido no cadastro de Cargo (somente leitura).
                                </p>
                                <div class="form-group">
                                    <label class="mybp-label" :for="`ti-form-cargo_${hash}`">Adicionar cargo</label>
                                    <autocomplete
                                        :caminho="`autocomplete/cargos_ativos`"
                                        :formsm="true"
                                        v-model="form.autocomplete_label_cargo"
                                        placeholder="Selecione um cargo"
                                        :id="`cargo_${hash}`"
                                        @onselect="selecionaCargo"
                                    ></autocomplete>
                                </div>

                                <div v-if="form.cargos.length > 0" class="mybp-cargos-vinculados-lista">
                                    <div
                                        class="mybp-cargo-vinculado-item"
                                        v-for="(cargo, index) in form.cargos"
                                        :key="cargo.id"
                                    >
                                        <span class="mybp-cargo-vinculado-nome">{{ cargo.nome }}</span>
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            title="Remover cargo"
                                            @click.prevent="removerCargo(index)"
                                        >
                                            <i class="fa fa-times" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>
                                <p v-else class="text-muted small mb-0">Nenhum cargo vinculado.</p>
                            </fieldset>
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
                <button type="button" class="btn btn-sm mr-1 btn-secondary" :disabled="preload" @click="fecharModal">
                    <i class="fa fa-times"></i> Cancelar
                </button>
                <button
                    type="button"
                    class="btn btn-sm mr-1 btn-primary"
                    v-show="!cadastrado"
                    :disabled="preload"
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
                    :disabled="preload"
                    @click="alterarformTreinamentoIndustria()"
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
                        <label class="mybp-label" for="treinamento-industria-filtro-busca">Buscar</label>
                        <input
                            id="treinamento-industria-filtro-busca"
                            type="text"
                            placeholder="Buscar por nome, descrição ou ID"
                            autocomplete="off"
                            class="form-control form-control-sm"
                            :disabled="controle.carregando"
                            v-model="controle.dados.campoBusca"
                        />
                    </div>
                </div>

                <div class="col-12 col-lg-3">
                    <div class="form-group mb-2 mb-lg-0">
                        <label class="mybp-label" for="treinamento-industria-filtro-status">Status</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroStatus"
                                instance-id="filtro-status"
                                v-model="controle.dados.campoStatus"
                                :options="filtroStatusOpcoes"
                                :disabled="controle.carregando"
                                input-id="treinamento-industria-filtro-status"
                                placeholder-blur="Todos os status"
                                empty-message="Nenhuma opção encontrada."
                                :max-results="10"
                                @opening="fecharOutrosComboboxes('filtro-status')"
                                @select="onSelectFiltro"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-5">
                    <div class="form-group mb-2 mb-lg-0">
                        <label class="mybp-label" for="treinamento-industria-filtro-segmento">Segmento</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroSegmento"
                                instance-id="filtro-segmento"
                                v-model="controle.dados.segmento_treinamento_id"
                                :options="segmentoComboboxOpcoesFiltro"
                                :disabled="controle.carregando || !segmentos.length"
                                input-id="treinamento-industria-filtro-segmento"
                                placeholder-blur="Todos os segmentos"
                                empty-message="Nenhum segmento encontrado."
                                :max-results="50"
                                @opening="fecharOutrosComboboxes('filtro-segmento')"
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
                    <i class="fa fa-plus"></i> Treinamento Indústria
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" @click="$refs.modal_janelaAssinatura && $refs.modal_janelaAssinatura.abrirModal()">
                    <i class="fa fa-plus"></i> Assinatura Carteira
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
                                :rota="`cadastro/treinamentoindustria/${item.id}/ativa-desativa`"
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
                            <i class="fas fa-layer-group text-muted"></i>
                            <span class="mybp-detail-label">Padrão</span>
                            <span class="mybp-detail-value">{{ nomeSegmento(item) }}</span>
                        </div>
                        <div class="mybp-detail-item">
                            <i class="fas fa-briefcase text-muted"></i>
                            <span class="mybp-detail-label">Cargos vinculados</span>
                            <span class="mybp-detail-value">{{ resumoCargos(item) }}</span>
                        </div>
                        <div class="mybp-detail-item">
                            <i class="fas fa-calendar-alt text-muted"></i>
                            <span class="mybp-detail-label">Prazo fixo</span>
                            <span class="mybp-detail-value">{{ prazoFixoTreinamento(item) }}</span>
                        </div>
                        <div class="mybp-detail-item">
                            <i class="fas fa-sort-numeric-up text-muted"></i>
                            <span class="mybp-detail-label">Ordem</span>
                            <span class="mybp-detail-value">{{ valorOuNaoInformado(item.ordem) }}</span>
                        </div>
                        <div class="mybp-detail-item mybp-detail-item--full">
                            <i class="fas fa-info-circle text-muted"></i>
                            <span class="mybp-detail-label">A quem se destina</span>
                            <span class="mybp-detail-value">{{ valorOuNaoInformado(item.descricao) }}</span>
                        </div>
                    </div>
                    <div class="mybp-card-secoes-row mt-2" v-if="item.exibir_na_carteira || item.label_reduzida">
                        <div class="mybp-card-destaque mybp-card-destaque--info">
                            <i class="fas fa-id-badge text-info"></i>
                            <div class="mybp-card-destaque-info">
                                <small class="mybp-card-destaque-etapa">Carteira de Treinamento</small>
                                <strong class="mybp-card-destaque-valor">{{ labelCarteira(item) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <controle-paginacao
                class="d-flex justify-content-center"
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
import AssinaturaCarteira from './AssinaturaCarteira.vue'
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

const CAMPOS_FILTRO_URL = ['campoBusca', 'campoStatus', 'segmento_treinamento_id']

export default {
    components: {
        modal,
        controlePaginacao,
        AssinaturaCarteira,
        ComboboxAutoComplete,
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
        this.carregarSegmentos()
        this.formDefault = _.cloneDeep(this.form)
        lerFiltrosDaUrl(this.controle.dados, CAMPOS_FILTRO_URL)
        const paginaInicial = lerPaginacaoDaUrl(this.controle.dados, { pagesDefault: this.qntPag })
        document.addEventListener('click', this.onClickOutside)
        this.$nextTick(() => {
            aplicarPaginaInicialListagem(this, paginaInicial)
            this.buscarListagem(false)
        })
    },
    beforeUnmount() {
        document.removeEventListener('click', this.onClickOutside)
        if (this.syncUrlTimer) {
            clearTimeout(this.syncUrlTimer)
        }
    },
    computed: {
        filtroStatusOpcoes() {
            return [
                { value: '', label: 'Todos os status' },
                { value: 'true', label: 'Apenas ativos' },
                { value: 'false', label: 'Apenas inativos' }
            ]
        },
        segmentoComboboxOpcoesFiltro() {
            const segmentos = (this.segmentos || []).map((segmento) => ({
                value: segmento.id,
                label: segmento.nome,
                raw: segmento
            }))

            return [{ value: '', label: 'Todos os segmentos' }, ...segmentos]
        },
        segmentoComboboxOpcoesForm() {
            return (this.segmentos || []).map((segmento) => ({
                value: segmento.id,
                label: segmento.nome,
                raw: segmento
            }))
        },
        temFiltrosAtivos() {
            return temFiltrosPreenchidos(this.controle.dados, CAMPOS_FILTRO_URL)
        }
    },
    watch: {
        'controle.dados': {
            handler() {
                if (this.syncUrlTimer) {
                    clearTimeout(this.syncUrlTimer)
                }

                this.syncUrlTimer = setTimeout(() => this.syncUrlFiltros(), 400)
            },
            deep: true
        }
    },
    data() {
        return {
            hash: String(Math.random()).substr(2),
            titulo_janela: 'Treinamento Indústria',
            titulo_janela_assinatura: 'Assinatura Carteira',

            preload: false,
            salvando: false,
            editando: false,
            cadastrado: false,

            form: {
                label: '',
                label_reduzida: '',
                exibir_na_carteira: false,
                descricao: '',
                prazo_fixo: 365,
                ordem: 1,
                ativo: true,
                segmento_treinamento_id: null,
                vinculo_todos_cargos: false,
                cargos: [],
                cargo_ids: [],
                cargo_id: '',
                autocomplete_label_cargo: ''
            },

            formDefault: null,
            segmentos: [],

            lista: [],
            dropdownAbertoKey: null,
            syncUrlTimer: null,

            urlPaginacao: `${URL_ADMIN}/cadastro/treinamentoindustria/atualizar`,
            controle: {
                carregando: false,
                dados: {
                    campoBusca: '',
                    campoStatus: '',
                    segmento_treinamento_id: '',
                    pages: this.qntPag
                }
            }
        }
    },
    methods: {
        onSubmitFiltro() {
            this.atualizar()
        },
        fecharOutrosComboboxes(manter) {
            if (manter !== 'filtro-status' && this.$refs.comboFiltroStatus?.close) {
                this.$refs.comboFiltroStatus.close()
            }
            if (manter !== 'filtro-segmento' && this.$refs.comboFiltroSegmento?.close) {
                this.$refs.comboFiltroSegmento.close()
            }
            if (manter !== 'form-segmento' && this.$refs.comboFormSegmento?.close) {
                this.$refs.comboFormSegmento.close()
            }
        },
        onSelectFiltro() {
            this.atualizar()
        },
        limparFiltros() {
            limparFiltrosListagem(this.controle.dados, CAMPOS_FILTRO_URL)
            this.fecharOutrosComboboxes(null)
            this.atualizar()
        },
        syncUrlFiltros() {
            sincronizarFiltrosNaUrl(
                this.controle.dados,
                CAMPOS_FILTRO_URL,
                montarExtrasPaginacao(this, { pagesDefault: this.qntPag })
            )
        },
        buscarListagem(resetPagina = true) {
            if (resetPagina && this.$refs?.componente) {
                this.$refs.componente.atual = 1
            }

            this.$refs?.componente?.buscar?.()
        },
        onClickOutside(event) {
            if (event?.target?.closest?.('.dropdown')) return
            if (event?.target?.closest?.('.mybp-combobox-wrap')) return
            this.dropdownAbertoKey = null
            this.fecharOutrosComboboxes(null)
        },
        toggleDropdown(treinamentoIndustriaId) {
            if (!treinamentoIndustriaId) return
            const key = `treinamento-industria:${treinamentoIndustriaId}`
            this.dropdownAbertoKey = this.dropdownAbertoKey === key ? null : key
        },
        isDropdownOpen(treinamentoIndustriaId) {
            return this.dropdownAbertoKey === `treinamento-industria:${treinamentoIndustriaId}`
        },
        fecharDropdown() {
            this.dropdownAbertoKey = null
        },
        abrirModalNovo() {
            this.formNovo()
            this.$refs.modal_janelaCadastrar?.abrirModal?.()
        },
        async abrirModalAlterar(treinamentoIndustriaId) {
            this.fecharDropdown()
            try {
                await this.alterarTreinamentoIndustria(treinamentoIndustriaId)
                this.$refs.modal_janelaCadastrar?.abrirModal?.()
            } catch (error) {
                // erro já tratado em alterarTreinamentoIndustria
            }
        },
        fecharModal() {
            this.$refs.modal_janelaCadastrar?.fecharModal?.()
        },
        nomeSegmento(item) {
            return item?.segmento_treinamento?.nome || item?.SegmentoTreinamento?.nome || 'Não informado'
        },
        prazoFixoTreinamento(item) {
            return item?.prazo_fixo ? `${item.prazo_fixo} dias` : 'Não informado'
        },
        resumoCargos(item) {
            if (item?.vinculo_todos_cargos) {
                return 'Todos os cargos'
            }

            const quantidade = item?.vagas_count ?? item?.vagas?.length ?? item?.Vagas?.length
            if (typeof quantidade === 'number') {
                return quantidade === 1 ? '1 cargo' : `${quantidade} cargos`
            }

            return 'Nenhum cargo'
        },
        labelCarteira(item) {
            if (!item?.exibir_na_carteira) {
                return 'Não exibido'
            }

            return item.label_reduzida || 'Exibido sem nome reduzido'
        },
        valorOuNaoInformado(valor) {
            return valor === null || valor === undefined || valor === '' ? 'Não informado' : valor
        },
        removerCargo(index) {
            this.form.cargos.splice(index, 1)
            this.form.cargo_ids = this.form.cargos.map((item) => item.id)
        },
        selecionaCargo(obj) {
            if (this.form.vinculo_todos_cargos) {
                return
            }
            const cargo = {
                id: obj.id,
                nome: obj.nome || obj.label
            }

            const atual = this.form.cargos.findIndex((item) => item.id === cargo.id)
            if (atual >= 0) {
                mostraErro('', `O cargo ${cargo.nome} já está na lista.`)
                this.form.autocomplete_label_cargo = ''
                return
            }

            this.form.cargos.push(cargo)
            this.form.cargo_ids = this.form.cargos.map((item) => item.id)
            this.form.autocomplete_label_cargo = ''
        },
        onToggleVinculoTodosCargos() {
            if (this.form.vinculo_todos_cargos) {
                this.form.cargos = []
                this.form.cargo_ids = []
                this.form.autocomplete_label_cargo = ''
            }
        },
        carregarSegmentos() {
            axios
                .get(`${URL_ADMIN}/cadastro/segmentostreinamento/lista`)
                .then((res) => {
                    this.segmentos = res.data || []
                })
                .catch(() => {
                    this.segmentos = []
                })
        },
        formNovo() {
            this.form = _.cloneDeep(this.formDefault)
            this.titulo_janela = 'Treinamento Indústria'
            this.editando = false
            this.cadastrado = false
            this.salvando = false
            this.preload = false
            formReset()
            setupCampo()
        },

        validarFormularioModal() {
            $('#janelaCadastrar :input:visible').trigger('blur')
            if ($('#janelaCadastrar :input:visible.is-invalid').length) {
                mostraErro('', 'Verificar os erros')
                return false
            }

            return true
        },

        cadastrar() {
            if (!this.validarFormularioModal()) {
                return false
            }

            this.salvando = true
            const payload = {
                ...this.form,
                prazo_parada: null,
                cargo_ids: this.form.vinculo_todos_cargos ? [] : this.form.cargos.map((item) => item.id)
            }
            axios
                .post(`${URL_ADMIN}/cadastro/treinamentoindustria`, payload)
                .then((res) => {
                    if (res.status === 201) {
                        this.fecharModal()
                        mostraSucesso('', 'Treinamento Indústria cadastrado com sucesso')
                        this.atualizar()
                    }
                })
                .catch(() => {
                    // erro tratado globalmente
                })
                .finally(() => {
                    this.salvando = false
                })
        },
        async alterarTreinamentoIndustria(treinamentoindustria) {
            this.cadastrado = true
            this.editando = true
            this.titulo_janela = 'Alterando Treinamento Indústria'
            this.salvando = false
            this.preload = true
            formReset()
            this.form = _.cloneDeep(this.formDefault)

            try {
                const response = await axios.get(`${URL_ADMIN}/cadastro/treinamentoindustria/${treinamentoindustria}/editar`)
                Object.assign(this.form, response.data)

                const vagas = response.data.vagas || response.data.Vagas || []
                if (this.form.vinculo_todos_cargos) {
                    this.form.cargos = []
                    this.form.cargo_ids = []
                } else {
                    this.form.cargos = vagas.map((item) => ({
                        id: item.id,
                        nome: item.nome
                    }))
                    this.form.cargo_ids = this.form.cargos.map((item) => item.id)
                }

                this.form.autocomplete_label_cargo = ''
                setupCampo()
            } catch (error) {
                mostraErro('', 'Não foi possível carregar o treinamento.')
                throw error
            } finally {
                this.preload = false
            }
        },
        alterarformTreinamentoIndustria() {
            formReset()
            if (!this.validarFormularioModal()) {
                return false
            }

            this.salvando = true
            const payload = {
                ...this.form,
                prazo_parada: null,
                cargo_ids: this.form.vinculo_todos_cargos ? [] : this.form.cargos.map((item) => item.id)
            }
            axios
                .put(`${URL_ADMIN}/cadastro/treinamentoindustria/${this.form.id}`, payload)
                .then(() => {
                    this.fecharModal()
                    mostraSucesso('', 'Treinamento Indústria atualizado com sucesso')
                    this.atualizar()
                })
                .catch(() => {
                    // erro tratado globalmente
                })
                .finally(() => {
                    this.salvando = false
                })
        },
        carregou(dados) {
            this.lista = dados.items || []
            this.controle.carregando = false
            this.$nextTick(() => this.syncUrlFiltros())
        },
        carregando() {
            this.controle.carregando = true
        },
        atualizar() {
            this.buscarListagem(true)
        }
    }
}
</script>

<style scoped>
.mybp-cargos-vinculados-lista {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.mybp-cargo-vinculado-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    padding: 0.5rem 0.75rem;
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
    background: #fff;
}

.mybp-cargo-vinculado-nome {
    flex: 1;
    min-width: 0;
    word-break: break-word;
}
</style>

<template>
    <div>
        <modal id="modalEtiqueta" ref="modal_modalEtiqueta" :titulo="tituloModal" :fechar="true" size="g">
            <template #conteudo>
                <div class="mybp-modal-form">
                    <div class="form-group" v-if="isAdminGlobal && !editando">
                        <label class="mybp-label" for="etq_escopo">Escopo <span class="text-danger">*</span></label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFormEscopo"
                                instance-id="form-escopo"
                                v-model="formEscopo"
                                :options="formEscopoOpcoes"
                                input-id="etq_escopo"
                                placeholder-blur="Selecione o escopo"
                                empty-message="Nenhuma opção encontrada."
                                :max-results="10"
                                @opening="fecharOutrosComboboxes('form-escopo')"
                            />
                        </div>
                    </div>
                    <div class="form-group" v-if="isAdminGlobal && !editando && formEscopo === 'empresa'">
                        <label class="mybp-label" for="etq_empresa">Empresa <span class="text-danger">*</span></label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFormEmpresa"
                                instance-id="form-empresa"
                                v-model="formEmpresaId"
                                :options="formEmpresaOpcoes"
                                input-id="etq_empresa"
                                placeholder-blur="Selecione a empresa"
                                empty-message="Nenhuma empresa encontrada."
                                :max-results="20"
                                @opening="fecharOutrosComboboxes('form-empresa')"
                            />
                        </div>
                        <small class="text-muted">Template customizado fica disponível só para esta empresa no padrão de treinamento.</small>
                    </div>
                    <div class="alert alert-info py-2" v-if="isAdminGlobal && editando">
                        Escopo: <strong>{{ labelEscopoItem(form) }}</strong> (não alterável na edição)
                    </div>
                    <div class="form-group">
                        <label class="mybp-label" for="etq_nome">Nome <span class="text-danger">*</span></label>
                        <input id="etq_nome" v-model="form.nome" class="form-control form-control-sm" type="text" placeholder="Ex: ALUMAR">
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" v-model="form.ativo" class="custom-control-input" id="etq_ativo">
                            <label class="custom-control-label" for="etq_ativo">{{ form.ativo ? 'Ativo' : 'Inativo' }}</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="mybp-label" for="etq_ramal">Ramal de emergência</label>
                        <input id="etq_ramal" v-model="form.ramal_emergencia" class="form-control form-control-sm" type="text">
                    </div>
                    <div class="form-group">
                        <label class="mybp-label" for="etq_nao_use">Texto principal</label>
                        <input id="etq_nao_use" v-model="form.bloqueio_texto_nao_use" class="form-control form-control-sm" type="text">
                    </div>
                    <div class="form-group">
                        <label class="mybp-label" for="etq_demissao">Texto demissão</label>
                        <input id="etq_demissao" v-model="form.bloqueio_texto_demissao" class="form-control form-control-sm" type="text">
                    </div>
                    <div class="form-group">
                        <label class="mybp-label" for="etq_cuidado">Texto Cuidado</label>
                        <input id="etq_cuidado" v-model="form.bloqueio_texto_cuidado" class="form-control form-control-sm" type="text">
                    </div>
                    <div class="form-group">
                        <label class="mybp-label" for="etq_homens">Texto Homens trabalhando</label>
                        <input id="etq_homens" v-model="form.bloqueio_texto_homens_trabalhando" class="form-control form-control-sm" type="text">
                    </div>
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
                <div :class="isAdminGlobal ? 'col-12 col-lg-4' : 'col-12 col-lg-6'">
                    <div class="form-group mb-2 mb-lg-0">
                        <label class="mybp-label" for="etiqueta-filtro-busca">Buscar</label>
                        <input
                            id="etiqueta-filtro-busca"
                            type="text"
                            placeholder="Buscar por nome ou ID"
                            autocomplete="off"
                            class="form-control form-control-sm"
                            :disabled="controle.carregando"
                            v-model="controle.dados.campoBusca"
                        />
                    </div>
                </div>
                <div :class="isAdminGlobal ? 'col-12 col-lg-2' : 'col-12 col-lg-6'">
                    <div class="form-group mb-2 mb-lg-0">
                        <label class="mybp-label" for="etiqueta-filtro-status">Status</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroStatus"
                                instance-id="filtro-status"
                                v-model="controle.dados.campoStatus"
                                :options="filtroStatusOpcoes"
                                :disabled="controle.carregando"
                                input-id="etiqueta-filtro-status"
                                placeholder-blur="Todos os status"
                                empty-message="Nenhuma opção encontrada."
                                :max-results="10"
                                @opening="fecharOutrosComboboxes('filtro-status')"
                                @select="onSelectFiltro"
                            />
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-3" v-if="isAdminGlobal">
                    <div class="form-group mb-2 mb-lg-0">
                        <label class="mybp-label" for="etiqueta-filtro-escopo">Escopo</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroEscopo"
                                instance-id="filtro-escopo"
                                v-model="controle.dados.campoEscopo"
                                :options="filtroEscopoOpcoes"
                                :disabled="controle.carregando"
                                input-id="etiqueta-filtro-escopo"
                                placeholder-blur="Todos os escopos"
                                empty-message="Nenhuma opção encontrada."
                                :max-results="10"
                                @opening="fecharOutrosComboboxes('filtro-escopo')"
                                @select="onSelectFiltro"
                            />
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-3" v-if="isAdminGlobal && controle.dados.campoEscopo === 'empresa'">
                    <div class="form-group mb-2 mb-lg-0">
                        <label class="mybp-label" for="etiqueta-filtro-empresa">Empresa</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroEmpresa"
                                instance-id="filtro-empresa"
                                v-model="controle.dados.campoEmpresaId"
                                :options="filtroEmpresaOpcoes"
                                :disabled="controle.carregando"
                                input-id="etiqueta-filtro-empresa"
                                placeholder-blur="Todas as empresas"
                                empty-message="Nenhuma empresa encontrada."
                                :max-results="20"
                                @opening="fecharOutrosComboboxes('filtro-empresa')"
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
                            <i class="fas fa-globe text-muted"></i>
                            <span class="mybp-detail-label">Escopo</span>
                            <span class="mybp-detail-value">{{ labelEscopoItem(item) }}</span>
                        </div>
                        <div class="mybp-detail-item">
                            <i class="fas fa-phone text-muted"></i>
                            <span class="mybp-detail-label">Ramal</span>
                            <span class="mybp-detail-value">{{ item.ramal_emergencia || 'Não informado' }}</span>
                        </div>
                        <div class="mybp-detail-item mybp-detail-item--full">
                            <i class="fas fa-ban text-muted"></i>
                            <span class="mybp-detail-label">Texto principal</span>
                            <span class="mybp-detail-value">{{ item.bloqueio_texto_nao_use || 'Não informado' }}</span>
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
import templateTreinamentoEscopo from '../../../mixins/templateTreinamentoEscopo'
import {
    lerFiltrosDaUrl,
    sincronizarFiltrosNaUrl,
    temFiltrosPreenchidos,
    limparFiltrosListagem
} from '../../../utils/listagemQueryParams'

const CAMPOS_FILTRO_URL = ['campoBusca', 'campoStatus', 'campoEscopo', 'campoEmpresaId']

export default {
    components: { modal, ComboboxAutoComplete, FiltroListagem },
    mixins: [templateTreinamentoEscopo],
    props: {
        isAdminGlobal: { type: Boolean, default: false },
    },
    data() {
        return {
            lista: [],
            editando: false,
            form: this.formDefault(),
            dropdownAbertoKey: null,
            syncUrlTimer: null,
            controle: {
                carregando: false,
                dados: {
                    campoBusca: '',
                    campoStatus: '',
                    campoEscopo: '',
                    campoEmpresaId: '',
                },
            },
        }
    },
    computed: {
        tituloModal() {
            return this.editando ? 'Editar etiqueta' : 'Nova etiqueta'
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
        'controle.dados.campoEscopo'(val) {
            if (val !== 'empresa') this.controle.dados.campoEmpresaId = ''
        },
    },
    mounted() {
        lerFiltrosDaUrl(this.controle.dados, CAMPOS_FILTRO_URL)
        document.addEventListener('click', this.onClickOutside)
        this.carregarEmpresasEscopo().finally(() => {
            this.$nextTick(() => this.atualizar())
        })
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
                ativo: true,
                empresa_id: null,
                ramal_emergencia: '1199',
                bloqueio_texto_nao_use: 'NÃO USE, MOVA OU OPERE ENQUANTO ESTA ETIQUETA ESTIVER COLOCADA',
                bloqueio_texto_demissao: 'QUEM OPERAR O EQUIPAMENTO OU REMOVER A ETIQUETA ESTÁ SUJEITO A DEMISSÃO',
                bloqueio_texto_cuidado: 'CUIDADO!',
                bloqueio_texto_homens_trabalhando: 'HOMENS TRABALHANDO NÃO OPERE ESTE EQUIPAMENTO',
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
                ['filtro-escopo', 'comboFiltroEscopo'],
                ['filtro-empresa', 'comboFiltroEmpresa'],
                ['form-escopo', 'comboFormEscopo'],
                ['form-empresa', 'comboFormEmpresa'],
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
            const key = `etiqueta:${id}`
            this.dropdownAbertoKey = this.dropdownAbertoKey === key ? null : key
        },
        isDropdownOpen(id) {
            return this.dropdownAbertoKey === `etiqueta:${id}`
        },
        fecharDropdown() {
            this.dropdownAbertoKey = null
        },
        abrirModalNovo() {
            this.editando = false
            this.form = this.formDefault()
            this.resetFormEscopo()
            this.$refs.modal_modalEtiqueta?.abrirModal?.()
        },
        abrirModalAlterar(item) {
            this.fecharDropdown()
            this.editando = true
            this.form = { ...this.formDefault(), ...item }
            this.aplicarEscopoNoForm(item)
            this.$refs.modal_modalEtiqueta?.abrirModal?.()
        },
        atualizar() {
            this.controle.carregando = true
            const payload = {
                pages: 1,
                per_page: 100,
                campoBusca: this.controle.dados.campoBusca || '',
                campoStatus: this.controle.dados.campoStatus || '',
            }
            if (this.isAdminGlobal) {
                payload.campoEscopo = this.controle.dados.campoEscopo || ''
                payload.campoEmpresaId = this.controle.dados.campoEmpresaId || ''
            }
            axios.post(`${URL_ADMIN}/cadastro/etiquetasbloqueio/atualizar`, payload)
                .then((res) => {
                    this.lista = (res.data.dados && res.data.dados.items) ? res.data.dados.items : []
                })
                .finally(() => { this.controle.carregando = false })
        },
        salvar() {
            if (!this.form.nome) {
                if (typeof mostraErro === 'function') mostraErro('', 'Informe o nome')
                return
            }
            if (!this.editando && !this.validarEscopoStore()) return

            const payload = {
                nome: this.form.nome,
                ativo: Boolean(this.form.ativo),
                ramal_emergencia: this.form.ramal_emergencia,
                bloqueio_texto_nao_use: this.form.bloqueio_texto_nao_use,
                bloqueio_texto_demissao: this.form.bloqueio_texto_demissao,
                bloqueio_texto_cuidado: this.form.bloqueio_texto_cuidado,
                bloqueio_texto_homens_trabalhando: this.form.bloqueio_texto_homens_trabalhando,
            }
            if (!this.editando) {
                Object.assign(payload, this.payloadEscopoStore())
            }
            const req = this.editando
                ? axios.put(`${URL_ADMIN}/cadastro/etiquetasbloqueio/${this.form.id}`, payload)
                : axios.post(`${URL_ADMIN}/cadastro/etiquetasbloqueio`, payload)
            req.then(() => {
                this.$refs.modal_modalEtiqueta?.fecharModal?.()
                if (typeof mostraSucesso === 'function') mostraSucesso('', 'Etiqueta salva')
                this.atualizar()
            }).catch((err) => {
                const data = err.response && err.response.data
                let msg = data && (data.message || data.msg)
                if (data && data.errors) {
                    msg = Object.values(data.errors).flat().join(' ')
                }
                if (typeof mostraErro === 'function') mostraErro('', msg || 'Erro ao salvar')
            })
        },
    },
}
</script>

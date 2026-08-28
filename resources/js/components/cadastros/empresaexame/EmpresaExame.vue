<template>
    <div :id="hash">
        <modal :modal-pai="modal" :titulo="titulo_janela_pcmso" :size="90" id="janelaPcmso" ref="modal_janelaPcmso">
            <template #conteudo>
                <pcmso v-if="pcmsoOpen" modal="janelaPcmso"></pcmso>
            </template>
        </modal>

        <modal id="janelaCadastrar" :titulo="titulo_janela" :fechar="!preload" :size="90" :modal-pai="modal" ref="modal_janelaCadastrar">
            <template #conteudo>
                <preload v-show="preload"></preload>
                <div v-if="!preload && !cadastrado">
                    <fieldset>
                        <legend>Informações</legend>
                        <div class="row">
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label>CNPJ</label>
                                    <input
                                        v-model="form.dados.cnpj"
                                        class="form-control"
                                        type="text"
                                        placeholder="Informe o CNPJ"
                                        onblur="valida_cnpj_vazio(this)"
                                        v-mascara:cnpj
                                    />
                                </div>
                            </div>

                            <div class="col-12"></div>

                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label>Razão Social</label>
                                    <input
                                        v-model="form.nome"
                                        class="form-control"
                                        type="text"
                                        placeholder="Informe a Razão Social"
                                        onblur="valida_campo_vazio(this, 1)"
                                    />
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label>Nome Fantasia</label>
                                    <input v-model="form.dados.nome_fantasia" class="form-control" type="text" placeholder="Informe o nome fantasia" />
                                </div>
                            </div>

                            <div class="col-12 col-md-12">
                                <endereco :model="form.dados.endereco"></endereco>
                            </div>

                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label>Telefone</label>
                                    <input
                                        v-model="form.dados.telefone"
                                        class="form-control"
                                        onblur="valida_telefone(this)"
                                        type="text"
                                        v-mascara:telefone
                                        placeholder="Informe o numero de telefone"
                                    />
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label>E-mail</label>
                                    <input
                                        v-model="form.dados.email"
                                        class="form-control"
                                        type="text"
                                        onblur="validaEmailVazio(this)"
                                        placeholder="Informe o e-mail da empresa"
                                    />
                                </div>
                            </div>

                            <div class="col-12"></div>

                            <div class="col-12 mt-2">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" v-model="form.ativo" class="custom-control-input" id="EmpresaAtivo" />
                                    <label class="custom-control-label" for="EmpresaAtivo">{{ form.ativo ? 'Ativo' : 'Inativo' }}</label>
                                </div>
                            </div>
                            <!--                            <div class='col-12 col-md-4'>-->
                            <!--                                <div class='form-group'>-->
                            <!--                                    <label>Ativo</label>-->
                            <!--                                    <select class='form-control' onblur='valida_campo_vazio(this,1)'-->
                            <!--                                            onchange='valida_campo_vazio(this,1)' v-model='form.ativo'>-->
                            <!--                                        <option :value="''">Selecione</option>-->
                            <!--                                        <option :value='true'>Sim</option>-->
                            <!--                                        <option :value='false'>Não</option>-->
                            <!--                                    </select>-->
                            <!--                                </div>-->
                            <!--                            </div>-->
                        </div>
                    </fieldset>
                </div>
            </template>
            <template #rodape>
                <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="editando && !preload" @click="alterarformEmpresaExame()">Alterar</button>
                <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="!editando && !preload" @click="cadastrar()">Cadastrar</button>
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
                        <label class="mybp-label" for="empresa-exame-filtro-busca">Buscar</label>
                        <input
                            id="empresa-exame-filtro-busca"
                            type="text"
                            placeholder="Buscar por razão social, fantasia, CNPJ ou ID"
                            autocomplete="off"
                            class="form-control form-control-sm"
                            :disabled="controle.carregando"
                            v-model="controle.dados.campoBusca"
                        />
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="form-group mb-2 mb-lg-0">
                        <label class="mybp-label" for="empresa-exame-filtro-status">Status</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroStatus"
                                instance-id="filtro-status"
                                v-model="controle.dados.campoStatus"
                                :options="filtroStatusOpcoes"
                                :disabled="controle.carregando"
                                input-id="empresa-exame-filtro-status"
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
                <button type="button" class="btn btn-sm btn-outline-secondary" v-if="pcmsoOpen" @click="$refs.modal_janelaPcmso && $refs.modal_janelaPcmso.abrirModal()">
                    <i class="fa fa-plus"></i> PCMSO
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
                                <strong>{{ item.nome }}</strong>
                            </div>
                        </div>
                        <div class="mybp-card-right">
                            <bt-ativo
                                :rota="`cadastro/empresa-exame/${item.id}/ativa-desativa`"
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
                            <i class="fas fa-id-card text-muted"></i>
                            <span class="mybp-detail-label">CNPJ</span>
                            <span class="mybp-detail-value">{{ cnpjEmpresa(item) }}</span>
                        </div>
                        <div class="mybp-detail-item">
                            <i class="fas fa-building text-muted"></i>
                            <span class="mybp-detail-label">Fantasia</span>
                            <span class="mybp-detail-value">{{ nomeFantasiaEmpresa(item) }}</span>
                        </div>
                        <div class="mybp-detail-item">
                            <i class="fas fa-envelope text-muted"></i>
                            <span class="mybp-detail-label">E-mail</span>
                            <span class="mybp-detail-value">{{ emailEmpresa(item) }}</span>
                        </div>
                        <div class="mybp-detail-item">
                            <i class="fas fa-phone text-muted"></i>
                            <span class="mybp-detail-label">Telefone</span>
                            <span class="mybp-detail-value">{{ telefoneEmpresa(item) }}</span>
                        </div>
                        <div class="mybp-detail-item mybp-detail-item--full">
                            <i class="fas fa-map-marker-alt text-muted"></i>
                            <span class="mybp-detail-label">Endereço</span>
                            <span class="mybp-detail-value">{{ enderecoEmpresa(item) }}</span>
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
import Endereco from '../../Endereco'
import modal from '../../Modal'
import Pcmso from '../pcmso/Pcmso'
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

const CAMPOS_FILTRO_URL = ['campoBusca', 'campoStatus']

export default {
    components: {
        Pcmso,
        modal,
        controlePaginacao,
        Endereco,
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
            titulo_janela: 'Empresa Exame',
            titulo_janela_pcmso: 'PCMSO',
            titulo_janela_form_pcmso: 'PCMSO',

            preload: false,
            editando: false,
            cadastrado: false,
            atualizado: false,

            pcmsoOpen: false,
            permissoes: [],

            form: {
                nome: '',
                dados: {
                    cnpj: '',
                    nome_fantasia: '',
                    telefone: '',
                    email: '',
                    endereco: {
                        cep: '',
                        logradouro: '',
                        bairro: '',
                        end_end_numero: '',
                        complemento: '',
                        municipio: '',
                        uf: 'MA'
                    }
                },
                ativo: true
            },
            formDefault: null,

            lista: [],
            dropdownAbertoKey: null,
            syncUrlTimer: null,

            urlPaginacao: `${URL_ADMIN}/cadastro/empresa-exame/atualizar`,
            controle: {
                carregando: false,
                dados: {
                    campoBusca: '',
                    campoStatus: '',
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
            if (this.$refs.comboFiltroStatus?.containsTarget?.(event.target)) return
            this.dropdownAbertoKey = null
            this.fecharOutrosComboboxes(null)
        },
        toggleDropdown(empresaExameId) {
            if (!empresaExameId) return
            const key = `empresa-exame:${empresaExameId}`
            this.dropdownAbertoKey = this.dropdownAbertoKey === key ? null : key
        },
        isDropdownOpen(empresaExameId) {
            return this.dropdownAbertoKey === `empresa-exame:${empresaExameId}`
        },
        fecharDropdown() {
            this.dropdownAbertoKey = null
        },
        abrirModalNovo() {
            this.formNovo()
            this.$refs.modal_janelaCadastrar?.abrirModal?.()
        },
        abrirModalAlterar(empresaExameId) {
            this.fecharDropdown()
            this.alterarEmpresaExame(empresaExameId)
            this.$refs.modal_janelaCadastrar?.abrirModal?.()
        },
        cnpjEmpresa(item) {
            return item?.dados?.cnpj || 'Não informado'
        },
        nomeFantasiaEmpresa(item) {
            return item?.dados?.nome_fantasia || 'Não informado'
        },
        emailEmpresa(item) {
            return item?.dados?.email || 'Não informado'
        },
        telefoneEmpresa(item) {
            return item?.dados?.telefone || 'Não informado'
        },
        enderecoEmpresa(item) {
            return item?.dados?.endereco?.endereco_completo || 'Não informado'
        },
        formNovo() {
            this.form = _.cloneDeep(this.formDefault) //copia
            this.titulo_janela = 'Empresa Exame'
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
                .post(`${URL_ADMIN}/cadastro/empresa-exame`, this.form)
                .then((res) => {
                    if (res.status === 201) {
                        this.$refs.modal_janelaCadastrar && this.$refs.modal_janelaCadastrar.fecharModal()
                        mostraSucesso('', 'Empresa cadastrada com sucesso')
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

        alterarEmpresaExame(empresaexame) {
            this.cadastrado = false
            this.editando = true
            this.titulo_janela = 'Alterando Empresa'
            this.preload = true

            this.form = _.cloneDeep(this.formDefault) //copia
            formReset()

            axios
                .get(`${URL_ADMIN}/cadastro/empresa-exame/${empresaexame}/editar`)
                .then((response) => {
                    Object.assign(this.form, response.data)
                    this.editando = true
                    setupCampo()
                    this.preload = false
                })
                .catch((error) => (this.preloadAjax = false))
        },

        alterarformEmpresaExame() {
            formReset()
            $('#janelaCadastrar :input:enabled').trigger('blur')

            if ($('#janelaCadastrar :input:enabled.is-invalid').length) {
                mostraErro('', 'Verificar os erros')
                return false
            }

            this.preload = true

            axios
                .put(`${URL_ADMIN}/cadastro/empresa-exame/${this.form.id}`, this.form)
                .then((response) => {
                    this.$refs.modal_janelaCadastrar && this.$refs.modal_janelaCadastrar.fecharModal()
                    mostraSucesso('', 'Empresa Exame atualizado com sucesso')
                    this.preload = false
                    this.atualizado = true
                    this.atualizar()
                })
                .catch((error) => (this.preload = false))
        },
        carregou(dados) {
            this.lista = dados.items || []
            this.permissoes = dados.permissoes || {}
            this.pcmsoOpen = this.permissoes.pcmso
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
/* Estilos globais em resources/sass/_mybp-listagem-ui.scss - ver docs/PADRAO_UX_LISTAGEM_CADASTROS.md */
</style>

<template>
    <div id="componenteBeneficio">
        <modal :modal-pai="modal" :titulo="titulo_janela_tipo_beneficio" :fechar="!preloadTipoBeneficio" id="janelaFormTipoBeneficio" ref="modal_janelaFormTipoBeneficio">
            <template #conteudo>
                <div class="row">
                    <div class="col-12 col-md-12">
                        <button type="button" class="btn btn-sm mr-1 btn-success" @click="atualizar">
                            <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                            Atualizar
                        </button>

                        <button type="button" class="btn btn-sm mr-1 btn-secondary" @click="formNovoTipo; $refs.modal_janelaFormTipo && $refs.modal_janelaFormTipo.abrirModal()">
                            <i class="fa fa-plus"></i> Cadastrar Tipo de Beneficio
                        </button>
                    </div>
                </div>
                <br />
                <p class="mt-2 text-center" v-if="controle.carregando && atualizado"><i class="fa fa-spinner fa-pulse"></i> Carregando...</p>

                <div v-if="!controle.carregando" class="table-responsive">
                    <table class="tabela">
                        <thead>
                            <tr class="bg-default">
                                <td class="text-center">Nome</td>
                                <td class="text-center">Ativo</td>
                                <td class="text-center">Editar</td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, index) in tipos" :key="index">
                                <td class="text-center">{{ item.nome }}</td>
                                <td class="text-center">
                                    <bt-ativo :rota="`cadastro/beneficios/${item.id}/ativa-desativa`" :model="item"></bt-ativo>
                                </td>
                                <td class="text-center">
                                    <button
                                        type="button"
                                        class="btn btn-sm mr-1 btn-primary"
                                        @click="alterarTipo(item.id); $refs.modal_janelaFormTipo && $refs.modal_janelaFormTipo.abrirModal()"
                                    >
                                        <i class="fa fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </template>
        </modal>

        <modal :modal-pai="modal" :titulo="titulo_janela_form_tipo" :fechar="!preloadTipo" id="janelaFormTipo" ref="modal_janelaFormTipo">
            <template #conteudo>
                <p class="mt-2 text-center" v-if="preloadTipo"><i class="fa fa-spinner fa-pulse"></i>Carregando...</p>
                <fieldset v-if="!preloadTipo">
                    <legend>Cadastro de Tipo</legend>
                    <div class="row">
                        <div class="col-12 mb-2">
                            <label>Nome</label>
                            <input class="form-control" type="text" onblur="valida_campo_vazio(this, 1)" v-model="formTipo.nome" />
                        </div>

                        <div class="col-12">
                            <div class="switchToggle">
                                <input type="checkbox" v-model="formTipo.ativo" id="switch" />
                                <label for="switch">Ativo</label>
                            </div>
                        </div>
                    </div>
                </fieldset>
            </template>
            <template #rodape>
                <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="!cadastrado && !preloadTipo" @click="cadastraTipo">
                    <i class="fa fa-save"></i>Cadastrar
                </button>

                <button v-show="cadastrado" type="button" class="btn btn-sm mr-1 btn-primary" @click="alterarformTipo"><i class="fa fa-save"></i> Alterar</button>
            </template>
        </modal>

        <modal :modal-pai="modal" :titulo="titulo_janela_form_beneficio" id="janelaFormBeneficio" :size="65" ref="modal_janelaFormBeneficio">
            <template #conteudo>
                <p class="mt-2 text-center" v-if="preload"><i class="fa fa-spinner fa-pulse"></i>Carregando...</p>
                <div class="alert alert-success alert-dismissible" v-show="cadastrado">
                    <h6 class="text-center"><i class="icon fa fa-check"></i> Cadastrado com sucesso!</h6>
                </div>
                <div v-if="!preload && !cadastrado">
                    <fieldset>
                        <legend>Informações</legend>
                        <div class="row">
                            <div class="col-12">
                                <label>Nome</label>
                                <input type="text" v-model="form.nome" class="form-control" onblur="valida_campo_vazio(this, 1)" />
                            </div>
                            <div class="col-6">
                                <label>Tipo</label>
                                <select
                                    v-model="form.tipobeneficio_id"
                                    onchange="valida_campo_vazio(this, 1)"
                                    onblur="valida_campo_vazio(this, 1)"
                                    class="custom-select"
                                >
                                    <option value="">Selecione</option>
                                    <option v-for="(item, index) in tiposAtivos" :value="item.id" :key="index">{{ item.nome }}</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label>Valor</label>
                                <input type="text" v-model="form.valor" v-mascara:dinheiro class="form-control" onblur="valida_campo_vazio(this, 1)" />
                            </div>
                            <div class="col-6">
                                <label>Aplicação</label>
                                <select
                                    v-model="form.aplicacao"
                                    onchange="valida_campo_vazio(this, 1)"
                                    onblur="valida_campo_vazio(this, 1)"
                                    class="custom-select"
                                >
                                    <option value="">Selecione</option>
                                    <option value="reais">Em Reais</option>
                                    <option value="percentual">Percentual do Salário</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label>Periodicidade</label>
                                <select
                                    v-model="form.periodicidade"
                                    onchange="valida_campo_vazio(this, 1)"
                                    onblur="valida_campo_vazio(this, 1)"
                                    class="custom-select"
                                >
                                    <option value="">Selecione</option>
                                    <option value="diario">Diário</option>
                                    <option value="semanal">Semanal</option>
                                    <option value="quinzenal">Quinzenal</option>
                                    <option value="mensal">Mensal</option>
                                    <option value="anual">Anual</option>
                                </select>
                            </div>

                            <div class="col-6">
                                <label>Valor Descontado</label>
                                <input
                                    type="text"
                                    v-model="form.valor_descontado"
                                    v-mascara:dinheiro
                                    class="form-control"
                                    onblur="valida_campo_vazio(this, 1)"
                                />
                            </div>

                            <div class="col-6">
                                <label>Opção de Desconto</label>
                                <select
                                    v-model="form.opcao_desconto"
                                    onchange="valida_campo_vazio(this, 1)"
                                    onblur="valida_campo_vazio(this, 1)"
                                    class="custom-select"
                                >
                                    <option value="">Selecione</option>
                                    <option value="fixo">Valor Fixo</option>
                                    <option value="percentual">Percentual do Salário</option>
                                </select>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </template>
            <template #rodape>
                <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="!editando" @click="cadastrar()">Cadastrar</button>
                <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="editando" @click="alterarformBeneficio()">Alterar</button>
            </template>
        </modal>

        <!-- Filtro -->
        <FiltroListagem
            @submit="onSubmitFiltro"
            :mostrar-limpar-filtros="temFiltrosAtivos"
            :desabilitado="controle.carregando"
            @limpar="limparFiltros"
        >
            <template #filtros>
                <div class="col-12 col-lg-5">
                    <div class="form-group mb-2 mb-lg-0">
                        <label class="mybp-label" for="beneficio-filtro-busca">Buscar</label>
                        <input
                            id="beneficio-filtro-busca"
                            type="text"
                            placeholder="Buscar por nome ou tipo"
                            autocomplete="off"
                            class="form-control form-control-sm"
                            :disabled="controle.carregando"
                            v-model="controle.dados.campoBusca"
                        />
                    </div>
                </div>

                <div class="col-12 col-lg-4 mybp-combobox-wrap">
                    <div class="form-group mb-2 mb-lg-0">
                        <label class="mybp-label" for="beneficio-filtro-tipo-input">Tipo de benefício</label>
                        <combobox-auto-complete
                            ref="comboFiltroTipo"
                            instance-id="filtro-tipo-beneficio"
                            v-model="controle.dados.campoTipoBeneficio"
                            :options="tipoBeneficioComboboxOpcoesFiltro"
                            :disabled="controle.carregando || !tipos.length"
                            input-id="beneficio-filtro-tipo-input"
                            empty-message="Nenhum tipo encontrado."
                            :max-results="50"
                            @opening="fecharOutrosComboboxes('filtro-tipo-beneficio')"
                            @select="onSelectFiltroTipo"
                        />
                    </div>
                </div>
            </template>

            <template #acoes>
                <button type="button" class="btn btn-sm btn-success" :disabled="controle.carregando" @click="atualizar">
                    <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i> Atualizar
                </button>
                <button
                    type="button"
                    class="btn btn-sm btn-secondary"
                    @click="formNovo(); $refs.modal_janelaFormBeneficio && $refs.modal_janelaFormBeneficio.abrirModal()"
                >
                    <i class="fa fa-plus"></i> Cadastrar Benefício
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" @click="$refs.modal_janelaFormTipoBeneficio && $refs.modal_janelaFormTipoBeneficio.abrirModal()">
                    <i class="fa fa-tags"></i> Tipo de Benefício
                </button>
            </template>
        </FiltroListagem>

        <div id="conteudo">
            <preload class="text-center" v-if="controle.carregando"></preload>

            <div class="alert alert-warning text-center" v-show="!controle.carregando && lista.length === 0">
                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
            </div>

            <div class="mybp-cards-lista" v-show="!controle.carregando && lista.length > 0">
                <div class="mybp-card" v-for="beneficio in lista" :key="beneficio.id">
                    <div class="mybp-card-header-row">
                        <div class="mybp-card-left">
                            <span class="mybp-badge-id">#{{ beneficio.id }}</span>
                            <div class="mybp-card-titulo">
                                <strong>{{ beneficio.nome }}</strong>
                            </div>
                        </div>
                        <div class="mybp-card-right">
                            <div class="dropdown" :class="{ show: isDropdownOpen(beneficio.id) }">
                                <a
                                    class="mybp-btn-acoes-compact"
                                    href="#"
                                    role="button"
                                    aria-haspopup="true"
                                    :aria-expanded="isDropdownOpen(beneficio.id) ? 'true' : 'false'"
                                    @click.prevent.stop="toggleDropdown(beneficio.id)"
                                >
                                    <i class="fas fa-ellipsis-v"></i>
                                </a>
                                <div
                                    class="dropdown-menu mybp-dropdown-menu dropdown-menu-right"
                                    :class="{ show: isDropdownOpen(beneficio.id) }"
                                    @click="fecharDropdown"
                                >
                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        title="Editar"
                                        @click.prevent="abrirEdicaoBeneficio(beneficio.id)"
                                    >
                                        <i class="fa fa-edit mr-1"></i> Editar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mybp-card-details-row">
                        <div class="mybp-detail-item">
                            <i class="fas fa-tag text-muted"></i>
                            <span class="mybp-detail-label">Tipo</span>
                            <span class="mybp-detail-value">{{ nomeTipoBeneficio(beneficio) }}</span>
                        </div>
                        <div class="mybp-detail-item">
                            <i class="fas fa-money-bill-wave text-muted"></i>
                            <span class="mybp-detail-label">Valor</span>
                            <span class="mybp-detail-value">R$ {{ beneficio.valor_format }}</span>
                        </div>
                        <div class="mybp-detail-item">
                            <i class="fas fa-calendar-alt text-muted"></i>
                            <span class="mybp-detail-label">Periodicidade</span>
                            <span class="mybp-detail-value">{{ labelPeriodicidade(beneficio.periodicidade) }}</span>
                        </div>
                    </div>

                    <div class="mybp-card-secoes-row">
                        <div class="mybp-card-destaque mybp-card-destaque--primary">
                            <i class="fas fa-calculator text-primary"></i>
                            <div class="mybp-card-destaque-info">
                                <small class="mybp-card-destaque-etapa">Aplicação</small>
                                <strong class="mybp-card-destaque-valor">{{ labelAplicacao(beneficio.aplicacao) }}</strong>
                            </div>
                        </div>
                        <div class="mybp-card-destaque mybp-card-destaque--info">
                            <i class="fas fa-percent text-info"></i>
                            <div class="mybp-card-destaque-info">
                                <small class="mybp-card-destaque-etapa">Desconto</small>
                                <strong class="mybp-card-destaque-valor">
                                    {{ labelOpcaoDesconto(beneficio.opcao_desconto) }} — R$ {{ beneficio.valordescontado_format }}
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
import { temFiltrosPreenchidos, limparFiltrosListagem } from '../../../utils/listagemQueryParams'

const CAMPOS_FILTRO_URL = ['campoBusca', 'campoTipoBeneficio']

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
            // modal Pai
            type: String,
            required: false,
            default: ''
        }
    },

    mounted() {
        this.usuarioAutenticado()
        this.atualizar()
        this.formDefault = _.cloneDeep(this.form)
        this.formTipoDefault = _.cloneDeep(this.formTipo)
        document.addEventListener('click', this.onClickOutside)
    },

    beforeUnmount() {
        document.removeEventListener('click', this.onClickOutside)
    },

    computed: {
        tipoBeneficioComboboxOpcoesFiltro() {
            const tipos = (this.tipos || []).map((tipo) => ({
                value: tipo.id,
                label: tipo.nome,
                raw: tipo
            }))

            return [{ value: '', label: 'Todos os tipos' }, ...tipos]
        },
        temFiltrosAtivos() {
            return temFiltrosPreenchidos(this.controle.dados, CAMPOS_FILTRO_URL)
        }
    },
    data() {
        return {
            hash: String(Math.random()).substr(2),
            titulo_janela_form_beneficio: '',
            titulo_janela_form_tipo: '',
            titulo_janela_tipo_beneficio: 'Tipo de Benefício',

            preload: false,
            preloadTipo: false,
            preloadTipoBeneficio: false,
            editando: false,
            cadastrado: false,
            atualizado: false,

            cliente_id: '',

            form: {
                nome: '',
                tipobeneficio_id: '',
                cliente_id: '',
                valor: '',
                aplicacao: '',
                periodicidade: '',
                valor_descontado: '',
                opcao_desconto: ''
            },
            formDefault: null,

            formTipo: {
                nome: '',
                cliente_id: '',
                ativo: true
            },
            formTipoDefault: null,

            //Paginacao
            lista: [],
            tipos: [],
            tiposAtivos: [],
            clientes: [],
            permissoes: [],
            dropdownAbertoKey: null,

            urlPaginacao: `${URL_ADMIN}/cadastro/beneficios/atualizar`,
            controle: {
                carregando: false,
                dados: {
                    campoBusca: '',
                    campoTipoBeneficio: ''
                }
            }
        }
    },
    methods: {
        onSubmitFiltro() {
            this.atualizar()
        },
        fecharOutrosComboboxes(manter) {
            if (manter !== 'filtro-tipo-beneficio' && this.$refs.comboFiltroTipo?.close) {
                this.$refs.comboFiltroTipo.close()
            }
        },
        onSelectFiltroTipo() {
            this.atualizar()
        },
        limparFiltros() {
            limparFiltrosListagem(this.controle.dados, CAMPOS_FILTRO_URL)
            this.fecharOutrosComboboxes(null)
            this.atualizar()
        },
        onClickOutside(event) {
            if (event?.target?.closest?.('.dropdown')) return
            if (this.$refs.comboFiltroTipo?.containsTarget?.(event.target)) return
            this.dropdownAbertoKey = null
            this.fecharOutrosComboboxes(null)
        },
        toggleDropdown(beneficioId) {
            if (!beneficioId) return
            const key = `beneficio:${beneficioId}`
            this.dropdownAbertoKey = this.dropdownAbertoKey === key ? null : key
        },
        isDropdownOpen(beneficioId) {
            return this.dropdownAbertoKey === `beneficio:${beneficioId}`
        },
        fecharDropdown() {
            this.dropdownAbertoKey = null
        },
        abrirEdicaoBeneficio(beneficioId) {
            this.fecharDropdown()
            this.alterarBeneficio(beneficioId)
            this.$refs.modal_janelaFormBeneficio && this.$refs.modal_janelaFormBeneficio.abrirModal()
        },
        nomeTipoBeneficio(beneficio) {
            return beneficio?.tipo_beneficio?.nome ?? beneficio?.TipoBeneficio?.nome ?? 'Não informado'
        },
        labelAplicacao(valor) {
            const map = {
                reais: 'Em Reais',
                percentual: 'Percentual do Salário'
            }
            return map[valor] || valor || 'Não informado'
        },
        labelPeriodicidade(valor) {
            const map = {
                diario: 'Diário',
                semanal: 'Semanal',
                quinzenal: 'Quinzenal',
                mensal: 'Mensal',
                anual: 'Anual'
            }
            return map[valor] || valor || 'Não informado'
        },
        labelOpcaoDesconto(valor) {
            const map = {
                fixo: 'Valor Fixo',
                percentual: 'Percentual do Salário'
            }
            return map[valor] || valor || 'Não informado'
        },
        formNovo() {
            this.form = _.cloneDeep(this.formDefault) //copia
            this.titulo_janela_form_beneficio = 'Cadastro de Benefício'
            this.cadastrado = false
            this.finalizado = false
            this.atualizado = false
            this.editando = false

            formReset()
            setupCampo()
        },
        cadastrar() {
            this.form.cliente_id = this.cliente_id === 0 ? this.form.cliente_id : this.cliente_id

            $('#janelaFormBeneficio :input:visible').trigger('blur')
            if ($('#janelaFormBeneficio :input:visible.is-invalid').length) {
                mostraErro('', 'Verificar os erros')
                return false
            }
            this.preloadTipo = true
            axios
                .post(`${URL_ADMIN}/cadastro/beneficios`, this.form)
                .then((res) => {
                    if (res.status === 201) {
                        this.$refs.modal_janelaFormBeneficio && this.$refs.modal_janelaFormBeneficio.fecharModal()
                        mostraSucesso('', 'Benefício Cadastrado com sucesso')
                        this.preloadTipo = false
                        this.cadastrado = true
                        this.atualizar()
                    } else {
                        this.cadastrado = false
                        this.preloadTipo = false
                    }
                })
                .catch((error) => {
                    this.cadastrado = false
                    this.preloadTipo = false
                })
        },

        formNovoTipo() {
            this.formTipo = _.cloneDeep(this.formTipoDefault) //copia
            this.titulo_janela_form_tipo = 'Novo Tipo de Benefício'
            this.preloadTipo = false
            this.cadastrado = false
            this.atualizado = false
            formReset()
        },
        cadastraTipo() {
            this.formTipo.cliente_id = this.cliente_id === 0 ? this.formTipo.cliente_id : this.cliente_id
            $('#janelaFormTipo :input:visible').trigger('blur')
            if ($('#janelaFormTipo :input:visible.is-invalid').length) {
                mostraErro('', 'Verificar os erros')
                return false
            }
            this.preloadTipo = true
            axios
                .post(`${URL_ADMIN}/cadastro/beneficios/cadastro-tipo`, this.formTipo)
                .then((res) => {
                    this.$refs.modal_janelaFormTipo && this.$refs.modal_janelaFormTipo.fecharModal()
                    mostraSucesso('', 'Tipo cadastrado com sucesso')
                    this.cadastrado = true
                    this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
                    this.preloadTipo = false
                })
                .catch((error) => {
                    this.cadastrado = false
                    this.preloadTipo = false
                })
        },
        alterarBeneficio(beneficio) {
            this.form.cliente_id = this.cliente_id === 0 ? this.form.cliente_id : this.cliente_id

            this.cadastrado = false
            this.editando = true
            this.titulo_janela_form_beneficio = 'Alterando Benefício'
            formReset()

            this.form = _.cloneDeep(this.formDefault) //copia

            axios
                .get(`${URL_ADMIN}/cadastro/beneficios/${beneficio}/editar`)
                .then((response) => {
                    Object.assign(this.form, response.data)
                    this.editando = true
                    setupCampo()
                })
                .catch(() => (this.preloadAjax = false))
        },
        alterarformBeneficio() {
            formReset()
            $('#janelaFormBeneficio :input:enabled').trigger('blur')

            if ($('#janelaFormBeneficio :input:enabled.is-invalid').length) {
                mostraErro('', 'Verificar os erros')
                return false
            }

            this.preloadAjax = true

            axios
                .put(`${URL_ADMIN}/cadastro/beneficios/${this.form.id}`, this.form)
                .then((response) => {
                    this.$refs.modal_janelaFormBeneficio && this.$refs.modal_janelaFormBeneficio.fecharModal()
                    mostraSucesso('', 'Benefício Editado com sucesso')
                    this.preloadAjax = false
                    this.controle.carregando = true
                    this.atualizado = true
                    this.atualizar()
                })
                .catch((error) => (this.preloadAjax = false))
        },
        alterarTipo(tipobeneficio) {
            this.formTipo.cliente_id = this.cliente_id === 0 ? this.formTipo.cliente_id : this.cliente_id

            this.cadastrado = true
            this.editando = false
            this.tituloJanela = 'Alterando Tipo de Benefício'
            formReset()

            this.formTipo = _.cloneDeep(this.formTipoDefault) //copia

            axios
                .get(`${URL_ADMIN}/cadastro/beneficios/${tipobeneficio}/editarTipo`)
                .then((response) => {
                    Object.assign(this.formTipo, response.data)
                    // this.formTipo.nome = data.nome
                    this.editando = true
                    setupCampo()
                })
                .catch((error) => (this.preloadAjax = false))
        },
        alterarformTipo() {
            formReset()
            this.formTipo.cliente_id = this.cliente_id === 0 ? this.formTipo.cliente_id : this.cliente_id
            $('#janelaFormTipo :input:enabled').trigger('blur')

            if ($('#janelaFormTipo :input:enabled.is-invalid').length) {
                mostraErro('', 'Verificar os erros')
                return false
            }

            this.preloadAjax = true

            axios
                .put(`${URL_ADMIN}/cadastro/beneficios/updateTipos/${this.formTipo.id}`, this.formTipo)
                .then((response) => {
                    this.$refs.modal_janelaFormTipo && this.$refs.modal_janelaFormTipo.fecharModal()
                    this.preloadAjax = false
                    this.controle.carregando = true
                    this.atualizado = true
                    this.atualizar()
                })
                .catch((error) => (this.preloadAjax = false))
        },
        usuarioAutenticado() {
            this.controle.carregando = true
            axios
                .get(`${URL_ADMIN}/usuario/autenticado/`)
                .then((response) => {
                    let data = response.data

                    this.cliente_id = data.cliente_id

                    this.controle.dados.campoCliente = this.cliente_id !== 0 ? this.cliente_id : this.controle.dados.campoCliente
                })
                .catch((error) => {
                    this.preload = false
                })
        },
        carregou(dados) {
            this.lista = dados.items
            this.tipos = dados.tipos
            this.tiposAtivos = dados.tiposAtivos
            this.clientes = dados.clientes
            this.permissoes = dados.permissoes
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

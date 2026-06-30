<template>
    <div>
        <!-- Modal Cadastrar / Alterar -->
        <modal
            ref="janelaCadastrar"
            id="janelaCadastrar"
            :titulo="tituloJanela"
            size="g"
        >
            <template #conteudo>
                <span v-show="preloadAjax"><preload /></span>
                <div class="alert alert-success alert-dismissible" v-show="cadastrado">
                    <h4><i class="icon fa fa-check"></i> Usuário cadastrado com sucesso!</h4>
                </div>
                <div class="alert alert-success alert-dismissible" v-show="atualizado">
                    <h4><i class="icon fa fa-check"></i> Usuário alterado com sucesso!</h4>
                </div>
                <form
                    v-show="!preloadAjax && !cadastrado && !atualizado"
                    id="formUsuario"
                    @submit.prevent
                >
                    <div class="form-group">
                        <label>Nome do usuário</label>
                        <input
                            type="text"
                            class="form-control form-control-sm"
                            v-model="form.nome"
                            placeholder="Nome do usuário"
                            autocomplete="off"
                            @blur="validaCampoVazio($event, 3)"
                        />
                    </div>
                    <div class="form-group">
                        <label>E-mail</label>
                        <input
                            type="text"
                            class="form-control form-control-sm"
                            v-model="form.login"
                            placeholder="E-mail"
                            autocomplete="off"
                            @blur="validaEmail($event)"
                        />
                    </div>
                    <div class="form-group" v-if="isMybpEmpresa">
                        <label>Empresa</label>
                        <select
                            class="form-control form-control-sm"
                            v-model="form.empresa_id"
                            @change="onEmpresaChange"
                        >
                            <option value="">Selecione...</option>
                            <option
                                v-for="emp in listaEmpresas"
                                :key="emp.id"
                                :value="emp.id"
                            >
                                {{ emp.nome_fantasia }}
                            </option>
                        </select>
                    </div>
                    <div class="form-group" v-if="grupoempresa">
                        <label>Grupo</label>
                        <select class="form-control form-control-sm" v-model="form.grupo_id">
                            <option value="">Selecione...</option>
                            <option v-for="papel in listaPapeis" :key="papel.id" :value="papel.id">
                                {{ papel.nome }}
                            </option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tipo de Usuário</label>
                        <select class="form-control form-control-sm" v-model="form.tipo">
                            <option value="">Selecione...</option>
                            <option v-for="item in lista_tipos" :key="item" :value="item">
                                {{ item }}
                            </option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Ativo</label>
                        <select class="form-control form-control-sm" v-model="form.ativo">
                            <option :value="true">Sim</option>
                            <option :value="false">Não</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Gestor</label>
                        <select class="form-control form-control-sm" v-model="form.gestor">
                            <option :value="true">Sim</option>
                            <option :value="false">Não</option>
                        </select>
                    </div>
                    <fieldset>
                        <legend>Contatos</legend>
                        <telefone
                            :model="form.telefones"
                            :model-delete="form.telefonesDelete"
                            :pais="false"
                            :ramal="false"
                            :detalhe="false"
                            :qnt_min="0"
                        />
                    </fieldset>
                    <whatsapp-preferencias-form
                        v-if="canUpdate || canInsert"
                        :preferencias="whatsappPreferencias"
                        :whatsapp-liberado="whatsappLiberado"
                        input-prefix="usuario-whatsapp-pref"
                        descricao="Defina quais notificações WhatsApp este usuário deve receber."
                        @update:preferencias="whatsappPreferencias = $event"
                    />
                    <fieldset v-if="tipos_usuarios_gerenciais.includes(form.tipo)">
                        <legend>Tipos de e-mails que esse usuário pode receber:</legend>
                        <div
                            class="custom-control custom-switch"
                            v-for="tipo in listaTipoEmail"
                            :key="tipo.id"
                        >
                            <input
                                type="checkbox"
                                class="custom-control-input mb-1"
                                v-model="form.user_recebe_email[tipo.id]"
                                :value="tipo.id"
                                :id="`tipo_${tipo.id}`"
                            />
                            <label class="custom-control-label" style="cursor: pointer" :for="`tipo_${tipo.id}`">
                                {{ tipo.nome }}
                            </label>
                        </div>
                    </fieldset>
                    <fieldset v-if="form.gestor">
                        <legend>Privilégios</legend>
                        <div class="custom-control custom-switch">
                            <input
                                type="checkbox"
                                class="custom-control-input mb-1"
                                v-model="form.privilegio_gestor_area"
                                id="privilegio_gestor_area"
                            />
                            <label class="custom-control-label" style="cursor: pointer" for="privilegio_gestor_area">
                                Gestor de área
                            </label>
                        </div>
                        <div class="custom-control custom-switch">
                            <input
                                type="checkbox"
                                class="custom-control-input mb-1"
                                v-model="form.privilegio_gestor_centro_custo"
                                id="privilegio_gestor_centro_custo"
                            />
                            <label class="custom-control-label" style="cursor: pointer" for="privilegio_gestor_centro_custo">
                                Gestor de centro de custo
                            </label>
                        </div>
                    </fieldset>
                </form>
            </template>
            <template #rodape>
                <button
                    type="button"
                    class="btn btn-sm mr-1 btn-primary"
                    v-show="editando && !atualizado && !preloadAjax"
                    @click="alterar"
                >
                    Alterar
                </button>
                <button
                    type="button"
                    class="btn btn-sm mr-1 btn-primary"
                    v-show="!editando && !cadastrado && !preloadAjax"
                    @click="cadastrar"
                >
                    Cadastrar
                </button>
            </template>
        </modal>

        <!-- Modal Confirmar Apagar -->
        <modal
            ref="janelaConfirmar"
            id="janelaConfirmar"
            titulo="Apagar Usuário"
        >
            <template #conteudo>
                <span v-show="preloadAjax"><i class="fa fa-spinner fa-pulse"></i> Aguarde...</span>
                <div class="alert alert-success alert-dismissible" v-show="apagado">
                    <h4><i class="icon fa fa-check"></i> Usuário apagado com sucesso!</h4>
                </div>
                <h4 v-show="!apagado">Tem certeza que deseja apagar este usuário?</h4>
            </template>
            <template #rodape>
                <button
                    type="button"
                    class="btn btn-sm mr-1 btn-danger"
                    v-show="!apagado"
                    @click="apagar"
                >
                    Apagar
                </button>
            </template>
        </modal>

        <fieldset>
            <legend>Filtragem por</legend>
            <form id="formBusca" @keypress.enter="buscarPaginacao" @submit.prevent>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Buscar:</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-search"></i></span>
                                </span>
                                <input
                                    type="text"
                                    v-model="controle.dados.campoBusca"
                                    placeholder="Nome do usuário"
                                    autocomplete="off"
                                    class="form-control form-control-sm"
                                />
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>E-mail (login):</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-user"></i></span>
                                </span>
                                <input
                                    type="text"
                                    v-model="controle.dados.campoLogin"
                                    placeholder="E-mail (login) do usuário"
                                    autocomplete="off"
                                    class="form-control form-control-sm"
                                />
                            </div>
                        </div>
                    </div>
                    <template v-if="isMybpEmpresa">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Empresa:</label>
                                <select
                                    class="form-control form-control-sm select-custom"
                                    v-model="controle.dados.campoEmpresa"
                                    @change="onFiltroEmpresaChange"
                                >
                                    <option value="">Selecione...</option>
                                    <option
                                        v-for="c in listaEmpresas"
                                        :key="c.id"
                                        :value="c.id"
                                    >
                                        {{ c.nome_fantasia }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div
                            class="col-md-3"
                            v-if="controle.showCampoGrupo || controle.dados.campoEmpresa !== ''"
                        >
                            <div class="form-group">
                                <label>Grupo:</label>
                                <select
                                    class="form-control form-control-sm"
                                    v-model="controle.dados.campoGrupo"
                                    @change="buscarPaginacao"
                                >
                                    <option value="">Selecione...</option>
                                    <option
                                        v-for="papel in controle.dados.listaPapeis"
                                        :key="papel.id"
                                        :value="papel.id"
                                    >
                                        {{ papel.nome }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </template>
                    <template v-else>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Grupo:</label>
                                <select
                                    class="form-control form-control-sm"
                                    v-model="controle.dados.campoGrupo"
                                    @change="buscarPaginacao"
                                >
                                    <option value="">Selecione...</option>
                                    <option
                                        v-for="papel in controle.dados.listaPapeis"
                                        :key="papel.id"
                                        :value="papel.id"
                                    >
                                        {{ papel.nome }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </template>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Tipo:</label>
                            <select
                                class="form-control form-control-sm select-custom"
                                v-model="controle.dados.campoTipo"
                                @change="buscarPaginacao"
                            >
                                <option value="">Selecione...</option>
                                <option v-for="tip in lista_tipos" :key="tip" :value="tip">
                                    {{ tip }}
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Status:</label>
                            <select
                                class="form-control form-control-sm"
                                :disabled="controle.carregando"
                                v-model="controle.dados.campoStatus"
                                @change="buscarPaginacao"
                            >
                                <option value="">Todos os Status</option>
                                <option :value="true">Apenas Ativos</option>
                                <option :value="false">Apenas Inativos</option>
                            </select>
                        </div>
                    </div>
                </div>
            </form>
            <button
                type="button"
                class="btn btn-sm mr-1 btn-success"
                :disabled="controle.carregando"
                @click="atualizar"
            >
                <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i> Atualizar
            </button>
            <button
                v-if="canInsert"
                type="button"
                class="btn btn-sm mr-1 btn-primary"
                @click="abrirModalNovo"
            >
                Criar novo usuário
            </button>
        </fieldset>

        <p class="text-center" v-if="controle.carregando">
            <preload />
        </p>

        <div id="conteudo">
            <div class="table-responsive">
                <table class="tabela" v-if="!controle.carregando && lista.length > 0">
                    <thead>
                        <tr class="bg-default">
                            <th>Nome</th>
                            <th v-if="isMybpEmpresa">Empresa</th>
                            <th>Grupo</th>
                            <th>Tipo</th>
                            <th>E-MAIL (LOGIN)</th>
                            <th>Status</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="usuario in lista" :key="usuario.id">
                            <td data-label="Nome">{{ usuario.nome }}</td>
                            <td v-if="isMybpEmpresa" data-label="Empresa">
                                {{ usuario.empresa ? usuario.empresa.nome_fantasia : '-' }}
                            </td>
                            <td data-label="Grupo">
                                {{ usuario.papel ? usuario.papel.nome : '-' }}
                            </td>
                            <td data-label="Tipo">{{ usuario.tipo }}</td>
                            <td data-label="LOGIN">{{ usuario.login }}</td>
                            <td data-label="Status">
                                <bt-ativo
                                    :rota="`usuarios/${usuario.id}/ativa-desativa`"
                                    :model="usuario"
                                />
                            </td>
                            <td>
                                <button
                                    v-if="canUpdate"
                                    type="button"
                                    class="btn btn-sm mr-1 btn-success"
                                    @click.prevent="abrirModalAlterar(usuario.id)"
                                >
                                    <i class="fa fa-edit" aria-hidden="true"></i>
                                </button>
                                <button
                                    v-if="podeSimular"
                                    type="button"
                                    class="btn btn-sm mr-1 btn-success"
                                    @click.prevent="simularUsuario(usuario.id)"
                                >
                                    <i class="fa fa-user"></i>
                                </button>
                                <button
                                    v-if="canDelete"
                                    type="button"
                                    class="btn btn-sm mr-1 btn-danger"
                                    @click.prevent="abrirModalConfirmarApagar(usuario.id)"
                                >
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <controle-paginacao
                class="d-flex justify-content-center"
                id="controle"
                ref="componente"
                :url="urlAtualizar"
                :por-pagina="controle.dados.por_pagina"
                :dados="controle.dados"
                @carregou="carregou"
                @carregando="carregando"
            />
        </div>
    </div>
</template>

<script>
import { defineComponent } from 'vue'
import { REFS_MODAL, MODAL_IDS, API_PATHS } from './constants'
import WhatsappPreferenciasForm from '../WhatsappPreferenciasForm.vue'

const getBaseUrl = () => (typeof URL_ADMIN !== 'undefined' ? URL_ADMIN : '')

const api = () => {
    const base = getBaseUrl()
    return {
        get: (path) => (typeof axios !== 'undefined' ? axios.get(`${base}/${path}`) : Promise.reject(new Error('axios not found'))),
        post: (path, data) => (typeof axios !== 'undefined' ? axios.post(`${base}/${path}`, data) : Promise.reject(new Error('axios not found'))),
        put: (path, data) => (typeof axios !== 'undefined' ? axios.put(`${base}/${path}`, data) : Promise.reject(new Error('axios not found'))),
        delete: (path) => (typeof axios !== 'undefined' ? axios.delete(`${base}/${path}`) : Promise.reject(new Error('axios not found')))
    }
}

const formDefault = () => ({
    id: '',
    nome: '',
    login: '',
    grupo_id: '',
    tipo: '',
    grupo_cloud_id: '',
    empresa_id: '',
    ativo: true,
    gestor: false,
    telefones: [],
    telefonesDelete: [],
    user_recebe_email: [],
    privilegio_gestor_area: false,
    privilegio_gestor_centro_custo: false
})

export default defineComponent({
    name: 'Usuarios',

    components: { WhatsappPreferenciasForm },

    props: {
        urlAtualizar: { type: String, required: true },
        listaEmpresas: { type: Array, default: () => [] },
        empresaId: { type: [Number, String], default: '' },
        isMybpEmpresa: { type: Boolean, default: false },
        canInsert: { type: Boolean, default: false },
        canUpdate: { type: Boolean, default: false },
        canDelete: { type: Boolean, default: false },
        podeSimular: { type: Boolean, default: false }
    },

    data() {
        return {
            REFS_MODAL,
            MODAL_IDS,
            tituloJanela: 'Cadastrando usuário',
            preloadAjax: false,
            editando: false,
            cadastrado: false,
            atualizado: false,
            apagado: false,
            grupoempresa: false,
            idApagar: null,

            form: formDefault(),
            listaPapeis: [],
            listaCloud: [],
            listaTipoEmail: [],
            lista_tipos: [],
            tipos_usuarios_gerenciais: [],
            user_recebe_emailDefault: null,
            whatsappLiberado: false,
            whatsappPreferencias: [],

            lista: [],
            empresa_id: '',
            controle: {
                carregando: false,
                showCampoGrupo: false,
                dados: {
                    campoBusca: '',
                    campoLogin: '',
                    por_pagina: 50,
                    campoEmpresa: '',
                    campoGrupo: '',
                    campoTipo: '',
                    listaPapeis: [],
                    campoStatus: ''
                }
            }
        }
    },

    created() {
        this.formDefault = formDefault()
    },

    mounted() {
        if (this.empresaId !== '' && this.empresaId !== undefined) {
            this.empresa_id = this.empresaId
        }
        this.atualizar()
    },

    methods: {
        validaCampoVazio(ev, min) {
            if (typeof window.valida_campo_vazio === 'function') {
                window.valida_campo_vazio(ev.target, min)
            }
        },

        validaEmail(ev) {
            if (typeof window.validaEmail === 'function') {
                window.validaEmail(ev.target)
            }
        },

        hasInvalidFields(selector = '#formUsuario') {
            if (typeof window.formReset === 'function') {
                const form = document.querySelector(selector)
                if (form) {
                    const inputs = form.querySelectorAll('input:not([disabled]), select:not([disabled])')
                    const visibleInputs = Array.from(inputs).filter(
                        (el) => el.offsetWidth > 0 && el.offsetHeight > 0
                    )
                    visibleInputs.forEach((el) => el.dispatchEvent(new Event('blur', { bubbles: true })))
                }
            }
            const invalid = document.querySelectorAll(`${selector} .is-invalid`)
            return invalid && invalid.length > 0
        },

        async abrirModalNovo() {
            await this.formNovo()
            await this.$nextTick()
            this.$refs.janelaCadastrar?.abrirModal?.()
        },

        async formNovo() {
            if (typeof window.formReset === 'function') window.formReset()
            this.form = { ...formDefault(), telefones: [], telefonesDelete: [] }
            this.cadastrado = false
            this.atualizado = false
            this.editando = false
            this.tituloJanela = 'Cadastrando usuário'
            if (Number(this.empresaId) !== 100) {
                this.form.empresa_id = this.empresaId
            }
            this.form.user_recebe_email = this.user_recebe_emailDefault
                ? { ...this.user_recebe_emailDefault }
                : {}
            await this.selecionaEmpresa(this.empresaId)
            await this.carregarWhatsappPreferencias(this.form.empresa_id || this.empresaId)
        },

        async abrirModalAlterar(id) {
            await this.formAlterar(id)
            await this.$nextTick()
            this.$refs.janelaCadastrar?.abrirModal?.()
        },

        async formAlterar(id) {
            if (typeof window.formReset === 'function') window.formReset()
            this.form = { ...formDefault(), telefones: [], telefonesDelete: [] }
            this.selecionaEmpresa(this.empresaId)
            if (Number(this.empresaId) !== 100) {
                this.form.empresa_id = this.empresaId
            }
            this.form.id = id
            this.cadastrado = false
            this.atualizado = false
            this.editando = false
            this.tituloJanela = 'Alterando usuário'
            this.preloadAjax = true
            try {
                const base = getBaseUrl()
                const { data } = await api().get(API_PATHS.editar(id))
                const usuario = data.usuario || {}
                Object.assign(this.form, usuario)
                this.form.telefones = usuario.telefones || []
                this.form.telefonesDelete = []
                if (usuario.grupo_id == null) this.form.grupo_id = ''
                if (usuario.gestor == null) this.form.gestor = false
                this.listaPapeis = data.papeis || []
                this.listaCloud = data.cloud || []
                this.form.user_recebe_email = data.formulario_vazio ? { ...data.formulario_vazio } : {}
                this.whatsappLiberado = !!data.whatsapp_liberado
                this.whatsappPreferencias = this.normalizarWhatsappPreferencias(data.whatsapp_preferencias || [])
                this.editando = true
            } catch (err) {
                // erro tratado
            } finally {
                this.preloadAjax = false
            }
        },

        async cadastrar() {
            if (this.hasInvalidFields()) {
                if (typeof toastr !== 'undefined') toastr.error('Verificar os erros', 'Atenção!')
                return
            }
            if (!this.form.gestor) {
                this.form.privilegio_gestor_centro_custo = false
                this.form.privilegio_gestor_area = false
            }
            this.preloadAjax = true
            try {
                const base = getBaseUrl()
                const payload = {
                    ...this.form,
                    login: (this.form.login || '').toLowerCase().trim(),
                    whatsapp_preferencias: this.whatsappPreferencias.map((item) => ({
                        modulo: item.modulo,
                        receber: !!item.receber,
                    })),
                }
                await api().post(API_PATHS.usuarios, payload)
                this.cadastrado = true
                this.atualizar()
            } catch (err) {
                // erro tratado
            } finally {
                this.preloadAjax = false
            }
        },

        async alterar() {
            if (this.hasInvalidFields()) {
                if (typeof toastr !== 'undefined') toastr.error('Verificar os erros', 'Atenção!')
                return
            }
            if (!this.form.gestor) {
                this.form.privilegio_gestor_centro_custo = false
                this.form.privilegio_gestor_area = false
            }
            this.preloadAjax = true
            try {
                const base = getBaseUrl()
                const payload = {
                    ...this.form,
                    login: (this.form.login || '').toLowerCase().trim(),
                    whatsapp_preferencias: this.whatsappPreferencias.map((item) => ({
                        modulo: item.modulo,
                        receber: !!item.receber,
                    })),
                }
                await api().put(`${API_PATHS.usuarios}/${this.form.id}`, payload)
                this.atualizado = true
                this.atualizar()
            } catch (err) {
                // erro tratado
            } finally {
                this.preloadAjax = false
            }
        },

        abrirModalConfirmarApagar(id) {
            this.idApagar = id
            this.apagado = false
            this.$nextTick(() => {
                this.$refs.janelaConfirmar?.abrirModal?.()
            })
        },

        async apagar() {
            if (!this.idApagar) return
            this.preloadAjax = true
            try {
                const base = getBaseUrl()
                await api().delete(`${API_PATHS.usuarios}/${this.idApagar}`)
                this.apagado = true
                this.atualizar()
            } catch (err) {
                // erro tratado
            } finally {
                this.preloadAjax = false
            }
        },

        async simularUsuario(userId) {
            this.preloadAjax = true
            try {
                const base = getBaseUrl()
                const { data } = await api().put(API_PATHS.simularUsuario, {
                    user_id: userId
                })
                if (data && data.simulacao) {
                    window.location.href = `${base}/dashboard`
                }
            } catch (err) {
                // erro tratado
            } finally {
                this.preloadAjax = false
            }
        },

        async selecionaEmpresa(id) {
            this.grupoempresa = false
            this.listaPapeis = []
            this.form.grupo_id = ''
            if (!id || id === '100') return
            try {
                const { data } = await api().get(API_PATHS.buscaGrupoEmpresa(id))
                if (data) {
                    this.listaPapeis = data.papeis || []
                    this.listaCloud = data.cloud || []
                    this.grupoempresa = true
                }
            } catch (err) {
                // ignorar
            }
        },

        async onEmpresaChange() {
            await this.selecionaEmpresa(this.form.empresa_id)
            await this.carregarWhatsappPreferencias(this.form.empresa_id)
        },

        normalizarWhatsappPreferencias(preferencias) {
            return (preferencias || []).map((item) => ({
                ...item,
                receber: !!item.receber,
                habilitado_empresa: item.habilitado_empresa !== false,
            }))
        },

        async carregarWhatsappPreferencias(empresaId) {
            const id = empresaId || this.empresaId
            if (!id || Number(id) === 100) {
                this.whatsappLiberado = false
                this.whatsappPreferencias = []
                return
            }

            try {
                const { data } = await api().get(API_PATHS.whatsappPreferenciasModelo(id))
                this.whatsappLiberado = !!data.whatsapp_liberado
                this.whatsappPreferencias = this.normalizarWhatsappPreferencias(data.preferencias || [])
            } catch (err) {
                this.whatsappLiberado = false
                this.whatsappPreferencias = []
            }
        },

        async buscarGruposEmpresa(id) {
            this.controle.showCampoGrupo = false
            this.controle.dados.listaPapeis = []
            this.controle.dados.campoGrupo = ''
            if (!id) return
            try {
                const { data } = await api().get(API_PATHS.buscaGrupoEmpresa(id))
                if (data) {
                    this.controle.dados.listaPapeis = data.papeis || []
                    this.controle.showCampoGrupo = true
                }
            } catch (err) {
                // ignorar
            }
        },

        onFiltroEmpresaChange() {
            this.buscarGruposEmpresa(this.controle.dados.campoEmpresa)
            this.buscarPaginacao()
        },

        buscarPaginacao() {
            this.$refs.componente?.buscar?.()
        },

        carregou(dados) {
            if (!dados) return
            this.lista = dados.resultado || []
            if (dados.empresa !== undefined) this.empresa_id = dados.empresa
            this.listaTipoEmail = dados.tipo_email || []
            this.tipos_usuarios_gerenciais = dados.tipos_usuarios_gerenciais || []
            this.user_recebe_emailDefault = dados.formulario_vazio
                ? { ...dados.formulario_vazio }
                : null
            this.lista_tipos = dados.lista_tipos || []
            if (Number(this.empresa_id) !== 100) {
                this.controle.dados.listaPapeis = dados.lista_grupos || []
            } else {
                this.controle.dados.listaPapeis = []
            }
            this.form.user_recebe_email = this.user_recebe_emailDefault
                ? { ...this.user_recebe_emailDefault }
                : {}
            this.controle.carregando = false
        },

        carregando() {
            this.controle.carregando = true
        },

        atualizar() {
            this.$refs.componente?.buscar?.()
        }
    }
})
</script>

<template>
    <div id="componente">
        <ModalComponent :modal-pai="modal" :titulo="titulo_janela_form" size="g" :fechar="!preload" id="janelaForm" ref="modal_janelaForm">
            <template #conteudo>
                <p class="mt-2 text-center" v-if="preload"><i class="fa fa-spinner fa-pulse"></i>Carregando...</p>
                <fieldset class="mt-0" v-if="!preload">
                    <legend>Dados do Centro de Custo</legend>
                    <div class="row">
                        <div class="col-12 mb-2">
                            <label>Nome</label>
                            <input
                                class="form-control form-control-sm"
                                type="text"
                                placeholder="Informe o nome "
                                onblur="valida_campo_vazio(this, 1)"
                                v-model="form.label"
                            />
                        </div>

                        <gestor label="Gestor responsável" :model="form" :verifica="false" :hash="hash"></gestor>

                        <div class="col-12 mt-2">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" v-model="form.ativo" class="custom-control-input" id="ativo" />
                                <label class="custom-control-label" for="ativo">{{ form.ativo ? 'Ativo' : 'Inativo' }}</label>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <fieldset v-if="!preload && temFilial">
                    <legend>Associar Filial</legend>
                    <div class="row">
                        <div class="col-12 mb-2" v-for="(item, key) in form.filiais" :key="item.id">
                            <div class="custom-control custom-switch">
                                <input
                                    type="checkbox"
                                    class="custom-control-input mb-1"
                                    v-model="form.filiais[key].selecionado"
                                    :value="item.selecionado"
                                    :id="`item_${item.id}`"
                                />
                                <label class="custom-control-label" style="cursor: pointer" :for="`item_${item.id}`">
                                    {{ item.dados.razao_social }}
                                </label>
                            </div>
                        </div>
                    </div>
                </fieldset>
            </template>
            <template #rodape>
                <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="!cadastrado && !preload" @click="cadastra">
                    <i class="fa fa-save"></i> Cadastrar
                </button>

                <button v-show="cadastrado" type="button" class="btn btn-sm mr-1 btn-primary" @click="alterarForm"><i class="fa fa-save"></i> Alterar</button>
            </template>
        </ModalComponent>
        <fieldset>
            <legend>Filtro</legend>
            <form class="row" @submit.prevent="onSubmitFiltro">
                <div class="col-12 col-md-5">
                    <div class="form-group">
                        <label>Buscar</label>
                        <input
                            type="text"
                            placeholder="Buscar por nome"
                            autocomplete="off"
                            class="form-control form-control-sm"
                            :disabled="controle.carregando"
                            v-model="controle.dados.campoBusca"
                        />
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-control form-control-sm" :disabled="controle.carregando" v-model="controle.dados.campoStatus" @change="atualizar()">
                            <option value="">Todos os Status</option>
                            <option :value="true">Apenas Ativos</option>
                            <option :value="false">Apenas Inativos</option>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-md-12">
                    <button type="button" class="btn btn-sm mr-1 btn-success" :disabled="controle.carregando" @click="atualizar">
                        <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                        Atualizar
                    </button>
                    <button type="button" class="btn btn-sm mr-1 btn-secondary" @click="abrirModalFormNovo">
                        <i class="fa fa-plus"></i> Cadastrar
                    </button>
                </div>
            </form>
        </fieldset>

        <div id="conteudo">
            <p class="mt-2 text-center" v-if="controle.carregando"><i class="fa fa-spinner fa-pulse"></i> Carregando...</p>

            <div class="alert alert-warning text-center" v-show="!controle.carregando && lista.length === 0">
                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
            </div>

            <div class="table-responsive" v-show="!controle.carregando && lista.length > 0">
                <table class="tabela">
                    <thead>
                        <tr class="bg-default">
                            <td class="text-center">ID</td>
                            <td class="text-center">Nome</td>
                            <td class="text-center">Gestor</td>
                            <td class="text-center" v-if="temFilial">Possui Filial</td>
                            <td class="text-center">Ativo</td>
                            <td class="text-center">Opções</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(centrocusto, index) in lista" :key="centrocusto.id || index">
                            <td class="text-center">{{ centrocusto.id }}</td>
                            <td class="text-center">{{ centrocusto.label }}</td>
                            <td class="text-center">{{ centrocusto.gestor ? centrocusto.gestor.nome : 'Não informado' }}</td>
                            <td class="text-center" v-if="temFilial">{{ centrocusto.filiais_count > 0 ? 'Sim' : 'Não' }}</td>
                            <td class="text-center">
                                <bt-ativo :rota="`cadastro/centrocusto/${centrocusto.id}/ativa-desativa`" :model="centrocusto"></bt-ativo>
                            </td>
                            <td class="text-center">
                                <button
                                    type="button"
                                    class="btn btn-sm mr-1 btn-primary"
                                    @click="abrirModalAlterar(centrocusto.id)"
                                >
                                    <i class="fa fa-edit"></i>
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
                :url="urlPaginacao"
                :por-pagina="qntPag"
                :dados="controle.dados"
                v-on:carregou="carregou"
                v-on:carregando="carregando"
            ></controle-paginacao>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import gestor from '../../GestorAprovacao'
import controlePaginacao from '../../ControlePaginacao'
import ModalComponent from '../../Modal'

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
const preload = ref(false)
const editando = ref(false)
const cadastrado = ref(false)
const atualizado = ref(false)
const cliente_id = ref('')
const authconfiguracao = ref(null)
const formDefault = ref(null)
const lista = ref([])
const listaFilial = ref([])
const clientes = ref([])
const modal_janelaForm = ref(null)
const componente = ref(null)

const form = reactive({
    gestor_id: '',
    autocomplete_label_gestor_modal: '',
    autocomplete_label_gestor_modal_anterior: '',
    label: '',
    filiais: [],
    ativo: true
})

const urlPaginacao = `${URL_ADMIN}/cadastro/centrocusto/atualizar`
const controle = reactive({
    carregando: false,
    dados: {
        campoBusca: '',
        campoStatus: ''
    }
})

const temFilial = computed(() => authconfiguracao.value?.temFilial ?? false)

function formNovo() {
    titulo_janela_form.value = 'Cadastro Centro de Custos'
    preload.value = false
    cadastrado.value = false
    atualizado.value = false
    Object.assign(form, _.cloneDeep(formDefault.value))
    form.filiais = []
    ;(listaFilial.value || []).forEach((filial) => {
        filial.selecionado = false
        filial.cliente_filial_id = filial.id
        form.filiais.push(filial)
    })
    formReset()
}

async function cadastra() {
    form.cliente_id = cliente_id.value === 0 ? form.cliente_id : cliente_id.value
    $('#janelaForm :input:visible').trigger('blur')
    if ($('#janelaForm :input:visible.is-invalid').length) {
        mostraErro('', 'Verificar os erros')
        return false
    }
    preload.value = true
    try {
        await axios.post(`${URL_ADMIN}/cadastro/centrocusto`, form)
        modal_janelaForm.value?.fecharModal()
        mostraSucesso('', 'Centro de Custo cadastrado com sucesso')
        cadastrado.value = true
        componente.value?.buscar?.()
    } catch (error) {
        cadastrado.value = false
    } finally {
        preload.value = false
    }
}

async function alterar(centrocustoId) {
    form.cliente_id = cliente_id.value === 0 ? form.cliente_id : cliente_id.value
    cadastrado.value = true
    editando.value = true
    preload.value = true
    titulo_janela_form.value = 'Alterando Centro de Custo'
    formReset()
    Object.assign(form, _.cloneDeep(formDefault.value))
    form.filiais = []

    try {
        const response = await axios.get(`${URL_ADMIN}/cadastro/centrocusto/${centrocustoId}/editar`)
        const data = response.data
        Object.assign(form, data)

        if (temFilial.value) {
            const fl = []
            ;(listaFilial.value || []).forEach((filial) => {
                if (_.find(data.filiais, { cliente_filial_id: filial.id, ativo: true })) {
                    filial.selecionado = true
                    filial.cliente_filial_id = filial.id
                    fl.push(filial)
                } else {
                    filial.selecionado = false
                    filial.cliente_filial_id = filial.id
                    fl.push(filial)
                }
            })
            form.filiais = fl
        }

        editando.value = true
        setupCampo()
    } catch (error) {
        // silencioso
    } finally {
        preload.value = false
    }
}

async function alterarForm() {
    form.cliente_id = cliente_id.value === 0 ? form.cliente_id : cliente_id.value
    $('#janelaForm :input:visible').trigger('blur')
    if ($('#janelaForm :input:visible.is-invalid').length) {
        mostraErro('', 'Verificar os erros')
        return false
    }
    preload.value = true
    try {
        await axios.put(`${URL_ADMIN}/cadastro/centrocusto/${form.id}`, form)
        modal_janelaForm.value?.fecharModal()
        mostraSucesso('', 'Centro de Custo Alterado com sucesso')
        componente.value?.buscar?.()
    } catch (error) {
        cadastrado.value = false
    } finally {
        preload.value = false
    }
}

function carregou(dados) {
    lista.value = dados.items
    clientes.value = dados.clientes ?? []
    listaFilial.value = dados.listaFilial ?? []
    controle.carregando = false
}

function carregando() {
    controle.carregando = true
}

function atualizar() {
    if (componente.value) {
        componente.value.atual = 1
        componente.value.buscar?.()
    }
}

function abrirModalFormNovo() {
    formNovo()
    modal_janelaForm.value?.abrirModal()
}

function abrirModalAlterar(centrocustoId) {
    alterar(centrocustoId)
    modal_janelaForm.value?.abrirModal()
}

function onSubmitFiltro() {
    componente.value?.buscar?.()
}

onMounted(async () => {
    try {
        const { data } = await axios.get(`${URL_ADMIN}/usuario/autenticado/`)
        authconfiguracao.value = data
    } catch (error) {
        // silencioso
    }
    formDefault.value = _.cloneDeep(form)
    atualizar()
})
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

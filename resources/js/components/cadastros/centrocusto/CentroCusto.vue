<template>
    <div id="componente">
        <modal :modal-pai="modal" :titulo="titulo_janela_form" size="g" :fechar="!preload" id="janelaForm" ref="modal_janelaForm">
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
        </modal>
        <fieldset>
            <legend>Filtro</legend>
            <form class="row" @submit.prevent="this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null">
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
                    <button type="button" class="btn btn-sm mr-1 btn-secondary" @click="formNovo(); $refs.modal_janelaForm && $refs.modal_janelaForm.abrirModal()">
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
                                    @click="alterar(centrocusto.id); $refs.modal_janelaForm && $refs.modal_janelaForm.abrirModal()"
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

<script>
import gestor from '../../GestorAprovacao'
import controlePaginacao from '../../ControlePaginacao'
import modal from '../../Modal'
import editor from '@tinymce/tinymce-vue'
import configuracoes from '../../../mixins/Configuracoes'

export default {
    mixins: [configuracoes],
    components: {
        modal,
        controlePaginacao,
        editor,
        gestor
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
        this.atualizar()
        this.formDefault = _.cloneDeep(this.form)
    },
    data() {
        return {
            hash: String(Math.random()).substr(2),
            titulo_janela_form: 'Centro de Custos',

            preload: false,
            editando: false,
            cadastrado: false,
            atualizado: false,

            cliente_id: '',

            form: {
                gestor_id: '',
                autocomplete_label_gestor_modal: '',
                autocomplete_label_gestor_modal_anterior: '',
                label: '',
                filiais: [],
                ativo: true
            },
            formDefault: null,

            //Paginacao
            lista: [],
            listaFilial: [],

            urlPaginacao: `${URL_ADMIN}/cadastro/centrocusto/atualizar`,
            controle: {
                carregando: false,
                dados: {
                    campoBusca: '',
                    campoStatus: ''
                }
            }
        }
    },
    methods: {
        formNovo() {
            this.titulo_janela_form = 'Cadastro Centro de Custos'
            this.preload = false
            this.cadastrado = false
            this.atualizado = false
            this.form = _.cloneDeep(this.formDefault) //copia

            this.listaFilial.forEach((filial) => {
                filial.selecionado = false
                filial.cliente_filial_id = filial.id
                this.form.filiais.push(filial)
            })

            formReset()
        },
        cadastra() {
            this.form.cliente_id = this.cliente_id === 0 ? this.form.cliente_id : this.cliente_id
            $('#janelaForm :input:visible').trigger('blur')
            if ($('#janelaForm :input:visible.is-invalid').length) {
                mostraErro('', 'Verificar os erros')
                return false
            }
            this.preload = true
            axios
                .post(`${URL_ADMIN}/cadastro/centrocusto`, this.form)
                .then((res) => {
                    this.$refs.modal_janelaForm && this.$refs.modal_janelaForm.fecharModal()
                    mostraSucesso('', 'Centro de Custo cadastrado com sucesso')
                    this.cadastrado = true
                    this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
                    this.preload = false
                })
                .catch((error) => {
                    this.cadastrado = false
                    this.preload = false
                })
        },
        alterar(centrocusto) {
            this.form.cliente_id = this.cliente_id === 0 ? this.form.cliente_id : this.cliente_id
            this.cadastrado = true
            this.editando = true
            this.preload = true
            this.titulo_janela_form = 'Alterando Centro de Custo'
            formReset()

            this.form = _.cloneDeep(this.formDefault) //copia

            axios
                .get(`${URL_ADMIN}/cadastro/centrocusto/${centrocusto}/editar`)
                .then((response) => {
                    let data = response.data
                    Object.assign(this.form, data)

                    if (this.temFilial) {
                        let fl = []
                        this.listaFilial.forEach((filial) => {
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
                        this.form.filiais = fl
                    }

                    this.editando = true
                    this.preload = false
                    setupCampo()
                })
                .catch((error) => (this.preload = false))
        },
        alterarForm() {
            this.form.cliente_id = this.cliente_id === 0 ? this.form.cliente_id : this.cliente_id
            $('#janelaForm :input:visible').trigger('blur')
            if ($('#janelaForm :input:visible.is-invalid').length) {
                mostraErro('', 'Verificar os erros')
                return false
            }
            this.preload = true
            axios
                .put(`${URL_ADMIN}/cadastro/centrocusto/${this.form.id}`, this.form)
                .then((res) => {
                    this.$refs.modal_janelaForm && this.$refs.modal_janelaForm.fecharModal()
                    mostraSucesso('', 'Centro de Custo Alterado com sucesso')
                    this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
                    this.preload = false
                })
                .catch((error) => {
                    this.cadastrado = false
                    this.preload = false
                })
        },
        carregou(dados) {
            this.lista = dados.items
            this.clientes = dados.clientes
            this.listaFilial = dados.listaFilial
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

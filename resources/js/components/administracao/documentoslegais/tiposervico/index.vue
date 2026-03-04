<template>
    <div id="componente">
        <modal :modal-pai="modal" :titulo="titulo_janela_form" :fechar="!preload" id="janelaForm">
            <template #conteudo>
                <p class="mt-2 text-center" v-if="preload"><i class="fa fa-spinner fa-pulse"></i>Carregando...</p>
                <fieldset v-if="!preload">
                    <legend>Cadastro Tipo de Serviço</legend>
                    <div class="row">
                        <div class="col-12">
                            <label>Nome</label>
                            <input class="form-control" type="text" placeholder="Informe o nome" onblur="valida_campo_vazio(this, 1)" v-model="form.titulo" />
                        </div>
                        <br /><br />
                        <div class="col-12 mt-3">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" v-model="form.ativo" class="custom-control-input" id="ativo" />
                                <label class="custom-control-label" for="ativo">{{ form.ativo ? 'Ativo' : 'Inativo' }}</label>
                            </div>
                        </div>
                    </div>
                </fieldset>
            </template>
            <template #rodape>
                <button type="button" class="btn btn-sm btn-primary" v-show="!editando && !preload" @click="cadastra">
                    <i class="fa fa-save"></i> Cadastrar
                </button>

                <button v-show="editando && !preload" type="button" class="btn btn-sm btn-primary" @click="alterarForm">
                    <i class="fa fa-save"></i> Alterar
                </button>
            </template>
        </modal>
        <fieldset>
            <legend>Filtro</legend>
            <form class="row" @submit.prevent="this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null">
                <div class="col-12 col-md-4">
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
                <div class="col-12 col-md-12">
                    <button type="button" class="btn btn-sm btn-success" :disabled="controle.carregando" @click="atualizar">
                        <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                        Atualizar
                    </button>
                    <button
                        type="button"
                        class="btn btn-sm btn-secondary"
                        v-if="permissoes.insert"
                        @click="formNovo"
                        data-toggle="modal"
                        data-target="#janelaForm"
                    >
                        <i class="fa fa-plus"></i> Cadastrar
                    </button>
                </div>
            </form>
        </fieldset>

        <div id="conteudo">
            <preload class="mt-2 text-center" v-if="controle.carregando"></preload>

            <div class="alert alert-warning text-center" v-show="!controle.carregando && lista.length === 0">
                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
            </div>

            <div class="table-responsive" v-show="!controle.carregando && lista.length > 0">
                <table class="tabela">
                    <thead>
                        <tr class="bg-default">
                            <td class="text-center">Nome</td>
                            <td class="text-center">Ativo</td>
                            <td class="text-center">Opções</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="tiposervico in lista">
                            <td class="text-center">{{ tiposervico.titulo }}</td>
                            <td class="text-center">
                                <bt-ativo :rota="`administracao/documentoslegais/tiposervico/${tiposervico.id}/ativa-desativa`" :model="tiposervico"></bt-ativo>
                            </td>
                            <td class="text-center">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-primary"
                                    v-if="permissoes.update"
                                    @click="alterar(tiposervico.id)"
                                    data-toggle="modal"
                                    data-target="#janelaForm"
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
import controlePaginacao from '../../../ControlePaginacao'
import modal from '../../../Modal'
import editor from '@tinymce/tinymce-vue'
import Validacoes from '../../../../mixins/Validacoes'

export default {
    name: 'tiposervico',
    mixins: [Validacoes],

    components: {
        modal,
        controlePaginacao,
        editor
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
            titulo_janela_form: 'Tipo Serviço',

            preload: false,
            editando: false,
            cadastrado: false,
            atualizado: false,

            form: {
                titulo: '',
                ativo: true
            },
            formDefault: null,

            //Paginacao
            lista: [],
            permissoes: [],

            urlPaginacao: `${URL_ADMIN}/administracao/documentoslegais/tiposervico/atualizar`,

            controle: {
                carregando: false,
                dados: {
                    campoBusca: ''
                }
            }
        }
    },
    methods: {
        formNovo() {
            this.titulo_janela_form = 'Cadastro Tipo Serviço'
            this.preload = false
            this.editando = false
            this.atualizado = false
            this.form = _.cloneDeep(this.formDefault) //copia
            formReset()
        },
        cadastra() {
            this.validaBlur()
            this.$nextTick(() => {
                $('#janelaForm :input:visible').trigger('blur')
                if ($('#janelaForm :input:visible.is-invalid').length) {
                    mostraErro('', 'Verificar os erros')
                    return false
                }
                this.preload = true
                axios
                    .post(`${URL_ADMIN}/administracao/documentoslegais/tiposervico`, this.form)
                    .then((res) => {
                        $('#janelaForm').modal('hide')
                        mostraSucesso('', 'Tipo de serviço cadastrado com sucesso')
                        this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
                        this.preload = false
                    })
                    .catch((error) => {
                        this.preload = false
                    })
            })
        },
        alterar(tiposervico) {
            this.editando = true
            this.titulo_janela_form = 'Alterando tipo de serviço'
            formReset()

            this.form = _.cloneDeep(this.formDefault) //copia

            axios
                .get(`${URL_ADMIN}/administracao/documentoslegais/tiposervico/${tiposervico}`)
                .then((response) => {
                    Object.assign(this.form, response.data)
                    this.editando = true
                    setupCampo()
                })
                .catch((error) => (this.preloadAjax = false))
        },
        alterarForm() {
            this.validaBlur()
            this.$nextTick(() => {
                $('#janelaForm :input:visible').trigger('blur')
                if ($('#janelaForm :input:visible.is-invalid').length) {
                    mostraErro('', 'Verificar os erros')
                    return false
                }
                this.preload = true
                axios
                    .put(`${URL_ADMIN}/administracao/documentoslegais/tiposervico/${this.form.id}`, this.form)
                    .then((res) => {
                        $('#janelaForm').modal('hide')
                        mostraSucesso('', 'Tipo de serviço alterado com sucesso')
                        this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
                        this.preload = false
                    })
                    .catch((error) => {
                        this.preload = false
                    })
            })
        },
        carregou(dados) {
            this.lista = dados.items
            this.permissoes = dados.permissoes
            this.controle.carregando = false
        },
        carregando() {
            this.controle.carregando = true
        },
        atualizar() {
            this.$refs && this && this && this.$refs && this.$refs.componente && (this.$refs.componente.atual = 1)
            this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
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

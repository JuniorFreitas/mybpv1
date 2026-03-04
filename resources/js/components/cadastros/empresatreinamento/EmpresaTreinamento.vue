<template>
    <div id="componenteEmpresaTreinamento">
        <modal id="janelaCadastrar" :titulo="titulo_janela" :fechar="!preload" :size="90">
            <template #conteudo>
                <preload v-show="preload"></preload>
                <div v-if="!preload && !cadastrado">
                    <fieldset>
                        <legend>Informações</legend>
                        <div class="row">
                            <div class="col-12 col-md-12">
                                <div class="form-group">
                                    <label>Nome</label>
                                    <input v-model="form.nome" class="form-control" type="text" onblur="valida_campo_vazio(this, 1)" />
                                </div>
                            </div>
                            <div class="col-12 col-md-12">
                                <div class="form-group">
                                    <label>Endereço</label>
                                    <input v-model="form.endereco" class="form-control" type="text" onblur="valida_campo_vazio(this, 1)" />
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label>Ativo</label>
                                    <select
                                        class="form-control form-control-sm"
                                        onblur="valida_campo_vazio(this, 1)"
                                        onchange="valida_campo_vazio(this, 1)"
                                        v-model="form.ativo"
                                    >
                                        <option :value="''">Selecione</option>
                                        <option :value="true">Sim</option>
                                        <option :value="false">Não</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </template>
            <template #rodape>
                <button type="button" class="btn btn-sm btn-primary" v-show="editando" @click="alterarformEmpresaTreinamento()">Salvar</button>
                <button type="button" class="btn btn-sm btn-primary" v-show="!editando" @click="cadastrar()">Cadastrar</button>
            </template>
        </modal>

        <!-- Filtro -->
        <fieldset>
            <legend>Filtro</legend>
            <form class="row" @submit.prevent="this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null">
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

                <div class="col-12 col-md-12">
                    <button type="button" class="btn btn-sm btn-success" :disabled="controle.carregando" @click="atualizar">
                        <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                        Atualizar
                    </button>

                    <button
                        type="button"
                        class="btn btn-sm btn-primary"
                        :disabled="controle.carregando"
                        @click="formNovo"
                        data-toggle="modal"
                        data-target="#janelaCadastrar"
                    >
                        <i class="fa fa-plus"></i> Empresa Treinamento
                    </button>
                </div>
            </form>
        </fieldset>

        <div id="conteudo">
            <p class="mt-2 text-center" v-if="controle.carregando">
                <preload></preload>
            </p>

            <div class="alert alert-warning text-center" v-show="!controle.carregando && lista.length === 0">
                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
            </div>

            <div class="table-responsive" v-show="!controle.carregando && lista.length > 0">
                <table class="tabela">
                    <thead>
                        <tr class="bg-default">
                            <td class="text-center">Nº</td>
                            <td class="text-center">Nome</td>
                            <td class="text-center">Endereco</td>
                            <td class="text-center">Ativo</td>
                            <td class="text-center">Ação</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in lista">
                            <td class="text-center">{{ item.id }}</td>
                            <td class="text-center">{{ item.nome }}</td>
                            <td class="text-center">{{ item.endereco }}</td>
                            <td class="text-center">
                                <bt-ativo :rota="`cadastro/empresatreinamento/${item.id}/ativa-desativa`" :model="item"></bt-ativo>
                            </td>
                            <td class="text-center">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-primary mb-1"
                                    data-toggle="modal"
                                    data-target="#janelaCadastrar"
                                    @click="alterarEmpresaTreinamento(item.id)"
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
import controlePaginacao from '../../ControlePaginacao'
import modal from '../../Modal'

export default {
    components: {
        modal,
        controlePaginacao
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
        this.atualizar()
        this.formDefault = _.cloneDeep(this.form)
    },
    data() {
        return {
            hash: String(Math.random()).substr(2),
            titulo_janela: '',

            preload: false,
            editando: false,
            cadastrado: false,

            form: {
                nome: '',
                endereco: '',
                ativo: ''
            },

            formDefault: null,

            lista: [],
            listaClientes: [],

            urlPaginacao: `${URL_ADMIN}/cadastro/empresatreinamento/atualizar`,
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
            this.form = _.cloneDeep(this.formDefault) //copia
            this.titulo_janela = 'Empresa Treinamento'
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
                .post(`${URL_ADMIN}/cadastro/empresatreinamento`, this.form)
                .then((res) => {
                    if (res.status === 201) {
                        $('#janelaCadastrar').modal('hide')
                        mostraSucesso('', 'Empresa Treinamento cadastrado com sucesso')
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
        alterarEmpresaTreinamento(empresatreinamento) {
            this.cadastrado = false
            this.editando = true
            this.titulo_janela = 'Alterando EmpresaTreinamento'
            formReset()

            this.form = _.cloneDeep(this.formDefault) //copia

            axios
                .get(`${URL_ADMIN}/cadastro/empresatreinamento/${empresatreinamento}/editar`)
                .then((response) => {
                    Object.assign(this.form, response.data)
                    this.editando = true
                    setupCampo()
                })
                .catch((error) => (this.preloadAjax = false))
        },
        alterarformEmpresaTreinamento() {
            formReset()
            $('#janelaCadastrar :input:enabled').trigger('blur')

            if ($('#janelaCadastrar :input:enabled.is-invalid').length) {
                mostraErro('', 'Verificar os erros')
                return false
            }

            this.preload = true

            axios
                .put(`${URL_ADMIN}/cadastro/empresatreinamento/${this.form.id}`, this.form)
                .then((response) => {
                    $('#janelaCadastrar').modal('hide')
                    mostraSucesso('', 'Empresa Treinamento atualizado com sucesso')
                    this.preload = false
                    this.atualizado = true
                    this.atualizar()
                })
                .catch((error) => (this.preload = false))
        },
        carregou(dados) {
            this.lista = dados.items
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

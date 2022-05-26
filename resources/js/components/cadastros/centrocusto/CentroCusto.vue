<template>
    <div id="componente">

        <modal :modal-pai="modal" :titulo="titulo_janela_form" :fechar="!preload" id="janelaForm">
            <template slot="conteudo">
                <p class=" mt-2 text-center" v-if="preload"><i class="fa fa-spinner fa-pulse"></i>Carregando...</p>
                <fieldset v-if="!preload">
                    <legend>Cadastro de Centro de Custo</legend>
                    <div class="row">
                        <div class="col-12">
                            <label>Nome</label>
                            <input class="form-control" type="text"
                                   onblur="valida_campo_vazio(this,1)" v-model="form.label">
                        </div>
                        <div class="col-12">
                            <div class="switchToggle">
                                <input type="checkbox" v-model="form.ativo" id="switch">
                                <label for="switch">Ativo</label>
                            </div>
                        </div>
                    </div>
                </fieldset>
            </template>
            <template slot="rodape">
                <button type="button" class="btn btn-sm btn-primary" v-show="!cadastrado && !preload"
                        @click="cadastra">
                    <i class="fa fa-save"></i>Cadastrar
                </button>

                <button v-show="cadastrado" type="button" class="btn btn-sm btn-primary"
                        @click="alterarForm">
                    <i class="fa fa-save"></i> Alterar
                </button>
            </template>
        </modal>
        <fieldset>
            <legend>Filtro</legend>
            <form class="row" @submit.prevent="$refs.componente.buscar()">
                <div class="col-12 col-md-3">
                    <div class="form-group">
                        <div class="input-group">
                        <span class="input-group-prepend">
                            <span class="input-group-text">Buscar</span>
                        </span>
                            <input type="text"
                                   placeholder="Buscar por conteudo"
                                   autocomplete="off"
                                   class="form-control" :disabled="controle.carregando"
                                   v-model="controle.dados.campoBusca">
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-12">
                    <button type="button" class="btn btn-sm btn-success" :disabled="controle.carregando"
                            @click="atualizar"><i
                        :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                        Atualizar
                    </button>
                    <button type="button" class="btn btn-sm btn-secondary"
                            @click="formNovo"
                            data-toggle="modal"
                            data-target="#janelaForm">
                        <i class="fa fa-plus"></i> Cadastrar Centro de Custo
                    </button>
                </div>
            </form>
        </fieldset>

        <div id="conteudo">

            <p class=" mt-2 text-center" v-if="controle.carregando">
                <i class="fa fa-spinner fa-pulse"></i> Carregando...
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
                        <td class="text-center">Ativo</td>
                        <td class="text-center">Opções</td>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="centrocusto in lista">
                        <td class="text-center">{{centrocusto.id}}</td>
                        <td class="text-center">{{centrocusto.label}}</td>
                        <td class="text-center">
                            <bt-ativo :rota="`cadastro/centrocusto/${centrocusto.id}/ativa-desativa`"
                                      :model="centrocusto"></bt-ativo>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-primary"
                                    @click="alterar(centrocusto.id)"
                                    data-toggle="modal"
                                    data-target="#janelaForm">
                                <i class="fa fa-edit"></i>
                            </button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <controle-paginacao class="d-flex justify-content-center" id="controle" ref="componente"
                                :url="urlPaginacao" :por-pagina="qntPag"
                                :dados="controle.dados"
                                v-on:carregou="carregou" v-on:carregando="carregando"></controle-paginacao>
        </div>

    </div>
</template>

<script>
import controlePaginacao from '../../ControlePaginacao';
import modal from '../../Modal';
import editor from '@tinymce/tinymce-vue';

export default {
    components: {
        modal,
        controlePaginacao,
        editor,
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
        modal: { // modal Pai
            type: String,
            required: false,
            default: ''
        },
    },

    mounted() {
        this.atualizar();
        this.formDefault = _.cloneDeep(this.form);
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
                label: '',
                empresa_id: '',
                ativo: true,
            },
            formDefault: null,

            //Paginacao
            lista: [],
            clientes: [],

            urlPaginacao: `${URL_ADMIN}/cadastro/centrocusto/atualizar`,
            controle: {
                carregando: false,
                dados: {
                    campoBusca: '',
                },
            },
        }
    },
    methods: {
        formNovo() {
            this.titulo_janela_form = 'Cadastro Centro de Custos';
            this.preload = false;
            this.cadastrado = false;
            this.atualizado = false;
            this.form = _.cloneDeep(this.formDefault) //copia
            formReset();
        },
        cadastra() {
            this.form.cliente_id = this.cliente_id === 0 ? this.form.cliente_id : this.cliente_id;
            $('#janelaForm :input:visible').trigger('blur');
            if ($('#janelaForm :input:visible.is-invalid').length) {
                mostraErro('', 'Verificar os erros');
                return false;
            }
            this.preload = true;
            axios.post(`${URL_ADMIN}/cadastro/centrocusto`, this.form)
                .then((res) => {
                    $('#janelaForm').modal('hide');
                    mostraSucesso('', 'Centro de Custo cadastrado com sucesso');
                    this.cadastrado = true;
                    this.$refs.componente.buscar();
                    this.preload = false;
                })
                .catch(error => {
                    this.cadastrado = false;
                    this.preload = false;
                });
        },
        alterar(centrocusto) {
            this.form.cliente_id = this.cliente_id === 0 ? this.form.cliente_id : this.cliente_id;
            this.cadastrado = true;
            this.editando = true;
            this.titulo_janela_form = "Alterando Centro de Custo";
            formReset();

            this.form = _.cloneDeep(this.formDefault) //copia

            axios.get(`${URL_ADMIN}/cadastro/centrocusto/${centrocusto}/editar`)
                .then(response => {
                    Object.assign(this.form, response.data);
                    // this.form.nome = data.nome
                    this.editando = true;
                    setupCampo();
                }).catch(
                error => (this.preloadAjax = false)
            );

        },
        alterarForm() {
            this.form.cliente_id = this.cliente_id === 0 ? this.form.cliente_id : this.cliente_id;
            $('#janelaForm :input:visible').trigger('blur');
            if ($('#janelaForm :input:visible.is-invalid').length) {
                mostraErro('', 'Verificar os erros');
                return false;
            }
            this.preload = true;
            axios.put(`${URL_ADMIN}/cadastro/centrocusto/${this.form.id}`, this.form)
                .then((res) => {
                    $('#janelaForm').modal('hide');
                    mostraSucesso('', 'Centro de Custo Alterado com sucesso');
                    this.cadastrado = true;
                    this.$refs.componente.buscar();
                    this.preload = false;
                })
                .catch(error => {
                    this.cadastrado = false;
                    this.preload = false;
                });
        },
        carregou(dados) {
            this.lista = dados.items;
            this.clientes = dados.clientes;
            this.controle.carregando = false;
        },
        carregando() {
            this.controle.carregando = true;
        },
        atualizar() {
            this.$refs.componente.atual = 1;
            this.$refs.componente.buscar();
        },
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
    padding: .5rem .8rem;
    background-color: #f4f4f4;
    border-radius: .5rem;
}
</style>

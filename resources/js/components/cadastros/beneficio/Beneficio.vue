<template>
    <div id="componenteBeneficio">

        <modal :modal-pai="modal" :titulo="titulo_janela_tipo_beneficio" :fechar="!preloadTipoBeneficio"
               id="janelaFormTipoBeneficio">
            <template slot="conteudo">
                <div class="row">
                    <div class="col-12 col-md-12">
                        <button type="button" class="btn btn-sm btn-success" @click="atualizar"><i
                            :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                            Atualizar
                        </button>

                        <button type="button" class="btn btn-sm btn-secondary"
                                @click="formNovoTipo"
                                data-toggle="modal"
                                data-target="#janelaFormTipo">
                            <i class="fa fa-plus"></i> Cadastrar Tipo de Beneficio
                        </button>
                    </div>
                </div>
                <br>
                <p class=" mt-2 text-center" v-if="controle.carregando && atualizado">
                    <i class="fa fa-spinner fa-pulse"></i> Carregando...
                </p>

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
                        <tr v-for="item in tipos">
                            <td class="text-center">{{item.nome}}</td>
                            <td class="text-center">
                                <bt-ativo :rota="`cadastro/beneficios/${item.id}/ativa-desativa`"
                                          :model="item"></bt-ativo>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-primary"
                                        @click="alterarTipo(item.id)"
                                        data-toggle="modal"
                                        data-target="#janelaFormTipo">
                                    <i class="fa fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </template>
        </modal>

        <modal :modal-pai="modal" :titulo="titulo_janela_form_tipo" :fechar="!preloadTipo" id="janelaFormTipo">
            <template slot="conteudo">
                <p class=" mt-2 text-center" v-if="preloadTipo"><i class="fa fa-spinner fa-pulse"></i>Carregando...</p>
                <fieldset v-if="!preloadTipo">
                    <legend>Cadastro de Tipo</legend>
                    <div class="row">
                        <div class="col-12">
                            <label>Nome</label>
                            <input class="form-control" type="text"
                                   onblur="valida_campo_vazio(this,1)" v-model="formTipo.nome">
                        </div>

                        <div class="col-12" v-if="cliente_id === 0">
                            <label>Cliente</label>
                            <select v-model="formTipo.cliente_id"
                                    onchange="valida_campo_vazio(this,1)"
                                    onblur="valida_campo_vazio(this,1)" class="custom-select">
                                <option value="">Selecione</option>
                                <option v-for="item in clientes" :value="item.id">{{ item.nome_fantasia }}
                                </option>
                            </select>
                        </div>

                        <div class="col-12">
                            <div class="switchToggle">
                                <input type="checkbox" v-model="formTipo.ativo" id="switch">
                                <label for="switch">Ativo</label>
                            </div>
                        </div>
                    </div>
                </fieldset>
            </template>
            <template slot="rodape">
                <button type="button" class="btn btn-sm btn-primary" v-show="!cadastrado && !preloadTipo"
                        @click="cadastraTipo">
                    <i class="fa fa-save"></i>Cadastrar
                </button>

                <button v-show="cadastrado" type="button" class="btn btn-sm btn-primary"
                        @click="alterarformTipo">
                    <i class="fa fa-save"></i> Alterar
                </button>
            </template>
        </modal>

        <modal :modal-pai="modal" :titulo="titulo_janela_form_beneficio" id="janelaFormBeneficio"
               :size="65">
            <template slot="conteudo">
                <p class=" mt-2 text-center" v-if="preload"><i class="fa fa-spinner fa-pulse"></i>Carregando...</p>
                <div class="alert alert-success alert-dismissible" v-show="cadastrado">
                    <h6 class="text-center"><i class="icon fa fa-check"></i> Cadastrado com sucesso!</h6>
                </div>
                <div v-if="!preload && !cadastrado">
                    <fieldset>
                        <legend>Informações</legend>
                        <div class="row">
                            <div class="col-12">
                                <label>Nome</label>
                                <input type="text" v-model="form.nome" class="form-control"
                                       onblur="valida_campo_vazio(this,1)">
                            </div>
                            <div class="col-12" v-if="cliente_id === 0">
                                <label>Cliente</label>
                                <select v-model="form.cliente_id"
                                        onchange="valida_campo_vazio(this,1)"
                                        onblur="valida_campo_vazio(this,1)" class="custom-select">
                                    <option value="">Selecione</option>
                                    <option v-for="item in clientes" :value="item.id">{{ item.nome_fantasia }}
                                    </option>
                                </select>
                            </div>

                            <div class="col-6">
                                <label>Tipo</label>
                                <select v-model="form.tipobeneficio_id"
                                        onchange="valida_campo_vazio(this,1)"
                                        onblur="valida_campo_vazio(this,1)" class="custom-select">
                                    <option value="">Selecione</option>
                                    <option v-for="item in tiposAtivos" :value="item.id">{{ item.nome }}</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label>Valor</label>
                                <input type="text" v-model="form.valor" v-mascara:dinheiro class="form-control"
                                       onblur="valida_campo_vazio(this,1)">
                            </div>
                            <div class="col-6">
                                <label>Aplicação</label>
                                <select v-model="form.aplicacao"
                                        onchange="valida_campo_vazio(this,1)"
                                        onblur="valida_campo_vazio(this,1)" class="custom-select">
                                    <option value="">Selecione</option>
                                    <option value="reais">Em Reais</option>
                                    <option value="percentual">Percentual do Salário</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label>Periodicidade</label>
                                <select v-model="form.periodicidade"
                                        onchange="valida_campo_vazio(this,1)"
                                        onblur="valida_campo_vazio(this,1)" class="custom-select">
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
                                <input type="text" v-model="form.valor_descontado" v-mascara:dinheiro
                                       class="form-control"
                                       onblur="valida_campo_vazio(this,1)">
                            </div>

                            <div class="col-6">
                                <label>Opção de Desconto</label>
                                <select v-model="form.opcao_desconto"
                                        onchange="valida_campo_vazio(this,1)"
                                        onblur="valida_campo_vazio(this,1)" class="custom-select">
                                    <option value="">Selecione</option>
                                    <option value="fixo">Valor Fixo</option>
                                    <option value="percentual">Percentual do Salário</option>
                                </select>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </template>
            <template slot="rodape">
                <button type="button" class="btn btn-sm btn-primary" v-show="!editando"
                        @click="cadastrar()">
                    Cadastrar
                </button>
                <button type="button" class="btn btn-sm btn-primary" v-show="editando"
                        @click="alterarformBeneficio()">
                    Alterar
                </button>
            </template>
        </modal>


        <!-- Filtro -->
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

                    <button type="button" class="btn btn-sm btn-primary" :disabled="controle.carregando"
                            @click="formNovo"
                            data-toggle="modal"
                            data-target="#janelaFormBeneficio">
                        <i class="fa fa-plus"></i> Cadastrar Benefício
                    </button>

                    <button type="button" class="btn btn-sm btn-secondary"
                            data-toggle="modal"
                            data-target="#janelaFormTipoBeneficio">
                        <i class="fa fa-plus"></i> Tipo de Benefício
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
                        <td class="text-center" v-if="cliente_id === 0">Cliente</td>
                        <td class="text-center">Tipo do Beneficio</td>
                        <td class="text-center">Valor do Benefício</td>
                        <td class="text-center">Periodicidade</td>
                        <td class="text-center">Opção Desconto</td>
                        <td class="text-center">Valor Descontado</td>
                        <td class="text-center">Opções</td>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="beneficio in lista">
                        <td class="text-center">{{beneficio.id}}</td>
                        <td class="text-center">{{beneficio.nome}}</td>
                        <td class="text-center" v-if="cliente_id === 0">{{beneficio.cliente.nome_fantasia}}</td>
                        <td class="text-center">{{beneficio.tipo_beneficio.nome}}</td>
                        <td class="text-center">{{"R$ "+beneficio.valor_format}}</td>
                        <td class="text-center">{{beneficio.periodicidade}}</td>
                        <td class="text-center">{{beneficio.opcao_desconto}}</td>
                        <td class="text-center">{{"R$ "+beneficio.valordescontado_format}}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-primary"
                                    @click="alterarBeneficio(beneficio.id)"
                                    data-toggle="modal"
                                    data-target="#janelaFormBeneficio">
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
        this.usuarioAutenticado();
        this.atualizar();
        this.formDefault = _.cloneDeep(this.form);
        this.formTipoDefault = _.cloneDeep(this.formTipo);
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
                opcao_desconto: '',
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

            urlPaginacao: `${URL_ADMIN}/cadastro/beneficios/atualizar`,
            controle: {
                carregando: false,
                dados: {
                    campoBusca: '',
                    campoFiltro: '',
                },
            },
        }
    },
    methods: {
        formNovo() {
            this.form = _.cloneDeep(this.formDefault) //copia
            this.titulo_janela_form_beneficio = 'Cadastro de Benefício';
            this.cadastrado = false;
            this.finalizado = false;
            this.atualizado = false;
            this.editando = false;

            formReset();
            setupCampo();
        },
        cadastrar() {

            this.form.cliente_id = this.cliente_id === 0 ? this.form.cliente_id : this.cliente_id;

            $('#janelaFormBeneficio :input:visible').trigger('blur');
            if ($('#janelaFormBeneficio :input:visible.is-invalid').length) {
                mostraErro('', 'Verificar os erros');
                return false;
            }
            this.preloadTipo = true;
            axios.post(`${URL_ADMIN}/cadastro/beneficios`, this.form)
                .then(res => {
                    if (res.status === 201) {
                        $('#janelaFormBeneficio').modal('hide');
                        mostraSucesso('', 'Benefício Cadastrado com sucesso');
                        this.preloadTipo = false;
                        this.cadastrado = true;
                        this.atualizar();
                    } else {
                        this.cadastrado = false;
                        this.preloadTipo = false;
                    }
                })
                .catch(error => {
                    this.cadastrado = false;
                    this.preloadTipo = false;
                });
        },

        formNovoTipo() {
            this.formTipo = _.cloneDeep(this.formTipoDefault) //copia
            this.titulo_janela_form_tipo = 'Novo Tipo de Benefício';
            this.preloadTipo = false;
            this.cadastrado = false;
            this.atualizado = false;
            formReset();
        },
        cadastraTipo() {
            this.formTipo.cliente_id = this.cliente_id === 0 ? this.formTipo.cliente_id : this.cliente_id;
            $('#janelaFormTipo :input:visible').trigger('blur');
            if ($('#janelaFormTipo :input:visible.is-invalid').length) {
                mostraErro('', 'Verificar os erros');
                return false;
            }
            this.preloadTipo = true;
            axios.post(`${URL_ADMIN}/cadastro/beneficios/cadastro-tipo`, this.formTipo)
                .then((res) => {
                    $('#janelaFormTipo').modal('hide');
                    mostraSucesso('', 'Tipo cadastrado com sucesso');
                    this.cadastrado = true;
                    this.$refs.componente.buscar();
                    this.preloadTipo = false;
                })
                .catch(error => {
                    this.cadastrado = false;
                    this.preloadTipo = false;
                });
        },
        alterarBeneficio(beneficio) {

            this.form.cliente_id = this.cliente_id === 0 ? this.form.cliente_id : this.cliente_id;

            this.cadastrado = false;
            this.editando = true;
            this.tituloJanela = "Alterando Benefício";
            formReset();

            this.form = _.cloneDeep(this.formDefault) //copia

            axios.get(`${URL_ADMIN}/cadastro/beneficios/${beneficio}/editar`)
                .then(response => {
                    Object.assign(this.form, response.data);
                    // this.formTipo.nome = data.nome
                    this.editando = true;
                    setupCampo();
                }).catch(
                error => (this.preloadAjax = false)
            );

        },
        alterarformBeneficio() {
            formReset();
            $('#janelaFormBeneficio :input:enabled').trigger('blur');

            if ($('#janelaFormBeneficio :input:enabled.is-invalid').length) {
                mostraErro('', 'Verificar os erros');
                return false;
            }

            this.preloadAjax = true;

            axios.put(`${URL_ADMIN}/cadastro/beneficios/${this.form.id}`, this.form).then(response => {
                $('#janelaFormBeneficio').modal('hide');
                mostraSucesso('', 'Benefício Editado com sucesso');
                this.preloadAjax = false;
                this.controle.carregando = true;
                this.atualizado = true;
                this.atualizar();
            }).catch(error => (this.preloadAjax = false));

        },
        alterarTipo(tipobeneficio) {
            this.formTipo.cliente_id = this.cliente_id === 0 ? this.formTipo.cliente_id : this.cliente_id;

            this.cadastrado = true;
            this.editando = false;
            this.tituloJanela = "Alterando Tipo de Benefício";
            formReset();

            this.formTipo = _.cloneDeep(this.formTipoDefault) //copia

            axios.get(`${URL_ADMIN}/cadastro/beneficios/${tipobeneficio}/editarTipo`)
                .then(response => {
                    Object.assign(this.formTipo, response.data);
                    // this.formTipo.nome = data.nome
                    this.editando = true;
                    setupCampo();
                }).catch(
                error => (this.preloadAjax = false)
            );

        },
        alterarformTipo() {
            formReset();
            this.formTipo.cliente_id = this.cliente_id === 0 ? this.formTipo.cliente_id : this.cliente_id;
            $('#janelaFormTipo :input:enabled').trigger('blur');

            if ($('#janelaFormTipo :input:enabled.is-invalid').length) {
                mostraErro('', 'Verificar os erros');
                return false;
            }

            this.preloadAjax = true;

            axios.put(`${URL_ADMIN}/cadastro/beneficios/updateTipos/${this.formTipo.id}`, this.formTipo).then(response => {
                $('#janelaFormTipo').modal('hide');
                this.preloadAjax = false;
                this.controle.carregando = true;
                this.atualizado = true;
                this.atualizar();
            }).catch(error => (this.preloadAjax = false));

        },
        usuarioAutenticado() {
            this.controle.carregando = true;
            axios.get(`${URL_ADMIN}/usuario/autenticado/`)
                .then(response => {
                    let data = response.data;

                    this.cliente_id = data.cliente_id;

                    this.controle.dados.campoCliente = this.cliente_id !== 0 ? this.cliente_id : this.controle.dados.campoCliente;
                })
                .catch(error => {
                    this.preload = false;
                })
        },
        carregou(dados) {
            this.lista = dados.items;
            this.tipos = dados.tipos;
            this.tiposAtivos = dados.tiposAtivos;
            this.clientes = dados.clientes;
            this.permissoes = dados.permissoes;
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

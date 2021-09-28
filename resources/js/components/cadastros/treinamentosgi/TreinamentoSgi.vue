<template>
    <div id="componenteTreinamentoSgi">
        <modal id="janelaCadastrar" :titulo="titulo_janela" :fechar="!preload" :size="90">
            <template slot="conteudo">
                <preload v-show="preload"></preload>
                <div v-if="!preload && !cadastrado">
                    <fieldset>
                        <legend>Informações</legend>
                        <div class="row">
                            <div class="col-12 col-md-12">
                                <div class="form-group">
                                    <label>Nome</label>
                                    <input v-model="form.nome" class="form-control" type="text"
                                           onblur="valida_campo_vazio(this,1)">
                                </div>
                            </div>
                            <div class="col-12 col-md-12">
                                <div class="form-group">
                                    <label>Titulo Certificado</label>
                                    <input v-model="form.titulo_certificado" class="form-control" type="text"
                                           onblur="valida_campo_vazio(this,1)">
                                </div>
                            </div>
                            <div class="col-12 col-md-12">
                                <div class="form-group">
                                    <label>Conteúdo Abordado</label>
                                    <textarea v-model="form.conteudo_abordado" cols="5" rows="5"  class="form-control"></textarea>
                                </div>
                            </div>
                            <div class="col-12 col-md-12">
                                <div class="form-group">
                                    <label>Conteúdo Programático</label>
                                    <textarea v-model="form.conteudo_programatico" cols="5" rows="5" class="form-control"></textarea>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label>Carga Horária</label>
                                    <input v-model="form.carga_horaria" onblur="valida_campo_vazio(this,1)" class="form-control" type="number">
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label>Validade</label>
                                    <input v-model="form.validade" class="form-control" type="number">
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </template>
            <template slot="rodape">
                <button type="button" class="btn btn-sm btn-primary" v-show="editando"
                        @click="alterarformTreinamentoSgi()">
                    Salvar
                </button>
                <button type="button" class="btn btn-sm btn-primary" v-show="!editando"
                        @click="cadastrar()">
                    Cadastrar
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
                            data-target="#janelaCadastrar">
                        <i class="fa fa-plus"></i> Treinamento
                    </button>
                </div>
            </form>
        </fieldset>

        <div id="conteudo">

            <p class=" mt-2 text-center" v-if="controle.carregando">
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
                        <td class="text-center">Titulo Certificado</td>
<!--                        <td class="text-center">Conteúdo Abordado</td>-->
<!--                        <td class="text-center">Conteúdo Programático</td>-->
                        <td class="text-center">Carga Horária</td>
                        <td class="text-center">Validade</td>
                        <td class="text-center">Ação</td>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="item in lista">
                        <td class="text-center">{{ item.id }}</td>
                        <td class="text-center">{{ item.nome }}</td>
                        <td class="text-center">{{ item.titulo_certificado }}</td>
<!--                        <td class="text-center">{{ item.conteudo_abordado }}</td>-->
<!--                        <td class="text-center">{{ item.conteudo_programatico }}</td>-->
                        <td class="text-center">{{ item.carga_horaria }}</td>
                        <td class="text-center">{{ item.validade }}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-primary mb-1" data-toggle="modal"
                                    data-target="#janelaCadastrar" @click="alterarTreinamentoSgi(item.id)">
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

    export default {
        components: {
            modal,
            controlePaginacao,
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
                titulo_janela: '',

                preload: false,
                editando: false,
                cadastrado: false,

                form: {
                    nome: '',
                    titulo_certificado: '',
                    conteudo_abordado: '',
                    conteudo_programatico: '',
                    carga_horaria: '',
                    validade: '',
                },

                formDefault: null,

                lista: [],

                urlPaginacao: `${URL_ADMIN}/cadastro/treinamentosgi/atualizar`,
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
                this.form = _.cloneDeep(this.formDefault) //copia
                this.titulo_janela = 'Treinamento';
                this.editando = false;
                this.cadastrado = false;
                this.preload = false;
                formReset();
                setupCampo();
            },

            cadastrar() {
                $('#janelaCadastrar :input:visible').trigger('blur');
                if ($('#janelaCadastrar :input:visible.is-invalid').length) {
                    mostraErro('', 'Verificar os erros');
                    return false;
                }
                this.preload = true;
                axios.post(`${URL_ADMIN}/cadastro/treinamentosgi`, this.form)
                    .then(res => {
                        if (res.status === 201) {
                            $('#janelaCadastrar').modal('hide');
                            mostraSucesso('', 'Treinamento cadastrado com sucesso');
                            this.cadastrado = true;
                            this.preload = false;
                            this.atualizar();
                        }
                    })
                    .catch(error => {
                        this.cadastrado = false;
                        this.preload = false;
                    });
            },
            alterarTreinamentoSgi(treinamentosgi) {
                this.cadastrado = false;
                this.editando = true;
                this.titulo_janela = "Alterando Treinamento";
                formReset();

                this.form = _.cloneDeep(this.formDefault) //copia

                axios.get(`${URL_ADMIN}/cadastro/treinamentosgi/${treinamentosgi}/editar`)
                    .then(response => {
                        Object.assign(this.form, response.data);
                        this.editando = true;
                        setupCampo();
                    }).catch(
                    error => (this.preloadAjax = false)
                );

            },
            alterarformTreinamentoSgi() {
                formReset();
                $('#janelaCadastrar :input:enabled').trigger('blur');

                if ($('#janelaCadastrar :input:enabled.is-invalid').length) {
                    mostraErro('', 'Verificar os erros');
                    return false;
                }

                this.preload = true;

                axios.put(`${URL_ADMIN}/cadastro/treinamentosgi/${this.form.id}`, this.form).then(response => {
                    $('#janelaCadastrar').modal('hide');
                    mostraSucesso('', 'Treinamento atualizado com sucesso');
                    this.preload = false;
                    this.atualizado = true;
                    this.atualizar();
                }).catch(error => (this.preload = false));

            },
            carregou(dados) {
                this.lista = dados.items;
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

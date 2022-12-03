<template>
    <div :id="hash">
        <modal id="janelaVinculo" :titulo="janelaVinculo" :fechar="!preload" :size="90">
            <template slot="conteudo">
                <vincula-avaliador :obj="avaliacaoSelecionada" v-if="abrirVinculo"></vincula-avaliador>
            </template>
        </modal>

        <modal id="janelaCadastrar" :titulo="titulo_janela" :fechar="!preload" :size="90">
            <template slot="conteudo">
                <preload v-show="preload"></preload>
                <div v-if="!preload && !cadastrado">
                    <fieldset>
                        <legend>Informações</legend>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Título</label>
                                    <input v-model="form.titulo" class="form-control" type="text"
                                           placeholder="Informe o titulo da avaliação"
                                           onblur="valida_campo_vazio(this,1)">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Tipo de avaliação</label>
                                    <select class="form-control" v-model="form.avaliacao_tipo_id"
                                            onchange="valida_campo_vazio(this,1)" onblur="valida_campo_vazio(this,1)">
                                        <option value="">Selecione ...</option>
                                        <option v-for="item in lista_avaliacoes_tipos" :value="item.id" :key="item.id">
                                            {{ item.nome }}
                                        </option>

                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
                                <date-picker formsm label="Data Iníco" v-model="form.data_inicio_prazo"></date-picker>
                            </div>
                            <div class="col-6">
                                <date-picker formsm label="Data Fim" v-model="form.data_fim_prazo"></date-picker>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select class="form-control" v-model="form.status"
                                            onchange="valida_campo_vazio(this,1)" onblur="valida_campo_vazio(this,1)">
                                        <option value="">Selecione ...</option>
                                        <option v-for="item in lista_status" :value="item">{{ item }}</option>

                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Ativo</label>
                                    <select class="form-control" onblur="valida_campo_vazio(this,1)"
                                            onchange="valida_campo_vazio(this,1)" v-model="form.ativo">
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
            <template slot="rodape">
                <button type="button" class="btn btn-sm btn-primary" v-show="editando && !preload"
                        @click="alterar()">
                    Alterar
                </button>
                <button type="button" class="btn btn-sm btn-primary" v-if="lista_avaliacoes_tipos.length > 0"
                        v-show="!editando && !preload"
                        @click="cadastrar()">
                    Cadastrar
                </button>
            </template>
        </modal>

        <!-- Filtro -->
        <fieldset>
            <legend>Filtro</legend>
            <form class="row" @submit.prevent="$refs.componente.buscar()">
                <div class="col-12 col-md-4">
                    <div class="form-group">
                        <label>Buscar</label>
                        <input type="text"
                               placeholder="Buscar por título"
                               autocomplete="off"
                               class="form-control form-control-sm" :disabled="controle.carregando"
                               v-model="controle.dados.campoBusca">
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
                        <i class="fa fa-plus"></i> Cadastrar
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

            <div v-show="!controle.carregando && lista.length > 0">
                <table class="tabela">
                    <thead>
                    <tr class="bg-default">
                        <td class="text-center">Título</td>
                        <td class="text-center">Tipo Avaliação</td>
                        <td class="text-center">Data Início</td>
                        <td class="text-center">Data Fim</td>
                        <td class="text-center">Status</td>
                        <td class="text-center">Ativo</td>
                        <td class="text-center">Ação</td>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="item in lista">
                        <td class="text-center">{{ item.titulo }}</td>
                        <td class="text-center">{{ item.avaliacao_tipo.nome }}</td>
                        <td class="text-center">{{ item.data_inicio_prazo }}</td>
                        <td class="text-center">{{ item.data_fim_prazo }}</td>
                        <td class="text-center">{{ item.status }}</td>
                        <td class="text-center">
                            <bt-ativo :rota="`cadastro/avaliacoes/avaliacao/${item.id}/ativa-desativa`"
                                      :model="item"></bt-ativo>
                        </td>
                        <td class="text-center">

                            <div class="dropdown show">
                                <a class="btn btn-secondary dropdown-toggle" href="#" role="button"
                                   id="dropdownMenuLink"
                                   data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v"></i>
                                </a>

                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink">
                                    <a class="dropdown-item" href="javascript://" title="Editar"
                                       data-toggle="modal" data-target="#janelaCadastrar" @click="alterarForm(item)">
                                        Editar
                                    </a>

                                    <a class="dropdown-item" href="javascript://" title="Vincular avaliadores"
                                       data-toggle="modal"
                                       data-target="#janelaVinculo"
                                       @click="vinculo(item)"
                                    >
                                        Vincular avaliadores
                                    </a>
                                </div>

                            </div>
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
import controlePaginacao from "../../../ControlePaginacao";
import modal from "../../../Modal";
import DatePicker from "../../../DatePicker";
import vinculaAvaliador from "./vinculaAvaliador";

export default {
    components: {
        modal,
        controlePaginacao,
        DatePicker,
        vinculaAvaliador
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
            default: ""
        }
    },
    mounted() {
        this.atualizar();
        this.formDefault = _.cloneDeep(this.form);
    },
    data() {
        return {
            hash: String(Math.random()).substr(2),
            titulo_janela: "",
            janelaVinculo: "",
            preload: false,
            editando: false,
            cadastrado: false,
            abrirVinculo: false,

            form: {
                titulo: "",
                data_inicio_prazo: "",
                data_fim_prazo: "",
                status: "",
                ativo: true,
                avaliacao_tipo_id: ''
            },

            formDefault: null,

            lista: [],
            lista_avaliacoes_tipos: [],
            lista_status: [],

            avaliacaoSelecionada: null,

            urlPaginacao: `${URL_ADMIN}/cadastro/avaliacoes/avaliacao/atualizar`,
            controle: {
                carregando: false,
                dados: {
                    campoBusca: "",
                }
            }
        };
    },
    methods: {
        vinculo(obj) {
            this.abrirVinculo = false;
            this.janelaVinculo = `Vinculo de avaliadores  avaliação - ${obj.titulo}`;
            this.avaliacaoSelecionada = obj;
            setTimeout(() => {
                this.abrirVinculo = true;
            }, 300);

        },
        formNovo() {
            this.form = _.cloneDeep(this.formDefault); //copia
            this.titulo_janela = "Montagem da Avaliação";
            this.editando = false;
            this.cadastrado = false;
            this.preload = false;
            formReset();
            setupCampo();
        },

        cadastrar() {
            $("#janelaCadastrar :input:visible").trigger("blur");
            if ($("#janelaCadastrar :input:visible.is-invalid").length) {
                mostraErro("", "Verificar os erros");
                return false;
            }
            this.preload = true;
            axios.post(`${URL_ADMIN}/cadastro/avaliacoes/avaliacao`, this.form)
                .then(res => {
                    if (res.status === 201) {
                        $("#janelaCadastrar").modal("hide");
                        mostraSucesso("", "Avaliação cadastrada com sucesso");
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

        alterarForm(avaliacao) {
            this.cadastrado = false;
            this.editando = true;
            this.titulo_janela = `Alterando Avaliação ${avaliacao.id}`;
            this.preload = true;

            this.form = _.cloneDeep(this.formDefault); //copia
            formReset();

            axios.get(`${URL_ADMIN}/cadastro/avaliacoes/avaliacao/${avaliacao.id}/editar`)
                .then(response => {
                    Object.assign(this.form, response.data);
                    this.editando = true;
                    setupCampo();
                    this.preload = false;
                }).catch(
                error => (this.preloadAjax = false)
            );

        },

        alterar() {
            formReset();
            $("#janelaCadastrar :input:enabled").trigger("blur");

            if ($("#janelaCadastrar :input:enabled.is-invalid").length) {
                mostraErro("", "Verificar os erros");
                return false;
            }

            this.preload = true;

            axios.put(`${URL_ADMIN}/cadastro/avaliacoes/avaliacao/${this.form.id}`, this.form).then(response => {
                $("#janelaCadastrar").modal("hide");
                mostraSucesso("", "Avaliação alterada com sucesso");
                this.preload = false;
                this.atualizado = true;
                this.atualizar();
            }).catch(error => (this.preload = false));

        },
        carregou(dados) {
            this.lista = dados.itens;
            this.lista_avaliacoes_tipos = dados.avaliacoes_tipos;
            this.lista_status = dados.lista_status;
            this.controle.carregando = false;
        },
        carregando() {
            this.controle.carregando = true;
        },
        atualizar() {
            this.$refs.componente.atual = 1;
            this.$refs.componente.buscar();
        }
    }

};
</script>

<style scoped>
.card-header {
    background-color: white;
}

.btn-link {
    font-weight: 400;
    color: white;
    text-decoration: none;
}

.btn-link:hover {
    color: #dddddd;
}

</style>

<template>
    <div>
        <!--        <modal id="janelaRelatorio" :titulo="tituloJanela" :fechar="!preloadAjax" size="g">-->
        <!--            <template slot="conteudo">-->
        <!--                <fieldset v-if="cliente_id == 1">-->
        <!--                    <legend>Selecione o Cliente</legend>-->
        <!--                    <div class="form-group">-->
        <!--                        <select class="form-control" v-model="cliente_relatorio" onblur="valida_campo_vazio(this,1)"-->
        <!--                                onchange="valida_campo_vazio(this,1)">-->
        <!--                            <option value="">Selecione</option>-->
        <!--                            <option v-for="item in listaClientes" :value="item.id">@{{ item.razao_social }}</option>-->
        <!--                        </select>-->
        <!--                    </div>-->
        <!--                </fieldset>-->

        <!--                <fieldset>-->
        <!--                    <legend>Escolha o período</legend>-->
        <!--                    <div class="form-group">-->
        <!--                        <date-picker label="Período" v-model="datarelatorio" :id="`data_relatorio_${hash}`"-->
        <!--                                     :range="true"></date-picker>-->
        <!--                    </div>-->
        <!--                </fieldset>-->

        <!--            </template>-->
        <!--            <template slot="rodape">-->
        <!--                <form method="post" target="_blank" v-show="tipoRelatorio === 'pdf'"-->
        <!--                      action="{{ route('g.admissao.intermitente.relatorioPdf') }}">-->
        <!--                    @csrf-->
        <!--                    <input type="hidden" name="cliente_relatorio" :value="cliente_relatorio">-->
        <!--                    <input type="hidden" name="intervalo" :value="datarelatorio">-->
        <!--                    <button class="btn btn-sm btn-primary">Gerar PDF</button>-->
        <!--                </form>-->

        <!--                <form method="post" target="_blank" v-show="tipoRelatorio === 'excel'"-->
        <!--                      action="{{ route('g.admissao.intermitente.relatorioExcel') }}">-->
        <!--                    @csrf-->
        <!--                    <input type="hidden" name="cliente_relatorio" :value="cliente_relatorio">-->
        <!--                    <input type="hidden" name="intervalo" :value="datarelatorio">-->
        <!--                    <button class="btn btn-sm btn-primary">Gerar Excel</button>-->
        <!--                </form>-->
        <!--            </template>-->
        <!--        </modal>-->

        <modal :titulo="titulo_janela_form_prorrogacao" :fechar="!preloadProrrogacao" :size="90"
               id="janelaFormProrrogacao">
            <template slot="conteudo">
                <fieldset class=" mb-2">
                    <div class="table-responsive">
                        <table class="tabela">
                            <thead>
                            <tr class="bg-default">
                                <th class="text-center">Prorrogação</th>
                                <th class="text-center">Data Início</th>
                                <th class="text-center">Data Fim</th>
                                <th class="text-center">Solicitante</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(obj, index) in listaProrrogacao">
                                <td class="text-center">{{ index + 1 }}ª Prorrogação</td>
                                <td class="text-center">{{ obj.data_inicio }}</td>
                                <td class="text-center">{{ obj.data_fim }}</td>
                                <td class="text-center">{{ obj.solicitante }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </fieldset>

                <button class="btn btn-sm btn-primary mb-3" @click="addLIProrrogacao">
                    <i class="fa fa-plus"></i> Adicionar Prorrogação
                </button>

                <p class=" mt-2 text-center" v-if="preloadProrrogacao"><i class="fa fa-spinner fa-pulse"></i>Carregando...
                </p>
                <div v-if="!preloadProrrogacao && !cadastrado">
                    <fieldset class=" mb-2" v-if="formProrrogacao.prorrogacao.length > 0"
                              v-for="(obj, index) in formProrrogacao.prorrogacao" :key="index + 100">
                        <legend>Nova Prorrogação</legend>
                        <div class="row">
                            <div class="col-12">
                                <date-picker formsm label="Data Início" v-model="obj.data_inicio"
                                             :disabled="!obj.novo"></date-picker>
                            </div>

                            <div class="col-12">
                                <date-picker formsm label="Data Fim" v-model="obj.data_fim"
                                             :disabled="!obj.novo"></date-picker>
                            </div>

                            <div class="col-12">
                                <label>Solicitante</label>
                                <input v-model="obj.solicitante" class="form-control" :disabled="!obj.novo">
                            </div>

                            <div class="col-12 mt-3" v-show="obj.novo">
                                <button class="btn btn-sm btn-danger" @click="removerLIProrrogacao(index)"><i
                                    class="fa fa-times"></i> Remover
                                </button>

                                <button class="btn btn-sm btn-primary mt" @click="addLIProrrogacao" v-show="index >= 1">
                                    <i class="fa fa-plus"></i> Adicionar
                                </button>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </template>
            <template slot="rodape">
                <button type="button" class="btn btn-sm btn-primary" v-show="!cadastrado && !preloadTipo"
                        @click="cadastrarProrrogacao">
                    <i class="fa fa-save"></i>Cadastrar
                </button>
            </template>
        </modal>

        <modal :titulo="titulo_janela_form_tipo" :fechar="!preloadTipo" id="janelaFormTipo">
            <template slot="conteudo">
                <p class=" mt-2 text-center" v-if="preloadTipo"><i class="fa fa-spinner fa-pulse"></i>Carregando...</p>
                <div v-if="!preloadTipo && !cadastrado">
                    <fieldset>
                        <legend>Cadastro de Tipo de Intermitente</legend>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <div class="input-group">
                                <span class="input-group-prepend">
                                    <span class="input-group-text">Nome</span>
                                </span>
                                        <input class="form-control" type="text"
                                               onblur="valida_campo_vazio(this,1)" v-model="formTipo.label">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </template>
            <template slot="rodape">
                <button type="button" class="btn btn-sm btn-primary" v-show="!cadastrado && !preloadTipo"
                        @click="cadastraTipo">
                    <i class="fa fa-save"></i>Cadastrar
                </button>
            </template>
        </modal>

        <modal id="janelaCadastrar" :titulo="tituloJanela" :fechar="!preloadAjax" :size="90">
            <template slot="conteudo">
                <div v-show="preloadAjax"><i class="fa fa-spinner fa-pulse"></i> Aguarde...</div>
                <div class="alert alert-success alert-dismissible" v-show="cadastrado">
                    <h4><i class="icon fa fa-check"></i>Intermitente cadastrado com sucesso!</h4>
                </div>
                <div class="alert alert-success alert-dismissible" v-show="atualizado">
                    <h4><i class="icon fa fa-check"></i>Intermitente alterado com sucesso!</h4>
                </div>
                <fieldset>
                    <legend>Lançamento</legend>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <date-picker :disabled="encerrado" :range="true" label="Data da Ocorrência"
                                             v-model="form.data_lancamento"></date-picker>
                            </div>
                        </div>

                        <div class="col-12"></div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tipo</label>
                                <select :disabled="encerrado" v-model="form.tipo_id"
                                        onblur="valida_campo_vazio(this,1)"
                                        onchange="valida_campo_vazio(this,1)" class="form-control">
                                    <option value="">Selecione...</option>
                                    <option v-for="item in listaTipos" :value="item.id">{{ item.label }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12"></div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Área</label>
                                <select :disabled="encerrado" v-model="form.area_id"
                                        onblur="valida_campo_vazio(this,1)"
                                        onchange="valida_campo_vazio(this,1)" class="form-control">
                                    <option value="">Selecione...</option>
                                    <option v-for="item in listaAreas" :value="item.id">{{ item.label }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6" v-if="form.area_id === 0">
                            <div class="form-group">
                                <label>Especifique</label>
                                <input type="text" class="form-control" onblur="valida_campo_vazio(this,1)"
                                       :disabled="encerrado" v-model="form.outra_area">
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group">
                                <label>Colaborador</label>
                                <autocomplete :caminho="colaborador_ativo"
                                              :valido="form.feedback_id !== ''"
                                              v-model="form.autocomplete_label_colaborador"
                                              placeholder="Selecione um(a) colaborador(a)"
                                              :disabled="encerrado"
                                              @onblur="resetaCampoColaborador"
                                              @onselect="selecionaColaborador"></autocomplete>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group">
                                <label>Ação</label>
                                <input type="text" class="form-control" :disabled="encerrado"
                                       onblur="valida_campo_vazio(this,1)" v-model="form.acao">
                            </div>
                        </div>

                        <div class="col-12">
                            <fieldset>
                                <legend>Anexo</legend>
                                <upload :model="form.anexos"
                                        :model-delete="form.anexosDel"
                                        :leitura="form.id ? true : false"
                                        :url="url_anexo"
                                        label="Selecionar"
                                        @onProgresso="anexoUploadAndamento=true"
                                        @onFinalizado="anexoUploadAndamento=false"></upload>
                            </fieldset>
                        </div>

                        <div class="col-12">
                            <div class="form-group">
                                <label>Observação</label>
                                <textarea class="form-control" :disabled="encerrado" v-model="form.obs_lancamento"
                                          cols="3" rows="3"></textarea>
                            </div>
                        </div>

                        <div class="col-12">
                            <fieldset>
                                <legend>Treinamentos</legend>
                                <div class="table-responsive">
                                    <table class="tabela">
                                        <thead>
                                        <tr class="bg-default">
                                            <th class="text-center">Tipo</th>
                                            <th class="text-center">Data do Treinamento</th>
                                            <th class="text-center">Data do Vencimento</th>
                                            <th class="text-center">Prazo Fixo</th>
                                            <th class="text-center">Prazo Parada</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="t in form.treinamentos">
                                            <td class="text-center">{{ t.label }}</td>
                                            <td class="text-center">{{ t.pivot.data_treinamento }}</td>
                                            <td class="text-center">{{ t.pivot.data_vencimento }}</td>
                                            <td class="text-center">{{ t.prazo_fixo }}</td>
                                            <td class="text-center">{{ t.prazo_parada }}</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </fieldset>
                        </div>

                        <div class="col-12">
                            <fieldset>
                                <legend>Exames</legend>
                                <!--                                <div class="table-responsive">-->
                                <!--                                    <table class="tabela">-->
                                <!--                                        <thead>-->
                                <!--                                        <tr class="bg-default">-->
                                <!--                                            <th class="text-center">Tipo</th>-->
                                <!--                                            <th class="text-center">Data do Treinamento</th>-->
                                <!--                                            <th class="text-center">Data do Vencimento</th>-->
                                <!--                                            <th class="text-center">Prazo Fixo</th>-->
                                <!--                                            <th class="text-center">Prazo Parada</th>-->
                                <!--                                        </tr>-->
                                <!--                                        </thead>-->
                                <!--                                        <tbody>-->
                                <!--                                        <tr v-for="t in form.treinamentos">-->
                                <!--                                            <td class="text-center">{{t.label}}</td>-->
                                <!--                                            <td class="text-center">{{t.pivot.data_treinamento}}</td>-->
                                <!--                                            <td class="text-center">{{t.pivot.data_vencimento}}</td>-->
                                <!--                                            <td class="text-center">{{t.prazo_fixo}}</td>-->
                                <!--                                            <td class="text-center">{{t.prazo_parada}}</td>-->
                                <!--                                        </tr>-->
                                <!--                                        </tbody>-->
                                <!--                                    </table>-->
                                <!--                                </div>-->
                            </fieldset>
                        </div>

                    </div>
                </fieldset>

                <fieldset class=" mb-2" v-if="encerrado">
                    <legend>Prorrogação</legend>
                    <div class="row">
                        <div class="table-responsive">
                            <table class="tabela">
                                <thead>
                                <tr class="bg-default">
                                    <th class="text-center">Prorrogação</th>
                                    <th class="text-center">Data Início</th>
                                    <th class="text-center">Data Fim</th>
                                    <th class="text-center">Solicitante</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="(obj, index) in form.prorrogacao">
                                    <td class="text-center">{{ index + 1 }}ª Prorrogação</td>
                                    <td class="text-center">{{ obj.data_inicio }}</td>
                                    <td class="text-center">{{ obj.data_fim }}</td>
                                    <td class="text-center">{{ obj.solicitante }}</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="mb-2" v-if="encerrado">
                    <legend>Outras Informações</legend>
                    <div class="row">
                        <div class="col-12">
                            <label>Devolve EPI</label>
                            <select class="form-control" onblur="valida_campo_vazio(this,1)"
                                    onchange="valida_campo_vazio(this,1)" :disabled="!editando"
                                    v-model="form.devolve_epi">
                                <option :value="''">Selecione</option>
                                <option :value="true">Sim</option>
                                <option :value="false">Não</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label>Devolve Crachá</label>
                            <select class="form-control" onblur="valida_campo_vazio(this,1)"
                                    onchange="valida_campo_vazio(this,1)" :disabled="!editando"
                                    v-model="form.devolve_cracha">
                                <option :value="''">Selecione</option>
                                <option :value="true">Sim</option>
                                <option :value="false">Não</option>
                            </select>
                        </div>
                    </div>
                </fieldset>
            </template>
            <template slot="rodape">
                <div>
                    <button type="button" class="btn btn-sm btn-primary" v-if="form.status === 'aberto'"
                            v-show="editando"
                            @click="confirmaEncerraConvocacao()">
                        <i class="fa fa-save"></i> Encerrar
                    </button>
                    <button type="button" class="btn btn-sm btn-primary"
                            v-show="!aprovando && !cadastrado && !preloadAjax && !encerrado"
                            @click="cadastrar()">
                        <i class="fa fa-save"></i> Lançar
                    </button>
                </div>
            </template>
        </modal>

        <fieldset>
            <legend>Filtro</legend>
            <form class="row" @submit.prevent="$refs.componente.buscar()">
                <div class="col-12 col-md-4">
                    <div class="form-group">
                        <label>Buscar</label>
                        <input type="text"
                               placeholder="Buscar por nome"
                               autocomplete="off"
                               class="form-control form-control-sm" :disabled="controle.carregando"
                               v-model="controle.dados.campoBusca">
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-control form-control-sm" v-model="controle.dados.campoStatus" @change="atualizar()">
                            <option value="">Todos os Status</option>
                            <option :value="true">Apenas Ativos</option>
                            <option :value="false">Apenas Inativos</option>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-md-9">
                    <button type="button" class="btn btn-sm btn-success mb-1 mr-1" :disabled="controle.carregando"
                            @click="atualizar">
                        <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>Atualizar
                    </button>
                    <button type="button" class="btn btn-sm btn-primary mb-1 mr-1" data-toggle="modal"
                            :disabled="controle.carregando"
                            data-target="#janelaCadastrar"
                            @click="formNovo()">
                        <i class="fa fa-plus"></i> Cadastrar
                    </button>

                    <button type="button" class="btn btn-sm btn-secondary mb-1 mr-1" :disabled="controle.carregando"
                            @click="formNovoTipo"
                            data-toggle="modal"
                            data-target="#janelaFormTipo">
                        <i class="fa fa-plus"></i> Cadastrar Tipo
                    </button>
                    <button type="button" class="btn btn-sm btn-primary mb-1 mr-1"
                            @click.prevent="exportaExcel()"
                            :disabled="controle.carregando|| preloadExportacao || (!controle.carregando && lista.length===0) ">
                        <i class="fas fa-file-excel"></i> EXPORTAR EXCEL
                    </button>
                </div>
            </form>
        </fieldset>

        <div class="col-12 mb-2 mt-2 pt-1 pb-1 border-bottom">
        <span class="small">
                Legenda:
                <i class="fas fa-circle text-warning"></i> Aberto
                <i class="fas fa-circle text-success ml-2"></i> Convocação Encerrada
            </span>
        </div>

        <p class="text-center" v-if="controle.carregando">
            <i class="fa fa-spinner fa-pulse"></i> Carregando...
        </p>

        <div id="conteudo">
            <div class="alert alert-warning" v-show="!controle.carregando && lista.length === 0">
                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
            </div>


            <div class="table-responsive" v-show="!controle.carregando && lista.length > 0">
                <table class="tabela">
                    <thead>
                    <tr class="bg-default">
                        <th class="text-center">ID</th>
                        <th v-if="cliente_id == 1">Cliente</th>
                        <th class="text-center">Colaborador</th>
                        <th class="text-center">Lançamento</th>
                        <th class="text-center">Encerrado</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Ação</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="item in lista" :class="
                                        item.status === 'aberto' ? 'table-warning' : item.status === 'encerrado' ? 'table-success' : null
                        ">
                        <td class="text-center">
                            {{ item.id }}
                        </td>

                        <td v-if="cliente_id === 1">
                            {{ item.cliente.nome_fantasia }}
                        </td>

                        <td>
                            {{ item.colaborador.curriculo.nome }}
                        </td>

                        <td class="text-center">
                            Lançado por {{ item.responsavel_lancamento.nome }} <br>
                            em {{ item.data_lancamento }}
                        </td>
                        <td class="text-center">
                                <span v-if="item.status === 'encerrado'">
                                    Encerrado por {{ item.responsavel_aprovacao.nome }} <br>
                                    em {{ item.data_aprovacao }}
                                </span>
                        </td>

                        <td class="text-center">
                            {{ item.status }}
                        </td>

                        <td class="text-center">
                            <a v-show="item.status === 'aberto'" href="javascript://" class="btn btn-sm btn-default"
                               @click="formNovoProrrogacao(item.id)"
                               data-toggle="modal"
                               data-target="#janelaFormProrrogacao">
                                <i class="fa fa-plus"></i> Prorrogação
                            </a>

                            <a v-show="item.status === 'aberto'" href="javascript://" class="btn btn-sm btn-default"
                               @click="encerrarConvocacao(item.id)"
                               data-toggle="modal"
                               data-target="#janelaCadastrar">
                                <i class="fa fa-times"></i> Encerrar Convocação
                            </a>

                            <a v-show="item.status === 'encerrado'" href="javascript://"
                               class="btn btn-sm btn-default"
                               @click="visualizar(item.id)"
                               data-toggle="modal"
                               data-target="#janelaCadastrar">
                                <i class="fa fa-search"></i> Visualizar
                            </a>
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
import DatePicker from "../../DatePicker";
import autocomplete from "../../AutoComplete";
import Upload from "../../Upload";
import ExportacaoMixin from "../../../mixins/Exportacoes";

export default {
    mixins: [ExportacaoMixin],

    components: {
        autocomplete,
        DatePicker,
        Upload
    },
    props: {
        qntPag: {
            type: Number,
            required: false,
            default: 20
        },
    },
    data() {
        return {
            tituloJanela: 'Cadastrando INTERMITENTE',
            titulo_janela_form_tipo: '',
            titulo_janela_form_prorrogacao: '',
            preloadAjax: false,
            editando: false,
            leitura: false,
            apagado: false,
            aprovando: false,
            encerrado: false,
            preloadTipo: false,
            preloadProrrogacao: false,
            preloadExportacao: false,

            urlExportacao: `${URL_ADMIN}/apontamento/intermitente/export`,
            urlPaginacao: `${URL_ADMIN}/apontamento/intermitente/atualizar`,

            colaborador_ativo: `autocomplete/colaboradorIntermitente`,
            todos_municipios: `autocomplete/todos-municipios`,

            hash: `mastertag_${parseInt((Math.random() * 999999))}`,
            cliente_id: 0,

            datarelatorio: '',
            tipoRelatorio: 'pdf',
            cliente_relatorio: '',

            hoje: '',

            form: {
                tipo_id: '',
                feedback_id: '',
                autocomplete_label_colaborador: '',
                autocomplete_label_colaborador_anterior: '',
                cliente_id: '',
                area_id: '',
                acao: '',
                user_lancamento_id: '',
                obs_lancamento: '',
                data_lancamento: '',
                user_aprovacao_id: '',
                obs_aprovacao: '',
                data_aprovacao: '',
                status: '',
                status_aprovacao: '',
                anexos: [],
                anexosDel: [],
                devolve_epi: '',
                devolve_cracha: '',
                treinamentos: [],
                exames: [],
            },

            formProrrogacao: {
                intermitente_id: '',
                prorrogacao: [],
                prorrogacaoDelete: []
            },
            formProrrogacaoDefault: null,

            formTipo: {
                label: '',
            },
            formTipoDefault: null,

            url_anexo: `${URL_ADMIN}/storage/uploadAnexos`,
            anexoUploadAndamento: false,

            formDefault: null,

            campoNome: null,

            cadastrado: false,
            atualizado: false,

            lista: [],
            listaTipos: [],
            listaAreas: [],
            listaClientes: [],
            listaProrrogacao: [],

            controle: {
                carregando: false,
                dados: {
                    campoBusca: "",
                    campoStatus: "",
                    pages: 50,
                },
            }
        }
    },
    mounted() {
        this.formDefault = _.cloneDeep(this.form) //copia
        this.formProrrogacaoDefault = _.cloneDeep(this.formProrrogacao) //copia
        this.formTipoDefault = _.cloneDeep(this.formTipo) //copia
        this.atualizar();
    },
    methods: {
        selecionaColaborador(obj) {
            this.form.feedback_id = obj.id;
            this.form.cliente_id = obj.cliente_id;
            this.form.treinamentos = obj.curriculo.treinamentos.vencimentos;
            this.form.exames = obj.exames;
            this.form.autocomplete_label_colaborador = obj.label;
            this.form.autocomplete_label_colaborador_anterior = obj.label;
        },
        resetaCampoColaborador() {
            if (this.form.autocomplete_label_colaborador_anterior !== this.form.autocomplete_label_colaborador) {
                this.form.autocomplete_label_colaborador_anterior = '';
                this.form.autocomplete_label_colaborador = '';
                this.form.feedback_id = '';
                this.form.cliente_id = '';

                setTimeout(() => {
                    if (this.form.feedback_id === '') {
                        valida_campo_vazio($('#colaborador_' + this.hash), 1);
                        // $('#janelaCadastrar #' + this.hash).focus().trigger('blur');
                        $('#janelaCadastrar #colaborador_' + this.hash).focus().trigger('blur');
                        mostraErro('Erro', 'O Campo Vaga não pode ficar vazio');
                    }
                }, 100);
            }
        },
        addLIProrrogacao() {
            const obj = {};
            obj.novo = true;
            obj.data_inicio = '';
            obj.data_fim = '';
            obj.solicitante = '';

            this.formProrrogacao.prorrogacao.push(obj);
        },
        removerLIProrrogacao(index) {
            if (this.editando) {
                this.formProrrogacao.prorrogacaoDelete.push(this.formProrrogacao.prorrogacao[index].id);
            }
            this.formProrrogacao.prorrogacao.splice(index, 1);
        },
        formAdicionarProrrogacao() {
            this.cadastrado = false;
            this.atualizado = false;
            this.editando = false;
            this.aprovando = false;

            this.titulo_janela_form_prorrogacao = "Cadastrando PRORROGAÇÃO";

            formReset();
            setupCampo();

            this.formProrrogacao = _.cloneDeep(this.formProrrogacaoDefault) //copia
            this.form.status = 'aberto';

        },
        formNovoProrrogacao(id) {
            this.titulo_janela_form_prorrogacao = `Cadastrando PRORROGAÇÃO`;
            formReset();

            this.formProrrogacao = _.cloneDeep(this.formProrrogacaoDefault) //copia

            axios.get(`${URL_ADMIN}/apontamento/intermitente/prorrogacao/${id}/editar`)
                .then(response => {

                    this.listaProrrogacao = response.data
                    this.formProrrogacao.intermitente_id = id;
                    setupCampo();
                }).catch(
                error => (this.preloadAjax = false)
            );

        },
        cadastrarProrrogacao() {
            formReset();
            $('#janelaFormProrrogacao :input:enabled').trigger('blur');
            if ($('#janelaFormProrrogacao :input:enabled.is-invalid').length) {
                mostraErro('', 'Verificar os erros');
                return false;
            }

            this.preloadAjax = true;
            this.form.status = "aberto";

            axios.post(`${URL_ADMIN}/apontamento/intermitente/prorrogacao`, this.formProrrogacao)
                .then(response => {
                    if (response.status === 201) {
                        this.preloadAjax = false;
                        this.cadastrado = true;
                        $('#janelaFormProrrogacao').modal('hide');
                        mostraSucesso('', 'Prorrogação cadastrado com sucesso');
                        this.atualizar();
                    }
                }).catch(error => (this.preloadAjax = false));
        },
        formNovo() {
            this.cadastrado = false;
            this.atualizado = false;
            this.editando = false;
            this.encerrado = false;
            this.aprovando = false;

            this.tituloJanela = "Cadastrando INTERMITENTE";

            formReset();
            setupCampo();

            this.form = _.cloneDeep(this.formDefault) //copia
            this.form.status = 'aberto';

        },
        cadastrar() {
            formReset();
            $('#janelaCadastrar :input:enabled').trigger('blur');
            if ($('#janelaCadastrar :input:enabled.is-invalid').length) {
                mostraErro('', 'Verificar os erros');
                return false;
            }

            if (this.form.feedback_id === '') {
                valida_campo_vazio($(`#colaborador_${this.hash}`), 1);
                $(`#janelaCadastrar #colaborador_${this.hash}`).focus().trigger('blur');
                mostraErro('Erro', 'O campo vaga não pode ficar vazio');
                return false;
            }

            this.preloadAjax = true;
            this.form.status = "aberto";

            axios.post(`${URL_ADMIN}/apontamento/intermitente`, this.form)
                .then(response => {
                    if (response.status === 201) {
                        this.preloadAjax = false;
                        this.cadastrado = true;
                        $('#janelaCadastrar').modal('hide');
                        mostraSucesso('', 'Intermitente cadastrado com sucesso');
                        this.atualizar();
                    }
                }).catch(error => (this.preloadAjax = false));
        },
        visualizar(id) {
            this.cadastrado = false;
            this.atualizado = false;
            this.editando = false;
            this.encerrado = true;
            this.tituloJanela = `Visualizando INTERMITENTE #${id}`;
            formReset();

            this.form = _.cloneDeep(this.formDefault) //copia
            this.leitura = true;

            axios.get(`${URL_ADMIN}/apontamento/intermitente/${id}/editar`)
                .then(response => {
                    Object.assign(this.form, response.data);
                    this.editando = false;
                    this.encerrado = true;
                    this.preloadAjax = false;
                    setupCampo();
                }).catch(
                error => (this.preloadAjax = false)
            );
        },
        encerrarConvocacao(id) {
            this.cadastrado = false;
            this.atualizado = false;
            this.editando = true;
            this.encerrado = true;
            this.tituloJanela = `Visualizando INTERMITENTE #${id}`;
            formReset();

            this.form = _.cloneDeep(this.formDefault) //copia
            this.leitura = true;

            axios.get(`${URL_ADMIN}/apontamento/intermitente/${id}/editar`)
                .then(response => {
                    Object.assign(this.form, response.data);
                    this.form.devolve_cracha = '';
                    this.form.devolve_epi = '';
                    this.editando = true;
                    this.encerrado = true;
                    this.preloadAjax = false;
                    setupCampo();
                }).catch(
                error => (this.preloadAjax = false)
            );
        },
        confirmaEncerraConvocacao() {
            formReset();
            $('#janelaCadastrar :input:enabled').trigger('blur');
            if ($('#janelaCadastrar :input:enabled.is-invalid').length) {
                mostraErro('', 'Verificar os erros');
                return false;
            }

            this.form._method = 'PUT';
            this.preloadAjax = true;

            axios.put(`${URL_ADMIN}/apontamento/intermitente/encerrar-convocacao/${this.form.id}`, this.form).then(response => {
                this.preloadAjax = false;
                this.atualizado = true;
                $('#janelaCadastrar').modal('hide');
                mostraSucesso('', 'Intermitente alterado com sucesso');
                this.atualizar();
            }).catch(error => (this.preloadAjax = false));

        },
        formNovoTipo() {
            this.formTipo = _.cloneDeep(this.formTipoDefault) //copia
            this.titulo_janela_form_tipo = 'Novo Tipo';
            this.preloadTipo = false;
            this.cadastrado = false;
            this.atualizado = false;
            formReset();
        },
        cadastraTipo() {
            $('#janelaFormTipo :input:visible').trigger('blur');
            if ($('#janelaFormTipo :input:visible.is-invalid').length) {
                mostraErro('', 'Verificar os erros');
                return false;
            }
            this.preloadTipo = true;
            axios.post(`${URL_ADMIN}/apontamento/intermitente/storeTipo`, this.formTipo)
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
        carregou(dados) {
            this.lista = dados.itens;
            this.listaTipos = dados.tipos;
            this.listaAreas = dados.areas;
            this.cliente_id = dados.cliente_id;
            this.datarelatorio = dados.intervalo;
            this.hoje = dados.hoje;
            this.listaClientes = dados.listaClientes;
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

</style>

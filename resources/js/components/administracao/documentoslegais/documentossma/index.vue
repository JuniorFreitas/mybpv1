<template>
    <div>
        <modal id="janelaCadastrar" :titulo="tituloJanela" :size="90">
            <template slot="conteudo">
                <div v-show="preloadAjax"><i class="fa fa-spinner fa-pulse"></i> Aguarde...</div>
                <div class="alert alert-success alert-dismissible" v-show="cadastrado">
                    <h4><i class="icon fa fa-check"></i>{{ form.tipo_documento }} cadastrado com sucesso!</h4>
                </div>
                <div class="alert alert-success alert-dismissible" v-show="atualizado">
                    <h4><i class="icon fa fa-check"></i>{{ form.tipo_documento }} alterado com sucesso!</h4>
                </div>
                <form v-if="!preloadAjax && (!cadastrado && !atualizado)" id="form" onsubmit="return false;">

                    <div>

                        <fieldset>
                            <legend class="text-uppercase">Tipo de Cadastro Documento</legend>
                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <label>Selecione o Tipo</label>
                                    <select class="form-control validacampo" v-model="form.tipo_ssma"
                                            :disabled="editando"
                                            @change.prevent="valida_campo_vazio($event.target, 1)"
                                            onblur="valida_campo_vazio(this, 1)"
                                            @change="form.documentos_ssma.tipo_descricao = ''">
                                        <option value="">Selecione ...</option>
                                        <option :value="true">Empresa</option>
                                        <option :value="false">Contrato</option>
                                    </select>
                                </div>

                                <div class="col-12 col-md-6" v-if="form.tipo_ssma === false">
                                    <label>Selecione o Contrato</label>
                                    <select class="form-control validacampo" v-model="form.contrato_id"
                                            :disabled="editando"
                                            @change.prevent="valida_campo_vazio($event.target, 1)"
                                            onblur="valida_campo_vazio(this, 1)">
                                        <option value="">Selecione ...</option>
                                        <option :value="item.id" :key="item.id" v-for="item in listaContratos">
                                            {{ item.tipo === tipo_pessoa_fisica ? item.nome : item.razao_social }}
                                        </option>
                                    </select>
                                </div>

                            </div>
                        </fieldset>

                        <div v-if="form.tipo_ssma !== ''">

                            <ul class="nav nav-tabs bg-light" id="tabslist" role="tablist"
                                style="border-bottom: 1px solid #653232">
                                <li class="nav-item">
                                    <a class="nav-item nav-link active" id="nav-documentossma-tab" data-toggle="tab"
                                       href="#nav-documentossma"
                                       role="tab" aria-controls="nav-documentossma" aria-selected="false">DOCUMENTO
                                        LEGAIS</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-item nav-link" id="nav-config-ssma-tab" data-toggle="tab"
                                       href="#nav-config-ssma"
                                       role="tab" aria-controls="nav-config-ssma"
                                       aria-selected="false">CONFIGURAÇÕES</a>
                                </li>
                            </ul>

                            <div class="tab-content py-3 p-2">

                                <div class="tab-pane  show active"
                                     id="nav-documentossma"
                                     role="tabpanel" aria-labelledby="nav-documentossma-tab">

                                    <fieldset>
                                        <legend class="text-uppercase">
                                            <span>Documentos Gerenciaveis</span>
                                        </legend>
                                        <div class="row">

                                            <div class="col-12">
                                                <div class="row py-3">

                                                    <div class="col-12 col-sm-4">
                                                        <div class="form-group">
                                                            <datepicker label="Data Início" posicao="up"
                                                                        v-model="form.documentos_ssma.data_inicio"></datepicker>
                                                        </div>
                                                    </div>

                                                    <div class="col-12 col-sm-4">
                                                        <div class="form-group">
                                                            <datepicker label="Data Vencimento" posicao="up"
                                                                        v-model="form.documentos_ssma.data_encerramento"></datepicker>
                                                        </div>
                                                    </div>

                                                    <div class="col-12 col-sm-12 col-lg-4">
                                                        <div class="form-group">
                                                            <label>Tipo de documento</label>
                                                            <select v-model="form.documentos_ssma.tipo_id"
                                                                    class="form-control validacampo"
                                                                    @change.prevent="valida_campo_vazio($event.target, 1)"
                                                                    onblur="valida_campo_vazio(this, 1)">
                                                                <option value="">Selecione ...</option>
                                                                <option v-for="item in listaDocumentosFiltrados"
                                                                        :value="item.id" v-text="item.nome"></option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label>Observação</label>
                                                            <textarea class="form-control"
                                                                      v-model="form.documentos_ssma.observacao" rows="3"
                                                                      cols="3"></textarea>
                                                        </div>
                                                    </div>


                                                    <div class="col-12">
                                                        <fieldset>
                                                            <legend>ANEXO(S)</legend>
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <upload :model="form.documentos_ssma.anexos"
                                                                            :model-delete="form.documentos_ssma.anexosDel"
                                                                            :url="urlAnexoUpload"
                                                                            label="Selecionar Arquivo(s)"
                                                                            @onProgresso="anexoUploadAndamento=true"
                                                                            @onFinalizado="anexoUploadAndamento=false"></upload>
                                                                </div>
                                                            </div>
                                                        </fieldset>
                                                    </div>

                                                    <div class="col-12 col-sm-6 col-lg-4">
                                                        <div class="form-group">
                                                            <label>Status</label>
                                                            <select v-model="form.documentos_ssma.status"
                                                                    class="form-control">
                                                                <option value="Iniciado">INICIADO</option>
                                                                <option value="Concluido">CONCLUIDO</option>
                                                                <option value="Não iniciado">NÃO INICIADO</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-12 col-sm-6 col-lg-4">
                                                        <div class="form-group">
                                                            <label>Ativo</label>
                                                            <select v-model="form.documentos_ssma.ativo"
                                                                    class="form-control">
                                                                <option :value="true">SIM</option>
                                                                <option :value="false">NÃO</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <hr style="margin-top: 0; margin-bottom: 0; border: 0; width: 97%; border-top: 1px dashed rgba(0, 0, 0, 0.3);">

                                                </div>

                                            </div>
                                        </div>
                                    </fieldset>

                                </div>

                                <div class="tab-pane fade" id="nav-config-ssma"
                                     role="tabpanel" aria-labelledby="nav-config-ssma-tab">
                                    <fieldset>
                                        <legend class="text-uppercase">
                                            <span>Configurações Gerais</span>
                                        </legend>
                                        <div class="row">
                                            <div class="col-12 col-sm-6 col-lg-4">
                                                <div class="form-group">
                                                    <label>Notificar Vencimento E-mail</label>
                                                    <select
                                                        v-model="form.documentos_ssma.verifica_mes_vencimento"
                                                        class="form-control validacampo"
                                                        @keyup.prevent="valida_campo_vazio($event.target, 1)"
                                                        onblur="valida_campo_vazio(this, 1)">
                                                        <option value="">Selecione ...</option>
                                                        <option value="1">30 dias</option>
                                                        <option value="2">60 dias</option>
                                                        <option value="3">90 dias</option>
                                                        <option value="4">120 dias</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-6 col-lg-4">
                                                <div class="form-group" v-if="permissoes.whatsapp">
                                                    <label>Envia notificação no whatsapp</label>
                                                    <select v-model="form.documentos_ssma.envia_whatsapp"
                                                            class="form-control validacampo"
                                                            @keyup.prevent="valida_campo_vazio($event.target, 1)"
                                                            onblur="valida_campo_vazio(this, 1)">
                                                        <option value="">Selecione ...</option>
                                                        <option :value="true">Sim</option>
                                                        <option :value="false">Não</option>
                                                    </select>
                                                </div>
                                            </div>

                                        </div>
                                    </fieldset>
                                </div>
                            </div>
                        </div>
                    </div>

                </form>
            </template>
            <template slot="rodape">
                <button type="button" class="btn btn-sm btn-primary" v-show="editando && !atualizado && !preloadAjax"
                        @click="alterar()">
                    Alterar
                </button>
                <button type="button" class="btn btn-sm btn-primary" v-show="!editando && !cadastrado && !preloadAjax"
                        @click="cadastrar()">
                    Cadastrar
                </button>
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
                        <select class="form-control form-control-sm" v-model="controle.dados.campoStatus"
                                :disabled="controle.carregando"
                                @change="$refs.componente.buscar()">
                            <option value="">Todos os Status</option>
                            <option :value="true">Apenas Ativos</option>
                            <option :value="false">Apenas Inativos</option>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-md-9">
                    <button type="button" class="btn btn-sm btn-success" :disabled="controle.carregando"
                            @click="atualizar">
                        <i
                            :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                        Atualizar
                    </button>

                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" v-if="permissoes.insert"
                            :disabled="controle.carregando"
                            data-target="#janelaCadastrar"
                            @click="formNovo()">
                        Cadastrar
                    </button>

                </div>
            </form>
        </fieldset>
        <p class="text-center" v-if="controle.carregando">
            <preload></preload>
        </p>
        <div id="conteudo">
            <div class="alert alert-warning" v-show="!controle.carregando && lista.length === 0">
                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
            </div>
            <div class="table-responsive" v-show="!controle.carregando && lista.length > 0">
                <table class="tabela">
                    <thead>
                    <tr class="bg-default">
                        <th>ID</th>
                        <th>Documento</th>
                        <th>Referente</th>
                        <th>Data Inicio</th>
                        <th>Data Encerramento</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="item in lista">
                        <td data-label="ID">
                            {{ item.id }}
                        </td>

                        <td data-label="Documento">
                            {{ item.documentos_ssma.tipo_descricao }}
                        </td>

                        <td data-label="TipoDescricao">
                            {{
                                item.contrato_id ? item.contrato.dados_cadastrais.nome ? item.contrato.dados_cadastrais.nome : item.contrato.dados_cadastrais.razao_social : item.empresa.razao_social
                            }}
                        </td>

                        <td data-label="DataInicio">
                            {{ item.documentos_ssma.data_inicio }}
                        </td>

                        <td data-label="DataFim">
                            {{ item.documentos_ssma.data_encerramento }}
                        </td>

                        <td data-label="Status">
                            <bt-ativo :rota="`administracao/documentoslegais/ssma/${item.id}/ativa-desativa`"
                                      :model="item.documentos_ssma"></bt-ativo>
                        </td>

                        <td data-label="Ações">
                            <a :href="`ssma/${item.id}/pdf`" v-if="permissoes.pdf"
                               class="btn btn-sm btn-primary mb-1" v-tippy content="PDF"
                               target="_blank">
                                <i class="fa fa-file-pdf"></i>
                            </a>

                            <a href="javascript://" class="btn btn-sm btn-primary mb-1" v-tippy content="Editar"
                               v-if="permissoes.update"
                               @click.prevent="formAlterar(item.id)"
                               data-toggle="modal"
                               data-target="#janelaCadastrar">
                                <i class="fa fa-edit" aria-hidden="true"></i>
                            </a>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <controle-paginacao class="d-flex justify-content-center" id="controle" ref="componente"
                                :url="rotapaginacao" :por-pagina="controle.dados.pages"
                                :dados="controle.dados"
                                v-on:carregou="carregou" v-on:carregando="carregando"></controle-paginacao>
        </div>

    </div>
</template>

<script>
import ExportacaoMixin from "../../../../mixins/Exportacoes";
import Validacoes from "../../../../mixins/Validacoes";
import telefone from "../../../Telefones";
import endereco from "../../../Endereco";
import datepicker from "../../../DatePicker";
import upload from "../../../Upload";
import ControlePaginacao from "../../../ControlePaginacao";

export default {
    name: "documentossma",
    mixins: [ExportacaoMixin, Validacoes],
    components: {
        telefone,
        endereco,
        datepicker,
        upload,
        ControlePaginacao
    },
    data() {
        return {
            tituloJanela: 'Cadastrando Documento SSMA',
            preloadAjax: false,
            editando: false,
            apagado: false,
            tipo_pessoa_fisica: '',

            form: {
                tipo_ssma: '',
                contrato_id: '',
                documentos_ssma: {
                    servico_id: '',
                    data_inicio: moment().format('L'),
                    data_encerramento: moment().add(6, 'months').format('L'),
                    observacao: '',
                    tipo_descricao: '',
                    tipo_id: '',
                    status: 'Iniciado',
                    ativo: true,
                    id: '',
                    verifica_mes_vencimento: '',
                    envia_whatsapp: '',
                    anexos: [],
                    anexosDel: [],
                },
                ativo: '',

                documentos_empresaDelete: [],

            },
            lista_tipos_documentos: [],
            formDefault: null,

            urlAnexoUpload: `${URL_ADMIN}/administracao/documentoslegais/ssma/uploadAnexos`,
            rotapaginacao: `${URL_ADMIN}/administracao/documentoslegais/ssma/atualizar`,
            anexoUploadAndamento: false,

            cadastrado: false,
            atualizado: false,
            leitura: false,

            lista: [],
            listaContratos: [],
            permissoes: [],

            controle: {
                carregando: false,
                dados: {
                    campoBusca: "",
                    campoTipo: "",
                    campoStatus: "",
                    pages: 50
                },
            },
        }
    },
    mounted() {
        this.formDefault = _.cloneDeep(this.form) //copia
        this.atualizar();

    },
    computed: {
        listaDocumentosFiltrados() {
            return _.filter(this.lista_tipos_documentos, {tipo: this.form.tipo_ssma ? 'ssma' : 'contrato'})
        }
    },

    methods: {

        formNovo() {
            this.tituloJanela = "Cadastrando Documento SSMA";

            this.cadastrado = false;
            this.atualizado = false;
            this.editando = false;
            this.leitura = false;

            formReset();
            setupCampo();

            this.form = _.cloneDeep(this.formDefault) //copia

        },

        cadastrar() {
            this.validaBlur();
            this.$nextTick(() => {
                $("#janelaCadastrar :input:enabled").trigger("blur");

                $('#nav-documentossma :input:enabled.is-invalid').length > 0 ? $('#nav-documentossma-tab').addClass('bg-danger text-white') : $('#nav-documentossma-tab').removeClass('bg-danger text-white');
                $('#nav-config-ssma :input:enabled.is-invalid').length > 0 ? $('#nav-config-ssma-tab').addClass('bg-danger text-white') : $('#nav-config-ssma-tab').removeClass('bg-danger text-white');

                if ($("#janelaCadastrar :input:enabled.is-invalid").length) {
                    this.mostraErro("", "Existem campos obrigatórios não preenchidos");
                    return false;
                }
                this.preloadAjax = true;
                this.form.documentos_ssma.tipo_descricao = _.filter(this.lista_tipos_documentos, {id: this.form.documentos_ssma.tipo_id})[0].nome;

                axios.post(`${URL_ADMIN}/administracao/documentoslegais/ssma`, this.form)
                    .then(response => {
                        if (response.status === 201) {
                            this.preloadAjax = false;
                            this.cadastrado = true;
                            this.atualizar();
                        }
                    }).catch(error => (this.preloadAjax = false));
            });
        },

        formAlterar(id) {
            this.tituloJanela = "Alterando Empresa";

            this.cadastrado = false;
            this.atualizado = false;
            this.editando = true;
            this.preloadAjax = true;
            formReset();

            this.form = _.cloneDeep(this.formDefault) //copia
            this.leitura = false;

            axios.get(`${URL_ADMIN}/administracao/documentoslegais/ssma/${id}/editar`)
                .then(response => {
                    Object.assign(this.form, response.data);
                    this.preloadAjax = false;
                }).catch(
                error => (this.preloadAjax = false)
            );

        },

        alterar() {
            this.validaBlur();
            this.$nextTick(() => {

                $("#janelaCadastrar :input:enabled").trigger("blur");

                $('#nav-documentossma :input:enabled.is-invalid').length > 0 ? $('#nav-documentossma-tab').addClass('bg-danger text-white') : $('#nav-documentossma-tab').removeClass('bg-danger text-white');
                $('#nav-config-ssma :input:enabled.is-invalid').length > 0 ? $('#nav-config-ssma-tab').addClass('bg-danger text-white') : $('#nav-config-ssma-tab').removeClass('bg-danger text-white');

                if ($("#janelaCadastrar :input:enabled.is-invalid").length) {
                    this.mostraErro("", "Existem campos obrigatórios não preenchidos");
                    return false;
                }

                this.preloadAjax = true;
                this.form.documentos_ssma.tipo_descricao = _.filter(this.lista_tipos_documentos, {id: this.form.documentos_ssma.tipo_id})[0].nome;

                axios.put(`${URL_ADMIN}/administracao/documentoslegais/ssma/${this.form.id}`, this.form).then(response => {
                    this.atualizado = true;
                    this.atualizar();
                    this.preloadAjax = false;

                }).catch(error => (this.preloadAjax = false));

            });
        },

        carregou(dados) {
            this.lista = dados.itens;
            this.tipo_pessoa_fisica = dados.tipo_pessoa_fisica;
            this.listaContratos = dados.lista_contratos;
            this.lista_tipos_documentos = dados.tipos_documentos;
            this.controle.carregando = false;
            this.permissoes = dados.permissoes;

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

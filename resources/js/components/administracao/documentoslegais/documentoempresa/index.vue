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
                                    <select class="form-control" v-model="form.tipo_empresa"
                                            :disabled="editando"
                                            onblur="valida_campo_vazio(this,1)" onchange="valida_campo_vazio(this,1)">
                                        <option value="">Selecione ...</option>
                                        <option :value="true">Empresa</option>
                                        <option :value="false">Contrato</option>
                                    </select>
                                </div>

                                <div class="col-12 col-md-6" v-if="form.tipo_empresa === false">
                                    <label>Selecione o Contrato</label>
                                    <select class="form-control" v-model="form.contrato_id"
                                            :disabled="editando"
                                            onblur="valida_campo_vazio(this,1)" onchange="valida_campo_vazio(this,1)">
                                        <option value="">Selecione ...</option>
                                        <option :value="item.id" :key="item.id" v-for="item in listaContratos">{{ item.tipo === tipo_pessoa_fisica ? item.nome : item.razao_social }}</option>
                                    </select>
                                </div>

                            </div>
                        </fieldset>

                        <div v-if="form.tipo_empresa !== ''">

                            <ul class="nav nav-tabs bg-light" id="tabslist" role="tablist"
                                style="border-bottom: 1px solid #653232">
                                <li class="nav-item">
                                    <a class="nav-item nav-link active" id="nav-documentoempresa-tab" data-toggle="tab"
                                       href="#nav-documentoempresa"
                                       role="tab" aria-controls="nav-documentoempresa" aria-selected="false">DOCUMENTO
                                        LEGAIS</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-item nav-link" id="nav-config-empresa-tab" data-toggle="tab"
                                       href="#nav-config-empresa"
                                       role="tab" aria-controls="nav-config-empresa"
                                       aria-selected="false">CONFIGURAÇÕES</a>
                                </li>
                            </ul>

                            <div class="tab-content py-3 p-2">
                                <div class="tab-pane show active"
                                     id="nav-documentoempresa"
                                     role="tabpanel" aria-labelledby="nav-documentoempresa-tab">

                                    <fieldset>
                                        <legend class="text-uppercase">
                                            <span>Documentos Gerenciaveis</span>
                                        </legend>
                                        <div class="row">
                                            <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                                                <button class="btn btn-sm btn-secondary mb-2"
                                                        @click="addLIDocumentoEmpresa($event.target)">
                                                    <span class="fas fa-plus" aria-hidden="true"></span>
                                                    Adicionar Documentos
                                                </button>
                                            </div>

                                            <div class="col-12" v-if="form.documentos_empresa.length>0"
                                                 v-for="(obj, index) in form.documentos_empresa" :key="obj.id">
                                                <div class="row py-3">

                                                    <div class="col-12 col-sm-4">
                                                        <div class="form-group">
                                                            <datepicker label="Data Iníco" posicao="up"
                                                                        v-model="obj.data_inicio"
                                                                        default=""></datepicker>
                                                        </div>
                                                    </div>

                                                    <div class="col-12 col-sm-4">
                                                        <div class="form-group">

                                                            <datepicker label="Data Vencimento" posicao="up"
                                                                        v-model="obj.data_encerramento"
                                                                        default=""></datepicker>
                                                        </div>
                                                    </div>

                                                    <div class="col-12"></div>

                                                    <div class="col-12 col-sm-6 col-lg-4">
                                                        <div class="form-group">
                                                            <label>Tipo de documento</label>
                                                            <input type="text" class="form-control"
                                                                   v-model="obj.tipo_descricao">
                                                            <!--                                                        <select v-model="obj.servico_id" class="form-control"-->
                                                            <!--                                                                onblur="valida_campo_vazio(this,1)">-->
                                                            <!--                                                            <option value="">Selecione ...</option>-->
                                                            <!--                                                            <option v-for="item in listaServicos" :value="item.id">-->
                                                            <!--                                                                {{ item.titulo }}-->
                                                            <!--                                                            </option>-->
                                                            <!--                                                        </select>-->
                                                        </div>
                                                    </div>


                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label>Observação</label>
                                                            <textarea class="form-control" v-model="obj.observacao"
                                                                      rows="3"
                                                                      cols="3"></textarea>
                                                        </div>
                                                    </div>


                                                    <div class="col-12">
                                                        <fieldset>
                                                            <legend>ANEXO(S)</legend>
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <upload :model="obj.anexos"
                                                                            :model-delete="obj.anexosDel"
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
                                                            <select v-model="obj.status" class="form-control">
                                                                <option value="Iniciado">INICIADO</option>
                                                                <option value="Concluido">CONCLUIDO</option>
                                                                <option value="Não iniciado">NÃO INICIADO</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-12 col-sm-6 col-lg-4">
                                                        <div class="form-group">
                                                            <label>Ativo</label>
                                                            <select v-model="obj.ativo" class="form-control">
                                                                <option :value="true">SIM</option>
                                                                <option :value="false">NÃO</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-12 mb-3">
                                                        <button class="btn btn-sm btn-danger"
                                                                @click="removerLIServicoCliente(index)"
                                                                v-show="obj.nova">
                                                            <i
                                                                class="fa fa-trash"></i> Remover
                                                        </button>
                                                    </div>

                                                    <hr style="margin-top: 0; margin-bottom: 0; border: 0; width: 97%; border-top: 1px dashed rgba(0, 0, 0, 0.3);">

                                                </div>

                                            </div>
                                        </div>
                                    </fieldset>

                                </div>

                                <div class="tab-pane fade" id="nav-config-empresa"
                                     role="tabpanel" aria-labelledby="nav-config-empresa-tab">
                                    <fieldset>
                                        <legend class="text-uppercase">
                                            <span>Configurações Gerais</span>
                                        </legend>
                                        <div class="row">
                                            <div class="col-12 col-sm-6 col-lg-4">
                                                <div class="form-group">
                                                    <label>Notificar Vencimento E-mail</label>
                                                    <select
                                                        v-model="form.documento_empresa_config.verifica_mes_vencimento"
                                                        class="form-control"
                                                        onblur="valida_campo_vazio(this,1)">
                                                        <option value="">Selecione ...</option>
                                                        <option value="1">30 dias</option>
                                                        <option value="2">60 dias</option>
                                                        <option value="3">90 dias</option>
                                                        <option value="4">120 dias</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-6 col-lg-4">
                                                <div class="form-group">
                                                    <label>Envia notificação no whatsapp</label>
                                                    <select v-model="form.documento_empresa_config.envia_whatsapp"
                                                            class="form-control"
                                                            onblur="valida_campo_vazio(this,1)">
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

                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal"
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
                        <th>Descrição Tipo</th>
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

                        <td data-label="TipoDescricao">
                            {{ item.documentos_empresa.tipo_descricao }}
                        </td>

                        <td data-label="Datainicio">
                            {{ item.documentos_empresa.data_inicio }}
                        </td>

                        <td data-label="Dataencerramento">
                            {{ item.documentos_empresa.data_encerramento }}
                        </td>
                        <td data-label="Status">
                            <bt-ativo :rota="`administracao/documentoslegais/empresa/${item.id}/ativa-desativa`"
                                      :model="item"></bt-ativo>
                        </td>

                        <td data-label="Ações">
                            <a :href="`documentoslegais/${item.id}/pdf`"
                               class="btn btn-sm btn-primary mb-1" v-tippy content="Ficha"
                               target="_blank">
                                <i class="fa fa-file-pdf"></i>
                            </a>

                            <a href="javascript://" class="btn btn-sm btn-primary mb-1" v-tippy content="Editar"
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
import telefone from "../../../Telefones";
import endereco from "../../../Endereco";
import datepicker from "../../../DatePicker";
import upload from "../../../Upload";
import ControlePaginacao from "../../../ControlePaginacao";
import ExportacaoMixin from "../../../../mixins/Exportacoes";
import Validacoes from "../../../../mixins/Validacoes";

export default {
    name: "documentoempresa",
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
            tituloJanela: 'Cadastrando Documento Empresa',
            preloadAjax: false,
            editando: false,
            apagado: false,
            tipo_pessoa_fisica: '',

            form: {
                tipo_empresa: '',
                contrato_id: '',
                documentos_empresa: [],
                ativo: '',
                anexos: [],
                anexosDel: [],

                documento_empresa_config: {
                    id: '',
                    verifica_mes_vencimento: '',
                    envia_whatsapp: '',
                },

                documentos_empresaDelete: [],

            },
            formDefault: null,

            urlAnexoUpload: `${URL_ADMIN}/administracao/documentoslegais/empresa/uploadAnexos`,
            rotapaginacao: `${URL_ADMIN}/administracao/documentoslegais/empresa/atualizar`,
            anexoUploadAndamento: false,

            cadastrado: false,
            atualizado: false,
            leitura: false,

            lista: [],
            listaContratos: [],


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
    methods: {
        addLIDocumentoEmpresa() {
            const obj = {};
            obj.nova = true;
            obj.servico_id = '';
            obj.data_inicio = moment().format('L');
            obj.data_encerramento = moment().add(6, 'months').format('L');
            obj.observacao = '';
            obj.tipo_descricao = '';
            obj.status = 'Iniciado';
            obj.feedback = '';
            obj.ativo = true;

            obj.anexos = [];
            obj.anexosDel = [];
            this.form.documentos_empresa.unshift(obj);
        },
        removerLIDocumentoEmpresa(index) {
            if (this.editando) {
                this.form.documentos_empresaDelete.push(this.form.documentos_empresa[index].id);
            }
            this.form.documentos_empresa.splice(index, 1);
        },

        formNovo() {
            this.tituloJanela = "Cadastrando Documento Empresa";

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
                if ($("#janelaCadastrar :input:enabled.is-invalid").length) {
                    this.mostraErro("", "Existem campos obrigatórios não preenchidos");
                    return false;
                }
                this.preloadAjax = true;

                axios.post(`${URL_ADMIN}/administracao/documentoslegais/empresa`, this.form)
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

            axios.get(`${URL_ADMIN}/administracao/documentoslegais/empresa/${id}/editar`)
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
                if ($("#janelaCadastrar :input:enabled.is-invalid").length) {
                    this.mostraErro("", "Existem campos obrigatórios não preenchidos");
                    return false;
                }

                if (this.form.telefones.length === 0) {
                    mostraErro('', 'Por favor insira um Telefone');
                    return false;
                }

                this.preloadAjax = true;

                axios.put(`${URL_ADMIN}/administracao/documentoslegais/empresa/${this.form.id}`, this.form).then(response => {
                    this.atualizado = true;
                    this.atualizar();
                    this.preloadAjax = false;

                }).catch(error => (this.preloadAjax = false));

            });
        },

        carregou(dados) {
            this.lista = dados.itens;
            this.listaContratos = dados.lista_contratos;
            this.tipo_pessoa_fisica = dados.tipo_pessoa_fisica;
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

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
                        <ul class="nav nav-tabs bg-light" id="tabslist" role="tablist"
                            style="border-bottom: 1px solid #653232">
                            <li class="nav-item">
                                <a class="nav-item nav-link active" id="nav-dados-cadastrais-tab" data-toggle="tab"
                                   href="#nav-dados-cadastrais"
                                   role="tab" aria-controls="nav-dados-cadastrais" aria-selected="true">DADOS
                                    CADASTRAIS</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-item nav-link" id="nav-service-tab" data-toggle="tab" href="#nav-service"
                                   role="tab" aria-controls="nav-service" aria-selected="false">SERVIÇOS</a>
                            </li>

                        </ul>

                        <div class="tab-content py-3 p-2">
                            <div class="tab-pane fade show active" id="nav-dados-cadastrais" role="tabpanel"
                                 aria-labelledby="nav-dados-cadastrais-tab">
                                <fieldset>
                                    <legend class="text-uppercase">Dados do Contratante</legend>
                                    <div class="row">
                                        <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                            <div class="form-group">
                                                <label>Tipo</label>
                                                <select class="form-control" v-model="form.dados_cadastrais.tipo"
                                                        :disabled="editando">
                                                    <option
                                                        value="Pessoa Jurídica">Pessoa Jurídica
                                                    </option>
                                                    <option
                                                        value="Pessoa Física">Pessoa Física
                                                    </option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                            <div class="form-group"
                                                 v-if="form.dados_cadastrais.tipo === 'Pessoa Jurídica'">
                                                <label>CNPJ</label>
                                                <input type="text" id="cnpj" class="form-control validacampo" placeholder="CNPJ"
                                                       v-model="form.dados_cadastrais.cnpj" :disabled="editando"
                                                       autocomplete="off" @keyup.prevent="valida_cnpj_vazio($event.target)"  onblur="valida_cnpj_vazio(this)"
                                                       v-mascara:cnpj>
                                            </div>

                                            <div class="form-group"
                                                 v-if="form.dados_cadastrais.tipo === 'Pessoa Física'">
                                                <label>CPF</label>
                                                <input type="text" class="form-control validacampo" placeholder="CPF"
                                                       v-model="form.dados_cadastrais.cpf" :disabled="editando"
                                                       autocomplete="off"
                                                       @keyup.prevent="valida_cpf_vazio($event.target)"  onblur="valida_cpf_vazio(this)" v-mascara:cpf>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                            <div class="form-group"
                                                 v-if="form.dados_cadastrais.tipo === 'Pessoa Jurídica'">
                                                <label>Razão Social</label>
                                                <input type="text" class="form-control validacampo"
                                                       v-model="form.dados_cadastrais.razao_social"
                                                       placeholder="Razão Social"
                                                       autocomplete="off" @keyup.prevent="valida_campo_vazio($event.target, 3)"  onblur="valida_campo_vazio(this, 3)">
                                            </div>

                                            <div class="form-group"
                                                 v-if="form.dados_cadastrais.tipo === 'Pessoa Física'">
                                                <label>Nome</label>
                                                <input type="text" class="form-control validacampo"
                                                       v-model="form.dados_cadastrais.nome"
                                                       placeholder="Nome do Cliente"
                                                       autocomplete="off" @keyup.prevent="valida_campo_vazio($event.target, 3)"  onblur="valida_campo_vazio(this, 3)">
                                            </div>
                                        </div>

                                        <div class="col-12 col-sm-6 col-lg-6 col-xl-6"
                                             v-if="form.dados_cadastrais.tipo === 'Pessoa Jurídica'">
                                            <div class="form-group">
                                                <label>Nome Fantasia</label>
                                                <input type="text" class="form-control validacampo"
                                                       v-model="form.dados_cadastrais.nome_fantasia"
                                                       placeholder="Nome Fantasia"
                                                       autocomplete="off" @keyup.prevent="valida_campo_vazio($event.target, 3)"  onblur="valida_campo_vazio(this, 3)">
                                            </div>
                                        </div>

                                        <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                            <div class="form-group">
                                                <label>Área de Atuação</label>
                                                <select v-model="form.dados_cadastrais.area_id" class="form-control validacampo"
                                                        @change.prevent="valida_campo_vazio($event.target, 1)"  onblur="valida_campo_vazio(this, 1)">
                                                    <option value="">Selecione</option>
                                                    <option v-for="item in listaAreas" :value="item.id">{{ item.label }}
                                                    </option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                            <div class="form-group">
                                                <label>Ramo</label>
                                                <input type="text" class="form-control validacampo"
                                                       v-model="form.dados_cadastrais.ramo"
                                                       placeholder="Ramo"
                                                       autocomplete="off" @keyup.prevent="valida_campo_vazio($event.target, 3)"  onblur="valida_campo_vazio(this, 3)">
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>

                                <fieldset>
                                    <legend class="text-uppercase">Endereço</legend>
                                    <endereco :model="form.dados_cadastrais"></endereco>
                                </fieldset>

                                <fieldset>
                                    <legend class="text-uppercase">Contatos</legend>
                                    <div class="row">
                                        <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                            <div class="form-group">
                                                <label>Responsável</label>
                                                <input type="text" class="form-control validacampo"
                                                       placeholder="Nome do Responsável"
                                                       v-model="form.dados_cadastrais.responsavel"
                                                       autocomplete="off" @keyup.prevent="valida_campo_vazio($event.target, 3)"  onblur="valida_campo_vazio(this, 3)">
                                            </div>
                                        </div>

                                    </div>

                                    <div class="row">
                                        <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                            <div class="form-group">
                                                <label>E-mail</label>
                                                <input type="text" class="form-control validacampo" id="email" placeholder="E-mail"
                                                       v-model="form.dados_cadastrais.email"
                                                       autocomplete="off" @keyup.prevent="valida_campo_vazio($event.target, 3)"  onblur="valida_campo_vazio(this, 3)"
                                                       v-mascara:email>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <fieldset>
                                                <legend class="text-uppercase">Telefones</legend>
                                                <telefone :model="form.dados_cadastrais.telefones" :ramal="false"
                                                          :pais="false"
                                                          :model-delete="form.dados_cadastrais.telefonesDelete"></telefone>
                                            </fieldset>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <fieldset>
                                                <legend class="text-uppercase">Upload da Logo</legend>
                                                <div class="row">
                                                    <div class="col-12">
                                                        <upload :model="form.dados_cadastrais.logo"
                                                                :model-delete="form.dados_cadastrais.logoDel"
                                                                :url="urlLogoUpload"
                                                                :quantidade="1"
                                                                :multi="false"
                                                                :apenas-imagens="true"
                                                                label="Selecione a Logo"
                                                                @onProgresso="logoUploadAndamento=true"
                                                                @onFinalizado="logoUploadAndamento=false"></upload>
                                                    </div>
                                                </div>
                                            </fieldset>
                                        </div>
                                    </div>
                                </fieldset>

                                <div class="custom-control custom-switch">
                                    <input type="checkbox" v-model="form.ativo" class="custom-control-input" id="ativo">
                                    <label class="custom-control-label"
                                           for="ativo">{{ form.ativo ? 'Ativo' : 'Inativo' }}</label>
                                </div>

                            </div>

                            <div class="tab-pane fade" id="nav-service"
                                 role="tabpanel" aria-labelledby="nav-service-tab">

                                <fieldset>
                                    <legend class="text-uppercase">
                                        <span>SERVIÇOS CONTRATADOS</span>
                                    </legend>
                                    <div class="row">
                                        <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                                            <button class="btn btn-sm btn-secondary mb-2"
                                                    @click="addLIServicoContrato($event.target)">
                                                <span class="fas fa-plus" aria-hidden="true"></span>
                                                Adicionar Serviço
                                            </button>
                                        </div>

                                        <div class="col-12" v-if="form.dados_cadastrais.servicos_contrato.length>0"
                                             v-for="(obj, index) in form.dados_cadastrais.servicos_contrato" :key="obj.id">
                                            <div class="row py-3">

                                                <div class="col-12 col-sm-4">
                                                    <div class="form-group">
                                                        <datepicker label="Data Início" posicao="up"
                                                                    v-model="obj.data_inicio"></datepicker>
                                                    </div>
                                                </div>

                                                <div class="col-12 col-sm-4">
                                                    <div class="form-group">
                                                        <datepicker label="Data Vencimento" posicao="up"
                                                                    v-model="obj.data_encerramento"></datepicker>
                                                    </div>
                                                </div>

                                                <div class="col-12"></div>

                                                <div class="col-12 col-sm-6 col-lg-4">
                                                    <label>Tipo de serviço</label>
                                                    <select class="form-control validacampo" v-model="obj.select_tipo_servico"
                                                            @change.prevent="valida_campo_vazio($event.target, 1)"  onblur="valida_campo_vazio(this, 1)">
                                                        <option value="">Selecione ...</option>
                                                        <option v-for="item in listaServicos" :value="item.id">{{ item.titulo }}</option>
                                                    </select>
                                                </div>

                                                <div class="col-12 col-sm-6 col-lg-4">
                                                    <div class="form-group">
                                                        <label>Valor R$</label>
                                                        <input type="text" v-mascara:dinheiro
                                                               class="form-control text-right"
                                                               onblur="valida_dinheiro(this)"
                                                               placeholder="0,00"
                                                               v-model="obj.valor">
                                                    </div>
                                                </div>

                                                <div class="col-12 col-sm-6 col-lg-4">
                                                    <div class="form-group">
                                                        <label>Tipo do Faturamento</label>
                                                        <select v-model="obj.tipo_faturamento" class="form-control validacampo"
                                                                @change.prevent="valida_campo_vazio($event.target, 1)"  onblur="valida_campo_vazio(this, 1)">
                                                            <option value="">Selecione</option>
                                                            <option value="Único">ÚNICO</option>
                                                            <option value="Por execução">POR EXECUÇÃO</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <div class="form-group">
                                                        <label>Observação</label>
                                                        <textarea class="form-control validacampo" v-model="obj.observacao" rows="3" cols="3"></textarea>
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
                                                    <label>Tipo Contrato</label>
                                                    <select class="form-control validacampo" v-model="obj.tipo_contrato"
                                                            @change.prevent="valida_campo_vazio($event.target, 1)"  onblur="valida_campo_vazio(this, 1)">
                                                        <option value="">Selecione ...</option>
                                                        <option v-for="item in listaFormasContrato" :value="item.id">{{ item.titulo }}</option>
                                                    </select>
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
                                                            @click="removerLIServicoContrato(index)"
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
                        <th>Nome</th>
                        <th>Área / Ramo</th>
                        <th>Contato</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="item in lista">
                        <td data-label="ID">
                            {{ item.id }}
                        </td>

                        <td data-label="Nome">
                            {{
                                item.dados_cadastrais.tipo == 'Pessoa Física' ? item.dados_cadastrais.nome :
                                    item.dados_cadastrais.razao_social
                            }}
                        </td>

                        <td data-label="Área / Ramo">
                            {{ item.dados_cadastrais.ramo }}
                        </td>

                        <td data-label="Contato">
                            {{ item.dados_cadastrais.responsavel }} -
                            <span v-for="tel in item.dados_cadastrais.telefones">{{ tel.numero }}</span>
                        </td>


                        <td data-label="Status">
                            <bt-ativo :rota="`administracao/documentoslegais/contrato/${item.id}/ativa-desativa`"
                                      :model="item"></bt-ativo>
                        </td>

                        <td data-label="Ações">
                            <a :href="`contrato/${item.id}/pdf`" v-if="permissoes.pdf"
                               class="btn btn-sm btn-primary mb-1" v-tippy content="PDF"
                               target="_blank">
                                <i class="fa fa-file-pdf"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-info mb-1" v-if="permissoes.pdf && temDocumentoAssinatura(item)"
                                    v-tippy content="Gerenciar assinatura digital"
                                    @click.prevent="abrirModalGerenciarAssinatura(item)">
                                <i class="fa fa-cog"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-success mb-1" v-else-if="permissoes.pdf"
                                    v-tippy content="Enviar para assinatura digital"
                                    @click.prevent="abrirModalAssinatura(item)">
                                <i class="fa fa-pen-fancy"></i>
                            </button>

                            <a href="javascript://" class="btn btn-sm btn-primary mb-1" v-tippy content="Editar" v-if="permissoes.update"
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

        <acao-assinatura-documento
            ref="acaoAssinaturaContrato"
            :id-prefix="'contrato_documentos_legais'"
            :titulo-enviar="'Enviar para assinatura digital'"
            :get-nome-documento="getNomeDocumentoAssinaturaContrato"
            :get-signatarios-iniciais="getSignatariosIniciaisAssinaturaContrato"
            :enviar-handler="enviarAssinaturaContrato"
            :atualizar-handler="atualizar">
        </acao-assinatura-documento>
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
import AcaoAssinaturaDocumento from "../../documentoassinatura/AcaoAssinaturaDocumento.vue";

export default {
    name: "contrato",
    mixins: [ExportacaoMixin, Validacoes],
    components: {
        telefone,
        endereco,
        datepicker,
        upload,
        ControlePaginacao,
        AcaoAssinaturaDocumento
    },
    data() {
        return {
            tituloJanela: 'Cadastrando Documento Contrato',
            preloadAjax: false,
            editando: false,
            apagado: false,

            form: {
                dados_cadastrais: {
                    cnpj: '',
                    cpf: '',
                    nome: '',
                    tipo: 'Pessoa Jurídica',
                    razao_social: '',
                    nome_fantasia: '',
                    area_id: '',
                    ramo: '',
                    cep: '',
                    logradouro: '',
                    numero: '',
                    complemento: '',
                    bairro: '',
                    municipio: '',
                    tipo_contrato: '',
                    uf: '',
                    responsavel: '',
                    email: '',

                    tel_principal: '',

                    logo: [],
                    logoDel: [],

                    telefones: [{
                        tipo: 'comercial',
                        pais: 55,
                        numero: '',
                        ramal: '',
                        detalhe: '',
                    }],
                    telefonesDelete: [],

                    servicos_contrato: [],
                    servicos_contratoDelete: [],
                },
                ativo: '',



            },
            formDefault: null,

            urlAnexoUpload: `${URL_ADMIN}/administracao/documentoslegais/contrato/uploadAnexos`,
            urlLogoUpload: `${URL_ADMIN}/administracao/documentoslegais/contrato/uploadLogo`,
            rotapaginacao: `${URL_ADMIN}/administracao/documentoslegais/contrato/atualizar`,
            anexoUploadAndamento: false,

            cadastrado: false,
            atualizado: false,
            leitura: false,

            lista: [],
            listaServicos: [],
            listaAreas: [],
            listaFormasContrato: [],
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

    methods: {
        temDocumentoAssinatura(item) {
            const doc = item && item.documento_para_assinatura;
            return !!(doc && doc.id);
        },
        abrirModalAssinatura(item) {
            this.$refs.acaoAssinaturaContrato.abrirEnvio(item);
        },
        abrirModalGerenciarAssinatura(item) {
            const doc = item && item.documento_para_assinatura;
            if (!doc || !doc.id) return;
            this.$refs.acaoAssinaturaContrato.abrirGerenciar(doc, item);
        },
        getNomeDocumentoAssinaturaContrato(item) {
            if (!item || !item.dados_cadastrais) return 'Contrato (Documentos Legais)';
            const dc = item.dados_cadastrais;
            const nome = dc.tipo === 'Pessoa Jurídica' ? dc.razao_social : dc.nome;
            return `Contrato (Documentos Legais) - ${nome || dc.responsavel || 'Cliente'}`;
        },
        getSignatariosIniciaisAssinaturaContrato(item) {
            if (!item || !item.dados_cadastrais) return [{ nome: '', email: '', cpf: '' }];
            const dc = item.dados_cadastrais;
            const nome = (dc.tipo === 'Pessoa Jurídica' ? dc.razao_social : dc.nome) || dc.responsavel || '';
            const email = dc.email || '';
            const cpf = dc.cpf || '';
            return [{ nome, email, cpf }];
        },
        enviarAssinaturaContrato({ contexto, signatarios }) {
            return axios.post(`${URL_ADMIN}/administracao/documentoslegais/contrato/enviar-para-assinatura`, {
                contrato_id: contexto.id,
                signatarios: signatarios.filter(s => s.email && s.nome),
                ordem_assinatura: 'sequencial',
            });
        },

        addLIServicoContrato() {
            const obj = {};
            obj.nova = true;
            obj.servico_id = '';
            obj.data_inicio = moment().format('L');
            obj.data_encerramento = moment().add(6, 'months').format('L');
            obj.observacao = '';
            obj.valor = '0.00';
            obj.select_tipo_servico = '';
            obj.status = 'Iniciado';
            obj.feedback = '';
            obj.tipo_contrato = '';
            obj.tipo_faturamento = '';
            obj.ativo = true;

            obj.anexos = [];
            obj.anexosDel = [];
            this.form.dados_cadastrais.servicos_contrato.unshift(obj);
        },
        removerLIServicoContrato(index) {
            if (this.editando) {
                this.form.dados_cadastrais.servicos_contratoDelete.push(this.form.dados_cadastrais.servicos_contrato[index].id);
            }
            this.form.dados_cadastrais.servicos_contrato.splice(index, 1);
        },

        formNovo() {
            this.tituloJanela = "Cadastrando Contrato";

            this.cadastrado = false;
            this.atualizado = false;
            this.editando = false;
            this.leitura = false;

            $('#nav-dados-cadastrais-tab').removeClass('bg-danger text-white');
            $('#nav-service-tab').removeClass('bg-danger text-white');

            formReset();
            setupCampo();

            this.form = _.cloneDeep(this.formDefault) //copia

        },

        cadastrar() {
            this.validaBlur();
            this.$nextTick(() => {
                $("#janelaCadastrar :input:enabled").trigger("blur");

                $('#nav-dados-cadastrais :input:enabled.is-invalid').length > 0 ? $('#nav-dados-cadastrais-tab').addClass('bg-danger text-white') : $('#nav-dados-cadastrais-tab').removeClass('bg-danger text-white');
                $('#nav-service :input:enabled.is-invalid').length > 0 ? $('#nav-service-tab').addClass('bg-danger text-white') : $('#nav-service-tab').removeClass('bg-danger text-white');

                if ($("#janelaCadastrar :input:enabled.is-invalid").length) {
                    this.mostraErro("", "Existem campos obrigatórios não preenchidos");
                    return false;
                }

                if (this.form.dados_cadastrais.telefones.length === 0) {
                    mostraErro('', 'Por favor insira um Telefone');
                    return false;
                }

                this.preloadAjax = true;

                axios.post(`${URL_ADMIN}/administracao/documentoslegais/contrato`, this.form)
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
            this.tituloJanela = "Alterando Contrato";

            this.cadastrado = false;
            this.atualizado = false;
            this.editando = true;
            this.preloadAjax = true;
            formReset();

            this.form = _.cloneDeep(this.formDefault) //copia
            this.leitura = false;

            axios.get(`${URL_ADMIN}/administracao/documentoslegais/contrato/${id}/editar`)
                .then(response => {
                    let dados = response.data;
                    dados.dados_cadastrais.area_id = !dados.dados_cadastrais.area_id ? '' : dados.dados_cadastrais.area_id;
                    dados.select_tipo_servico = !dados.select_tipo_servico ? '' : dados.select_tipo_servico;
                    Object.assign(this.form, response.data);
                    this.preloadAjax = false;
                }).catch(
                error => (this.preloadAjax = false)
            );

        },

        alterar() {
            this.validaBlur();
            this.$nextTick(() => {
                formReset();

                $("#janelaCadastrar :input:enabled").trigger("blur");

                $('#nav-dados-cadastrais :input:enabled.is-invalid').length > 0 ? $('#nav-dados-cadastrais-tab').addClass('bg-danger text-white') : $('#nav-dados-cadastrais-tab').removeClass('bg-danger text-white');
                $('#nav-service :input:enabled.is-invalid').length > 0 ? $('#nav-service-tab').addClass('bg-danger text-white') : $('#nav-service-tab').removeClass('bg-danger text-white');

                if ($("#janelaCadastrar :input:enabled.is-invalid").length) {
                    this.mostraErro("", "Existem campos obrigatórios não preenchidos");
                    return false;
                }

                if (this.form.dados_cadastrais.telefones.length === 0) {
                    mostraErro('', 'Por favor insira um Telefone');
                    return false;
                }

                this.preloadAjax = true;

                axios.put(`${URL_ADMIN}/administracao/documentoslegais/contrato/${this.form.id}`, this.form).then(response => {
                    this.atualizado = true;
                    this.atualizar();
                    this.preloadAjax = false;

                }).catch(error => (this.preloadAjax = false));

            });
        },

        verificaCpf() {
            if (!this.editando) {
                axios.get(`${URL_ADMIN}/administracao/documentoslegais/contrato/buscar-cpf?cpf=${this.form.dados_cadastrais.cpf}`)
                    .then(response => {
                    });
            }
        },
        verificaCnpj() {
            if (!this.editando) {
                axios.get(`${URL_ADMIN}/administracao/documentoslegais/contrato/buscar-cnpj?cnpj=${this.form.dados_cadastrais.cnpj}`)
                    .then(response => {
                    });
            }
        },

        carregou(dados) {
            this.lista = dados.itens;
            this.listaServicos = dados.tipos_servicos;
            this.listaFormasContrato = dados.formas_contrato;
            this.listaAreas = dados.areas;
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

</style>

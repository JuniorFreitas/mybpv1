@extends('layouts.sistema')
@section('title', 'Documentos Legais')
@push('css')
    <style type="text/css">
        .card-header {
            background-color: #174257 !important;
            border-radius: 10px;
        }

        .btn-link {
            font-weight: 400 !important;
            color: #ffffff !important;
        }
    </style>
@endpush
@section('content_header')
    <h4 class="text-default">Documentos Legais</h4>
    <hr class="bg-default" style="margin-top: -5px;">
@stop
@section('content')
    <modal id="janelaCadastrar" :titulo="tituloJanela" :size="90">
        <template slot="conteudo">
            <div v-show="preloadAjax"><i class="fa fa-spinner fa-pulse"></i> Aguarde...</div>
            <div class="alert alert-success alert-dismissible" v-show="cadastrado">
                <h4><i class="icon fa fa-check"></i>@{{form.tipo_documento}} cadastrado com sucesso!</h4>
            </div>
            <div class="alert alert-success alert-dismissible" v-show="atualizado">
                <h4><i class="icon fa fa-check"></i>@{{form.tipo_documento}} alterado com sucesso!</h4>
            </div>
            <form v-if="!preloadAjax && (!cadastrado && !atualizado)" id="form" onsubmit="return false;">

                <fieldset>
                    <legend class="text-uppercase">Tipo</legend>
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <label>Selecione o Tipo</label>
                            <select class="form-control" v-model="form.tipo_documento"
                                    :disabled="editando"
                                    onblur="valida_campo_vazio(this,1)" onchange="valida_campo_vazio(this,1)">
                                <option value="">Selecione ...</option>
                                <option value="Contrato">Contrato</option>
                                <option value="Documentos Empresa">Documentos Empresa</option>
                                <option value="Documentos SSMA">Documentos SSMA</option>
                            </select>
                        </div>
                    </div>
                </fieldset>

                <div v-if="form.tipo_documento !== '' && form.tipo_documento === 'Contrato'">

                    <ul class="nav nav-tabs bg-light" id="tabslist" role="tablist"
                        style="border-bottom: 1px solid #653232">
                        <li class="nav-item">
                            <a class="nav-item nav-link active" id="nav-dados-cadastrais-tab" data-toggle="tab"
                               href="#nav-dados-cadastrais"
                               role="tab" aria-controls="nav-dados-cadastrais" aria-selected="true">DADOS CADASTRAIS</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-item nav-link" id="nav-service-tab" data-toggle="tab" href="#nav-service"
                               role="tab" aria-controls="nav-service" aria-selected="false">SERVIÇOS</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-item nav-link" id="nav-prop-tab" data-toggle="tab" href="#nav-prop"
                               role="tab" aria-controls="nav-prop" aria-selected="false">PROPOSTAS</a>
                        </li>

                    </ul>

                    <div class="tab-content py-3 p-2">
                        <div class="tab-pane fade show active" id="nav-dados-cadastrais" role="tabpanel"
                             aria-labelledby="nav-dados-cadastrais-tab">
                            <fieldset>
                                <legend class="text-uppercase">Dados do @{{form.tipo_documento}}</legend>
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
                                        <div class="form-group" v-if="form.dados_cadastrais.tipo === 'Pessoa Jurídica'">
                                            <label>CNPJ</label>
                                            <input type="text" id="cnpj" class="form-control" placeholder="CNPJ"
                                                   v-model="form.dados_cadastrais.cnpj" :disabled="editando"
                                                   autocomplete="off"
                                                   onblur="valida_cnpj_vazio(this)" @blur="verificaCnpj"
                                                   v-mascara:cnpj>
                                        </div>

                                        <div class="form-group" v-if="form.dados_cadastrais.tipo === 'Pessoa Física'">
                                            <label>CPF</label>
                                            <input type="text" class="form-control" placeholder="CPF"
                                                   v-model="form.dados_cadastrais.cpf" :disabled="editando"
                                                   autocomplete="off"
                                                   onblur="valida_cpf_vazio(this)" v-mascara:cpf>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                        <div class="form-group" v-if="form.dados_cadastrais.tipo === 'Pessoa Jurídica'">
                                            <label>Razão Social</label>
                                            <input type="text" class="form-control"
                                                   v-model="form.dados_cadastrais.razao_social"
                                                   placeholder="Razão Social"
                                                   autocomplete="off" onblur="valida_campo_vazio(this,3)">
                                        </div>

                                        <div class="form-group" v-if="form.dados_cadastrais.tipo === 'Pessoa Física'">
                                            <label>Nome</label>
                                            <input type="text" class="form-control" v-model="form.dados_cadastrais.nome"
                                                   placeholder="Nome do Cliente"
                                                   autocomplete="off" onblur="valida_campo_vazio(this,3)">
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-6 col-lg-6 col-xl-6"
                                         v-if="form.dados_cadastrais.tipo === 'Pessoa Jurídica'">
                                        <div class="form-group">
                                            <label>Nome Fantasia</label>
                                            <input type="text" class="form-control"
                                                   v-model="form.dados_cadastrais.nome_fantasia"
                                                   placeholder="Nome Fantasia"
                                                   autocomplete="off">
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                        <div class="form-group">
                                            <label>Área de Atuação</label>
                                            <select v-model="form.dados_cadastrais.area_id" class="form-control"
                                                    onblur="valida_campo_vazio(this,1)">
                                                <option value="">Selecione</option>
                                                <option v-for="item in listaAreas" :value="item.id">@{{item.label}}
                                                </option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                        <div class="form-group">
                                            <label>Ramo</label>
                                            <input type="text" class="form-control" v-model="form.dados_cadastrais.ramo"
                                                   placeholder="Ramo"
                                                   autocomplete="off">
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
                                            <input type="text" class="form-control" placeholder="Nome do Responsável"
                                                   v-model="form.dados_cadastrais.responsavel"
                                                   autocomplete="off">
                                        </div>
                                    </div>

                                </div>

                                <div class="row">
                                    <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                        <div class="form-group">
                                            <label>E-mail</label>
                                            <input type="text" class="form-control" id="email" placeholder="E-mail"
                                                   v-model="form.dados_cadastrais.email"
                                                   autocomplete="off" onblur="validaEmailVazio(this)" v-mascara:email>
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
                                       for="ativo">@{{form.ativo ? 'Ativo' : 'Inativo'}}</label>
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
                                                @click="addLIServicoCliente($event.target)">
                                            <span class="fas fa-plus" aria-hidden="true"></span>
                                            Adicionar Serviço
                                        </button>
                                    </div>

                                    <div class="col-12" v-if="form.servicos_cliente.length>0"
                                         v-for="(obj, index) in form.servicos_cliente" :key="obj.id">
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
                                                <div class="form-group">
                                                    <label>Tipo de documento</label>
                                                    <input type="text" class="form-control"
                                                           v-model="obj.tipo_descricao">
                                                    {{--                                                    <select v-model="obj.servico_id" class="form-control"--}}
                                                    {{--                                                            onblur="valida_campo_vazio(this,1)">--}}
                                                    {{--                                                        <option value="">Selecione ...</option>--}}
                                                    {{--                                                        <option v-for="item in listaServicos" :value="item.id">--}}
                                                    {{--                                                            @{{item.titulo}}--}}
                                                    {{--                                                        </option>--}}
                                                    {{--                                                    </select>--}}
                                                </div>
                                            </div>


                                            <div class="col-12">
                                                <div class="form-group">
                                                    <label>Observação</label>
                                                    <textarea class="form-control" v-model="obj.observacao" rows="3"
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

                                            {{--                                            <div class="col-12 col-sm-6 col-lg-4">--}}
                                            {{--                                                <div class="form-group">--}}
                                            {{--                                                    <label>Status</label>--}}
                                            {{--                                                    <select v-model="obj.status" class="form-control">--}}
                                            {{--                                                        <option value="Iniciado">INICIADO</option>--}}
                                            {{--                                                        <option value="Concluido">CONCLUIDO</option>--}}
                                            {{--                                                        <option value="Não iniciado">NÃO INICIADO</option>--}}
                                            {{--                                                    </select>--}}
                                            {{--                                                </div>--}}
                                            {{--                                            </div>--}}

                                            {{--                                            <div class="col-12 col-sm-6 col-lg-4">--}}
                                            {{--                                                <div class="form-group">--}}
                                            {{--                                                    <label>Tipo Contrato</label>--}}
                                            {{--                                                    <select v-model="obj.tipo_contrato" class="form-control">--}}
                                            {{--                                                        <option--}}
                                            {{--                                                            value="{{\App\Models\ServicosCliente::TIPO_CONTRATO_FIXO}}">{{\App\Models\ServicosCliente::TIPO_CONTRATO_FIXO}}</option>--}}
                                            {{--                                                        <option--}}
                                            {{--                                                            value="{{\App\Models\ServicosCliente::TIPO_CONTRATO_SPOT}}">{{\App\Models\ServicosCliente::TIPO_CONTRATO_SPOT}}</option>--}}
                                            {{--                                                    </select>--}}
                                            {{--                                                </div>--}}
                                            {{--                                            </div>--}}

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

                        <div class="tab-pane fade" id="nav-prop"
                             role="tabpanel" aria-labelledby="nav-service-tab">

                            <fieldset>
                                <legend class="text-uppercase">
                                    <span>DADOS DA PROPOSTA</span>
                                </legend>
                                <div class="row">
                                    <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                                        <button class="btn btn-sm btn-secondary mb-2"
                                                @click="addLIServicoCliente($event.target)">
                                            <span class="fas fa-plus" aria-hidden="true"></span>
                                            Adicionar Proposta
                                        </button>
                                    </div>

                                    <div class="col-12" v-if="form.servicos_cliente.length>0"
                                         v-for="(obj, index) in form.servicos_cliente" :key="obj.id">
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
                                                <div class="form-group">
                                                    <label>Tipo de documento</label>
                                                    <input type="text" class="form-control"
                                                           v-model="obj.tipo_descricao">
                                                    {{--                                                    <select v-model="obj.servico_id" class="form-control"--}}
                                                    {{--                                                            onblur="valida_campo_vazio(this,1)">--}}
                                                    {{--                                                        <option value="">Selecione ...</option>--}}
                                                    {{--                                                        <option v-for="item in listaServicos" :value="item.id">--}}
                                                    {{--                                                            @{{item.titulo}}--}}
                                                    {{--                                                        </option>--}}
                                                    {{--                                                    </select>--}}
                                                </div>
                                            </div>


                                            <div class="col-12">
                                                <div class="form-group">
                                                    <label>Observação</label>
                                                    <textarea class="form-control" v-model="obj.observacao" rows="3"
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

                                            {{--                                            <div class="col-12 col-sm-6 col-lg-4">--}}
                                            {{--                                                <div class="form-group">--}}
                                            {{--                                                    <label>Status</label>--}}
                                            {{--                                                    <select v-model="obj.status" class="form-control">--}}
                                            {{--                                                        <option value="Iniciado">INICIADO</option>--}}
                                            {{--                                                        <option value="Concluido">CONCLUIDO</option>--}}
                                            {{--                                                        <option value="Não iniciado">NÃO INICIADO</option>--}}
                                            {{--                                                    </select>--}}
                                            {{--                                                </div>--}}
                                            {{--                                            </div>--}}

                                            {{--                                            <div class="col-12 col-sm-6 col-lg-4">--}}
                                            {{--                                                <div class="form-group">--}}
                                            {{--                                                    <label>Tipo Contrato</label>--}}
                                            {{--                                                    <select v-model="obj.tipo_contrato" class="form-control">--}}
                                            {{--                                                        <option--}}
                                            {{--                                                            value="{{\App\Models\ServicosCliente::TIPO_CONTRATO_FIXO}}">{{\App\Models\ServicosCliente::TIPO_CONTRATO_FIXO}}</option>--}}
                                            {{--                                                        <option--}}
                                            {{--                                                            value="{{\App\Models\ServicosCliente::TIPO_CONTRATO_SPOT}}">{{\App\Models\ServicosCliente::TIPO_CONTRATO_SPOT}}</option>--}}
                                            {{--                                                    </select>--}}
                                            {{--                                                </div>--}}
                                            {{--                                            </div>--}}

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

                    </div>
                </div>

                <div v-if="form.tipo_documento !== '' && form.tipo_documento === 'Documentos Empresa'">
                    <ul class="nav nav-tabs bg-light" id="tabslist" role="tablist"
                        style="border-bottom: 1px solid #653232">
                        <li class="nav-item">
                            <a class="nav-item nav-link active" id="nav-documento-empresa-tab" data-toggle="tab"
                               href="#nav-documento-empresa"
                               role="tab" aria-controls="nav-documento-empresa" aria-selected="false">DOCUMENTO
                                LEGAIS</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-item nav-link" id="nav-config-empresa-tab" data-toggle="tab"
                               href="#nav-config-empresa"
                               role="tab" aria-controls="nav-config-empresa" aria-selected="false">CONFIGURAÇÕES</a>
                        </li>
                    </ul>

                    <div class="tab-content py-3 p-2">

                        <div v-if="form.tipo_documento === 'Documentos Empresa'" class="tab-pane show active"
                             id="nav-documento-empresa"
                             role="tabpanel" aria-labelledby="nav-documento-empresa-tab">

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
                                                                v-model="obj.data_inicio" default=""></datepicker>
                                                </div>
                                            </div>

                                            <div class="col-12 col-sm-4">
                                                <div class="form-group">

                                                    <datepicker label="Data Vencimento" posicao="up"
                                                                v-model="obj.data_encerramento" default=""></datepicker>
                                                </div>
                                            </div>

                                            <div class="col-12"></div>

                                            <div class="col-12 col-sm-6 col-lg-4">
                                                <div class="form-group">
                                                    <label>Tipo de documento</label>
                                                    <input type="text" class="form-control"
                                                           v-model="obj.tipo_descricao">
                                                    {{--                                                    <select v-model="obj.servico_id" class="form-control"--}}
                                                    {{--                                                            onblur="valida_campo_vazio(this,1)">--}}
                                                    {{--                                                        <option value="">Selecione ...</option>--}}
                                                    {{--                                                        <option v-for="item in listaServicos" :value="item.id">--}}
                                                    {{--                                                            @{{item.titulo}}--}}
                                                    {{--                                                        </option>--}}
                                                    {{--                                                    </select>--}}
                                                </div>
                                            </div>


                                            <div class="col-12">
                                                <div class="form-group">
                                                    <label>Observação</label>
                                                    <textarea class="form-control" v-model="obj.observacao" rows="3"
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

                                            {{--                                            <div class="col-12 col-sm-6 col-lg-4">--}}
                                            {{--                                                <div class="form-group">--}}
                                            {{--                                                    <label>Status</label>--}}
                                            {{--                                                    <select v-model="obj.status" class="form-control">--}}
                                            {{--                                                        <option value="Iniciado">INICIADO</option>--}}
                                            {{--                                                        <option value="Concluido">CONCLUIDO</option>--}}
                                            {{--                                                        <option value="Não iniciado">NÃO INICIADO</option>--}}
                                            {{--                                                    </select>--}}
                                            {{--                                                </div>--}}
                                            {{--                                            </div>--}}

                                            {{--                                            <div class="col-12 col-sm-6 col-lg-4">--}}
                                            {{--                                                <div class="form-group">--}}
                                            {{--                                                    <label>Tipo Contrato</label>--}}
                                            {{--                                                    <select v-model="obj.tipo_contrato" class="form-control">--}}
                                            {{--                                                        <option--}}
                                            {{--                                                            value="{{\App\Models\ServicosCliente::TIPO_CONTRATO_FIXO}}">{{\App\Models\ServicosCliente::TIPO_CONTRATO_FIXO}}</option>--}}
                                            {{--                                                        <option--}}
                                            {{--                                                            value="{{\App\Models\ServicosCliente::TIPO_CONTRATO_SPOT}}">{{\App\Models\ServicosCliente::TIPO_CONTRATO_SPOT}}</option>--}}
                                            {{--                                                    </select>--}}
                                            {{--                                                </div>--}}
                                            {{--                                            </div>--}}

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
                                            <select v-model="form.documento_empresa_config.verifica_mes_vencimento"
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

                <div v-if="form.tipo_documento !== '' && form.tipo_documento === 'Documentos SSMA'">
                    <ul class="nav nav-tabs bg-light" id="tabslist" role="tablist"
                        style="border-bottom: 1px solid #653232">
                        <li class="nav-item">
                            <a class="nav-item nav-link active" id="nav-documento-ssma-tab" data-toggle="tab"
                               href="#nav-documento-ssma"
                               role="tab" aria-controls="nav-documento-ssma" aria-selected="false">DOCUMENTO LEGAIS</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-item nav-link" id="nav-config-ssma-tab" data-toggle="tab"
                               href="#nav-config-ssma"
                               role="tab" aria-controls="nav-config-ssma" aria-selected="false">CONFIGURAÇÕES</a>
                        </li>
                    </ul>

                    <div class="tab-content py-3 p-2">

                        <div v-if="form.tipo_documento === 'Documentos SSMA'" class="tab-pane  show active"
                             id="nav-documento-ssma"
                             role="tabpanel" aria-labelledby="nav-documento-ssma-tab">

                            <fieldset>
                                <legend class="text-uppercase">
                                    <span>Documentos Gerenciaveis</span>
                                </legend>
                                <div class="row">
                                    <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                                        <button class="btn btn-sm btn-secondary mb-2"
                                                @click="addLIDocumentoSsma($event.target)">
                                            <span class="fas fa-plus" aria-hidden="true"></span>
                                            Adicionar Documentos
                                        </button>
                                    </div>

                                    <div class="col-12" v-if="form.documentos_ssma.length>0"
                                         v-for="(obj, index) in form.documentos_ssma" :key="obj.id">
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
                                                <div class="form-group">
                                                    <label>Tipo de documento</label>
                                                    <input type="text" class="form-control"
                                                           v-model="obj.tipo_descricao">
                                                    {{--                                                    <select v-model="obj.servico_id" class="form-control"--}}
                                                    {{--                                                            onblur="valida_campo_vazio(this,1)">--}}
                                                    {{--                                                        <option value="">Selecione ...</option>--}}
                                                    {{--                                                        <option v-for="item in listaServicos" :value="item.id">--}}
                                                    {{--                                                            @{{item.titulo}}--}}
                                                    {{--                                                        </option>--}}
                                                    {{--                                                    </select>--}}
                                                </div>
                                            </div>


                                            <div class="col-12">
                                                <div class="form-group">
                                                    <label>Observação</label>
                                                    <textarea class="form-control" v-model="obj.observacao" rows="3"
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

                                            {{--                                            <div class="col-12 col-sm-6 col-lg-4">--}}
                                            {{--                                                <div class="form-group">--}}
                                            {{--                                                    <label>Status</label>--}}
                                            {{--                                                    <select v-model="obj.status" class="form-control">--}}
                                            {{--                                                        <option value="Iniciado">INICIADO</option>--}}
                                            {{--                                                        <option value="Concluido">CONCLUIDO</option>--}}
                                            {{--                                                        <option value="Não iniciado">NÃO INICIADO</option>--}}
                                            {{--                                                    </select>--}}
                                            {{--                                                </div>--}}
                                            {{--                                            </div>--}}

                                            {{--                                            <div class="col-12 col-sm-6 col-lg-4">--}}
                                            {{--                                                <div class="form-group">--}}
                                            {{--                                                    <label>Tipo Contrato</label>--}}
                                            {{--                                                    <select v-model="obj.tipo_contrato" class="form-control">--}}
                                            {{--                                                        <option--}}
                                            {{--                                                            value="{{\App\Models\ServicosCliente::TIPO_CONTRATO_FIXO}}">{{\App\Models\ServicosCliente::TIPO_CONTRATO_FIXO}}</option>--}}
                                            {{--                                                        <option--}}
                                            {{--                                                            value="{{\App\Models\ServicosCliente::TIPO_CONTRATO_SPOT}}">{{\App\Models\ServicosCliente::TIPO_CONTRATO_SPOT}}</option>--}}
                                            {{--                                                    </select>--}}
                                            {{--                                                </div>--}}
                                            {{--                                            </div>--}}

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
                                            <select v-model="form.documento_empresa_config.verifica_mes_vencimento"
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
    <modal id="janelaConfirmar" titulo="Apagar documento empresa">
        <template slot="conteudo">
            <span v-show="preloadAjax"><i class="fa fa-spinner fa-pulse"></i>Aguarde...</span>
            <div class="alert alert-success alert-dismissible" v-show="apagado">
                <h4><i class="icon fa fa-check"></i>Documento apagado com sucesso!</h4>
            </div>
            <h4 v-show="!apagado">Tem certeza que deseja apagar este documento?</h4>
        </template>
        <template slot="rodape">
            <button type="button" class="btn btn-sm btn-danger" @click="apagar()" v-show="!apagado">Apagar</button>
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

            {{--            <div class="col-12 col-md-4">--}}
            {{--                <div class="form-group">--}}
            {{--                    <label>Tipo</label>--}}
            {{--                    <select class="form-control form-control-sm" v-model="controle.dados.campoTipo"--}}
            {{--                            @change="$refs.componente.buscar()">--}}
            {{--                        <option value="">Todos os Tipos</option>--}}
            {{--                        <option--}}
            {{--                            value="Prospect">Prospect--}}
            {{--                        </option>--}}
            {{--                        <option--}}
            {{--                            value="Cliente">Cliente--}}
            {{--                        </option>--}}
            {{--                    </select>--}}
            {{--                </div>--}}
            {{--            </div>--}}

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
                <button type="button" class="btn btn-sm btn-success" :disabled="controle.carregando" @click="atualizar">
                    <i
                        :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                    Atualizar
                </button>

                <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" :disabled="controle.carregando"
                        data-target="#janelaCadastrar"
                        @click="formNovo()">
                    Cadastrar
                </button>

                {{--                <a href="{{ route('clientes.excel') }}" :disabled="controle.carregando"--}}
                {{--                   class="btn btn-sm btn-primary"><i--}}
                {{--                        class="fas fa-file-excel"></i>--}}
                {{--                    Exportar Excel</a>--}}
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
                        @{{item.id}}
                    </td>

                    <td data-label="Nome">
                        @{{ item.dados_cadastrais.tipo == 'Pessoa Física' ? item.dados_cadastrais.nome :
                        item.dados_cadastrais.razao_social }}
                    </td>

                    <td data-label="Área / Ramo">
                        @{{item.dados_cadastrais.ramo}}
                    </td>

                    <td data-label="Contato">
                        @{{ item.dados_cadastrais.responsavel }} -
                        <span v-for="tel in item.dados_cadastrais.telefones">@{{tel.numero}}</span>
                    </td>


                    <td data-label="Status">
                        <bt-ativo :rota="`administracao/documentoslegais/${item.id}/ativa-desativa`"
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

                        <a href="javascript://" class="btn btn-sm btn-danger mb-1" v-tippy content="Excluir"
                           @click.prevent="janelaConfirmar(item.id)"
                           data-toggle="modal"
                           data-target="#janelaConfirmar">
                            <i class="fa fa-trash" aria-hidden="true"></i>
                        </a>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <controle-paginacao class="d-flex justify-content-center" id="controle" ref="componente"
                            url="{{route('g.administracao.documentoslegais.atualizar')}}" por-pagina="100"
                            :dados="controle.dados"
                            v-on:carregou="carregou" v-on:carregando="carregando"></controle-paginacao>
    </div>
@stop

@push('js')
    <script src="{{mix('js/g/documentoslegais/app.js')}}"></script>
@endpush


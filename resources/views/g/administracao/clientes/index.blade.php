@extends('layouts.sistema')
@section('title', 'Cliente / Prospect')
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
    <h4 class="text-default">CLIENTES</h4>
    <hr class="bg-default" style="margin-top: -5px;">
@stop
@section('content')
    <modal id="janelaCadastrar" :titulo="tituloJanela" :size="90">
        <template slot="conteudo">
            <div v-show="preloadAjax"><i class="fa fa-spinner fa-pulse"></i> Aguarde...</div>
            <div class="alert alert-success alert-dismissible" v-show="cadastrado">
                <h4><i class="icon fa fa-check"></i>@{{form.tipo_cliente}} cadastrado com sucesso!</h4>
            </div>
            <div class="alert alert-success alert-dismissible" v-show="atualizado">
                <h4><i class="icon fa fa-check"></i>@{{form.tipo_cliente}} alterado com sucesso!</h4>
            </div>
            <form v-if="!preloadAjax && (!cadastrado && !atualizado)" id="form" onsubmit="return false;">

                <fieldset>
                    <legend class="text-uppercase">Tipo do cliente</legend>
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <label>Selecione o Tipo</label>
                            <select class="form-control" v-model="form.tipo_cliente"
                                    :disabled="editando && form.tipo_cliente === 'Cliente'"
                                    onblur="valida_campo_vazio(this,1)" onchange="valida_campo_vazio(this,1)">
                                <option value="">Selecione ...</option>
                                <option value="Cliente">Cliente</option>
                                <option value="Prospect">Prospect</option>
                            </select>
                        </div>
                    </div>
                </fieldset>

                <div v-if="form.tipo_cliente !== ''">

                    <ul class="nav nav-tabs bg-light" id="tabslist" role="tablist"
                        style="border-bottom: 1px solid #653232">
                        <li class="nav-item">
                            <a class="nav-item nav-link active" id="nav-dados-cadastrais-tab" data-toggle="tab"
                               href="#nav-dados-cadastrais"
                               role="tab" aria-controls="nav-dados-cadastrais" aria-selected="true">DADOS CADASTRAIS</a>
                        </li>
                        <li class="nav-item" v-if="form.tipo_cliente === 'Cliente'">
                            <a class="nav-item nav-link" id="nav-servicos-tab" data-toggle="tab" href="#nav-servicos"
                               role="tab" aria-controls="nav-servicos" aria-selected="false">SERVIÇOS</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-item nav-link" id="nav-proposta-tab" data-toggle="tab" href="#nav-proposta"
                               role="tab" aria-controls="nav-proposta" aria-selected="false">PROPOSTAS</a>
                        </li>
                        <li class="nav-item" v-if="editando">
                            <a class="nav-item nav-link" id="nav-dados-filiais-tab" data-toggle="tab"
                               href="#nav-dados-filiais"
                               role="tab" aria-controls="nav-dados-filiais" aria-selected="true">FILIAIS</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-item nav-link" id="nav-config-tab" data-toggle="tab" href="#nav-config"
                               role="tab" aria-controls="nav-config" aria-selected="false">CONFIGURAÇÕES</a>
                        </li>

                    </ul>

                    <div class="tab-content py-3 p-2">
                        <div class="tab-pane fade show active" id="nav-dados-cadastrais" role="tabpanel"
                             aria-labelledby="nav-dados-cadastrais-tab">
                            <fieldset>
                                <legend class="text-uppercase">Dados do @{{form.tipo_cliente}}</legend>
                                <div class="row">
                                    <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                        <div class="form-group">
                                            <label>Tipo</label>
                                            <select class="form-control" v-model="form.tipo" :disabled="editando">
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
                                        <div class="form-group" v-if="form.tipo === 'Pessoa Jurídica'">
                                            <label>CNPJ</label>
                                            <input type="text" id="cnpj" class="form-control" placeholder="CNPJ"
                                                   v-model="form.cnpj" :disabled="editando" autocomplete="off"
                                                   onblur="valida_cnpj_vazio(this)" @blur="verificaCnpj"
                                                   v-mascara:cnpj>
                                        </div>

                                        <div class="form-group" v-if="form.tipo === 'Pessoa Física'">
                                            <label>CPF</label>
                                            <input type="text" class="form-control" placeholder="CPF"
                                                   v-model="form.cpf" :disabled="editando" autocomplete="off"
                                                   onblur="valida_cpf_vazio(this)" v-mascara:cpf>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                        <div class="form-group" v-if="form.tipo === 'Pessoa Jurídica'">
                                            <label>Razão Social</label>
                                            <input type="text" class="form-control" v-model="form.razao_social"
                                                   placeholder="Razão Social"
                                                   autocomplete="off" onblur="valida_campo_vazio(this,3)">
                                        </div>

                                        <div class="form-group" v-if="form.tipo === 'Pessoa Física'">
                                            <label>Nome</label>
                                            <input type="text" class="form-control" v-model="form.nome"
                                                   placeholder="Nome do Cliente"
                                                   autocomplete="off" onblur="valida_campo_vazio(this,3)">
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-6 col-lg-6 col-xl-6"
                                         v-if="form.tipo === 'Pessoa Jurídica'">
                                        <div class="form-group">
                                            <label>Nome Fantasia</label>
                                            <input type="text" class="form-control" v-model="form.nome_fantasia"
                                                   placeholder="Nome Fantasia"
                                                   autocomplete="off">
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                        <div class="form-group">
                                            <label>Área de Atuação</label>
                                            <select v-model="form.area_id" class="form-control"
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
                                            <input type="text" class="form-control" v-model="form.ramo"
                                                   placeholder="Ramo"
                                                   autocomplete="off">
                                        </div>
                                    </div>
                                </div>
                            </fieldset>

                            <fieldset>
                                <legend class="text-uppercase">Endereço</legend>
                                <endereco :model="form"></endereco>
                            </fieldset>

                            <fieldset>
                                <legend class="text-uppercase">Contatos</legend>
                                <div class="row">
                                    <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                        <div class="form-group">
                                            <label>Responsável</label>
                                            <input type="text" class="form-control" placeholder="Nome do Responsável"
                                                   v-model="form.contato"
                                                   autocomplete="off">
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-6 col-lg-3 col-xl-3">
                                        <div class="form-group">
                                            <label>Data de aniversário</label>
                                            <input type="text" class="form-control" v-model="form.aniversario"
                                                   v-mascara:aniversario
                                                   onblur="valida_campo(this,5)">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                        <div class="form-group">
                                            <label>E-mail</label>
                                            <input type="text" class="form-control" id="email" placeholder="E-mail"
                                                   v-model="form.email"
                                                   autocomplete="off" onblur="validaEmailVazio(this)" v-mascara:email>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <fieldset>
                                            <legend class="text-uppercase">Telefones</legend>
                                            <telefone :model="form.telefones" :ramal="false" :pais="false"
                                                      :model-delete="form.telefonesDelete"></telefone>
                                        </fieldset>
                                    </div>
                                </div>
                            </fieldset>


                            <fieldset v-if="form.tipo_cliente === 'Cliente'">
                                <legend class="text-uppercase">Informações para Carteira de Treinamento <span
                                        class="small text-warning">(* Obrigatório quando o cliente gerar carteira de treinamento)</span>
                                </legend>


                                <div class="form-group">
                                    <label for="politica_ehs">Política de EHS </label>
                                    <textarea class="form-control" cols="5" rows="5"
                                              v-model="form.politica_ehs"></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="politica_ehs">Missão</label>
                                    <textarea class="form-control" cols="5" rows="5"
                                              v-model="form.missao"></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="politica_ehs">Visão</label>
                                    <textarea class="form-control" cols="5" rows="5"
                                              v-model="form.visao"></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="politica_ehs">Valores</label>
                                    <textarea class="form-control" cols="5" rows="5"
                                              v-model="form.valores"></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="politica_ehs">Política de Gestão da Qualidade</label>
                                    <textarea class="form-control" cols="5" rows="5"
                                              v-model="form.politica_gq"></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="politica_ehs">Apelido <span
                                            class="small text-danger">ex: BPSE</span></label>
                                    <input type="text" class="form-control" v-model="form.apelido">
                                </div>

                                <div class="form-group">
                                    <label for="politica_ehs">Telefone Principal <span class="small text-danger">Telefone Principal</span></label>
                                    <input type="text" v-mascara:telefone class="form-control"
                                           v-model="form.tel_principal">
                                </div>


                                <fieldset>
                                    <legend class="text-uppercase">Upload da Logo</legend>
                                    <div class="row">
                                        <div class="col-12">
                                            <upload :model="form.logo"
                                                    :model-delete="form.logoDel"
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

                                <fieldset>
                                    <legend class="text-uppercase">Upload do Mascote</legend>
                                    <div class="row">
                                        <div class="col-12">
                                            <upload :model="form.mascote"
                                                    :model-delete="form.mascoteDel"
                                                    :url="urlMascoteUpload"
                                                    :quantidade="1"
                                                    :multi="false"
                                                    :apenas-imagens="true"
                                                    label="Selecione a Mascote"
                                                    @onProgresso="mascoteUploadAndamento=true"
                                                    @onFinalizado="mascoteUploadAndamento=false"></upload>
                                        </div>
                                    </div>
                                </fieldset>

                                <div class="form-group" v-show="form.como_conheceu === 'OUTROS'">
                                    <input type="text" class="form-control" v-model="form.como_conheceu_outro"
                                           placeholder="Por onde?">
                                </div>
                            </fieldset>

                            <fieldset>
                                <legend class="text-uppercase">Como conheceu a BPSE?</legend>
                                <div class="form-group">
                                    <select class="form-control" v-model="form.como_conheceu"
                                            onblur="valida_campo_vazio(this,1)">
                                        <option value="">
                                            SELECIONE UMA OPÇÃO...
                                        </option>
                                        <option value="INDICAÇÃO DE UM AMIGO/ CLIENTE/ PARCEIRO DE NEGÓCIOS">
                                            INDICAÇÃO DE UM AMIGO/ CLIENTE/ PARCEIRO DE NEGÓCIOS
                                        </option>
                                        <option value="ATRAVÉS DAS REDES SOCIAIS  - LinkedIn, Instagram, Facebook">
                                            ATRAVÉS DAS REDES SOCIAIS - LinkedIn, Instagram, Facebook
                                        </option>
                                        <option value="EM EVENTOS COM OUTRAS EMPRESAS PARCEIRAS DA BPSE">EM EVENTOS
                                            COM OUTRAS EMPRESAS PARCEIRAS DA BPSE
                                        </option>
                                        <option value="PESQUISANDO NO GOOGLE">PESQUISANDO NO GOOGLE</option>
                                        <option value="OUTROS">OUTROS</option>
                                    </select>
                                </div>

                                <div class="form-group" v-if="form.como_conheceu === 'OUTROS'">
                                    <input type="text" class="form-control" v-model="form.como_conheceu_outro"
                                           placeholder="Por onde?">
                                </div>
                            </fieldset>

                            <div class="custom-control custom-switch">
                                <input type="checkbox" v-model="form.ativo" class="custom-control-input" id="ativo">
                                <label class="custom-control-label"
                                       for="ativo">@{{form.ativo ? 'Ativo' : 'Inativo'}}</label>
                            </div>

                        </div>

                        <div v-if="form.tipo_cliente === 'Cliente'" class="tab-pane fade" id="nav-servicos"
                             role="tabpanel" aria-labelledby="nav-servicos-tab">

                            <fieldset>
                                <legend class="text-uppercase">
                                    <span>Serviços Contratados</span>
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
                                                    <label>Data Início</label>
                                                    <datepicker posicao="up" v-model="obj.data_inicio"></datepicker>
                                                </div>
                                            </div>

                                            <div class="col-12 col-sm-4">
                                                <div class="form-group">
                                                    <label>Data Encerramento</label>
                                                    <datepicker posicao="up"
                                                                v-model="obj.data_encerramento"></datepicker>
                                                </div>
                                            </div>

                                            <div class="col-12"></div>

                                            <div class="col-12 col-sm-6 col-lg-4">
                                                <div class="form-group">
                                                    <label>Tipo de Serviço</label>
                                                    <select v-model="obj.servico_id" class="form-control"
                                                            onblur="valida_campo_vazio(this,1)">
                                                        <option value="">Selecione ...</option>
                                                        <option v-for="item in listaServicos" :value="item.id">
                                                            @{{item.titulo}}
                                                        </option>
                                                    </select>
                                                </div>
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
                                                    <select v-model="obj.tipo_faturamento" class="form-control">
                                                        <option value="Único">ÚNICO</option>
                                                        <option value="Por execução">POR EXECUÇÃO</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group">
                                                    <label>Escopo</label>
                                                    <textarea class="form-control" v-model="obj.escopo" rows="3"
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
                                                    <label>Tipo Contrato</label>
                                                    <select v-model="obj.tipo_contrato" class="form-control">
                                                        <option
                                                            value="{{\App\Models\ServicosCliente::TIPO_CONTRATO_FIXO}}">{{\App\Models\ServicosCliente::TIPO_CONTRATO_FIXO}}</option>
                                                        <option
                                                            value="{{\App\Models\ServicosCliente::TIPO_CONTRATO_SPOT}}">{{\App\Models\ServicosCliente::TIPO_CONTRATO_SPOT}}</option>
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

                        <div class="tab-pane fade" id="nav-proposta"
                             role="tabpanel" aria-labelledby="nav-proposta-tab">
                            <fieldset>
                                <legend class="text-uppercase">
                                    <span>Dados da Proposta</span>
                                </legend>

                                <div class="row">
                                    <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                                        <button class="btn btn-sm btn-secondary mb-2"
                                                @click="addLIServicoProspect($event.target)">
                                            <span class="fas fa-plus" aria-hidden="true"></span>
                                            Adicionar Serviço
                                        </button>
                                    </div>

                                    <div class="col-12" v-if="form.servicos_prospect.length>0"
                                         v-for="(obj, index) in form.servicos_prospect" :key="obj.id">
                                        <div class="row py-3">

                                            <div class="col-12 col-sm-4">
                                                <div class="form-group">
                                                    <label>Data Envio Proposta</label>
                                                    <datepicker posicao="up"
                                                                v-model="obj.data_envio_proposta"></datepicker>
                                                </div>
                                            </div>

                                            <div class="col-12 col-sm-6 col-lg-4">
                                                <div class="form-group">
                                                    <label>Tipo de Serviço</label>
                                                    <select v-model="obj.servico_id" class="form-control"
                                                            onblur="valida_campo_vazio(this,1)">
                                                        <option value="">Selecione ...</option>
                                                        <option v-for="item in listaServicos" :value="item.id">
                                                            @{{item.titulo}}
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>


                                            <div class="col-12">
                                                <div class="form-group">
                                                    <label>Escopo</label>
                                                    <textarea class="form-control" v-model="obj.escopo" rows="3"
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
                                                        <option value="Iniciado">ACEITA</option>
                                                        <option value="Concluido">NÃO ACEITA</option>
                                                        <option value="Não iniciado">STAND BY</option>
                                                        <option value="Não iniciado">SEM RETORNO</option>
                                                        <option value="Não iniciado">DECLINAMOS</option>
                                                    </select>
                                                </div>
                                            </div>


                                            <div class="col-12 mb-3">
                                                <button class="btn btn-sm btn-danger"
                                                        @click="removerLIServicoProspect(index)"
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

                        <div class="tab-pane fade" id="nav-dados-filiais" role="tabpanel" v-if="editando"
                             aria-labelledby="nav-dados-filiais-tab">
                            <filial :empresa_id="form.id"></filial>
                        </div>

                        <div class="tab-pane fade" id="nav-config"
                             role="tabpanel" aria-labelledby="nav-config-tab">
                            <fieldset>
                                <legend class="text-uppercase">
                                    <span>Configurações Gerais</span>
                                </legend>
                                <div class="row">
                                    <div class="col-12 col-sm-6 col-lg-4">
                                        <div class="form-group">
                                            <label>Vencimento de Férias</label>
                                            <select v-model="form.cliente_config.verifica_mes_vencimento"
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
                                            <select v-model="form.cliente_config.envia_whatsapp" class="form-control"
                                                    onblur="valida_campo_vazio(this,1)">
                                                <option value="">Selecione ...</option>
                                                <option :value="true">Sim</option>
                                                <option :value="false">Não</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6 col-lg-4">
                                        <div class="form-group">
                                            <label>Vencimento de ASO</label>
                                            <select v-model="form.cliente_config.vencimento_aso" class="form-control"
                                                    onblur="valida_campo_vazio(this,1)">
                                                <option value="">Selecione ...</option>
                                                <option value="1">30 dias</option>
                                                <option value="2">60 dias</option>
                                                <option value="3">90 dias</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6 col-lg-4" v-if="editando">
                                        <div class="form-group">
                                            <label>Modelo CIH</label>
                                            <select v-model="form.cliente_config.modelo_cih" class="form-control"
                                                    onblur="valida_campo_vazio(this,1)">
                                                <option value="">Selecione ...</option>
                                                <option v-for="(item, key) in listaModeloCih" :value="key" :key="key" v-text="item"></option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                            <fieldset>
                                <legend class="text-uppercase">
                                    <span>Parametrização - Módulos</span>
                                </legend>
                                <div class="container">
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <a class="btn btn-sm btn-success float-right" href="javascript://"
                                               @click.prevent="selecionarTodas()" v-if="!todasHabilidades">
                                                <span class="fa fa-ok" aria-hidden="true"></span> Todos os módulos
                                            </a>
                                            <a class="btn btn-sm btn-danger float-right" href="javascript://"
                                               @click.prevent="selecionarTodas()" v-if="todasHabilidades">
                                                <span class="fa fa-remove" aria-hidden="true"></span> Todos os módulos
                                            </a>
                                        </div>
                                        <div class="col-md-12" id="accordion"
                                             v-for="(menu, index) in todosMenu">
                                            <div class="card">
                                                <div class="card-header" :id="'heading'+index">
                                                    <div class="row justify-content-between">
                                                        <div class="col-4">
                                                            <h5 class="mb-0">
                                                                <button class="btn btn-link text-uppercase"
                                                                        data-toggle="collapse"
                                                                        :data-target="'#collapseOne'+index"
                                                                        aria-expanded="true"
                                                                        aria-controls="collapseOne">
                                                                    @{{menu}}
                                                                </button>
                                                            </h5>
                                                        </div>
                                                        <div class="col-4">
                                                            <a class="btn btn-sm btn-info float-right"
                                                               href="javascript://"
                                                               @click.prevent="selecionarPorModulo(menu)">
                                                                <span class="fa fa-ok" aria-hidden="true"></span> Todos
                                                                em @{{menu}}
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div :id="'collapseOne'+index" class="collapse"
                                                     :aria-labelledby="'headingOne'+index"
                                                     data-parent="#accordion">
                                                    <div class="card-body"
                                                         v-for="habilidade in listaDeHabilidades"
                                                         v-if="menu === habilidade.menu">
                                                        @{{habilidade.nome}}
                                                        <div class="float-right col-md-1">
                                                            <a class="btn btn-sm btn-block btn-success"
                                                               href="javascript://"
                                                               @click.prevent="habilidade.acesso=!habilidade.acesso"
                                                               v-if="habilidade.acesso">
                                                                            <span class="fa fa-check"
                                                                                  aria-hidden="true"></span>
                                                            </a>
                                                            <a class="btn btn-sm btn-block btn-danger"
                                                               href="javascript://"
                                                               @click.prevent="habilidade.acesso=!habilidade.acesso"
                                                               v-if="!habilidade.acesso">
                                                                            <span class="fa fa-times"
                                                                                  aria-hidden="true"></span>
                                                            </a>
                                                        </div>
                                                        <hr>
                                                    </div>
                                                </div>
                                            </div>
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
    <modal id="janelaConfirmar" titulo="Apagar clientes">
        <template slot="conteudo">
            <span v-show="preloadAjax"><i class="fa fa-spinner fa-pulse"></i>Aguarde...</span>
            <div class="alert alert-success alert-dismissible" v-show="apagado">
                <h4><i class="icon fa fa-check"></i>Cliente apagado com sucesso!</h4>
            </div>
            <h4 v-show="!apagado">Tem certeza que deseja apagar este cliente?</h4>
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

            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label>Tipo</label>
                    <select class="form-control form-control-sm" v-model="controle.dados.campoTipo"
                            @change="$refs.componente.buscar()">
                        <option value="">Todos os Tipos</option>
                        <option
                            value="Prospect">Prospect
                        </option>
                        <option
                            value="Cliente">Cliente
                        </option>
                    </select>
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
        <div class="alert alert-warning" v-show="!controle.carregando && lista.length===0">
            <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
        </div>
        <div class="table-responsive" v-show="!controle.carregando && lista.length > 0">
            <table class="tabela">
                <thead>
                <tr class="bg-default">
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Tipo</th>
                    <th>Área / Ramo</th>
                    <th>Contato</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="cliente in lista">
                    <td data-label="ID">
                        @{{cliente.id}}
                    </td>

                    <td data-label="Nome">
                        @{{ cliente.tipo == 'Pessoa Física' ? cliente.nome : cliente.razao_social }}
                    </td>

                    <td data-label="Tipo">
                        @{{ cliente.tipo_cliente }}
                    </td>

                    <td data-label="Área / Ramo">
                        @{{cliente.area.label}} <span v-show="cliente.ramo">/ @{{ cliente.ramo }}</span>
                    </td>

                    <td data-label="Contato">
                        @{{ cliente.contato }} -
                        <span v-for="tel in cliente.telefones">@{{tel.numero}}</span>
                    </td>


                    <td data-label="Status">
                        <bt-ativo :rota="`administracao/clientes/${cliente.id}/ativa-desativa`"
                                  :model="cliente"></bt-ativo>
                    </td>

                    <td data-label="Ações">
                        <a :href="`clientes/${cliente.id}/pdf`"
                           class="btn btn-sm btn-primary mb-1" v-tippy content="Ficha"
                           target="_blank">
                            <i class="fa fa-file-pdf"></i>
                        </a>

                        <a href="javascript://" class="btn btn-sm btn-primary mb-1" v-tippy content="Editar"
                           @click.prevent="formAlterar(cliente.id)"
                           data-toggle="modal"
                           data-target="#janelaCadastrar">
                            <i class="fa fa-edit" aria-hidden="true"></i>
                        </a>

                        <a href="javascript://" class="btn btn-sm btn-danger mb-1" v-tippy content="Excluir"
                           @click.prevent="janelaConfirmar(cliente.id)"
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
                            url="{{route('g.administracao.clientes.atualizar')}}" por-pagina="100"
                            :dados="controle.dados"
                            v-on:carregou="carregou" v-on:carregando="carregando"></controle-paginacao>
    </div>
@stop

@push('js')
    <script src="{{mix('js/g/clientes/app.js')}}"></script>
@endpush


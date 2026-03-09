@extends('layouts.sistema')
@section('title', 'Fornecedores')
@section('content_header')
    <h4 class="text-default">FORNECEDORES</h4>
    <hr class="bg-default" style="margin-top: -5px;">
@stop
@section('content')
    <modal id="janelaConfirmar" titulo="Apagar">
        <template #conteudo>
            <preload v-show="preloadAjax"></preload>
            <div class="alert alert-success alert-dismissible" v-show="apagado">
                <h4><i class="icon fa fa-check"></i>Registro apagado com sucesso!</h4>
            </div>
            <h4 v-show="!apagado">Tem certeza que deseja apagar este registro?</h4>
        </template>
        <template #rodape>
            <button type="button" class="btn btn-sm mr-1 btn-danger" @click="apagar" v-show="!apagado">Apagar</button>
        </template>
    </modal>

    <modal id="janelaCadastrar" :titulo="tituloJanela" :size="90">
        <template #conteudo>
            <preload v-show="preloadAjax"></preload>
            <div class="alert alert-success alert-dismissible" v-show="cadastrado">
                <h4><i class="icon fa fa-check"></i>@{{form.tipo}} cadastrado com sucesso!</h4>
            </div>
            <div class="alert alert-success alert-dismissible" v-show="atualizado">
                <h4><i class="icon fa fa-check"></i>@{{form.tipo}} alterado com sucesso!</h4>
            </div>
            <form v-if="!preloadAjax && (!cadastrado && !atualizado)" id="form" onsubmit="return false;">

                <fieldset>
                    <legend class="text-uppercase">Tipo</legend>
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <label>Selecione o Tipo</label>
                            <select class="form-control" v-model="form.tipo">
                                <option
                                    value="{{\App\Models\Fornecedor::TIPO_FORNECEDOR}}">Fornecedor
                                </option>
                                <option
                                    value="{{\App\Models\Fornecedor::TIPO_PARCEIRO}}">Parceiro
                                </option>
                                <option
                                    value="{{\App\Models\Fornecedor::TIPO_TERCEIRO}}">Terceiro
                                </option>
                            </select>
                        </div>
                    </div>
                </fieldset>

                <div v-if="form.tipo !== ''">

                    <ul class="nav nav-tabs bg-light" id="tabslist" role="tablist"
                        style="border-bottom: 1px solid #653232">
                        <li class="nav-item">
                            <a class="nav-item nav-link active" id="nav-dados-cadastrais-tab" data-toggle="tab"
                               href="#nav-dados-cadastrais"
                               role="tab" aria-controls="nav-dados-cadastrais" aria-selected="true">DADOS CADASTRAIS</a>
                        </li>
                        <li class="nav-item" v-if="false">
                            <a class="nav-item nav-link" id="nav-servicos-tab" data-toggle="tab" href="#nav-servicos"
                               role="tab" aria-controls="nav-servicos" aria-selected="false">SERVIÇOS</a>
                        </li>

                    </ul>

                    <div class="tab-content py-3 p-2">
                        <div class="tab-pane fade show active" id="nav-dados-cadastrais" role="tabpanel"
                             aria-labelledby="nav-dados-cadastrais-tab">
                            <fieldset>
                                <legend class="text-uppercase">Dados do @{{form.tipo}}</legend>
                                <div class="row">
                                    <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                        <div class="form-group">
                                            <label>Tipo</label>
                                            <select class="form-control" v-model="form.tipo_pessoa"
                                                    :disabled="editando">
                                                <option
                                                    value="{{\App\Models\Fornecedor::PESSOA_JURIDICA}}">Pessoa Jurídica
                                                </option>

                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-6 col-lg-6 col-xl-6"
                                         v-show="form.tipo_pessoa === '{{\App\Models\Fornecedor::PESSOA_JURIDICA}}'">
                                        <div class="form-group">
                                            <label>CNPJ</label>
                                            <input type="text" id="cnpj" class="form-control" placeholder="CNPJ"
                                                   v-model="form.cnpj" :disabled="editando" autocomplete="mastertag"
                                                   onblur="valida_cnpj_vazio(this);"
                                                   @blur="verificaCnpj"
                                                   v-mascara:cnpj>
                                        </div>
                                    </div>

{{--                                    <div class="col-12 col-sm-6 col-lg-6 col-xl-6"--}}
{{--                                         v-show="form.tipo_pessoa === '{{\App\Models\Fornecedor::PESSOA_FISICA}}'">--}}
{{--                                        <div class="form-group">--}}
{{--                                            <label>CPF</label>--}}
{{--                                            <input type="text" class="form-control" placeholder="CPF"--}}
{{--                                                   v-model="form.cpf" :disabled="editando" autocomplete="mastertag"--}}
{{--                                                   onblur="valida_cpf_vazio(this)" v-mascara:cpf>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
                                </div>

                                <div class="row">
                                    <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                        <div class="form-group"
                                             v-if="form.tipo_pessoa === '{{\App\Models\Fornecedor::PESSOA_JURIDICA}}'">
                                            <label>Razão Social</label>
                                            <input type="text" class="form-control" v-model="form.razao_social"
                                                   placeholder="Razão Social"
                                                   :disabled="preloadCnpj"
                                                   autocomplete="mastertag" onblur="valida_campo_vazio(this,3)">
                                        </div>

{{--                                        <div class="form-group"--}}
{{--                                             v-if="form.tipo_pessoa === '{{\App\Models\Fornecedor::PESSOA_FISICA}}'">--}}
{{--                                            <label>Nome</label>--}}
{{--                                            <input type="text" class="form-control" v-model="form.nome"--}}
{{--                                                   placeholder="Nome do Fornecedor"--}}
{{--                                                   :disabled="preloadCnpj"--}}
{{--                                                   autocomplete="mastertag" onblur="valida_campo_vazio(this,3)">--}}
{{--                                        </div>--}}
                                    </div>

                                    <div class="col-12 col-sm-6 col-lg-6 col-xl-6"
                                         v-if="form.tipo_pessoa === '{{\App\Models\Fornecedor::PESSOA_JURIDICA}}'">
                                        <div class="form-group">
                                            <label>Nome Fantasia</label>
                                            <input type="text" class="form-control" v-model="form.nome_fantasia"
                                                   placeholder="Nome Fantasia"
                                                   :disabled="preloadCnpj"
                                                   autocomplete="mastertag">
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
                                                   autocomplete="mastertag">
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                        <div class="form-group">
                                            <label>E-mail</label>
                                            <input type="text" class="form-control" id="email" placeholder="E-mail"
                                                   v-model="form.email"
                                                   autocomplete="mastertag" onblur="validaEmailVazio(this)"
                                                   v-mascara:email>
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

                            <fieldset>
                                <legend>ANEXOS</legend>
                                <small>Anexo de </small>
                                <br>
                                <upload :model="form.anexos"
                                        :model-delete="form.anexosDel"
                                        :url="urlAnexoUpload"
                                        label="Anexar ..."
                                        @onProgresso="anexoUploadAndamento=true"
                                        @onFinalizado="anexoUploadAndamento=false"></upload>
                            </fieldset>

                            <div class="custom-control custom-switch">
                                <input type="checkbox" v-model="form.ativo" class="custom-control-input" id="ativo">
                                <label class="custom-control-label"
                                       for="ativo">@{{form.ativo ? 'Ativo' : 'Inativo'}}</label>
                            </div>

                        </div>

                        <div class="tab-pane fade" id="nav-servicos" v-if="false"
                             role="tabpanel" aria-labelledby="nav-servicos-tab">

                            <fieldset>
                                <legend class="text-uppercase">
                                    <span>Serviços Contratados</span>
                                </legend>
                                <div class="row">
                                    <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                                        <button class="btn btn-sm mr-1 btn-secondary mb-2"
                                                @click="addLIServicoFornecedor($event.target)">
                                            <span class="fas fa-plus" aria-hidden="true"></span>
                                            Adicionar Serviço
                                        </button>
                                    </div>

                                    <div class="col-12" v-show="form.servicos.length>0"
                                         v-for="(obj, index) in form.servicos" :key="obj.id">
                                        <div class="row py-3">

                                            <div class="col-12 col-sm-4"
                                                 v-if="form.tipo === '{{ \App\Models\Fornecedor::TIPO_FORNECEDOR }}' ">
                                                <div class="form-group">
                                                    <label>Vencimento</label>
                                                    <select v-model="obj.vencimento" class="form-control"
                                                            onblur="valida_campo_vazio(this,1)"
                                                            onchange="valida_campo_vazio(this,1)">
                                                        <option value="">Selecione ...</option>
                                                        <option
                                                            value="{{ \App\Models\Fornecedor::VENCIMENTO_MENSAL }}">{{ ucfirst(\App\Models\Fornecedor::VENCIMENTO_MENSAL) }}</option>
                                                        <option
                                                            value="{{ \App\Models\Fornecedor::VENCIMENTO_TRIMESTRAL }}">{{ ucfirst(\App\Models\Fornecedor::VENCIMENTO_TRIMESTRAL) }}</option>
                                                        <option
                                                            value="{{ \App\Models\Fornecedor::VENCIMENTO_SEMESTRAL }}">{{ ucfirst(\App\Models\Fornecedor::VENCIMENTO_SEMESTRAL) }}</option>
                                                        <option
                                                            value="{{ \App\Models\Fornecedor::VENCIMENTO_ANUAL }}">{{ ucfirst(\App\Models\Fornecedor::VENCIMENTO_ANUAL) }}</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-12 col-sm-4"
                                                 v-if="form.tipo != '{{ \App\Models\Fornecedor::TIPO_FORNECEDOR }}' ">
                                                <div class="form-group">
                                                    <label>Data Início</label>
                                                    <datepicker posicao="up" v-model="obj.data_inicio"></datepicker>
                                                </div>
                                            </div>

                                            <div class="col-12 col-sm-4"
                                                 v-if="form.tipo != '{{ \App\Models\Fornecedor::TIPO_FORNECEDOR }}' ">
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
                                                    <select v-model="obj.tipo_servico_fornecedor_id"
                                                            class="form-control"
                                                            onblur="valida_campo_vazio(this,1)"
                                                            onchange="valida_campo_vazio(this,1)">
                                                        <option value="">Selecione ...</option>
                                                        <option v-for="item in listaServicos" :value="item.id">
                                                            @{{item.label}}
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-12 col-sm-6 col-lg-4">
                                                <div class="form-group">
                                                    <label>Valor R$</label>
                                                    <select v-model="obj.valor" class="form-control"
                                                            onblur="valida_campo_vazio(this,1)"
                                                            onchange="valida_campo_vazio(this,1)">
                                                        <option value="">Selecione ...</option>
                                                        <option
                                                            value="{{\App\Models\FornecedorServico::DE_ZERO_A_QUINHENTOS}}">{{\App\Models\FornecedorServico::DE_ZERO_A_QUINHENTOS}}</option>
                                                        <option
                                                            value="{{\App\Models\FornecedorServico::DE_QUINHENTOS_A_MIL}}">{{\App\Models\FornecedorServico::DE_QUINHENTOS_A_MIL}}</option>
                                                        <option
                                                            value="{{\App\Models\FornecedorServico::ACIMA_DE_MIL}}">{{\App\Models\FornecedorServico::ACIMA_DE_MIL}}</option>
                                                    </select>
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

                                            <div class="col-12 col-sm-6 col-lg-4">
                                                <div class="form-group">
                                                    <label>Feedback</label>
                                                    <select v-model="obj.feedback" class="form-control"
                                                    >
                                                        <option value="">Selecione ...</option>
                                                        <option
                                                            value="{{\App\Models\FornecedorServico::FEEDBACK_QUALIFICADO}}">{{\App\Models\FornecedorServico::FEEDBACK_QUALIFICADO}}</option>
                                                        <option
                                                            value="{{\App\Models\FornecedorServico::FEEDBACK_NAO_QUALIFICADO}}">{{\App\Models\FornecedorServico::FEEDBACK_NAO_QUALIFICADO}}</option>

                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-12 col-sm-6 col-lg-4">
                                                <div class="form-group">
                                                    <label>Status</label>
                                                    <select v-model="obj.status" class="form-control">
                                                        <option
                                                            value="{{\App\Models\FornecedorServico::STATUS_INICIADO}}">{{\App\Models\FornecedorServico::STATUS_INICIADO}}</option>
                                                        <option
                                                            value="{{\App\Models\FornecedorServico::STATUS_CONCLUIDO}}">{{\App\Models\FornecedorServico::STATUS_CONCLUIDO}}</option>
                                                        <option
                                                            value="{{\App\Models\FornecedorServico::STATUS_NAO_INICIADO}}">{{\App\Models\FornecedorServico::STATUS_NAO_INICIADO}}</option>
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

                                            <div class="col-12">
                                                <fieldset>
                                                    <legend>ANEXOS</legend>
                                                    <small>Anexo de Notas Fiscais, Contratos...</small>
                                                    <br>

                                                    <upload :model="obj.anexos"
                                                            :model-delete="obj.anexosDel"
                                                            :url="urlAnexoServicoUpload"
                                                            label="Anexar ..."
                                                            @onProgresso="anexoServicoUploadAndamento=true"
                                                            @onFinalizado="anexoServicoUploadAndamento=false"></upload>

                                                </fieldset>
                                            </div>

                                            <div class="col-12 mb-3">
                                                <button class="btn btn-sm mr-1 btn-danger"
                                                        @click="removerLIServicoFornecedor(index)"
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
        <template #rodape>
            <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="editando && !atualizado && !preloadAjax"
                    @click="alterar()">
                Alterar
            </button>
            <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="!editando && !cadastrado && !preloadAjax"
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
                    <label for="">Buscar</label>
                    <input type="text"
                           placeholder="Buscar por nome"
                           autocomplete="mastertag"
                           class="form-control form-control-sm" :disabled="controle.carregando"
                           v-model="controle.dados.campoBusca">
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label>Tipo</label>
                    <select class="form-control form-control-sm" :disabled="controle.carregando"
                            v-model="controle.dados.campoTipo"
                            @change="atualizar()">
                        <option value="">Todos os Tipos</option>
                        <option
                            value="{{\App\Models\Fornecedor::TIPO_FORNECEDOR}}">Fornecedor
                        </option>
                        <option
                            value="{{\App\Models\Fornecedor::TIPO_PARCEIRO}}">Parceiro
                        </option>
                        <option
                            value="{{\App\Models\Fornecedor::TIPO_TERCEIRO}}">Terceiro
                        </option>
                    </select>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label>Status</label>
                    <select class="form-control form-control-sm" :disabled="controle.carregando"
                            v-model="controle.dados.campoStatus" @change="atualizar()">
                        <option value="">Todos os Status</option>
                        <option :value="true">Apenas Ativos</option>
                        <option :value="false">Apenas Inativos</option>
                    </select>
                </div>
            </div>

            <div class="col-12 col-md-9">
                <button type="button" class="btn btn-sm mr-1 btn-success" :disabled="controle.carregando" @click="atualizar">
                    <i
                        :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                    Atualizar
                </button>

                <button type="button" class="btn btn-sm mr-1 btn-primary" data-toggle="modal" :disabled="controle.carregando"
                        data-target="#janelaCadastrar"
                        @click="formNovo()">
                    Cadastrar
                </button>

                {{--                <a href="{{ route('fornecedors.excel') }}" v-show="!controle.carregando"--}}
                {{--                   class="btn btn-sm mr-1 btn-primary"><i--}}
                {{--                        class="fas fa-file-excel"></i>--}}
                {{--                    Exportar Excel</a>--}}
            </div>
        </form>
    </fieldset>
    <p class="text-center" v-if="controle.carregando">
        <preload></preload>
    </p>
    <div id="conteudo">

        <div class="alert alert-warning" v-show="!controle.carregando && lista.length==0">
            <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
        </div>
        <div class="table-responsive" v-show="!controle.carregando && lista.length > 0">
            <table class="tabela">
                <thead>
                <tr class="bg-default">
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Tipo</th>
                    <th>Contato</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="fornecedor in lista">
                    <td data-label="ID">
                        @{{fornecedor.id}}
                    </td>

                    <td data-label="Nome">
                        @{{ fornecedor.tipo_pessoa == 'pessoa_física' ? fornecedor.nome : fornecedor.razao_social }}
                    </td>

                    <td data-label="Tipo">
                        @{{ fornecedor.tipo }}
                    </td>

                    <td data-label="Contato">
                        @{{ fornecedor.contato }} -
                        <span v-for="tel in fornecedor.telefones">@{{tel.numero}}</span>
                    </td>

                    <td data-label="Status">
                        <bt-ativo :rota="`administracao/fornecedor/${fornecedor.id}/ativa-desativa`"
                                  :model="fornecedor"></bt-ativo>
                    </td>

                    <td data-label="Ações">
                        <a href="javascript://" class="btn btn-sm mr-1 btn-primary" title="Editar"
                           @click.prevent="formAlterar(fornecedor.id)"
                           data-toggle="modal"
                           data-target="#janelaCadastrar">
                            <i class="fa fa-edit" aria-hidden="true"></i>
                        </a>

                        {{--                        <a href="javascript://" class="btn btn-sm mr-1 btn-danger" title="Excluir"--}}
                        {{--                           @click.prevent="janelaConfirmar(fornecedor.id)"--}}
                        {{--                           data-toggle="modal"--}}
                        {{--                           data-target="#janelaConfirmar">--}}
                        {{--                            <i class="fa fa-trash" aria-hidden="true"></i>--}}
                        {{--                        </a>--}}
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <controle-paginacao class="d-flex justify-content-center" id="controle" ref="componente"
                            url="{{route('g.administracao.fornecedor.atualizar')}}" por-pagina="50"
                            :dados="controle.dados"
                            v-on:carregou="carregou" v-on:carregando="carregando"></controle-paginacao>
    </div>
@stop
@push('js')
    <script src="{{mix('js/g/fornecedores/app.js')}}"></script>
@endpush

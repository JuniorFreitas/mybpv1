@inject('Cliente', 'App\Models\Cliente')
@extends('layouts.sistema')
@section('title', 'Clientes')
@section('content_header')
    <h4 class="text-default">CLIENTES</h4>
    <hr class="bg-default" style="margin-top: -5px;">
@stop
@section('content')

    <modal id="janelaCadastrar" :titulo="tituloJanela" :size="90">
        <template slot="conteudo">
            <span v-show="preloadAjax"><i class="fa fa-spinner fa-pulse"></i> Aguarde...</span>
            <div class="alert alert-success alert-dismissible" v-show="cadastrado">
                <h4><i class="icon fa fa-check"></i>Cliente cadastrado com sucesso!</h4>
            </div>
            <div class="alert alert-success alert-dismissible" v-show="atualizado">
                <h4><i class="icon fa fa-check"></i>Cliente alterado com sucesso!</h4>
            </div>
            <form v-show="!preloadAjax && (!cadastrado && !atualizado)" id="form" onsubmit="return false;">
                <fieldset>
                    <legend>Identificação</legend>
                    <div class="row">
                        <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                            <label>Tipo</label>
                            <select class="form-control" v-model="form.tipo" :disabled="editando">
                                <option value="{{$Cliente::$PESSOA_FISICA}}">Pessoa Física</option>
                                <option value="{{$Cliente::$PESSOA_JURIDICA}}">Pessoa Jurídica</option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-6 col-xl-6" v-show="form.tipo=='pessoa_juridica'">
                            <div class="form-group" v-if="form.clientepj">
                                <label>CNPJ</label>
                                <input type="text" class="form-control" placeholder="CNPJ"
                                       v-model="form.clientepj.cnpj" :disabled="editando" autocomplete="off"
                                       onblur="valida_cnpj_vazio(this)" v-mascara:cnpj>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-6 col-xl-6" v-show="form.tipo=='pessoa_fisica'">
                            <div class="form-group" v-if="form.clientepf">
                                <label>CPF</label>
                                <input type="text" class="form-control" placeholder="CPF" v-model="form.clientepf.cpf"
                                       value=""
                                       autocomplete="off" onblur="valida_cpf_vazio(this)" v-mascara:cpf>
                            </div>
                        </div>
                    </div>

                    <div class="row" v-show="form.tipo=='pessoa_juridica'">
                        <div class="col-12 col-sm-6 col-lg-6 col-xl-6" v-if="form.clientepj">
                            <div class="form-group">
                                <label>Razão Social</label>
                                <input type="text" class="form-control" v-model="form.clientepj.razaosocial"
                                       placeholder="Razão Social"
                                       autocomplete="off" onblur="valida_campo_vazio(this,3)">
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-6 col-xl-6" v-if="form.clientepj">
                            <div class="form-group">
                                <label>Nome Fantasia</label>
                                <input type="text" class="form-control" v-model="form.clientepj.nomefantasia"
                                       placeholder="Nome Fantasia"
                                       autocomplete="off">
                            </div>
                        </div>
                    </div>

                    <div class="row" v-show="form.tipo=='pessoa_fisica'">
                        <div class="col-12 col-sm-6 col-lg-6 col-xl-6" v-if="form.clientepf">
                            <div class="form-group">
                                <label>Nome</label>
                                <input type="text" class="form-control" v-model="form.clientepf.nome"
                                       placeholder="Nome"
                                       autocomplete="off" onblur="valida_campo_vazio(this,3)">
                            </div>
                        </div>
                    </div>

                </fieldset>

                <fieldset>
                    <legend>Endereço</legend>
                    <endereco :model="form"></endereco>
                </fieldset>

                <fieldset>
                    <legend>Contatos</legend>
                    <div class="row">
                        <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                            <div class="form-group">
                                <label>Telefone</label>
                                <input type="text" class="form-control" id="telefone" placeholder="Telefone"
                                       v-model="form.telefone"
                                       autocomplete="off" v-mascara:telefone>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                            <div class="form-group">
                                <label>Celular</label>
                                <input type="text" class="form-control" id="celular" placeholder="Celular"
                                       v-model="form.celular"
                                       autocomplete="off" v-mascara:telefone>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                            <div class="form-group">
                                <label>Site</label>
                                <input type="text" class="form-control" id="site" placeholder="Site"
                                       v-model="form.site"
                                       autocomplete="off">
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                            <div class="form-group">
                                <label>E-mail</label>
                                <input type="text" class="form-control" id="email" placeholder="E-mail"
                                       v-model="form.email"
                                       autocomplete="off" onblur="validaEmailVazio(this)" v-mascara:email>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>SERVIÇOS CONTRATADOS</legend>
                    <div class="row">
                        <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                            <button class="btn btn-secondary mb-2" @click="addLIServico($event.target)">
                                <span class="fas fa-plus" aria-hidden="true"></span>
                                Adicionar Serviço
                            </button>
                        </div>
                        <div class="col-12 col-sm-12" v-show="form.servicos.length>0"
                             v-for="(obj, index) in form.servicos" :key="obj.id" style="margin-bottom: 13px">

                            <div class="row">

                                <div class="col-12 col-sm-4 col-md-4 col-lg-3">
                                    <div class="form-group">
                                        <div class="input-group">
                                            <div class="input-group-append">
                                                <div class="input-group-text">Empresa
                                                </div>
                                            </div>
                                            <select v-model="obj.empresa_id" class="custom-select">
                                                @foreach($empresas as $e)
                                                    <option value="{{$e->id}}">{{$e->razao_social}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-9 col-sm-5 col-md-5 col-lg-3">
                                    <div class="form-group">
                                        <div class="input-group">
                                            <div class="input-group-append">
                                                <div class="input-group-text">Serviço</div>
                                            </div>
                                            <select v-model="obj.servico_id" class="custom-select">
                                                @foreach($servicos as $s)
                                                    <option value="{{$s->id}}">{{$s->nome}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>


                                <div class="col-9 col-sm-4 col-md-4 col-lg-2">
                                    <div class="form-group">
                                        <div class="input-group">
                                            <div class="input-group-append">
                                                <div class="input-group-text">Valor R$</div>
                                            </div>
                                            <input type="text" v-mascara:dinheiro class="form-control text-right"
                                                   onblur="valida_dinheiro(this)"
                                                   placeholder="0,00"
                                                   v-model="obj.ValorPrestacaoFormat">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-9 col-sm-4 col-md-4 col-lg-2">
                                    <div class="form-group">
                                        <div class="input-group">
                                            <div class="input-group-append">
                                                <div class="input-group-text">Ano</div>
                                            </div>
                                            <input type="text" class="form-control text-right"
                                                   maxlength="4"
                                                   placeholder=""
                                                   v-model="obj.ano">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-9 col-sm-5 col-md-5 col-lg-2">
                                    <div class="form-group">
                                        <div class="input-group">
                                            <div class="input-group-append">
                                                <div class="input-group-text">Ativo</div>
                                            </div>
                                            <select v-model="obj.ativo" class="custom-select">
                                                <option :value="true">SIM</option>
                                                <option :value="false">NÃO</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-1">
                                    <button class="btn btn-danger" @click="removerLIServico(index)"><i
                                            class="fa fa-times"></i></button>
                                </div>

                            </div>

                        </div>

                    </div>
                </fieldset>

                <fieldset>
                    <legend>Valores e Responsável</legend>

                    <div class="row">
                        <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                            <div class="form-group">
                                <label>Data de Faturamento</label>
                                <select name="datafaturamento" id="datafaturamento"
                                        v-model="form.datafaturamento" class="form-control">
                                    @foreach(range(1,31) as $r)
                                        <option value="{{$r}}">{{$r}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row" v-show="form.tipo=='pessoa_juridica'">
                        <div class="col-12 col-sm-6 col-lg-6 col-xl-6" v-if="form.clientepj">
                            <div class="form-group">
                                <label>Responsável Direto</label>
                                <input type="text" class="form-control" v-model="form.clientepj.responsaveldireto"
                                       placeholder="Responsável Direto"
                                       autocomplete="off" onblur="valida_campo_vazio(this,3)">
                            </div>
                        </div>

                        <div class="col-6 col-sm-6 col-lg-6 col-xl-6">
                            <div class="form-group">
                                <label>Ativo</label>
                                <select class="form-control" id="ativo" v-model="form.ativo">
                                    <option value="true">Sim</option>
                                    <option value="false">Não</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </fieldset>

            </form>
        </template>
        <template slot="rodape">
            <button type="button" class="btn btn-primary" v-show="editando && !atualizado" @click="alterar()">
                Alterar
            </button>
            <button type="button" class="btn btn-primary" v-show="!editando && !cadastrado" @click="cadastrar()">
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
            <button type="button" class="btn btn-danger" @click="apagar()" v-show="!apagado">Apagar</button>
        </template>
    </modal>


    <form id="formBusca">
        <div class="row">
            <div class="col-md-4 column">
                <div class="form-group">
                    <label>Buscar:</label>
                    <div class="input-group">
                        <span class="input-group-prepend">
                            <i class="input-group-text" id="basic-addon1"><i class="fa fa-search"></i></i>
                        </span>
                        <input type="text" id="campoBusca"
                               placeholder="Pesquise por (Razão Social, Nome Fantasia ou CNPJ)" autocomplete="off"
                               class="form-control">

                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                <div class="form-group">
                    <label>Por empresa:</label>
                    <div class="input-group">
                        <select class="form-control" v-model="controle.dados.empresa_id" @change="filtroEmpresa()">
                            <option value="" selected>Todas as empresas</option>
                            @foreach($empresas as $empresa)
                                <option value="{{$empresa->id}}">{{$empresa->razao_social}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                <div class="form-group">
                    <label>Por tipo de serviço:</label>
                    <div class="input-group">
                        <select class="form-control" v-model="controle.dados.servico_id" @change="filtroServico()">
                            <option value="" selected>Todos os tipos de serviços</option>
                            @foreach($servicos as $servico)
                                <option value="{{$servico->id}}">{{$servico->nome}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </form>



    <button type="button" class="btn btn-success" id="btnAtualizar">Atualizar</button>

    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#janelaCadastrar"
            @click="formNovo()">
        Cadastrar
    </button>

    <a href="{{route('g.administracao.clientes.excel')}}" class="btn btn-primary"><i class="fas fa-file-excel"></i>
        Exportar Excel</a>

    {{--<a href="{{route('g.administracao.clientes.excel')}}" class="btn btn-primary"><i class="fas fa-file-pdf"></i> Exportar PDF</a>--}}

    <p class="text-center" v-if="controle.carregando">
        <i class="fa fa-spinner fa-pulse"></i> Carregando...
    </p>

    <div id="conteudo">
        <h4 v-show="!controle.carregando && lista.length==0"></h4>
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-condensed"
                   v-if="!controle.carregando && lista.length > 0" style="font-size: 0.85em;">
                <thead>
                <tr class="bg-default">
                    <th class="text-center">ID</th>
                    <th>Cliente</th>
                    <th>CNPJ</th>
                    <th>Responsável Direto</th>
                    <th>Contato</th>
                    <th>Valor Prestação</th>
                    <th>Faturamento</th>
                    <th colspan="2"></th>

                </tr>
                </thead>
                <tbody>
                <tr v-for="cliente in lista">
                    <td class="text-center">
                        @{{cliente.id}}
                    </td>
                    <td>
                        @{{cliente.clientepj ? cliente.clientepj.razaosocial+' - '+cliente.clientepj.nomefantasia :
                        cliente.clientepf.nome+' - '+cliente.clientepf.cpf}}
                    </td>
                    <td>
                        @{{cliente.clientepj ? cliente.clientepj.cnpj : cliente.clientepf.cpf}}
                    </td>
                    <td>@{{cliente.clientepj ? cliente.clientepj.responsaveldireto : null}}</td>
                    <td>@{{cliente.contato}}</td>
                    <td>@{{cliente.ValorPrestacaoFormat}}</td>
                    <td>@{{cliente.datafaturamento}}</td>
                    <td class="text-center">
                        <a href="javascript://" class="text-dark" title="Editar"
                           @click.prevent="formAlterar(cliente.id)"
                           data-toggle="modal"
                           data-target="#janelaCadastrar">
                            <i class="fa fa-edit" aria-hidden="true"></i>
                        </a>

                    </td>
                    <td class="text-center">
                        <a href="javascript://" class="text-danger" title="Excluir"
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

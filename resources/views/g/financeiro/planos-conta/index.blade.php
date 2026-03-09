@inject('PlanoConta', 'App\Models\PlanoConta')
@extends('layouts.sistema')
@section('content_header', 'Planos de conta')

@section('breadcrumb')
    <li class="breadcrumb-item active">Financeiro - Planos de conta</li>
@endsection
@section('content')
    <modal id="janelaCadastrar" :titulo="tituloJanela" size="g">
        <template #conteudo>

            <preload v-show="preloadAjax" label="Aguarde..."></preload>
            <div class="alert alert-success alert-dismissible" v-show="cadastrado">
                <h4><i class="icon fa fa-check"></i> Plano de conta cadastrada com sucesso!</h4>
            </div>
            <div class="alert alert-success alert-dismissible" v-show="atualizado">
                <h4><i class="icon fa fa-check"></i> Plano de conta alterado com sucesso!</h4>
            </div>
            <form v-show="!preloadAjax && (!cadastrado && !atualizado)" id="form" onsubmit="return false;">

                <div class="row">
                    <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                        <div class="form-group">
                            <label>Classificação</label>
                            <select v-model="categoria_plano_id" onselect="valida_campo_vazio(this,1)" class="form-control">
                                <option :value="0">Selecione...</option>
                                @foreach ($categorias as $cat)
                                    <option value="{{$cat->id}}">{{$cat->descricao}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                        <div class="form-group">
                            <label>Operação</label>
                            <select v-model="operacao" class="form-control">
                                <option value="">Selecione...</option>
                                <option value="{{$PlanoConta::OPERACAO_CREDITO}}">Crédito</option>
                                <option value="{{$PlanoConta::OPERACAO_DEBITO}}">Débito</option>
                                <option value="{{$PlanoConta::OPERACAO_TODAS}}">Todas</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                            <label>Descrição</label>
                            <input type="text" class="form-control" v-model="descricao" id="descricao" autocomplete="off"
                                   onblur="valida_campo_vazio(this,3)">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label>Ativa</label>
                            <select class="form-control" id="ativo">
                                <option value="true">Sim</option>
                                <option value="false">Não</option>
                            </select>
                        </div>
                    </div>

                </div>

            </form>
        </template>
        <template #rodape>
            <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="editando && !atualizado" @click="alterar()">
                Alterar
            </button>
            <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="!editando && !cadastrado" @click="cadastrar()">
                Cadastrar
            </button>
        </template>
    </modal>

    <modal id="janelaConfirmar" titulo="Apagar plano de contas">
        <template #conteudo>
            <preload v-show="preloadAjax" label="Aguarde..."></preload>
            <div class="alert alert-success alert-dismissible" v-show="apagado">
                <h4><i class="icon fa fa-check"></i>Plano de conta apagado com sucesso!</h4>
            </div>
            <h4 v-show="!apagado && !preloadAjax">Atenção! Deseja realmente apagar este plano de conta:</h4>

        </template>
        <template #rodape>
            <button type="button" class="btn btn-sm mr-1 btn-danger" @click="apagar()" v-show="!apagado && !preloadAjax">Apagar</button>
        </template>
    </modal>

    <div class="row">
        <div class="col-md-4 column">
            <form id="formBusca" @submit.prevent="atualizar">
                <div class="form-group">
                    <label>Buscar:</label>
                    <div class="input-group">
                        <span class="input-group-prepend">
                            <i class="input-group-text" id="basic-addon1"><i class="fa fa-search"></i></i>
                        </span>
                        <input type="text" v-model="controle.dados.campoBusca" placeholder="Nome do plano de conta" autocomplete="off"
                               class="form-control">

                    </div>
                </div>
            </form>
        </div>
    </div>


    <button type="button" class="btn btn-sm mr-1 btn-success" id="btnAtualizar" @click="atualizar">Atualizar</button>

    <button type="button" class="btn btn-sm mr-1 btn-primary" data-toggle="modal" data-target="#janelaCadastrar"
            @click="formNovo()">
        Cadastrar
    </button>

    <p class="text-center" >
        <preload v-if="controle.carregando"></preload>
    </p>

    <div id="conteudo">
        <h4 v-show="!controle.carregando && lista.length==0"></h4>
        <div class="table-responsive">
            <table class="tabela"
                   v-if="!controle.carregando && lista.length > 0">
                <thead>
                <tr class="bg-default">
                    <th class="text-center">Código</th>
                    <th class="text-center">Descrição</th>
                    <th class="text-center">Classificação</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="plano in lista">
                    <td data-label="id" class="text-center" width="10%">@{{plano.id}}</td>
                    <td data-label="descrição">@{{plano.descricao}}</td>
                    <td data-label="classificação">@{{plano.categoria.descricao}}</td>
                    <td class="text-center">
                        <a href="#" class="btn btn-sm mr-1 btn-success"
                           @click.prevent="formAlterar(plano.id)"
                           data-toggle="modal"
                           data-target="#janelaCadastrar">
                            <i class="fa fa-edit" aria-hidden="true"></i> Alterar
                        </a>
                        <a href="#" class="btn btn-sm mr-1 btn-danger"
                           @click.prevent="janelaConfirmar(plano.id)"
                           data-toggle="modal"
                           data-target="#janelaConfirmar">
                            <i class="fa fa-trash" aria-hidden="true"></i> Excluir
                        </a>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <controle-paginacao class="d-flex justify-content-center" id="controle" ref="paginacao"
                            url="{{route('g.financeiro.plano-conta.atualizar')}}" por-pagina="10"
                            :dados="controle.dados"
                            v-on:carregou="carregou" v-on:carregando="carregando"></controle-paginacao>
    </div>


@stop
@push('js')
    <script src="{{mix('js/g/planos-conta/app.js')}}"></script>
@endpush

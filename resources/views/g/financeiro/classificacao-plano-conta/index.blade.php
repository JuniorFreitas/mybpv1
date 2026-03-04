@extends('layouts.sistema')
@push('css')
    <link rel="stylesheet" href="https://alexa.pro.br/driver/driver.min.css">
@endpush
@section('content_header', 'Classificação de planos de conta')

@section('breadcrumb')
    <li class="breadcrumb-item active">Financeiro - Classificação de planos de conta</li>
@endsection
@section('content')

    <modal id="janelaCadastrar" :titulo="tituloJanela" size="g">
        <template #conteudo>

            <preload v-show="preloadAjax" label="Aguarde..."></preload>
            <div class="alert alert-success alert-dismissible" v-show="cadastrado">
                <h4> <i class="icon fa fa-check"></i> Cadastrada com sucesso!</h4>
            </div>
            <div class="alert alert-success alert-dismissible" v-show="atualizado">
                <h4> <i class="icon fa fa-check"></i> Alterada com sucesso!</h4>
            </div>
            <form v-show="!preloadAjax && (!cadastrado && !atualizado)" id="form" onsubmit="return false;">
                <div class="form-group">
                    <label>Descrição</label>
                    <input type="text" id="descricao" placeholder="Nome da classificação" autocomplete="off"
                           class="form-control" v-model="descricao" @onblur="this.valida_campo_vazio($event.target,3)">
                </div>

                <div class="form-group">
                    <label>Ativo</label>
                    <select class="form-control" v-model="ativo">
                        <option :value="true">Sim</option>
                        <option :value="false">Não</option>
                    </select>
                </div>
            </form>
        </template>
        <template #rodape>
            <button type="button" class="btn btn-sm btn-primary" v-show="editando && !atualizado" @click="alterar()">
                Alterar
            </button>
            <button type="button" class="btn btn-sm btn-primary" v-show="!editando && !cadastrado" @click="cadastrar()">
                Cadastrar
            </button>
        </template>
    </modal>

    <modal id="janelaConfirmar" titulo="Apagar classificação">
        <template #conteudo>
            <preload v-show="preloadAjax" label="Aguarde..."></preload>
            <div class="alert alert-success alert-dismissible" v-show="apagado">
                <h4><i class="icon fa fa-check"></i> Classificação apagada com sucesso!</h4>
            </div>
            <h4 v-show="!apagado">Tem certeza que deseja apagar esta Classificação?</h4>
        </template>
        <template #rodape>
            <button type="button" class="btn btn-sm btn-danger" @click="apagar()" v-show="!apagado && !preloadAjax">Apagar</button>
        </template>
    </modal>

    <div class="row">
        <div class="col-md-4 column">
            <form @submit.prevent="atualizar">
                <div class="form-group">
                    <label>Buscar:</label>
                    <div class="input-group">
                        <span class="input-group-prepend">
                            <i class="input-group-text" id="basic-addon1"><i class="fa fa-search"></i></i>
                        </span>
                        <input v-model="controle.dados.campoBusca" type="text" id="campoBusca" placeholder="Nome da classificação" autocomplete="off"
                               class="form-control">

                    </div>
                </div>
            </form>
        </div>
    </div>


    <button type="button" class="btn btn-sm btn-success" id="guide-menu" @click="atualizar">Atualizar</button>
    @can('classificacao-plano-conta_insert')
    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" id="btCadastrar" data-target="#janelaCadastrar"
            @click="formNovo()">
        Cadastrar
    </button>
    @endcan
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
                    <th class="text-center">Descrição</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="categorias in lista">
                    <td data-label="descrição">@{{categorias.descricao}}</td>
                    <td>
                        @can('classificacao-plano-conta_update')
                        <a href="javascript://" class="btn btn-sm btn-success btnFormAlterar"
                           @click.prevent="formAlterar(categorias.id)"
                           data-toggle="modal"
                           data-target="#janelaCadastrar">
                            <i class="fa fa-edit" aria-hidden="true"></i> Alterar
                        </a>
                        @endcan
                        @can('classificacao-plano-conta_delete')
                        <a href="javascript://" class="btn btn-sm btn-danger btnFormExcluir"
                           @click.prevent="janelaConfirmar(categorias.id)"
                           data-toggle="modal"
                           data-target="#janelaConfirmar">
                            <i class="fa fa-trash" aria-hidden="true"></i> Excluir
                        </a>
                        @endcan
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <controle-paginacao class="d-flex justify-content-center" id="controle" ref="paginacao"
                            url="{{route('g.financeiro.classificacao-plano-conta.atualizar')}}" por-pagina="10"
                            :dados="controle.dados"
                            v-on:carregou="carregou" v-on:carregando="carregando"></controle-paginacao>
    </div>



@stop
@push('js')
    <script src="{{mix('js/g/classificacao-plano-conta/app.js')}}"></script>
@endpush

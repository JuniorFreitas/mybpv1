@extends('layouts.sistema')
@section('title', 'CLOUD - CADASTRO')
@section('content_header')
{{--    <h4 class="text-default">CLOUD - CADASTRO</h4>--}}
{{--    <hr class="bg-warning" style="margin-top: -5px;">--}}
@stop
@section('content')
    <cadastro></cadastro>
  {{--  <modal id="janelaCadastrar" :titulo="tituloJanela" size="g">
        <template #conteudo>
            <span v-show="preloadAjax">
                <i class="fa fa-spinner fa-pulse"></i> Carregando...
            </span>
            <div class="alert alert-success alert-dismissible" v-show="cadastrado">
                <h5>
                    <i class="icon fa fa-check"></i> Cloud cadastrado com sucesso!
                </h5>
            </div>
            <div class="alert alert-success alert-dismissible" v-show="atualizado">
                <h5>
                    <i class="icon fa fa-check"></i> Cloud alterado com sucesso!
                </h5>
            </div>

            <form v-show="!preloadAjax && (!cadastrado && !atualizado)" @submit.prevent="false">
                <div class="col-12">
                    <fieldset>
                        <legend>INFORMAÇÕES</legend>
                        <div class="form-group">
                            <label>Nome</label>
                            <input type="text" class="form-control form-control-sm" v-model="form.nome"
                                   placeholder="Nome do Cloud"
                                   autocomplete="off" onblur="valida_campo_vazio(this,2)">
                        </div>

                        <div class="form-group">
                            <label>Ativo</label>
                            <select class="form-control form-control-sm" v-model="form.ativo">
                                <option :value="true">Sim</option>
                                <option :value="false">Não</option>
                            </select>
                        </div>

                    </fieldset>
                </div>
            </form>

        </template>
        <template #rodape>
            <div v-show="!preloadAjax">
                <button type="button" class="btn btn-sm btn-primary" v-show="editando && !atualizado"
                        @click="alterar">Alterar
                </button>
                <button type="button" class="btn btn-sm btn-primary" v-show="!editando && !cadastrado"
                        @click="cadastrar">Cadastrar
                </button>
            </div>
        </template>
    </modal>

    <div class="row">
        <div class="col-md-4 column mb-2">
            <form id="formBusca" onsubmit="return false;">
                <label>Buscar:</label>
                <input type="text" placeholder="Nome do cloud" autocomplete="off"
                       class="form-control form-control-sm">
            </form>
        </div>
    </div>

    <button type="button" class="btn btn-sm btn-success" @click.prevent="atualizar">
        <i class="fa fa-sync"></i> Atualizar
    </button>

    <button type="button" class="btn btn-sm btn-primary" id="btnFormCadastrar" data-toggle="modal"
            data-target="#janelaCadastrar" @click="formNovo()">Cadastrar
    </button>

    <preload class="mt-2" v-if="controle.carregando"></preload>

    <div id="conteudo">
        <p class="alert alert-warning text-center" v-show="!controle.carregando && lista.length===0">
            <i class="fa fa-exclamation-triangle"></i> Nenhum registro encontrado!
        </p>
        <div class="table-responsive">
            <table class="tabela"
                   v-if="!controle.carregando && lista.length > 0">
                <thead>
                <tr class="bg-default">
                    <th class="text-center">ID</th>
                    <th class="text-center">Nome</th>
                    <th class="text-center">Ativo</th>
                    <th></th>
                </tr>
                </thead>

                <tbody>
                <tr v-for="item in lista">
                    <td class="text-center">@{{item.id}}</td>
                    <td class="text-center">@{{item.nome}}</td>
                    <td class="text-center">
                        <bt-ativo :rota="`clouds/cadastro/${item.id}/ativa-desativa`" :model="item"></bt-ativo>
                    </td>
                    <td class="text-center">
                        <a class="btn btn-sm btn-success btnFormAlterar" href="javascript://"
                           @click.prevent="formAlterar(item.id)" data-toggle="modal"
                           data-target="#janelaCadastrar">
                            <i class="fa fa-edit"></i>
                        </a>
                        <a class="btn btn-sm btn-danger btnFormExcluir" href="javascript://"
                           @click.prevent="janelaConfirmar(item.id)" data-toggle="modal"
                           data-target="#janelaConfirmar">
                            <i class="fa fa-trash" aria-hidden="true"></i>
                        </a>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

    <controle-paginacao class="d-flex justify-content-center" id="controle" ref="componente"
                        url="{{route('g.cloud.cadastro.listarClouds')}}"
                        :por-pagina="controle.dados.pages"
                        :dados="controle.dados"
                        v-on:carregou="carregou" v-on:carregando="carregando"></controle-paginacao>--}}
@stop
@push('js')
    <script src="{{mix('js/g/cloud/cadastro/app.js')}}"></script>
@endpush

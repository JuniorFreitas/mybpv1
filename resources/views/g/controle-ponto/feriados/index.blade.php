@extends('layouts.sistema')
@section('title', 'Feriados')
@section('content_header')
    <h4 class="text-default">FERIADOS</h4>
    <hr class="bg-warning" style="margin-top: -5px;">
@stop
@section('content')

    <modal id="janelaCadastrar" :titulo="tituloJanela" size="g">
        <template slot="conteudo">

            <span v-show="form.preload"><i class="fa fa-spinner fa-pulse"></i> Aguarde...</span>
            <div class="alert alert-success alert-dismissible" v-show="form.cadastrado">
                <h4> <i class="icon fa fa-check"></i>Feriado cadastrado com sucesso!</h4>
            </div>
            <div class="alert alert-success alert-dismissible" v-show="form.atualizado">
                <h4> <i class="icon fa fa-check"></i>Feriado alterado com sucesso!</h4>
            </div>
            <form v-show="!form.preload && (!form.cadastrado && !form.atualizado)" id="form">
                <div class="form-group">
                    <label>Descrição</label>
                    <input v-model="form.descricao" type="text" class="form-control" id="descricao" placeholder="Descrição do feriado"
                           autocomplete="off" onblur="valida_campo_vazio(this,3)">
                </div>
                <div class="form-group">
                    <datepicker label="Data" v-model="form.data" placeholder="dd/mm/aaaa" :disabled="form.preload"></datepicker>
                </div>
                <div class="form-group">
                    <label>Ativo</label>
                    <select class="form-control" id="ativo" v-model="form.ativo">
                        <option :value="true">Sim</option>
                        <option :value="false">Não</option>
                    </select>
                </div>
            </form>
        </template>
        <template slot="rodape">
            <button type="button" class="btn btn-sm btn-primary" v-show="!form.preload && form.editando && !form.atualizado" @click="alterar()">
                Alterar
            </button>
            <button type="button" class="btn btn-sm btn-primary" v-show="!form.preload && !form.editando && !form.cadastrado" @click="cadastrar()">
                Cadastrar
            </button>
        </template>
    </modal>

    <modal id="janelaConfirmar" titulo="Apagar feriados">
        <template slot="conteudo">
            <span v-show="form.preload"><i class="fa fa-spinner fa-pulse"></i>Aguarde...</span>
            <div class="alert alert-success alert-dismissible" v-show="form.apagado">
                <h4> <i class="icon fa fa-check"></i>Feriado apagado com sucesso!</h4>
            </div>
            <h4 v-show="!form.apagado">Tem certeza que deseja apagar este feriado?</h4>
        </template>
        <template slot="rodape">
            <button type="button" class="btn btn-sm btn-danger" @click="apagar()" v-show="!form.apagado">Apagar</button>
        </template>
    </modal>

    <div class="row">
        <div class="col-md-4 column">
            <form id="formBusca">
                <div class="form-group">
                    <label>Buscar:</label>
                    <div class="input-group">
                        <span class="input-group-prepend">
                            <i class="input-group-text" id="basic-addon1"><i class="fa fa-search"></i></i>
                        </span>
                        <input type="text" id="campoBusca" placeholder="Nome do feriado" autocomplete="off"
                               class="form-control">

                    </div>
                </div>
            </form>
        </div>
    </div>


    <button type="button" class="btn btn-sm btn-success" id="btnAtualizar" @click="atualizar">Atualizar</button>

    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#janelaCadastrar" @click="formNovo()">
        Cadastrar
    </button>

    <p class="text-center" v-if="controle.carregando">
        <i class="fa fa-spinner fa-pulse"></i> Carregando...
    </p>

    <div id="conteudo">
        <h4 v-show="!controle.carregando && lista.length==0"></h4>
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-condensed"
                   v-if="!controle.carregando && lista.length > 0">
                <thead>
                <tr class="bg-default">
                    <th class="text-center">Data</th>
                    <th class="text-center">Descrição</th>
                    <th class="text-center">Status</th>
                    <th></th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="feriado in lista" >
                    <td class="text-center">@{{feriado.data}}</td>
                    <td class="text-center">@{{feriado.descricao}}</td>
                    <td class="text-center">
                        <bt-ativo :rota="`controle-ponto/feriados/${feriado.id}/ativa-desativa`" :model="feriado"></bt-ativo>
                    </td>
                    <td class="text-center">
                        <a href="javascript://" class="btn btn-sm btn-success btnFormAlterar"
                           @click.prevent="formAlterar(feriado.id)"
                           data-toggle="modal"
                           data-target="#janelaCadastrar">
                            <i class="fa fa-edit" aria-hidden="true"></i> Alterar
                        </a>
                        <a href="javascript://" class="btn btn-sm btn-danger btnFormExcluir"
                           @click.prevent="janelaConfirmar(feriado.id)"
                           data-toggle="modal"
                           data-target="#janelaConfirmar">
                            <i class="fa fa-trash" aria-hidden="true"></i> Excluir
                        </a>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <controle-paginacao class="d-flex justify-content-center" id="controle" ref="componente"
                            url="{{route('g.controle-ponto.feriados.atualizar')}}" por-pagina="20"
                            :dados="controle.dados"
                            v-on:carregou="carregou" v-on:carregando="carregando"></controle-paginacao>
    </div>
@stop
@push('js')
    <script src="{{mix('js/g/controle-ponto/feriados/app.js')}}"></script>
@endpush

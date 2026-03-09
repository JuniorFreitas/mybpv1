@extends('layouts.sistema')
@section('content_header', 'Controle de ponto - Ocorrências de jornada')

@section('breadcrumb')
    <li class="breadcrumb-item active">Controle de ponto - Ocorrências de jornadas</li>
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
                    <input type="text" id="descricao" placeholder="Nome da ocorrência" autocomplete="off"
                           class="form-control" v-model="descricao" @onblur="this.valida_campo_vazio($event.target,3)">
                </div>

                <div class="form-group">
                    <label>Dia trabalhado</label>
                    <select class="form-control" v-model="trabalhado">
                        <option :value="true">Sim</option>
                        <option :value="false">Não</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Conta horas</label>
                    <select class="form-control" v-model="conta_horas">
                        <option :value="true">Sim</option>
                        <option :value="false">Não</option>
                    </select>
                    <small>Contabiliza para o saldo de horas</small>
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
            <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="editando && !atualizado" @click="alterar()">
                Alterar
            </button>
            <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="!editando && !cadastrado" @click="cadastrar()">
                Cadastrar
            </button>
        </template>
    </modal>

    <modal id="janelaConfirmar" titulo="Apagar ocorrência">
        <template #conteudo>
            <preload v-show="preloadAjax" label="Aguarde..."></preload>
            <div class="alert alert-success alert-dismissible" v-show="apagado">
                <h4><i class="icon fa fa-check"></i> Ocorrência apagada com sucesso!</h4>
            </div>
            <h4 v-show="!apagado">Tem certeza que deseja apagar esta ocorrência?</h4>
        </template>
        <template #rodape>
            <button type="button" class="btn btn-sm mr-1 btn-danger" @click="apagar()" v-show="!apagado && !preloadAjax">Apagar</button>
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
                        <input v-model="controle.dados.campoBusca" type="text" id="campoBusca" placeholder="Nome da ocorrência" autocomplete="off"
                               class="form-control">

                    </div>
                </div>
            </form>
        </div>
    </div>


    <button type="button" class="btn btn-sm mr-1 btn-success" id="guide-menu" @click="atualizar">Atualizar</button>
    @can('ocorrencias_jornadas_insert')
    <button type="button" class="btn btn-sm mr-1 btn-primary" data-toggle="modal" id="btCadastrar" data-target="#janelaCadastrar"
            @click="formNovo()">
        Cadastrar
    </button>
    @endcan
    <p class="text-center" >
        <preload v-if="controle.carregando"></preload>
    </p>

    <div id="conteudo">
        <h4 v-show="!controle.carregando && lista.length===0" class="text-center"> Nenhuma ocorrência cadastrada</h4>
        <div class="table-responsive">
            <table class="tabela"
                   v-if="!controle.carregando && lista.length > 0">
                <thead>
                <tr class="bg-default">
                    <th class="text-center">Descrição</th>
                    <th class="text-center">Dia trabalhado</th>
                    <th class="text-center">Conta horas</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="ocorrencia in lista">
                    <td data-label="descrição">@{{ocorrencia.descricao}}</td>
                    <td data-label="dia trabalhado">
                        <span class="badge badge-success" v-if="ocorrencia.trabalhado">Sim</span>
                        <span class="badge badge-danger" v-else>Não</span>
                    </td>
                    <td data-label="conta horas">
                        <span class="badge badge-success" v-if="ocorrencia.conta_horas">Sim</span>
                        <span class="badge badge-danger" v-else>Não</span>
                    </td>
                    <td>
                        @can('ocorrencias_jornadas_update')
                        <a href="javascript://" class="btn btn-sm mr-1 btn-success btnFormAlterar"
                           @click.prevent="formAlterar(ocorrencia.id)"
                           data-toggle="modal"
                           data-target="#janelaCadastrar">
                            <i class="fa fa-edit" aria-hidden="true"></i> Alterar
                        </a>
                        @endcan
                        @can('ocorrencias_jornadas_delete')
                        <a href="javascript://" class="btn btn-sm mr-1 btn-danger btnFormExcluir"
                           @click.prevent="janelaConfirmar(ocorrencia.id)"
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
                            url="{{route('g.controle-ponto.ocorrencias_jornadas.atualizar')}}" por-pagina="10"
                            :dados="controle.dados"
                            v-on:carregou="carregou" v-on:carregando="carregando"></controle-paginacao>
    </div>



@stop
@push('js')
    <script src="{{mix('js/g/controle-ponto/ocorrencias_jornadas/app.js')}}"></script>
@endpush

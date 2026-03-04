@extends('layouts.sistema')
@section('title', 'Cargos')
@section('content_header','Cargos')
@section('content')

    <modal id="janelaCadastrar" :titulo="tituloJanela" :size="90">
        <template #conteudo>
            <div v-show="preloadAjax"><i class="fa fa-spinner fa-pulse"></i> Aguarde...</div>
            <div class="alert alert-success alert-dismissible" v-show="cadastrado">
                <h4><i class="icon fa fa-check"></i>Cargo cadastrado com sucesso!</h4>
            </div>
            <div class="alert alert-success alert-dismissible" v-show="atualizado">
                <h4><i class="icon fa fa-check"></i>Cargo alterado com sucesso!</h4>
            </div>
            <form v-if="!preloadAjax && (!cadastrado && !atualizado)" id="form" onsubmit="return false;">

                <div class="form-group">
                    <label>Nome</label>
                    <input type="text" class="form-control" v-model="form.nome"
                           placeholder="Nome"
                           autocomplete="off">
                </div>

                <div class="switchToggle">
                    <input type="checkbox" v-model="form.ativo" id="switch">
                    <label for="switch">Ativo</label>
                </div>

            </form>
        </template>
        <template #rodape>
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
                           class="form-control form-control-sm" :disabled="controle.carregando" v-model="controle.dados.campoBusca">
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label>Status</label>
                    <select class="form-control form-control-sm" :disabled="controle.carregando" v-model="controle.dados.campoStatus" @change="atualizar()">
                        <option value="">Todos os Status</option>
                        <option :value="true">Apenas Ativos</option>
                        <option :value="false">Apenas Inativos</option>
                    </select>
                </div>
            </div>

            <div class="col-12 col-md-9">
                <button type="button" class="btn btn-sm btn-success" :disabled="controle.carregando" @click="atualizar">
                    <i
                        :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>Atualizar
                </button>
                <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" :disabled="controle.carregando"
                        data-target="#janelaCadastrar"
                        @click="formNovo()">
                    Cadastrar
                </button>
            </div>
        </form>
    </fieldset>

    <p class="text-center" v-if="controle.carregando">
        <i class="fa fa-spinner fa-pulse"></i> Carregando...
    </p>

    <div id="conteudo">
        <div class="alert alert-warning" v-show="!controle.carregando && lista.length===0">
            <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
        </div>

        <div class="table-responsive" v-show="!controle.carregando && lista.length > 0">
            <table class="tabela">
                <thead>
                <tr class="bg-default">
                    <th class="text-center">ID</th>
                    <th>Nome</th>
                    <th>Ativo</th>
                    <th>Ação</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="vaga in lista">
                    <td class="text-center">
                        @{{vaga.id}}
                    </td>

                    <td>
                        @{{vaga.nome}}
                    </td>

                    <td class="text-center">
                        <bt-ativo :rota="`cadastro/vagas/${vaga.id}/ativa-desativa`" :model="vaga"></bt-ativo>
                    </td>

                    <td class="text-center">
                        <a href="javascript://" class="btn btn-sm btn-primary mb-1" title="Editar"
                           @click.prevent="formAlterar(vaga.id)"
                           data-toggle="modal"
                           data-target="#janelaCadastrar">
                            <i class="fa fa-edit"></i>
                        </a>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <controle-paginacao
            class="d-flex justify-content-center"
            id="controle"
            ref="componente"
            url="{{route('g.vagas.vagas.atualizar')}}"
            por-pagina="100"
            :dados="controle.dados"
            v-on:carregou="carregou"
            v-on:carregando="carregando">
        </controle-paginacao>
    </div>
@stop
@push('js')
    <script src="{{mix('js/g/vagas/app.js')}}"></script>
@endpush

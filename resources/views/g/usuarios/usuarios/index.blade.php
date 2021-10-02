@extends('layouts.sistema')
@section('content_header', 'Usuários do sistema')
@section('breadcrumb')
    <li class="breadcrumb-item active">Usuários - Usuários do sistema</li>
@endsection
@section('content')
    <modal id="janelaCadastrar" :titulo="tituloJanela" size="g">
        <template slot="conteudo">

            <span v-show="preloadAjax"><preload></preload></span>
            <div class="alert alert-success alert-dismissible" v-show="cadastrado">
                <h4><i class="icon fa fa-check"></i>Usuário cadastrado com sucesso!</h4>
            </div>
            <div class="alert alert-success alert-dismissible" v-show="atualizado">
                <h4><i class="icon fa fa-check"></i>Usuário alterado com sucesso!</h4>
            </div>

            <form v-show="!preloadAjax && (!cadastrado && !atualizado)" id="form">
                <div class="form-group">
                    <label>Nome do usuário</label>
                    <input type="text" class="form-control form-control-sm" v-model="form.nome"
                           placeholder="Nome do usuário"
                           autocomplete="off"
                           onblur="valida_campo_vazio(this,3)">
                </div>
                <div class="form-group">
                    <label>Login</label>
                    <input type="text" class="form-control form-control-sm" v-model="form.login" placeholder="Login"
                           autocomplete="off"
                           onblur="valida_campo_vazio(this,3)">
                </div>

                <div class="form-check mb-3" v-if="editando">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox" v-model="form.alterarSenha">
                        Redefinir senha
                    </label>
                </div>

                <div class="form-group" v-if="editando && form.alterarSenha || !editando">
                    <label>Senha</label>
                    <input type="password" class="form-control form-control-sm" v-model="form.password"
                           placeholder="Senha"
                           autocomplete="off"
                           onblur="valida_campo_vazio(this,3)">
                </div>

                <div class="form-group" v-if="editando && form.alterarSenha || !editando">
                    <label>Redigitar senha</label>
                    <input type="password" class="form-control form-control-sm" v-model="form.password_confirmation"
                           placeholder="Redigitar senha"
                           autocomplete="off" onblur="valida_campo_vazio(this,3)">
                </div>

                <div class="form-group">
                    <label>Empresa</label>
                    <select class="form-control form-control-sm" v-model="form.empresa_id"
                            onchange="valida_campo_vazio(this,1)"
                            onblur="valida_campo_vazio(this,1)" @change="selecionaEmpresa(form.empresa_id)">
                        <option value="">Selecione...</option>
                        @foreach (\App\Models\Cliente::whereAtivo(true)->get() as $cliente)
                            <option value="{{$cliente->id}}">{{$cliente->nome_fantasia}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" v-if="grupoempresa">
                    <label>Grupo</label>
                    <select class="form-control form-control-sm" v-model="form.grupo_id"
                            onchange="valida_campo_vazio(this,1)"
                            onblur="valida_campo_vazio(this,1)">
                        <option value="">Selecione...</option>
                        <option v-for="papel in listaPapeis" :value="papel.id">@{{papel.nome}}</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Grupo cloud</label>
                    <select class="form-control form-control-sm" v-model="form.grupo_cloud_id"
                            onchange="valida_campo_vazio(this,1)"
                            onblur="valida_campo_vazio(this,1)">
                        <option value="">Selecione...</option>
                        @foreach (\App\Models\GrupoCloud::get() as $grupo_cloud)
                            <option value="{{$grupo_cloud->id}}">{{$grupo_cloud->nome}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Ativo</label>
                    <select class="form-control form-control-sm" v-model="form.ativo">
                        <option :value="true">Sim</option>
                        <option :value="false">Não</option>
                    </select>
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
    <modal id="janelaConfirmar" titulo="Apagar Usuário">
        <template slot="conteudo">
            <span v-show="preloadAjax"><i class="fa fa-spinner fa-pulse"></i>Aguarde...</span>
            <div class="alert alert-success alert-dismissible" v-show="apagado">
                <h4><i class="icon fa fa-check"></i>Usuário apagado com sucesso!</h4>
            </div>
            <h4 v-show="!apagado">Tem certeza que deseja apagar este usuário?</h4>
        </template>
        <template slot="rodape">
            <button type="button" class="btn btn-sm btn-danger" @click="apagar()" v-show="!apagado">Apagar</button>
        </template>
    </modal>
    <fieldset>
        <legend>Filtragem por</legend>

        <div class="row">
            <div class="col-md-4 column">
                <form id="formBusca" @keypress.enter="$refs.componente.buscar()" onsubmit="return false;">
                    <div class="form-group">
                        <label>Buscar:</label>
                        <div class="input-group input-group-sm">
                        <span class="input-group-prepend">
                            <i class="input-group-text" id="basic-addon1"><i class="fa fa-search"></i></i>
                        </span>
                            <input type="text" id="campoBusca" v-model="controle.dados.campoBusca"
                                   placeholder="Nome do usuário" autocomplete="off"
                                   class="form-control form-control-sm">
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <button type="button" class="btn btn-sm btn-success" :disabled="controle.carregando" @click="atualizar"><i
                :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
            Atualizar
        </button>
        @can('usuarios_insert')
            <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#janelaCadastrar"
                    @click="formNovo()">
                Criar novo usuário
            </button>
        @endcan
    </fieldset>

    <p class="text-center" v-if="controle.carregando">
        <preload></preload>
    </p>
    <div id="conteudo">
        <h4 v-show="!controle.carregando && lista.length==0"></h4>
        <div class="table-responsive">
            <table class="tabela" v-if="!controle.carregando && lista.length > 0">
                <thead>
                <tr class="bg-default">
                    <th>Nome</th>
                    <th>Grupo</th>
                    <th>Status</th>
                    <th>Ação</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="usuario in lista">
                    <td data-label="Nome">@{{usuario.nome}}</td>
                    <td data-label="Grupo">
                        <span v-if="usuario.papel">@{{usuario.papel.nome}}</span>
                        <span v-else> - </span>
                    </td>
                    <td data-label="Status">
                        <bt-ativo :rota="`usuarios/${usuario.id}/ativa-desativa`" :model="usuario"></bt-ativo>
                    </td>
                    <td>
                        @can('usuarios_update')
                            <a href="javascript://" class="btn btn-sm btn-success btnFormAlterar"
                               @click.prevent="formAlterar(usuario.id)"
                               data-toggle="modal"
                               data-target="#janelaCadastrar">
                                <i class="fa fa-edit" aria-hidden="true"></i>
                            </a>
                        @endcan
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <controle-paginacao class="d-flex justify-content-center" id="controle" ref="componente"
                            url="{{route('g.usuarios.usuarios.atualizar')}}" por-pagina="10" :dados="controle.dados"
                            v-on:carregou="carregou" v-on:carregando="carregando"></controle-paginacao>
    </div>
@stop

@push('js')
    <script src="{{mix('js/g/usuarios/app.js')}}"></script>
@endpush

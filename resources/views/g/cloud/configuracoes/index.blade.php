@extends('layouts.sistema')
@section('title', 'CLOUD - CONFIGURAÇÕES')
@section('content_header')
    <h4 class="text-default">CLOUD - CONFIGURAÇÕES</h4>
    <hr class="bg-warning" style="margin-top: -5px;">
@stop
@section('content')

    <modal id="janelaCadastrar" :titulo="tituloJanela" size="g">
        <template #conteudo>
            <span v-show="preloadAjax">
                <i class="fa fa-spinner fa-pulse"></i> Carregando...
            </span>
            <div class="alert alert-success alert-dismissible" v-show="cadastrado">
                <h5>
                    <i class="icon fa fa-check"></i> Grupo cadastrado com sucesso!
                </h5>
            </div>
            <div class="alert alert-success alert-dismissible" v-show="atualizado">
                <h5>
                    <i class="icon fa fa-check"></i> Grupo alterado com sucesso!
                </h5>
            </div>

            <form v-show="!preloadAjax && (!cadastrado && !atualizado)" id="form">
                <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="nav-item">
                        <a href="#abaIdentificacao" class="nav-link active" aria-controls="home" role="tab"
                           data-toggle="tab">Identificação</a>
                    </li>
                    <li role="presentation">
                        <a href="#abaHabilidades" class="nav-link" aria-controls="profile" role="tab"
                           data-toggle="tab">Membros</a>
                    </li>
                </ul>

                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane active" id="abaIdentificacao">
                        <div class="col-12 py-3">
                            <fieldset>
                                <legend>INFORMAÇÕES</legend>

                                <div class="form-group">
                                    <label>Nome</label>
                                    <input type="text" class="form-control form-control-sm" v-model="form.nome"
                                           :readonly="form.nome === 'Administradores'"
                                           :disabled="form.nome === 'Administradores'"
                                           placeholder="Nome do grupo"
                                           autocomplete="off" onblur="valida_campo_vazio(this,2)">
                                </div>


                                <div class="form-group">
                                    <label>Descrição</label>
                                    <input type="text" class="form-control  form-control-sm" v-model="form.descricao"
                                           placeholder="Descrição do grupo"
                                           :readonly="form.nome === 'Administradores'"
                                           :disabled="form.nome === 'Administradores'"
                                           autocomplete="off" onblur="valida_campo_vazio(this,3)">
                                </div>

                                <div class="form-group" v-if="form.nome !== 'Administradores'">
                                    <label>Ativo</label>
                                    <select class="form-control  form-control-sm" v-model="form.ativo">
                                        <option :value="true">Sim</option>
                                        <option :value="false">Não</option>
                                    </select>
                                </div>

                            </fieldset>


                            <fieldset>
                                <legend>HABILIDADES</legend>
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered table-condensed bg-white">
                                        <thead>
                                        <tr class="bg-default">
                                            <th><span class="ml-1">FUNÇÃO</span></th>
                                            <th class="text-center" v-if="form.nome !== 'Administradores'">
                                                <a class="btn btn-sm btn-success" href="javascript://"
                                                   @click.prevent="selecionarTodas" v-if="!form.todasHabilidades">
                                                    <span class="fa fa-ok" aria-hidden="true"></span> Permitir todas
                                                </a>
                                                <a class="btn btn-sm btn-danger" href="javascript://"
                                                   @click.prevent="selecionarTodas" v-if="form.todasHabilidades">
                                                    <span class="fa fa-remove" aria-hidden="true"></span> Negar todas
                                                </a>
                                            </th>
                                        </tr>
                                        </thead>

                                        <tbody>

                                        <tr v-for="habilidade in form.habilidades">
                                            <td><span class="ml-1">@{{habilidade.nome}}</span></td>
                                            <td class="text-center" v-if="form.nome !== 'Administradores'">
                                                <a class="btn btn-sm btn-success" href="javascript://"
                                                   @click="verificaHabilitados(habilidade)"
                                                   v-if="habilidade.acesso">
                                                    <span class="fa fa-ok" aria-hidden="true"></span> Permitir
                                                </a>
                                                <a class="btn btn-sm btn-danger" href="javascript://"
                                                   @click="verificaHabilitados(habilidade)"
                                                   v-if="!habilidade.acesso">
                                                    <span class="fa fa-remove" aria-hidden="true"></span> Negar
                                                </a>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>

                                </div>
                            </fieldset>
                        </div>
                    </div>

                    <div role="tabpanel" class="tab-pane" id="abaHabilidades">
                        <div class="col-12 py-3">

                            <fieldset>
                                <legend>Participantes</legend>

                                <div class="form-group">
                                    <label>Colaborador </label>
                                    <autocomplete :caminho="`autocomplete/buscaUsuariosAtivos`"
                                                  :formsm="true"
                                                  v-model="form.autocomplete_label_colaborador"
                                                  placeholder="Selecione um(a) colaborador(a)"
                                                  :id="`colaborador_${hash}`"
                                                  @onselect="selecionaColaborador"></autocomplete>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-condensed bg-white"
                                           v-if="form.usuarios.length > 0">
                                        <thead>
                                        <tr class="bg-default">
                                            <th class="text-center">#</th>
                                            <th class="text-center">Nome</th>
                                            <th class="text-center">Remover</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="(colaborador, index) in form.usuarios">
                                            <td class="text-center">@{{index + 1}}</td>
                                            <td class="text-center">@{{colaborador.nome}}</td>
                                            <td class="text-center">
                                                <a href="javascript://" class="btn btn-sm btn-danger"
                                                   @click.prevent="removerLIColaborador(index)">
                                                    <i class="fa fa-times" aria-hidden="true"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </fieldset>

                            {{--                            <fieldset>--}}
                            {{--                                <legend>USUÁRIOS</legend>--}}
                            {{--                                <div class="table-responsive">--}}
                            {{--                                    <table class="table table-hover table-bordered table-condensed">--}}
                            {{--                                        <thead>--}}
                            {{--                                        <tr>--}}
                            {{--                                            <th>Cód</th>--}}
                            {{--                                            <th>Nome</th>--}}
                            {{--                                        </tr>--}}
                            {{--                                        </thead>--}}

                            {{--                                        <tbody>--}}

                            {{--                                        <tr v-for="usuario in form.usuarios">--}}
                            {{--                                            <td>@{{usuario.id}}</td>--}}
                            {{--                                            <td>@{{usuario.nome}}</td>--}}

                            {{--                                        </tr>--}}

                            {{--                                        </tbody>--}}
                            {{--                                    </table>--}}
                            {{--                                </div>--}}
                            {{--                            </fieldset>--}}
                        </div>
                    </div>
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
        <div class="col-md-6 column">
            <form id="formBusca" onsubmit="return false;">
                <div class="form-group">
                    <label>Buscar:</label>
                    <input type="text" id="campoBusca" placeholder="Nome do grupo" autocomplete="off"
                           class="form-control  form-control-sm">
                </div>
            </form>
        </div>
    </div>

    <button type="button" class="btn btn-sm btn-success" @click.prevent="atualizar">
        <i class="fa fa-sync"></i> Atualizar
    </button>

    <button type="button" class="btn btn-sm btn-primary" id="btnFormCadastrar" data-toggle="modal"
            data-target="#janelaCadastrar" @click="formNovo()">Cadastrar
    </button>

    <p class="text-center" v-if="controle.carregando">
        <i class="fa fa-spinner fa-pulse"></i> Carregando...
    </p>

    <div id="conteudo">
        <h5 v-show="!controle.carregando && lista.length==0">
            <i class="fa fa-exclamation-triangle"></i> Nenhum registro encontrado!
        </h5>
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-condensed bg-white"
                   v-if="!controle.carregando && lista.length > 0">
                <thead>
                <tr class="bg-default">
                    <th class="text-center">Nome</th>
                    <th class="text-center">Descrição</th>
                    <th class="text-center">Qnt membros</th>
                    <th class="text-center">Ativo</th>
                    <th></th>
                </tr>
                </thead>

                <tbody>
                <tr v-for="grupo in lista">
                    <td class="text-center">@{{grupo.nome}}</td>
                    <td class="text-center">@{{grupo.descricao}}</td>
                    <td class="text-center">@{{grupo.usuarios_count}}</td>
                    <td class="text-center">
                        <span class="badge badge-success" v-if="grupo.ativo">Ativo</span>
                        <span class="badge badge-danger" v-if="!grupo.ativo">Inativo</span>
                    </td>
                    <td class="text-center">
                        <a class="btn btn-sm btn-success btnFormAlterar" href="javascript://"
                           @click.prevent="formAlterar(grupo.id)" data-toggle="modal"
                           data-target="#janelaCadastrar">
                            <i class="fa fa-edit"></i>
                        </a>
                        <a class="btn btn-sm btn-danger btnFormExcluir" v-if="grupo.nome !== 'Administradores'" href="javascript://"
                           @click.prevent="janelaConfirmar(grupo.id)" data-toggle="modal"
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
                        url="{{route('g.cloud.configuracoes.atualizar')}}"
                        :por-pagina="controle.dados.pages"
                        :dados="controle.dados"
                        v-on:carregou="carregou" v-on:carregando="carregando"></controle-paginacao>
@stop
@push('js')
    <script src="{{mix('js/g/cloud/configuracoes/app.js')}}"></script>
@endpush

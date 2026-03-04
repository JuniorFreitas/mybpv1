@extends('layouts.sistema')
@section('title', 'Projetos')
@section('content_header','Projetos')
@section('content')

    <modal id="janelaCadastrar" :titulo="tituloJanela" :size="90">
        <template #conteudo>
            <div v-show="preloadAjax"><i class="fa fa-spinner fa-pulse"></i> Aguarde...</div>
            <div class="alert alert-success alert-dismissible" v-show="cadastrado">
                <h4><i class="icon fa fa-check"></i>Projeto cadastrado com sucesso!</h4>
            </div>
            <div class="alert alert-success alert-dismissible" v-show="atualizado">
                <h4><i class="icon fa fa-check"></i>Projeto alterado com sucesso!</h4>
            </div>
            <form v-if="!preloadAjax && (!cadastrado && !atualizado)" id="form" onsubmit="return false;">

                <div class="form-group">
                    <label>Nome</label>
                    <input type="text" class="form-control form-control-sm" v-model="form.nome"
                           placeholder="Nome"
                           autocomplete="off">
                </div>

                <div class="form-group">
                    <label>Quantidade total de vagas</label>
                    <input type="number" class="form-control form-control-sm" v-model="form.qnt_total"
                           oninput="this.value = Math.abs(this.value)"
                           min="1"
                           placeholder="Quantidade total de vagas"
                           autocomplete="off">
                </div>

                <fieldset>
                    <legend>Vagas do projeto</legend>
                    <h5 class="alert alert-warning text-uppercase">Total de vagas restante: @{{totalRestanteVagas}}</h5>

                    <div class="form-group">
                        <autocomplete :caminho="`autocomplete/todas-vagas-abertas-ativas`"
                                      :formsm="true"
                                      :disabled="totalRestanteVagas <= 0"
                                      v-model="form.autocomplete_label_vaga_aberta"
                                      placeholder="Selecione uma vaga"
                                      :id="`vagas_projeto_${hash}`"
                                      @onselect="selecionaVaga"></autocomplete>
                    </div>

                    <div class="table-responsive" v-if="form.vagas_projeto.length">
                        <table class="table table-bordered table-hover table-condensed bg-white">
                            <thead>
                            <tr class="bg-default">
{{--                                <th class="text-center">#</th>--}}
                                <th class="text-center">Vaga Aberta/Cargo</th>
                                <th class="text-center">Qnt Total</th>
                                <th class="text-center">Qnt Preenchidas</th>
{{--                                <th class="text-center">Remover</th>--}}
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(item, index) in form.vagas_projeto">
                                <td class="text-center">@{{ item.vaga_aberta.titulo }}
                                    <br>
                                    <pre class="text-danger">Cargo: @{{ item.vaga_aberta.vaga.nome }}</pre>
                                </td>

                                <td class="text-center">
                                    <input type="number"
                                           min="1"
                                           oninput="this.value = Math.abs(this.value)"
                                           class="form-control form-control-sm text-center"
                                           :maxlength="totalRestanteVagas" v-model="item.qnt_total">
                                </td>

                                <td class="text-center">@{{ item.qnt_preenchida }}</td>
{{--                                <td class="text-center">--}}
{{--                                    <a href="javascript://" class="btn btn-sm btn-danger"--}}
{{--                                       @click.prevent="removerLIColaborador(index)">--}}
{{--                                        <i class="fa fa-times" aria-hidden="true"></i>--}}
{{--                                    </a>--}}
{{--                                </td>--}}
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </fieldset>

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
                           class="form-control form-control-sm" :disabled="controle.carregando"
                           v-model="controle.dados.campoBusca">
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
                    <th>Total de vagas</th>
                    <th>Restantes</th>
                    <th>Preenchidas</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="projeto in lista">
                    <td class="text-center">
                        @{{projeto.id}}
                    </td>

                    <td class="text-center">
                        @{{projeto.nome}}
                    </td>

                    <td>
                        @{{projeto.qnt_total}}
                    </td>

                    <td>
                        @{{projeto.qnt_total_restante}}
                    </td>

                    <td>
                        @{{projeto.preenchidas}}
                    </td>

                    <td class="text-center">
                        <a href="javascript://" class="btn btn-sm btn-primary mb-1" title="Editar"
                           @click.prevent="formAlterar(projeto.id)"
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
            url="{{route('g.projetos.projetos.atualizar')}}"
            por-pagina="100"
            :dados="controle.dados"
            v-on:carregou="carregou"
            v-on:carregando="carregando">
        </controle-paginacao>
    </div>
@stop
@push('js')
    <script src="{{mix('js/g/projeto/app.js')}}"></script>
@endpush

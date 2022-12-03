@extends('layouts.sistema')
@section('title', 'Vincular Avaliadores')
@section('content_header', 'Vincular Avaliadores')
@section('content')

    <modal id="janelaAssociarAvaliador" titulo="Associar avaliadores" :fechar="!preload"
           :size="80">
        <template slot="conteudo">
            <fieldset v-if="editando">
                <legend>Avaliadores</legend>

                <div class="form-group">
                    <label>Avaliador </label>
                    <autocomplete :caminho="`autocomplete/buscaAvaliadoresAtivos`"
                                  :formsm="true"
                                  v-model="form.autocomplete_label_avaliador"
                                  placeholder="Selecione um(a) avaliador(a)"
                                  :id="`avaliador_${hash}`"
                                  metodo="post"
                                  :dados="{
                                    funcionariosSelecionados: funcionariosSelecionados,
                                  }"
                                  @onselect="selecionaAvaliador"
                                  @onblur="resetaCampo"
                    ></autocomplete>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-condensed bg-white"
                           v-if="form.avaliadores.length > 0">
                        <thead>
                        <tr class="bg-default">
                            <th class="text-center">Nome</th>
                            <th class="text-center">Remover</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(avaliador, index) in form.avaliadores">
                            <td class="text-center">@{{ avaliador.nome }}</td>
                            <td class="text-center">
                                <a href="javascript://" class="btn btn-sm btn-danger"
                                   @click.prevent="removerLIAvaliador(index)">
                                    <i class="fa fa-times" aria-hidden="true"></i>
                                </a>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </fieldset>
        </template>
        <template slot="rodape">
            <button :disabled="listaFuncionarios.length === 0" v-if="!preload && !update" class="btn btn-sm btn-success"
                    type="button" @click="assosicarAvaliadores">
                <i class="fas fa-link"></i> Associar
            </button>
        </template>
    </modal>

    <div class="row">
        <div class="col-12 pt-5">
            <h4 v-show="!controle.carregando && listaFuncionarios.length===0" class="text-center mt-3"> Sem
                colaboradores cadastrados</h4>
            <form @submit.prevent="atualizarListaFuncionarios">
                <div class="form-row align-items-center">
                    <div class="col-sm-3 my-1">
                        <label class="sr-only">Buscar</label>
                        <div class="input-group mb-2">
                            <input type="text" class="form-control" placeholder="Nome colaborador"
                                   v-model="controle.dados.campoBusca"
                                   @keyup="controle.dados.campoBusca===''? atualizarListaFuncionarios():false">
                            <div class="input-group-append">
                                <div class="input-group-text"><i class="fas fa-search"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto mb-2">
                        <button type="button" class="btn btn-secondary" :disabled="funcionariosSelecionados.length===0"
                                data-toggle="modal" data-target="#janelaAssociarAvaliador"
                                @click="formAssociarAvaliador">
                            <i class="fas fa-link"></i> Associar Avaliadores
                        </button>
                    </div>
                </div>
            </form>
            <preload v-if="controle.carregando"></preload>
            <table class="tabela"
                   v-if="!controle.carregando && listaFuncionarios.length > 0">
                <thead>
                <tr class="bg-default">
                    <th>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" v-model="todosFuncionariosSelecionados"
                                   @change="selecionarTodosFuncionarios">
                            <label class="form-check-label" style="visibility: hidden"></label>
                        </div>
                    </th>
                    <th>Nome</th>
                    {{--                            <th >Empresa</th>--}}
                    <th>Avaliadores</th>
                </tr>
                </thead>
                <tbody>
                <tr class="pointer" v-for="funcionario in listaFuncionarios"
                    @click="selecionarFuncionario(funcionario)">
                    <td data-label="id" class="text-center" width="10%">
                        <div class="form-check">
                            <input type="checkbox" :value="funcionario.id" class="form-check-input"
                                   v-model="funcionariosSelecionados">
                            <label class="form-check-label" style="visibility: hidden"></label>
                        </div>
                    </td>
                    <td data-label="nome">@{{funcionario.curriculo.nome}}</td>
                    {{--                            <td data-label="empresa">@{{funcionario.empresa.nome}}</td>--}}
                    <td data-label="avaliadores">
                                <span class="badge badge-secondary ml-1 p-1" v-if="funcionario.avaliadores.length"
                                      v-for="avaliadores in funcionario.avaliadores">
                                    @{{avaliadores.avaliador.nome}}
                                </span>
                    </td>
                </tr>
                </tbody>
            </table>

            <controle-paginacao
                class="d-flex justify-content-center"
                id="controle"
                ref="componente"
                :url="urlPaginacao"
                por-pagina="100"
                :dados="controle.dados"
                v-on:carregou="carregou"
                v-on:carregando="carregando">
            </controle-paginacao>


        </div>

    </div>
@stop
@push('js')
    <script src="{{mix('js/g/avaliacoes/avaliador/app.js')}}"></script>
@endpush

@push('css')
    <style type="text/css">
        .googleMaps {
            height: 100%;
            border: 1px solid #aeb9c2;
        }

        .pac-container {
            z-index: 1051 !important;
        }

        .pointer {
            cursor: pointer;
        }
    </style>
@endpush

@extends('layouts.sistema')
@section('title', 'Vincular Avaliadores')
@section('content_header', 'Vincular Avaliadores')
@section('content')

    <!--Janela de Associar Avaliador-->
    <modal id="janelaAssociarAvaliador"  titulo="Associar avaliadores" :fechar="!formFuncionarios.preload" @fechou="resetFuncionariosSelecionados">
        <template slot="conteudo">
            <fieldset v-if="editando">
                <legend>Avaliadores</legend>

                <div class="form-group">
                    <label>Colaborador </label>
                    <autocomplete :caminho="`autocomplete/buscaUsuariosAtivos`"
                                  :formsm="true"
                                  v-model="form.autocomplete_label_colaborador"
                                  placeholder="Selecione um(a) colaborador(a)"
                                  :id="`colaborador_${hash}`"
                                  metodo="post"
                                  :dados=""
                                  @onselect="selecionaColaborador"></autocomplete>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-condensed bg-white"
                           v-if="form.usuarios.length > 0">
                        <thead>
                        <tr class="bg-default">
                            <th class="text-center">Nome</th>
                            <th class="text-center">Remover</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(colaborador, index) in form.usuarios">
                            <td class="text-center">{{colaborador.nome}}</td>
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
        </template>
        <template slot="rodape">
            <button :disabled="listaFuncionarios.length === 0" v-if="!formFuncionarios.preload && !formFuncionarios.update" class="btn btn-sm btn-success" type="button" @click="assosicarPerimetros">
                <i class="fas fa-link"></i> Aplicar
            </button>
        </template>
    </modal>

    <div class="row">
        <div class="col-12 pt-5">
            <h4 v-show="!paginacaoFuncionarios.carregando && listaFuncionarios.length===0" class="text-center mt-3"> Sem colaboradores cadastrados</h4>
            <form @submit.prevent="atualizarListaFuncionarios">
                <div class="form-row align-items-center">
                    <div class="col-sm-3 my-1">
                        <label class="sr-only">Buscar</label>
                        <div class="input-group mb-2">
                            <input type="text" class="form-control" placeholder="Nome colaborador" v-model="paginacaoFuncionarios.dados.campoBusca" @keyup="paginacaoFuncionarios.dados.campoBusca===''? atualizarListaFuncionarios():false">
                            <div class="input-group-append">
                                <div class="input-group-text"><i class="fas fa-search"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto mb-2">
                        <button type="button" class="btn btn-secondary" :disabled="formPerimetroFuncionarios.funcionariosSelecionados.length===0" data-toggle="modal" data-target="#janelaAssociarPerimetro" @click="formAssociarPerimetro" >
                            <i class="fas fa-link"></i> Associar Avaliadores
                        </button>
                    </div>
                </div>
            </form>
            <preload v-if="paginacaoFuncionarios.carregando"></preload>
            <table class="tabela"
                   v-if="!paginacaoFuncionarios.carregando && listaFuncionarios.length > 0">
                <thead>
                <tr class="bg-default">
                    <th>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" v-model="todosFuncionariosSelecionados" @change="selecionarTodosFuncionarios">
                            <label class="form-check-label" style="visibility: hidden"></label>
                        </div>
                    </th>
                    <th >Nome</th>
                    {{--                            <th >Empresa</th>--}}
                    <th >Avaliadores</th>
                </tr>
                </thead>
                <tbody>
                <tr class="pointer" v-for="funcionario in listaFuncionarios" @click="selecionarFuncionario(funcionario)">
                    <td data-label="id" class="text-center" width="10%">
                        <div class="form-check">
                            <input type="checkbox" :value="funcionario.id" class="form-check-input" v-model="formPerimetroFuncionarios.funcionariosSelecionados">
                            <label class="form-check-label" style="visibility: hidden"></label>
                        </div>
                    </td>
                    <td data-label="nome" >@{{funcionario.curriculo.nome}}</td>
                    {{--                            <td data-label="empresa">@{{funcionario.empresa.nome}}</td>--}}
                    <td data-label="avaliadores">
                                <span class="badge badge-secondary ml-1 p-1" v-if="funcionario.avaliadores.length" v-for="avaliadores in funcionario.avaliadores">
                                    @{{avaliadores.avaliador.nome}}
                                </span>
                    </td>
                </tr>
                </tbody>
            </table>
            <controle-paginacao class="d-flex justify-content-center" ref="paginacaoFuncionarios"
                                url="{{route('g.avaliadores.atualizarFuncionarios')}}" por-pagina="30"
                                :dados="paginacaoFuncionarios.dados"
                                v-on:carregou="carregou" v-on:carregando="carregando"></controle-paginacao>
        </div>

    </div>
@stop
@push('js')
<!--    <script
        src='https://maps.google.com/maps/api/js?key=AIzaSyAjFL_y1aNK8ROElPeoZWvDwX5h7UYePkI&language=pt-BR&libraries=places&callback=initMap'
        async defer
    ></script>-->
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
        .pointer{
            cursor: pointer;
        }
    </style>
@endpush

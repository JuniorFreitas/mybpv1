@extends('layouts.sistema')
@section('title', 'Efetivo')
@section('content_header')
    <h4 class="text-default">Efetivo</h4>
    <hr class="bg-default" style="margin-top: -5px;">
@stop
@section('content')
    <fieldset>
        <legend class="text-uppercase">Filtro</legend>
{{--        <form class="row" @submit.prevent="$refs.componente.buscar()">--}}
{{--            <div class="col-12 col-sm-4 col-md-3 col-lg-2">--}}
{{--                <div class="form-group">--}}
{{--                    <label>Centros de Custo</label>--}}
{{--                    <select v-model="controle.dados.campoCentrosDeCusto" :disabled="controle.carregando"--}}
{{--                            @change="atualizar()" class="form-control form-control-sm">--}}
{{--                        <option value="">Todas os centros de custo</option>--}}
{{--                        <option value="nenhum">SEM CENTRO DE CUSTO</option>--}}
{{--                        <option v-for="item in centros_de_custo" :value="item.id" :key="item.id"--}}
{{--                                v-text="item.label"></option>--}}
{{--                    </select>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </form>--}}

        <div class="col-12">
            <div class="row">
                <button type="button" class="btn btn-sm mr-1 btn-success mr-1 mb-2" :disabled="controle.carregando"
                        @click="atualizar"><i
                        :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                    Atualizar
                </button>
{{--                <button type="button" class="btn btn-sm mr-1 btn-primary mb-2 mr-1"--}}
{{--                        @click.prevent="exportaPdf()"--}}
{{--                        :disabled="controle.carregando|| preloadExportacao || (!controle.carregando && lista.length===0) ">--}}
{{--                    <i class="fas fa-file-pdf"></i> EXPORTAR PDF--}}
{{--                </button>--}}
                <button type="button" class="btn btn-sm mr-1 btn-primary mb-2 mr-1"
                        @click.prevent="exportaExcel()"
                        :disabled="controle.carregando|| preloadExportacao || (!controle.carregando && lista.length===0) ">
                    <i class="fas fa-file-excel"></i> EXPORTAR EXCEL
                </button>
            </div>
        </div>

    </fieldset>

    <preload v-if="controle.carregando" class="text-center"></preload>

    <div id="conteudo">
        <div class="alert alert-warning" v-show="!controle.carregando && lista.length===0">
            <h3 class="text-center mt-3">Listagem Sintética de Funcionários</h3>
            <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
        </div>
        <div v-show="!controle.carregando && lista.length > 0" >
            <h3 class="text-center mt-3 mb-3">Listagem Sintética de Funcionários</h3>
{{--            <div v-for="centro_de_custo in lista" class="mb-5">--}}
            <div class="mb-5">
                <table class="tabela">
                    <thead>
                    <tr class="bg-default">
                        <th>Código</th>
                        <th>Nome</th>
                        <th>Cargo</th>
                        <th>Salário</th>
                        <th>Tipo Admissão</th>
                        <th>Data da Admissão</th>
                        <th>Centro de Custo</th>
                    </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in lista">
                            <td>
                                @{{  item.feedback.curriculo.id }}
                            </td>
                            <td>
                                @{{  item.feedback.curriculo.nome }}
                            </td>
                            <td>
                                @{{  item.cargo }}
                            </td>
                            <td>
                                @{{  'R$ '+item.salario  }}
                            </td>
                            <td>
                                @{{  item.tipo_admissao }}
                            </td>
                            <td>
                                @{{  item.data_admissao }}
                            </td>
                            <td>
                                @{{  item.centro_custo_label }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <controle-paginacao class="d-flex justify-content-center" id="controle" ref="componente"
                            url="{{route('g.relatorios.efetivo.atualizar')}}"
                            :por-pagina="controle.dados.porPagina"
                            :dados="controle.dados"
                            v-on:carregou="carregou" v-on:carregando="carregando"></controle-paginacao>
    </div>
@stop
@push('js')
    <script src="{{mix('js/g/relatorios/efetivo/app.js')}}"></script>
@endpush

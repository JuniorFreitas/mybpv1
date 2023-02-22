@extends('layouts.sistema')
@section('title', 'Centros de Custo')
@section('content_header')
    <h4 class="text-default">Centros de Custo</h4>
    <hr class="bg-default" style="margin-top: -5px;">
@stop
@section('content')
    <fieldset>
        <legend class="text-uppercase">Filtro</legend>
        <form class="row" @submit.prevent="$refs.componente.buscar()">
            <div class="col-12 col-sm-4 col-md-3 col-lg-2">
                <div class="form-group">
                    <label>Centros de Custo</label>
                    <select v-model="controle.dados.campoCentrosDeCusto" :disabled="controle.carregando"
                            @change="atualizar()" class="form-control form-control-sm">
                        <option value="">Todas os centros de custo</option>
                        <option v-for="item in centros_de_custo" :value="item.id" :key="item.id"
                                v-text="item.label"></option>
                    </select>
                </div>
            </div>
        </form>

        <div class="col-12">
            <div class="row">
                <button type="button" class="btn btn-sm btn-success mr-1 mb-2" :disabled="controle.carregando"
                        @click="atualizar"><i
                        :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                    Atualizar
                </button>
                <button type="button" class="btn btn-sm btn-primary mb-2 mr-1"
                        @click.prevent="exportaPdf()"
                        :disabled="controle.carregando|| preloadExportacao || (!controle.carregando && lista.length===0) ">
                    <i class="fas fa-file-pdf"></i> EXPORTAR PDF
                </button>
                <button type="button" class="btn btn-sm btn-primary mb-2 mr-1"
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
        <div class="table-responsive" v-show="!controle.carregando && lista.length > 0" >
            <h3 class="text-center mt-3 mb-3">Listagem Sintética de Funcionários</h3>
            <div v-for="centro_de_custo in lista" class="mb-5">
                <h3>@{{ centro_de_custo.label }}</h3>
                <table class="tabela">
                    <thead>
                    <tr class="bg-default">
                        <th>Código</th>
                        <th>Nome</th>
                        <th>Cargo</th>
                        <th>Tipo Admissão</th>
                        <th>Data da Admissão</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-if="!centro_de_custo.admissao.length">
                        <td colspan="5">
                            <div class="alert alert-warning">
                                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
                            </div>
                        </td>
                    </tr>
                    <tr v-for="item in centro_de_custo.admissao" v-else>
                        <td>
                            @{{  item.feedback.curriculo.id }}
                        </td>
                        <td>
                            @{{  item.feedback.curriculo.nome }}
                        </td>
                        <td>
                            @{{item.feedback.vaga_aberta.vaga_selecionada.nome}} - @{{item.feedback.vaga_aberta.municipio.nome}} -
                            @{{item.feedback.vaga_aberta.municipio.uf}}
                        </td>
                        <td>
                            @{{item ? item.tipo_admissao : '' }}
                        </td>
                        <td>
                            @{{item ? item.data_admissao : '' }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="5" class="text-right">
                            <strong>Total de Funcionários: </strong> @{{ centro_de_custo.admissao.length }}
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div>
                <table class="tabela">
                    <tr>
                        <td class="text-center"><strong>TOTAL DE FUNCIONÁRIOS: @{{ total }}</strong></td>
                    </tr>
                </table>
            </div>
        </div>

        <controle-paginacao class="d-flex justify-content-center" id="controle" ref="componente"
                            url="{{route('g.relatorios.centrodecusto.atualizar')}}"
                            :por-pagina="controle.dados.porPagina"
                            :dados="controle.dados"
                            v-on:carregou="carregou" v-on:carregando="carregando"></controle-paginacao>
    </div>
@stop
@push('js')
    <script src="{{mix('js/g/relatorios/centrodecusto/app.js')}}"></script>
@endpush

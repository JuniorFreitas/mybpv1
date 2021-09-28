@extends('layouts.sistema')
@section('title', 'Controle de ponto: Folha de ponto')
@section('content_header', 'Controle de ponto: Folha de ponto')
@section('content')

    <!--Janela de detalhes-->
    <modal id="janelaFormDetalhes" titulo="Folha de ponto" :size="90" :fechar="!formPonto.preload">
        <template slot="conteudo">
            <p class="text-center">
                <preload v-if="formPonto.preload" label="Aguarde..."></preload>
            </p>
            <div v-if="!formPonto.preload && !formPonto.save">
                <div class="row">
                    <div class="col-6 col-sm-4">
                        <datepicker label="Intervalo" :range="true" v-model="formPonto.intervalo"
                                    :disabled="formPonto.preload" @onselect="buscarFrequencia"></datepicker>
                    </div>
                    <div class="col-6 col-sm-8 d-flex align-items-start align-self-end justify-content-end ">
                        <form :action="urlImprimir" method="post" target="_blank">
                            @csrf
                            <button type="submit" class="btn btn-primary mb-3"><i class="fas fa-print"></i> Imprimir
                            </button>
                            <input type="hidden" name="intervalo" v-model="formPonto.intervalo">
                        </form>

                    </div>
                </div>
                <preload v-if="this.formPonto.preloadFrequencia"></preload>
                <div class="col-12" v-else>
                    <h4>Frequência</h4>
                    <h5 class="text-center" v-if="formPonto.pontos.length === 0"> Sem registros encontrados de @{{
                        formPonto.intervalo }}</h5>
                    <div class="table-responsive">
                        <table class="table table-borderless table-hover table-sm" v-if="formPonto.pontos.length > 0">
                            <thead>
                            <tr>
                                <th scope="col">Data</th>
                                <th scope="col">Periodos trabalhados</th>
                                <th scope="col">Escala</th>
                                <th scope="col">Prevista</th>
                                <th scope="col">Normal</th>
                                <th scope="col">Noturna</th>
                                <th scope="col">Extra</th>
                                <th scope="col">Negativa</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="ponto in formPonto.pontos">
                            <td>
                                @{{ ponto.dia }} <small>@{{ ponto.diaSem }}</small>
                            </td>

                            <td>
                                <i class="fas fa-circle text-success ml-2" v-if="ponto.verificado"></i>
                                <i class="fas fa-circle text-warning ml-2" v-if="!ponto.verificado"></i>

                                <span v-for="(periodo,index) in ponto.periodos" v-if="ponto.ocorrencia.trabalhado">
                                    <span v-show="index > 0">|</span>
                                    @{{ periodo.horaEntrada }}<span
                                        v-if="periodo.horaSaida">-@{{ periodo.horaSaida }}</span>
                                    <span v-if="!periodo.horaSaida">-<span
                                            class="badge badge-warning">trabalhando</span></span>
                                </span>

                                <span v-if="!ponto.ocorrencia.trabalhado">@{{ ponto.ocorrencia.descricao }}</span>
                            </td>
                            <td>
                                @{{ ponto.jornada.escala.descricao }}
                            </td>
                            <td>
                                <span v-if="ponto.jornada.ocorrencia.trabalhado && ponto.ocorrencia.conta_horas">
                                    @{{ ponto.horasNormalOriginalFormat }}
                                </span>
                                <span v-else> -- </span>

                            </td>
                            <td>
                                <span
                                    v-if="ponto.jornada.ocorrencia.trabalhado && ponto.ocorrencia.conta_horas && ponto.periodos_em_aberto.length ===0">
                                    @{{ ponto.horasNormalFormat }}
                                </span>
                                <span v-else> -- </span>

                            </td>
                            <td>
                                <span
                                    v-if="ponto.jornada.ocorrencia.trabalhado && ponto.ocorrencia.conta_horas && ponto.periodos_em_aberto.length ===0">
                                    <span class="text-success" v-if="ponto.horasNoturna>0">@{{ ponto.horasNoturnaFormat }}</span>
                                    <span v-else>00h:00m</span>
                                </span>
                                <span v-else> -- </span>
                            </td>
                            <td>
                                <span
                                    v-if="ponto.jornada.ocorrencia.trabalhado && ponto.ocorrencia.conta_horas && ponto.periodos_em_aberto.length ===0">
                                    <span class="text-success"
                                          v-if="ponto.horasExtra>0">@{{ ponto.horasExtraFormat }}</span>
                                    <span v-else>00h:00m</span>
                                </span>
                                <span v-else> -- </span>

                            </td>
                            <td>
                                <span
                                    v-if="ponto.jornada.ocorrencia.trabalhado && ponto.ocorrencia.conta_horas && ponto.periodos_em_aberto.length ===0">
                                    <span class="text-danger"
                                          v-if="ponto.horasExtra<0">@{{ ponto.horasExtraFormat }}</span>
                                    <span v-else>00h:00m</span>
                                </span>
                                <span v-else> -- </span>
                            </td>
                        </tr>

                        </tbody>
                        </table>
                    </div>

                    <div class="row">
                        <div class="col-md-2 col-12">
                            <fieldset>
                                <legend>Faltas</legend>
                                <h4 class="text-center text-primary">
                                    @{{ quantidadeFaltas }}
                                </h4>
                            </fieldset>
                        </div>
                        <div class="col-md-2 col-12">
                            <fieldset>
                                <legend>Horas normais</legend>
                                <h4 class="text-center text-primary">
                                    @{{ formataHoras(totalHorasNormais) }}
                                </h4>
                            </fieldset>
                        </div>
                        <div class="col-md-2 col-12">
                            <fieldset>
                                <legend>Horas noturnas</legend>
                                <h4 class="text-center text-primary">
                                    @{{ formataHoras(totalHorasNoturnas) }}
                                </h4>
                            </fieldset>
                        </div>
                        <div class="col-md-2 col-12">
                            <fieldset>
                                <legend>Horas extra</legend>
                                <h4 class="text-center text-primary">
                                    @{{ formataHoras(totalHorasExtra) }}
                                </h4>
                            </fieldset>
                        </div>
                        <div class="col-md-2 col-12">
                            <fieldset>
                                <legend>Horas negativas</legend>
                                <h4 class="text-center text-primary">
                                    @{{ formataHoras(totalHorasNegativas) }}
                                </h4>
                            </fieldset>
                        </div>
                        <div class="col-md-2 col-12">
                            <fieldset>
                                <legend>Saldo</legend>
                                <h4 class="text-center"
                                    :class="{'text-primary':saldoHoras>0,'text-danger':saldoHoras<0}">
                                    <span v-show="saldoHoras<0">-</span>@{{ formataHoras(saldoHoras) }}

                                </h4>
                            </fieldset>
                        </div>
                    </div>

                </div>

            </div>

        </template>
        <template slot="rodape">
        </template>
    </modal>


    <div class="row">

        <div class="col-12">
            <div class="row">
                <div class="col-12 mt-3">
                    <form @submit.prevent="atualizar">
                        <div class="form-row">

                            <div class="col">
                                <div class="input-group">
                                    <span class="input-group-prepend">
                                        <i class="input-group-text" id="basic-addon1"><i class="fa fa-search"></i></i>
                                    </span>
                                    <input type="text" placeholder="Nome do colaborador" v-model="formBusca.funcionarioNome" autocomplete="off" class="form-control">
                                </div>
                            </div>
                            <div class="col">
                                <button type="button" class="btn btn-primary" :disabled="formBusca.preload" @click="atualizar">Buscar</button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
            <div class="row">

                <div class="col-12 mt-3">
                    <p class="text-center" v-if="formBusca.preload">
                        <preload />
                    </p>
                    <h4 class="text-center" v-if="lista.length === 0 && !formBusca.preload">Nenhum registro encontrado</h4>

                    <table class="tabela" v-if="lista.length > 0 && !formBusca.preload">
                        <thead class="bg-default">
                        <tr>
                            <th>Funcionário</th>
                            <th>Escala</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="funcionario in lista">
                            <td>
                                <div class="row">
                                    <div class="media ml-3">
                                        <div class="avatar-md mr-3">
                                            <span class="avatar-title rounded-circle bg-soft-primary text-primary">
                                                @{{ funcionario.nome | formataNome }}
                                            </span>
                                        </div>
                                        <div class="media-body">
                                            <span class="mt-0 mb-1">@{{ funcionario.nome }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @{{ funcionario.escalas_funcionario[0].descricao }}
                            </td>
                            <td>
                                <button type="button" class="btn btn-secondary btn-sm" data-toggle="modal" data-target="#janelaFormDetalhes" @click="verDetalhes(funcionario.id)"> <i class="fas fa-clipboard-list"></i> Detalhes</button>
                            </td>
                        </tr>
                    </table>
                    <controle-paginacao class="d-flex justify-content-center" ref="paginacao"
                                        url="{{route('g.controle-ponto.folha-ponto.atualizarLista')}}"
                                        :por-pagina="50"
                                        :dados="formBusca"
                                        v-on:carregou="carregou" v-on:carregando="carregando"></controle-paginacao>
                </div>
            </div>

        </div>

    </div>
@stop
@push('js')
    <script src="{{mix('js/g/controle-ponto/folha-ponto/app.js')}}"></script>
@endpush

@push('css')

@endpush

@extends('layouts.sistema')
@section('title', 'Controle de ponto: Folha de ponto')
@section('content_header', 'Controle de ponto: Folha de ponto')
@section('content')
    <modal id="janelaDetalhesFoto" titulo="Detalhes do ponto" modal-pai="janelaFormDetalhes" size="g">
        <template #conteudo>
            <p class="text-center">
                <preload v-if="preloadDetalheFoto" label="Aguarde..."></preload>
            </p>
            <div v-if="!preloadDetalheFoto && dadosDetalheFoto">
                <table class="tabela">
                    <tbody>
                    <tr>
                        <td>
                            Data/hora @{{ dadosDetalheFotoTipo }}
                        </td>
                        <td>
                            <div v-if="dadosDetalheFotoTipo === 'Entrada'">
                                @{{ dadosDetalheFoto.entrada }}
                            </div>
                            <div v-else>
                                @{{ dadosDetalheFoto.saida }}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Foto da @{{ dadosDetalheFotoTipo }}
                        </td>
                        <td>
                            <div v-if="dadosDetalheFotoTipo === 'Entrada'">
                                <img :src="dadosDetalheFoto.foto_entrada.url" alt="" style="width: 300px; border-radius: 6%;object-fit: cover;">
                            </div>
                            <div v-else>
                                <img :src="dadosDetalheFoto.foto_saida.url" alt=""  style="width: 300px; border-radius: 6%;object-fit: cover;">
                            </div>
                            {{--                            <img :src="modelRegistro.foto_entrada.url" alt="" width="300">--}}
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

        </template>
        <template #rodape></template>
    </modal>

    <!--Janela de detalhes-->
    <modal id="janelaFormDetalhes" titulo="Folha de ponto" :size="95" :fechar="!formPonto.preload">
        <template #conteudo>
            <div v-if="!formPonto.preload && !formPonto.save">
                <div class="row">
                    <div class="col-12" style="margin-top: -20px">
                        <fieldset>
                            <legend class="text-uppercase">Dados Pessoais</legend>
                            <div class="row">
                                <div class="col-12">
                                    <p>
                                        Nome: <strong>@{{ formPonto.nome }}</strong> <br>
                                        Matrícula: <strong>@{{ formPonto.matricula }}</strong> |
                                        Data admissão: <strong>@{{ formPonto.data_admissao }}</strong> <br>
                                        Cargo: <strong>@{{ formPonto.cargo }}</strong> <br>
                                        Centro de custo: <strong>@{{ formPonto.centro_custo }}</strong> | Área: <strong>@{{
                                            formPonto.area }}</strong>
                                    </p>
                                </div>
                            </div>
                        </fieldset>
                    </div>

                    <div class="col-12" style="margin-top: -20px">
                        <fieldset>
                            <legend>Filtro</legend>
                            <div class="row">
                                <div class="col-6 col-sm-4">
                                    <datepicker label="Intervalo" formsm :range="true" v-model="formPonto.intervalo"
                                                :disabled="formPonto.preload" @onselect="buscarFrequencia"></datepicker>
                                </div>
                                <div class="col-12">
                                    <form :action="urlImprimir" method="post" target="_blank">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-sm mb-3"><i
                                                class="fas fa-print"></i> Imprimir
                                        </button>
                                        <input type="hidden" name="intervalo" v-model="formPonto.intervalo">
                                    </form>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </div>
                <preload v-if="this.formPonto.preloadFrequencia"></preload>
                <div class="col-12" v-else>
                    <h4>Frequência</h4>
                    <h5 class="text-center" v-if="formPonto.pontos.length === 0"> Sem registros encontrados de
                        @{{ formPonto.intervalo }}
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm">
                            <thead>
                            <tr>
                                <th scope="col">Data</th>
                                <th scope="col">Escala</th>
                                <th scope="col">Periodos trabalhados</th>
                                <th scope="col">Prevista</th>
                                <th scope="col">Normal</th>
                                <th scope="col">Noturna</th>
                                <th scope="col">Extra</th>
                                <th scope="col">Negativa</th>
                            </tr>
                            </thead>
                            <tbody>

                            <tr v-for="calend in calendario">
                                <td>
                                    @{{ calend.dia }}<br><small>@{{ calend.diaSem }}</small>
                                    <small class="text-danger" v-if="calend.feriado">
                                        (@{{ calend.feriado.descricao}})
                                    </small>
                                </td>

                                <td>
                                    <div v-if="calend.ponto">
                                        @{{ calend.ponto.jornada.escala.descricao }}
                                    </div>
                                    <div v-else>
                                        --
                                    </div>
                                </td>

                                <td>
                                    <div v-if="calend.ponto">
                                        <i class="fas fa-circle text-success ml-2" v-if="calend.ponto.verificado"></i>
                                        <i class="fas fa-circle text-warning ml-2" v-if="!calend.ponto.verificado"></i>

                                        <span v-for="(periodo,index) in calend.ponto.periodos"
                                              v-if="calend.ponto.ocorrencia.trabalhado">
                                    <span v-show="index > 0">|</span>
                                    <a href="javascript://" data-toggle="modal"
                                       data-target="#janelaDetalhesFoto" v-tippy content="Detalhes"
                                       @click="mostraDetalheFoto(periodo,'Entrada')">
                                        @{{ periodo.horaEntrada }}
                                    </a>
                                            <span
                                                v-if="periodo.horaSaida">-
                                                <a href="javascript://" data-toggle="modal"
                                                   data-target="#janelaDetalhesFoto" v-tippy content="Detalhes"
                                                   @click="mostraDetalheFoto(periodo,'Saida')">
                                                    @{{ periodo.horaSaida }}
                                                </a>
                                            </span>
                                    <span v-if="!periodo.horaSaida">-<span
                                            class="badge badge-warning">trabalhando</span></span>
                                        </span>
                                    </div>
                                    <div v-else>
                                        --
                                    </div>
                                </td>

                                <td>
                                    <div v-if="calend.ponto">
                                        <span
                                            v-if="calend.ponto.jornada.ocorrencia.trabalhado && calend.ponto.ocorrencia.conta_horas">
                                            @{{ calend.ponto.horasNormalOriginalFormat }}
                                        </span>
                                        <span v-else> -- </span>
                                    </div>
                                    <div v-else>
                                        --
                                    </div>
                                </td>

                                <td>
                                    <div v-if="calend.ponto">
                                        <span
                                            v-if="calend.ponto.jornada.ocorrencia.trabalhado && calend.ponto.ocorrencia.conta_horas && calend.ponto.periodos_em_aberto.length ===0">
                                            @{{ calend.ponto.horasNormalFormat }}
                                        </span>
                                        <span v-else> -- </span>
                                    </div>
                                    <div v-else>
                                        --
                                    </div>
                                </td>
                                <td>
                                    <div v-if="calend.ponto">
                                    <span
                                        v-if="calend.ponto.jornada.ocorrencia.trabalhado && calend.ponto.ocorrencia.conta_horas && calend.ponto.periodos_em_aberto.length ===0">
                                            <span class="text-success" v-if="calend.ponto.horasNoturna>0">@{{ calend.ponto.horasNoturnaFormat }}</span>
                                            <span v-else>00h:00m</span>
                                        </span>
                                        <span v-else> -- </span>
                                    </div>
                                    <div v-else>
                                        --
                                    </div>
                                </td>
                                <td>
                                    <div v-if="calend.ponto">
                                    <span
                                        v-if="calend.ponto.jornada.ocorrencia.trabalhado && calend.ponto.ocorrencia.conta_horas && calend.ponto.periodos_em_aberto.length ===0">
                                        <span class="text-success"
                                              v-if="calend.ponto.horasExtra>0">@{{ calend.ponto.horasExtraFormat }}</span>
                                                <span v-else>00h:00m</span>
                                        </span>
                                        <span v-else> -- </span>
                                    </div>
                                    <div v-else>
                                        --
                                    </div>
                                </td>
                                <td>
                                    <div v-if="calend.ponto">
                                        <span
                                            v-if="calend.ponto.jornada.ocorrencia.trabalhado && calend.ponto.ocorrencia.conta_horas && calend.ponto.periodos_em_aberto.length ===0">
                                            <span class="text-danger"
                                                  v-if="calend.ponto.horasExtra<0">@{{ calend.ponto.horasExtraFormat }}</span>
                                            <span v-else>00h:00m</span>
                                        </span>
                                        <span v-else> -- </span>
                                    </div>
                                    <div v-else>
                                        --
                                    </div>
                                </td>

                            </tr>

                            <template v-if="false">
                                <tr v-for="ponto in formPonto.pontos">
                                <td>
                                    @{{ ponto.dia }} <small>@{{ ponto.diaSem }}</small>
                                </td>

                                <td>
                                    <i class="fas fa-circle text-success ml-2" v-if="ponto.verificado"></i>
                                    <i class="fas fa-circle text-warning ml-2" v-if="!ponto.verificado"></i>

                                    <template v-if="ponto.ocorrencia.trabalhado">
                                        <span v-for="(periodo,index) in ponto.periodos">
                                            <span v-show="index > 0">|</span>
                                            @{{ periodo.horaEntrada }}<span v-if="periodo.horaSaida">-@{{ periodo.horaSaida }}</span>
                                            <span v-if="!periodo.horaSaida">-<span class="badge badge-warning">trabalhando</span></span>
                                        </span>
                                    </template>

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
                            </template>

                            </tbody>
                        </table>
                    </div>

                    <div class="row">
                        <div class="col-md-4 col-12">
                            <fieldset>
                                <legend>Faltas</legend>
                                <h4 class="text-center text-primary">
                                    @{{ quantidadeFaltas }}
                                </h4>
                            </fieldset>
                        </div>
                        <div class="col-md-4 col-12">
                            <fieldset>
                                <legend>Horas normais</legend>
                                <h4 class="text-center text-primary">
                                    @{{ formataHoras(totalHorasNormais) }}
                                </h4>
                            </fieldset>
                        </div>
                        <div class="col-md-4 col-12">
                            <fieldset>
                                <legend>Horas noturnas</legend>
                                <h4 class="text-center text-primary">
                                    @{{ formataHoras(totalHorasNoturnas) }}
                                </h4>
                            </fieldset>
                        </div>
                        <div class="col-md-4 col-12">
                            <fieldset>
                                <legend>Horas extra</legend>
                                <h4 class="text-center text-primary">
                                    @{{ formataHoras(totalHorasExtra) }}
                                </h4>
                            </fieldset>
                        </div>
                        <div class="col-md-4 col-12">
                            <fieldset>
                                <legend>Horas negativas</legend>
                                <h4 class="text-center text-primary">
                                    @{{ formataHoras(totalHorasNegativas) }}
                                </h4>
                            </fieldset>
                        </div>
                        <div class="col-md-4 col-12">
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
        <template #rodape>
        </template>
    </modal>

    <div class="row">

        <div class="col-12">
            <div class="row" v-if="controle_ponto_adm">
                <div class="col-12 mt-3">
                    <form @submit.prevent="atualizar">
                        <div class="row">
                            <div class="col-12 col-sm-6 col-md-5 col-lg-4">
                                <label for="">Buscar</label>
                                <input type="text" placeholder="Nome do colaborador" v-model="formBusca.funcionarioNome"
                                       autocomplete="off" class="form-control form-control-sm">
                            </div>

                            <div class="col-12 col-sm-6 col-md-5 col-lg-4">
                                <div class="form-group">
                                    <label for="">Por Escala</label>
                                    <select class="form-control form-control-sm" v-model="formBusca.escala_id"
                                            @change="atualizar">
                                        <option value="">Selecione...</option>
                                        <option v-for="escala in todas_escalas" :value="escala.id"
                                                v-text="escala.descricao"></option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-12 col-sm-4 col-md-3 col-lg-2">
                                <div class="form-group">
                                    <label for="">Status</label>
                                    <select class="form-control form-control-sm" v-model="formBusca.status"
                                            @change="atualizar">
                                        <option value="admitidos">Admitidos</option>
                                        <option value="demitidos">Demitidos</option>
                                    </select>
                                </div>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col">
                                <button type="button" class="btn btn-primary btn-sm" :disabled="formBusca.preload"
                                        @click="atualizar">Buscar
                                </button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>

            <div class="row">

                <div class="col-12 mt-3">
                    <p class="text-center" v-if="formBusca.preload">
                        <preload/>
                    </p>
                    <h4 class="text-center" v-if="lista.length === 0 && !formBusca.preload">Nenhum registro
                        encontrado</h4>

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
                                    <div class="media ml-3" style="display: flex; align-items: center;">
                                        <div class="avatar-md mr-3">
                                            <span class="avatar-title rounded-circle bg-soft-primary text-primary">
                                                @{{ formatNome(funcionario.nome) }}
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
                                <button type="button" class="btn btn-secondary btn-sm" data-toggle="modal"
                                        data-target="#janelaFormDetalhes" @click="verDetalhes(funcionario.id)"><i
                                        class="fas fa-clipboard-list"></i> Detalhes
                                </button>
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

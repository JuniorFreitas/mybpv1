@extends('layouts.sistema')
@section('title', 'Controle de ponto: Ponto eletrônico')
@section('content_header', 'Controle de ponto:  ponto eletrônico')
@section('content')

    <!--Janela de detalhes-->
    <modal id="janelaFormDetalhes" titulo="Detalhes" size="g" :fechar="!formRegistro.preload">
        <template slot="conteudo">
            <p class="text-center">
                <preload v-if="formRegistro.preload" label="Aguarde..."></preload>
            </p>
            <div v-if="!formRegistro.preload && modelRegistro!=null">
                <table class="tabela">
                    <tbody>
                    <tr>
                        <td>
                            Data/hora Entrada
                        </td>
                        <td>
                            @{{ modelRegistro.entrada }}
                        </td>
                    </tr>
                    <tr v-if="modelRegistro.foto_entrada">
                        <td>
                            Foto da entrada
                        </td>
                        <td>
                            <img :src="modelRegistro.foto_entrada.url" alt="" width="300">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Data/hora Saída
                        </td>
                        <td>
                            <span v-if="modelRegistro.saida">@{{ modelRegistro.saida }}</span>
                            <span v-else><h5><span class="badge badge-warning">Trabalhando</span></h5></span>
                        </td>
                    </tr>
                    <tr v-if="modelRegistro.foto_saida">
                        <td>
                            Foto da saída
                        </td>
                        <td>
                            <img :src="modelRegistro.foto_saida.url" alt="" width="300">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Justificativa
                        </td>
                        <td>
                            @{{ modelRegistro.justificativa }}
                        </td>
                    </tr>

                    </tbody>
                </table>
            </div>

        </template>
        <template slot="rodape"></template>
    </modal>
    <!--Janela de registro-->
    <modal id="janelaFormPonto" :titulo="formPonto.titulo" :fechar="!formPonto.preload" size="g" @fechou="stopGPS">
        <template slot="conteudo">
            <h4 class="text-success text-center" v-if="!formPonto.preload && formPonto.save">
                <i class="fas fa-check fa-2x"></i>Ponto registrado
            </h4>
            <p class="text-center">
                <preload v-if="formPonto.preload" label="Aguarde..."></preload>
                <preload v-if="preloadGoogleMaps" label="Aguarde módulo Google Maps"></preload>
            </p>
            <div v-show="!formPonto.preload && !formPonto.save">
                <div class="googleMaps" id="mapaPrimetro" style="float:left;width:100%;height: 400px"
                     v-show="formPonto.telaGPS"></div>
                <div v-show="formPonto.telaCamera">
                    <div class="row">
                        <div class="col"></div>
                        <div class="col">
                            <h4 class="text-center">Registrar foto</h4>
                            <div class="text-center" ref="camera" id="camera"></div>
                        </div>
                        <div class="col"></div>
                    </div>
                    <div class="col-12 col-md-4 offset-md-4">

                    </div>

                </div>
            </div>

        </template>
        <template slot="rodape">
            <button :disabled="!formPonto.dentro" v-if="!formPonto.preload && !formPonto.save && formPonto.telaGPS"
                    class="btn btn-sm btn-success" type="button" @click="continuar">
                Continuar <i class="fas fa-arrow-right"></i>
            </button>
            <button v-if="!formPonto.preload && !formPonto.save && formPonto.telaCamera" class="btn btn-sm btn-success"
                    type="button" @click="registrarPonto">
                Registrar
            </button>
        </template>
    </modal>


    <div class="row">

        <div class="col-12">
            <ul class="nav nav-pills nav-fill mt-5">
                <li class="nav-item">
                    <a class="nav-link active" id="home-tab" data-toggle="tab" href="#marcao_dia" role="tab"
                       aria-controls="home" aria-selected="true">Marcações do dia</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="profile-tab" data-toggle="tab" href="#historico" role="tab"
                       aria-controls="profile" aria-selected="false">Histórico</a>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane fade show active p-2 " id="marcao_dia" role="tabpanel"
                     aria-labelledby="marcao_dia-tab">
                    <div class="row">
                        <div class="col-12" v-if="preload">
                            <preload></preload>
                        </div>
                        <div class="col-12" v-else>

                            <div class="card">
                                <div class="card-header bg-white">
                                    <h5 class="text-center">@{{ hoje }} </h5>
                                </div>
                                <ul class="list-group list-group-flush">
                                    <template v-if="registros.length > 0" v-for="reg in registros">
                                        <template v-for="per in reg.periodos">
                                            <li class="list-group-item text-center" v-if="per.entrada">ENTRADA :
                                                <strong>@{{ per.horaEntrada }}</strong></li>
                                            <li class="list-group-item text-center" v-if="per.saida!=null">SAÍDA :
                                                <strong>@{{ per.horaSaida }}</strong></li>
                                        </template>
                                    </template>
                                    <li v-if="registros.length === 0" class="list-group-item text-center">
                                        <span class="text-center text-primary">Nenhum registro realizado hoje</span>
                                    </li>
                                </ul>
                            </div>

                            <button :disabled="preloadGoogleMaps" type="button" class="btn btn-warning btn-block"
                                    data-toggle="modal" data-target="#janelaFormPonto" @click="formNovoRegistro"><i
                                    class="fas fa-user-clock"></i> Registrar ponto
                            </button>

                        </div>
                    </div>


                </div>
                <div class="tab-pane fade p-2" id="historico" role="tabpanel" aria-labelledby="historico-tab">

                    <div class="row mt-3">
                        <div class="col-12 col-md-4 offset-md-4">
                            <div class="form-group">
                                <h5 class="text-center">Registros no dia</h5>
                                <datepicker v-model="formHistorico.data" :disabled="formHistorico.preload"
                                            @onselect="atualizarHistorico"></datepicker>
                            </div>
                        </div>
                        <div class="col-12">
                            <p class="text-center">
                                <preload v-if="formHistorico.preload" label="Aguarde..."></preload>
                            </p>
                            <h4 class="text-center" v-if="historico.length === 0 && !formHistorico.preload">Nenhum
                                registro encontrado</h4>

                            <template v-if="historico.length > 0 && !formHistorico.preload" v-for="reg in historico">
                                <table class="tabela">
                                    <thead class="bg-default">
                                    <tr>
                                        <th>Entrada</th>
                                        <th>Saída</th>
                                        <th>Duração</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="per in reg.periodos">
                                        <td class="text-center">
                                            @{{ per.entrada }}
                                        </td>
                                        <td class="text-center">
                                            <span v-if="per.saida">@{{ per.saida }}</span>
                                            <span v-else><h5><span
                                                        class="badge badge-warning">Trabalhando</span></h5></span>
                                        </td>
                                        <td class="text-center">
                                            <span v-if="per.saida">@{{ per.horasTrabalhadasFormat }}</span>
                                            <span v-else> -- </span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-info" data-toggle="modal"
                                                    data-target="#janelaFormDetalhes"
                                                    @click="verDetalhes(reg.id,per.id)"><i
                                                    class="fas fa-info-circle"></i> Detalhes
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"></td>
                                        <td>Duração prevista:</td>
                                        <td>@{{ duracaoDiaHistorico.format('HH[h]:mm') }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"></td>
                                        <td>Trabalhado:</td>
                                        <td>@{{ tempoTrabalhadoHistorico.format('HH[h]:mm') }}</td>
                                    </tr>

                                    </tbody>
                                </table>
                            </template>

                        </div>


                    </div>

                </div>
            </div>

        </div>

    </div>
@stop
@push('js')
    <script src="{{mix('js/g/controle-ponto/ponto-eletronico/app.js')}}"></script>
    <script src="{{mix('js/g/controle-ponto/ponto-eletronico/webcam.min.js')}}"></script>
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

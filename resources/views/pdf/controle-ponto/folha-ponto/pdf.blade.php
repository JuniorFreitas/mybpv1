@extends('layouts.pdf')
@section('title','Folha de ponto')
@section('empresa')
    @include('layouts.cabecalioEmpresa')
@endsection
@push('style')
    <style type="text/css">
        .textoVermelho {
            color: #ff0000;
        }

        .textoVerde {
            color: #02660c;
        }
    </style>

@endpush
@section('conteudo')
    <h5 class="text-center" style="text-transform: uppercase; text-decoration: underline">ESPELHO DE PONTO
        - {{$intervaloText}}</h5>
    <table class="dados" width="100%">
        <thead>
        <tr>
            <th>
                Nome: <br><span style="font-weight: normal; line-height: 20px">{{ $dados->nome }}</span>
            </th>
            <th>CPF: <span style="font-weight: normal; line-height: 20px">{{ $dados->cpf }}</span></th>
            <th>Escala de trabalho atual {{ $escala->descricao }}</th>
        </tr>
        </thead>

    </table>
    @if(count($lista)==0)
        <h5 style="margin-top: 10px; margin-bottom: 5px; text-decoration: underline">SEM DADOS PARA EXIBIR</h5>
    @else
        <table class="dados" width="100%">
            <thead>
            <tr>
                <th>Data</th>
                <th>Sem</th>
                <th>Periodos trabalhados</th>
                <!--                    <th>Escala</th>-->
                <th>Prevista</th>
                <th>Normal</th>
                <th>Noturna</th>
                <th>Extra</th>
                <th>Negativa</th>
            </tr>
            </thead>
            <tbody>
            @foreach($lista as $calendar)
                <tr>
                    <td>{{ substr($calendar['dia'],0,5) }}</td>
                    <td>{{$calendar['diaSem'] }}</td>
                    <td>
                        @if($calendar['ponto'] && !$calendar['ponto']->verificado)
                            *
                        @else
                            @if($calendar['ponto'])
                               {{dd($calendar['ponto'])}}
                                @foreach($calendar['ponto']->periodos as $index =>$periodo)
                                    @if($calendar['ponto']->ocorrencia->trabalhado)
                                        @if($index > 0)
                                            |
                                        @endif {{ $periodo->horaEntrada }}
                                        @if($periodo->horaSaida)
                                            -{{ $periodo->horaSaida }}
                                        @endif
                                        @if(!$periodo->horaSaida)
                                            - trabalhando
                                        @endif
                                    @else
                                        @if(!$calendar['ponto']->ocorrencia->trabalhado)
                                            {{ $calendar['ponto']->ocorrencia->descricao }}
                                        @endif
                                    @endif
                                @endforeach
                            @endif
                        @endif
                    </td>
                    <!--                <td>
                    @if($calendar['ponto'])
                        {{ $calendar['ponto']->jornada->escala->descricao }}
                    @endif
                    </td>-->
                    <td>

                        @if($calendar['ponto'] && $calendar['ponto']->jornada->ocorrencia->trabalhado && $calendar['ponto']->ocorrencia->conta_horas)
                            {{ $calendar['ponto']->horasNormalOriginalFormat }}
                        @else
                            --
                        @endif
                    </td>
                    <td>
                        @if($calendar['ponto'] && $calendar['ponto']->jornada->ocorrencia->trabalhado && $calendar['ponto']->ocorrencia->conta_horas && $calendar['ponto']->PeriodosEmAberto()->count() ===0)
                            {{ $calendar['ponto']->horasNormalFormat }}
                        @else
                            --
                        @endif
                    </td>
                    <td>
                        @if($calendar['ponto'] && $calendar['ponto']->jornada->ocorrencia->trabalhado && $calendar['ponto']->ocorrencia->conta_horas && $calendar['ponto']->PeriodosEmAberto()->count() ===0)
                            @if($calendar['ponto']->horasNoturna>0)
                                {{ $calendar['ponto']->horasNoturnaFormat }}
                            @else
                                00h:00m
                            @endif
                        @else
                            --
                        @endif
                    </td>
                    <td>
                        @if($calendar['ponto'] && $calendar['ponto']->jornada->ocorrencia->trabalhado && $calendar['ponto']->ocorrencia->conta_horas && $calendar['ponto']->PeriodosEmAberto()->count() ===0)
                            @if($calendar['ponto']->horasExtra>0)
                                <span
                                    class="textoVerde">{{ $calendar['ponto']->horasExtraFormat }} </span>
                            @else
                                00h:00m
                            @endif
                        @else
                            --
                        @endif
                    </td>
                    <td>
                        @if($calendar['ponto'] && $calendar['ponto']->jornada->ocorrencia->trabalhado && $calendar['ponto']->ocorrencia->conta_horas && $calendar['ponto']->PeriodosEmAberto()->count() ===0)
                            @if($calendar['ponto']->horasExtra<0)
                                <span
                                    class="textoVermelho">{{ $calendar['ponto']->horasExtraFormat }} </span>
                            @else
                                00h:00m
                            @endif
                        @else
                            --
                        @endif
                    </td>


                </tr>
            @endforeach
            </tbody>
        </table>
        <table class="dados" width="100%">
            <thead>
            <tr>
                <th>Horas normais</th>
                <th>Horas noturnas</th>
                <th>Horas extras</th>
                <th>Horas negativas</th>
                <th>Saldo</th>
            </tr>
            </thead>

            <tr>
                <td align="center">
                    {{$totalHorasNormais}}
                </td>
                <td align="center">
                    {{$totalHorasNoturnas}}
                </td>
                <td align="center">
                    {{$totalHorasExtra}}
                </td>
                <td align="center">
                    {{$totalHorasNegativas}}
                </td>
                <td align="center">
                    @if($saldoValor < 0)
                        <span class="textoVermelho">-{{$saldoDeHoras}}</span>
                    @else
                        <span
                            class="textoVerde"> + {{$saldoDeHoras}}</span>
                    @endif
                </td>
            </tr>
        </table>

        <br>
        <br>
        <p>
            * Não verificado
        </p>
    @endif






    @include('layouts.rodapePdf')
@endsection

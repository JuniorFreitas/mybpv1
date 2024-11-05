@extends('layouts.pdf')
@section('title','Folha de ponto')
@section('empresa')
    @include('layouts.cabecalioEmpresa')
@endsection
@push('style')
    <style type="text/css">
        @page :first {
            margin: -10px !important;
            padding: 0px !important;
        }

        @page {
            margin: -10px !important;
            padding: 0px !important;
        }

        .textoVermelho {
            color: #ff0000;
        }

        .textoVerde {
            color: #02660c;
        }

        .textoLaranja {
            color: #af6700;
        }

        table.dados2, table.dados2 th, table.dados2 td {
            border: 0.1px solid black;
            border-collapse: collapse;
            font-size: 8.5pt;
            padding: 3px;
        }

        table.dados3 {
            width: 100%;
            border: 1px solid black;
            border-collapse: collapse;
            font-size: 7.7pt;
            padding: 3px;
        }

        table.dados3 th, table.dados3 td {
            border-collapse: collapse;
            font-size: 7.7pt;
            padding: 3px;
        }

        .dados2, .dados3 {
            width: 100%;
        }

        table.dados4, table.dados4 th, table.dados4 td {
            border: 1px solid black;
            border-collapse: collapse;
            font-size: 7.7pt;
            padding: 4px;
        }

    </style>

@endpush
@section('conteudo')
    <p class="text-center" style="text-transform: uppercase; text-decoration: underline; margin-top: -20px">ESPELHO DE
        PONTO
        - {{$intervaloText}}</p>
    <table class="dados2" width="100%" style="margin-bottom: -6px">
        <thead>
        <tr style="text-transform: uppercase">
            <th>
                Nome: <span style="font-weight: normal; line-height: 20px">{{ $dados->nome }}</span>
            </th>
            <th>CPF: <span style="font-weight: normal; line-height: 20px">{{ $cpf }}</span></th>
            <th>Escala:
                {{$multi_escalas ? 'Multi Escalas' : $escala[0]->descricao }}
            </th>
        </tr>
        </thead>

    </table>
    @if(count($lista)==0)
        <h5 style="margin-top: 10px; margin-bottom: 5px; text-decoration: underline">SEM DADOS PARA EXIBIR</h5>
    @else
        <table class="dados2" width="100%">
            <thead>
            <tr>
                <th>Data</th>
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
            @foreach($calendario as $calend)
                <tr>
                    <td>
                        {{ substr($calend['dia'],0,5) }} -
                        <small>{{substr($calend['diaSem'],0,$calend['diaSem'] == 'Sábado' ? 4 : 3) }}</small>
                        @if($calend['feriado'])
                            <small class="textoVermelho">
                                ({{ $calend['feriado']->descricao}})
                            </small>
                        @endif
                    </td>
                    <td>
                        @if(isset($calend['ponto']) && $calend['ponto']->verificado)
                            <span class="textoVerde">
                                    @foreach($calend['ponto']->periodos as $index => $periodo)
                                    @if($calend['ponto']->ocorrencia->trabalhado)
                                        @if($index > 0)
                                            |
                                        @endif
                                        {{ $periodo->horaEntrada }}
                                        @if($periodo->horaSaida)
                                            - {{ $periodo->horaSaida }}
                                        @endif
                                    @endif
                                @endforeach

                                @if(!$calend['ponto']->ocorrencia->trabalhado)
                                    {{ $calend['ponto']->ocorrencia->descricao }}
                                @endif
                                </span>
                        @elseif(isset($calend['ponto']))
                            <span class="textoLaranja">
                                @foreach($calend['ponto']->periodos as $index => $periodo)
                                    @if($calend['ponto']->ocorrencia->trabalhado)
                                        @if($index > 0)
                                            |
                                        @endif
                                        {{ $periodo->horaEntrada }}
                                        @if($periodo->horaSaida)
                                            - {{ $periodo->horaSaida }}
                                        @else
                                            - trabalhando
                                        @endif
                                    @endif
                                @endforeach

                                @if(!$calend['ponto']->ocorrencia->trabalhado)
                                    {{ $calend['ponto']->ocorrencia->descricao }}
                                @endif
                                </span>
                        @else
                            --
                        @endif

                    </td>
                    <td>
                        @if(isset($calend->ponto, $calend->ponto->jornada, $calend->ponto->jornada->ocorrencia, $calend->ponto->ocorrencia) &&
                            $calend->ponto->jornada->ocorrencia->trabalhado &&
                            $calend->ponto->ocorrencia->conta_horas)
                            {{ $calend->ponto->horasNormalOriginalFormat }}
                        @else
                            --
                        @endif
                    </td>
                    <td>
                        @if(isset($calend['ponto']) &&
                            isset($calend['ponto']->jornada) &&
                            isset($calend['ponto']->jornada->ocorrencia) &&
                            $calend['ponto']->jornada->ocorrencia->trabalhado &&
                            isset($calend['ponto']->ocorrencia) &&
                            $calend['ponto']->ocorrencia->conta_horas)
                            {{ $calend['ponto']->horasNormalFormat }}
                        @else
                            --
                        @endif
                    </td>
                    <td>
                        @if(isset($calend['ponto']) &&
                            isset($calend['ponto']->jornada) &&
                            isset($calend['ponto']->jornada->ocorrencia) &&
                            $calend['ponto']->jornada->ocorrencia->trabalhado &&
                            isset($calend['ponto']->ocorrencia) &&
                            $calend['ponto']->ocorrencia->conta_horas &&
                            method_exists($calend['ponto'], 'PeriodosEmAberto') &&
                            $calend['ponto']->PeriodosEmAberto()->count() === 0)
                            @if($calend['ponto']->horasNoturna > 0)
                                {{ $calend['ponto']->horasNoturnaFormat }}
                            @else
                                00h:00m
                            @endif
                        @else
                            --
                        @endif
                    </td>
                    <td>
                        @if(isset($calend['ponto']) &&
                            isset($calend['ponto']->jornada) &&
                            isset($calend['ponto']->jornada->ocorrencia) &&
                            $calend['ponto']->jornada->ocorrencia->trabalhado &&
                            isset($calend['ponto']->ocorrencia) &&
                            $calend['ponto']->ocorrencia->conta_horas &&
                            method_exists($calend['ponto'], 'PeriodosEmAberto') &&
                            $calend['ponto']->PeriodosEmAberto()->count() === 0)
                            @if($calend['ponto']->horasExtra > 0)
                                <span class="textoVerde">{{ $calend['ponto']->horasExtraFormat }}</span>
                            @else
                                00h:00m
                            @endif

                        @else
                            --
                        @endif
                    </td>
                    <td>
                        @if(isset($calend['ponto']) &&
                        isset($calend['ponto']->jornada) &&
                        isset($calend['ponto']->jornada->ocorrencia) &&
                        $calend['ponto']->jornada->ocorrencia->trabalhado &&
                        isset($calend['ponto']->ocorrencia) &&
                        $calend['ponto']->ocorrencia->conta_horas &&
                        method_exists($calend['ponto'], 'PeriodosEmAberto') &&
                        $calend['ponto']->PeriodosEmAberto()->count() === 0)
                            @if($calend['ponto']->horasExtra < 0)
                                <span class="textoVermelho">{{ $calend['ponto']->horasExtraFormat }}</span>
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
        <table class="dados2" width="100%" style="margin-top: -6px">
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

        {{--        <p>--}}
        {{--            * Não verificado--}}
        {{--        </p>--}}
        <p style="margin-top: 3px; margin-bottom: 3px">Observações:</p>
        <table class="dados4" style="width: 100%; margin-top: 4px ">
            <thead>
            <tr>
                <th style="height: 70px"></th>
            </tr>
            </thead>
        </table>

        <table class="dados3" style="margin-top: 40px; border: 0;">
            <thead>
            <tr>
                <th style="text-align: center !important; width:50%">
                    <hr style="width: 100%">
                    {{$razao_social}}
                </th>
                <th style="width:1%"></th>
                <th style="text-align: center; width:49%">
                    <hr style="width: 100%">
                    {{$dados->nome}}
                </th>
            </tr>
            </thead>
        </table>
    @endif
    @include('layouts.rodapePdf')
@endsection

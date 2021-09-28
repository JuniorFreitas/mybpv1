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
    <h5 class="text-center" style="text-transform: uppercase; text-decoration: underline">FICHA DE PONTO
        - {{$intervaloText}}</h5>
    <h5 style="margin-top: 5px; margin-bottom: 5px; text-decoration: underline">DADOS CADASTRAIS</h5>
    <h5>
        @if ($dados->tipo == \App\Models\Cliente::TIPO_PESSOA_JURIDICA)
            Razão Social: <span
                style="font-weight: normal; line-height: 20px">{{ $dados->razao_social }}</span> <br>
            Nome Fantasia: <span
                style="font-weight: normal; line-height: 20px">{{ $dados->razao_social }}</span> <br>
            CNPJ: <span style="font-weight: normal; line-height: 20px">{{ $dados->cnpj }}</span> <br>
        @else
            Nome: <span style="font-weight: normal; line-height: 20px">{{ $dados->nome }}</span> <br>
            CPF: <span style="font-weight: normal; line-height: 20px">{{ $dados->cpf }}</span> <br>
        @endif

    </h5>

    <h5 style="margin-top: 10px; margin-bottom: 5px; text-decoration: underline">FREQUÊNCIA</h5>
    <h5>
        Escala de trabalho atual: <span style="font-weight: normal; line-height: 20px">{{ $escala->descricao }}</span>
        <br>
    </h5>
    @if(count($lista)==0)
        <h5 style="margin-top: 10px; margin-bottom: 5px; text-decoration: underline">SEM DADOS PARA EXIBIR</h5>
    @else
        <table width="100%">
            <thead>
            <tr>
                <th scope="col">Data</th>
                <th scope="col">Sem</th>
                <th scope="col">Periodos trabalhados</th>
                <!--                    <th scope="col">Escala</th>-->
                <th scope="col">Prevista</th>
                <th scope="col">Normal</th>
                <th scope="col">Noturna</th>
                <th scope="col">Extra</th>
                <th scope="col">Negativa</th>
            </tr>
            </thead>
            <tbody>
            @foreach($lista as $ponto)
                <tr>
                    <td>{{ substr($ponto->dia,0,5) }}</td>
                    <td>{{$ponto->diaSem }}</td>
                    <td>
                        @if(!$ponto->verificado) * @endif
                        @foreach($ponto->periodos as $index =>$periodo)
                            @if($ponto->ocorrencia->trabalhado)
                                @if($index > 0)| @endif {{ $periodo->horaEntrada }}
                                @if($periodo->horaSaida)-{{ $periodo->horaSaida }}@endif
                                @if(!$periodo->horaSaida)- trabalhando @endif
                            @else
                                @if(!$ponto->ocorrencia->trabalhado) {{ $ponto->ocorrencia->descricao }}@endif
                            @endif
                        @endforeach
                    </td>
                <!--                <td>
                    {{ $ponto->jornada->escala->descricao }}
                    </td>-->
                    <td>
                        @if($ponto->jornada->ocorrencia->trabalhado && $ponto->ocorrencia->conta_horas)
                            {{ $ponto->horasNormalOriginalFormat }}
                        @else -- @endif
                    </td>
                    <td>
                        @if($ponto->jornada->ocorrencia->trabalhado && $ponto->ocorrencia->conta_horas && $ponto->PeriodosEmAberto()->count() ===0)
                            {{ $ponto->horasNormalFormat }}
                        @else -- @endif
                    </td>
                    <td>
                        @if($ponto->jornada->ocorrencia->trabalhado && $ponto->ocorrencia->conta_horas && $ponto->PeriodosEmAberto()->count() ===0)
                            @if($ponto->horasNoturna>0){{ $ponto->horasNoturnaFormat }}@else 00h:00m @endif
                        @else -- @endif
                    </td>
                    <td>
                        @if($ponto->jornada->ocorrencia->trabalhado && $ponto->ocorrencia->conta_horas && $ponto->PeriodosEmAberto()->count() ===0)
                            @if($ponto->horasExtra>0) <span
                                class="textoVerde">{{ $ponto->horasExtraFormat }} </span>@else 00h:00m @endif
                        @else -- @endif
                    </td>
                    <td>
                        @if($ponto->jornada->ocorrencia->trabalhado && $ponto->ocorrencia->conta_horas && $ponto->PeriodosEmAberto()->count() ===0)
                            @if($ponto->horasExtra<0) <span
                                class="textoVermelho">{{ $ponto->horasExtraFormat }} </span> @else 00h:00m @endif
                        @else -- @endif
                    </td>


                </tr>
            @endforeach
            </tbody>
        </table>
        <table width="100%">
            <thead>
            <tr>
                <th scope="col">Horas normais</th>
                <th scope="col">Horas noturnas</th>
                <th scope="col">Horas extras</th>
                <th scope="col">Horas negativas</th>
                <th scope="col">Saldo</th>
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
                    @if($saldoValor < 0)<span class="textoVermelho">-{{$saldoDeHoras}}</span> @else <span
                        class="textoVerde"> + {{$saldoDeHoras}}</span>@endif
                </td>
            </tr>
        </table>

        <br>
        <br>
        <p>
            * Não verificado
        </p>
    @endif







@endsection

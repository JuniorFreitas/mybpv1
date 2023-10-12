<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Ponto Manual</title>
    <style>
        table.dados, table.dados th, table.dados td {
            border: 1px solid black;
            border-collapse: collapse;
            font-size: 7.7pt;
            padding: 5px;
        }

        table.dados2 {
            width: 100%;
            border: 1px solid black;
            border-collapse: collapse;
            font-size: 7.7pt;
            padding: 3px;
        }

        table.dados2 th, table.dados2 td {
            border-collapse: collapse;
            font-size: 7.7pt;
            padding: 3px;
        }

        table.dados3, table.dados3 th, table.dados3 td {
            border: 1px solid black;
            border-collapse: collapse;
            font-size: 7.7pt;
            padding: 4px;
        }

        @page {
            size: A4;
            margin: 1mm 3mm 1mm 1mm;
        }

        body {
            height: 27cm;
            width: 21cm;
            margin-right: .4cm;
            font-family: 'Arial', sans-serif;
            font-size: 7.7pt;
        }

        .a4 {
            height: 27cm;
            width: 20cm;
            margin-top: 0px;
            margin-left: .5cm;
            margin-right: .5cm;
        }


        h4 {
            margin-top: 25px;
        }

        .dados {
            width: 100%;
        }

        .text-center {
            text-align: center;
        }

        .espaco {
            padding: 20px 20px;
        }

        .border-bottom {
            border-bottom: 1px solid #ccc;
        }

        .center {
            text-align: center;
        }

        .coluna {
            width: 50%;
            float: left;
        }

        .resetFloat {
            clear: both;
        }

        .text-left {
            text-align: left;
        }

        .footer {
            position: relative;
            /*bottom: 0px;*/
            font-size: 8.4pt;
            /*width: 10cm;*/
        }

        .f14 {
            font-size: 14pt;
        }

        .f12 {
            font-size: 12pt;
        }

        .obs {
            font-size: 8.4pt;
            color: #444444;
            margin-bottom: 10px;
        }

    </style>
</head>
<body>
@php
    $labeldia= '';
@endphp

@foreach($dados['dias_normais'] as $dia)
    @php
        $labeldia .= mb_strtoupper(substr($dia['label'],0,$dia['label'] == 'Sábado' ? 4 : 3)) . ' - ' . $dia['entrada'] . ' às ' . $dia['saida'] . ' | ';
    @endphp
@endforeach

@foreach($dados['selecionados'] as $key => $colaborador)
    <div class="a4">
        <table class="table" style="width: 100%; border-bottom: 2px black double">
            <thead>
            <tr>
                <th style="font-size: 15pt; font-weight: normal !important; padding-right: 10px; width: 20%">
                    <img
                        src="{{ $colaborador['empresa']['logo'] }}"
                        alt="BPSE" title="BPSE" style="height: 70px; margin-top: 10px;">
                    <br>
                </th>
                <th style="color: black">
                    <h1 style="text-align: center; font-size: 15px">
                        {{$colaborador['empresa']['razao_social']}}
                    </h1>
                    <p style="font-size: 9pt; text-align: center; margin-top: -10px ">
                        CNPJ: {{$colaborador['empresa']['cnpj']}}
                        <br>
                        {{$colaborador['empresa']['endereco_completo']}}
                    </p>
                    <br>
                </th>
                <th style="font-size: 17pt; font-weight: normal !important; padding-left: 10px; width: 60px">
                </th>
            </tr>
            </thead>
        </table>
        <h3 class="text-center" style="margin: 0; margin-top: 3px; margin-bottom: 3px">FOLHA DE PONTO DO COLABORADOR
            DE {{ $dados['periodo'] }}</h3>
        <table class="dados2">
            <thead>
            <tr>
                <th style="text-align: left" width="68%">
                    {{$colaborador['matricula']}} - {{$colaborador['nome']}}
                </th>
                <th style="text-align: left">REPOUSO: {{ mb_strtoupper(implode('/',$dados['repouso'])) }}

                </th>
            </tr>
            <tr>
                <th style="text-align: left">
                    CTPS: {{$colaborador['ctps']}}
                </th>
                <th style="text-align: left">ADMISSÃO: {{ $colaborador['data_admissao'] }}</th>
            </tr>
            <tr>
                <th style="text-align: left">
                    CARGO: {{$colaborador['cargo']}}
                </th>
                <th style="text-align: left">PIS/PASEP: {{ $colaborador['pis'] }}</th>
            </tr>
            </thead>
        </table>
        <table class="dados" style="margin-top: -10px; border: none">
            <thead style="border: none">
            <tr style="border: none">
                <th style="border: none">
                    <strong>HORÁRIOS:</strong>

                    {{ substr($labeldia,0,strlen($labeldia)-3) }}
                </th>
            </tr>
            </thead>
        </table>

        <table class="dados3" style="width: 100%;margin-top: -10px ">
            <thead>
            <tr>
                <th style="text-align: center" width="70px">
                    <strong>Data</strong>
                </th>
                <th style="text-align: center" width="100px">
                    <strong>Entrada</strong>
                </th>
                <th style="text-align: center" width="100px">
                    <strong>Ini. Interv.</strong>
                </th>
                <th style="text-align: center" width="100px">
                    <strong>Fim. Interv.</strong>
                </th>
                <th style="text-align: center" width="100px">
                    <strong>Saída</strong>
                </th>
                <th style="text-align: center">
                    <strong>Rubrica</strong>
                </th>
            </tr>
            </thead>
            <tbody>
            @foreach($dados['calendario'] as $calendario)
                <tr>
                    <td style="text-align: center">
                        {{ $calendario['dia'] }} - {{ $calendario['diaExt'] }}
                    </td>
                    <td style="text-align: center">
                        {{ $calendario['feriado'] ? 'FERIADO' : ':'}}
                    </td>
                    <td style="text-align: center">
                        @if($calendario['feriado'])
                            FERIADO
                        @elseif(!$calendario['feriado'] && !$calendario['repouso'])
                            {{$calendario['intervalo_almoco']}}
                        @else
                            :
                        @endif
                    </td>
                    <td style="text-align: center">
                        @if($calendario['feriado'])
                            FERIADO
                        @elseif(!$calendario['feriado'] && !$calendario['repouso'])
                            {{$calendario['fim_intervalo_almoco']}}
                        @else
                            :
                        @endif
                    </td>
                    <td style="text-align: center">
                        {{ $calendario['feriado'] ? 'FERIADO' : ':'}}
                    </td>
                    <td style="text-align: left">

                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <p style="margin-top: 3px; margin-bottom: 3px">Observações:</p>
        <table class="dados3" style="width: 100%; margin-top: 4px ">
            <thead>
            <tr>
                <th style="height: 70px"></th>
            </tr>
            </thead>
        </table>

        <table class="dados2" style="margin-top: 20px;border: 0px">
            <thead>
            <tr>
                <th style="text-align: center" width="50%">
                    <hr style="width: 90%">
                    {{$colaborador['empresa']['razao_social']}}
                </th>
                <th style="text-align: center">
                    <hr style="width: 90%">
                    {{$colaborador['nome']}}
                </th>
            </tr>
            </thead>
        </table>

        <div style="font-size: 8.4pt; margin-top: 7px">
            <p style="font-size: 7.4pt; color: #444444; margin-bottom: 2.5px;">
                Esse documento foi gerado automaticamente pelo usuário {{ $dados['quem_gerou'] }} Via Sistema Integrado
                MYBP em {{ (new \MasterTag\DataHora())->dataCompleta() }}
                às {{ (new \MasterTag\DataHora())->horaCompleta() }}.
            </p>
        </div>
    </div>
    <div style="page-break-after: always;"></div>
@endforeach
</body>
</html>

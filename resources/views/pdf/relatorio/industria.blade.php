@extends('layouts.pdf')
@section('title','RELATÓRIO - '.$cliente)
@section('empresa')
    @include('layouts.cabecalioEmpresa')
@endsection
@section('conteudo')
    <h5 class="text-center" style="margin-top: 30px">RELATÓRIO MOBILIZAÇÃO - ({{$cliente}})</h5>

    <table width="100%" border="0" class="tabela" style="margin-top: 30px">
        <tr class="topo">
            <td class="text-center">QNT</td>
            <td>O QUE</td>
        </tr>
        <tr class="linha">
            <td class="text-center">{{$curriculoQnt}}</td>
            <td>CURRÍCULOS RECEBIDOS</td>
        </tr>
        <tr class="linha">
            <td class="text-center">{{$curriculoAbertos}}</td>
            <td>CURRÍCULOS ABERTOS</td>
        </tr>

        <tr class="linha">
            <td class="text-center">{{$curriculosSelecionados}}</td>
            <td>CURRÍCULOS SELECIONADOS</td>
        </tr>

        <tr class="linha">
            <td class="text-center">{{$aprovadosParecerRH}}</td>
            <td>APROVADOS PARECER RH</td>
        </tr>

        <tr class="linha">
            <td class="text-center">{{$aprovadosRota}}</td>
            <td>ATENDE ROTA</td>
        </tr>

        <tr class="linha">
            <td class="text-center">{{$aprovadosTecnica}}</td>
            <td>APROVADOS EM ENTREVISTA TÉCNICA</td>
        </tr>

        <tr class="linha">
            <td class="text-center">{{$aprovadosTestePratico}}</td>
            <td>APROVADOS EM TESTE PRÁTICO</td>
        </tr>

        <tr class="linha">
            <td class="text-center">{{ $resultadoIntegrado }}</td>
            <td>RESULTADO INTEGRADO</td>
        </tr>

        <tr class="linha">
            <td class="text-center">{{ $treinados }}</td>
            <td>TREINADOS</td>
        </tr>

{{--        <tr class="linha">--}}
{{--            <td class="text-center">{{ $admitidos }}</td>--}}
{{--            <td>ADMITIDOS</td>--}}
{{--        </tr>--}}

    </table>

    @include('layouts.rodapePdf')
@endsection

@push('style')
    <style type="text/css">
        .tabela {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 9pt;
            border-collapse: collapse;
        }

        tr.topo td {
            border-bottom: 1px solid #ddd;
            font-weight: bold;
            text-transform: uppercase;
            font-family: Helvetica, Arial, sans-serif;
            color: #000;
            padding: 3px;
            background-color: #ccc;

        }

        tr.linha {
            color: #000;
            background-color: #F0F0F0;
        }

        tr.linha td {
            border-bottom: 1px solid #acacac;
            padding: 4px;
        }

        .proximaPagina {
            page-break-before: always;
        }
    </style>
@endpush

@extends('layouts.pdf')
@section('title','RELATÓRIO - '.$user->nome)
@section('empresa')
    @include('layouts.cabecalioEmpresa')
@endsection
@section('conteudo')
    <h5 class="text-center" style="margin-top: 30px">RELATÓRIO USUARIO - ({{$user->nome}}) periodo {{(new \MasterTag\DataHora($dataInicio))->dataCompleta()}} à {{(new \MasterTag\DataHora($dataFim))->dataCompleta()}}</h5>

    <table width="100%" border="0" class="tabela" style="margin-top: 30px">
        <tr class="topo">
            <td class="text-center">QNT</td>
            <td>O QUE</td>
        </tr>
        <tr class="linha">
            <td class="text-center">{{$curriculosAbertos}}</td>
            <td>CURRÍCULOS Abertos</td>
        </tr>
        <tr class="linha">
            <td class="text-center">{{$feedback}}</td>
            <td>CURRÍCULOS FEEDBACK</td>
        </tr>

        <tr class="linha">
            <td class="text-center">{{$parecerRh}}</td>
            <td>PARECER RH</td>
        </tr>

        <tr class="linha">
            <td class="text-center">{{$parecerTecnica}}</td>
            <td>PARECER TÉCNICA</td>
        </tr>

        <tr class="linha">
            <td class="text-center">{{$parecerRota}}</td>
            <td>PARECER ROTA</td>
        </tr>

        <tr class="linha">
            <td class="text-center">{{$parecerTeste}}</td>
            <td>PARECER TESTE</td>
        </tr>

        <tr class="linha">
            <td class="text-center">{{$resultadoIntegrado}}</td>
            <td>RESULTADO INTEGRADO</td>
        </tr>

        <tr class="linha">
            <td class="text-center">{{$admissao}}</td>
            <td>ADMISSÃO</td>
        </tr>

        <tr class="linha">
            <td class="text-center">{{$treinamentos}}</td>
            <td>TREINAMENTOS</td>
        </tr>


    </table>

    <br>
    <br>
    <h5>
        Data de Emissão:
        <span>{{ (new \MasterTag\DataHora())->dataCompleta()}} às {{ (new \MasterTag\DataHora())->horaCompleta()}}</span>
        <br/>
        Emitido por: <span>{{ \Illuminate\Support\Facades\Auth::user()->nome }}</span>
    </h5>
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

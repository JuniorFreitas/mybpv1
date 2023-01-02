@extends('layouts.pdf')
@section('title','RELATÓRIO ANIVERSARIANTES DE'.mb_strtoupper($dados['mes']))
@section('empresa')
    @include('layouts.cabecalioEmpresaJob')
@endsection
@section('conteudo')
    <h5 class="text-center text-uppercase" style="margin-top: 30px">
        {{ mb_strtoupper("Aniversariantes de ".$dados['mes']) }}
    </h5>

    <table width="100%" border="0" class="dados">
        <thead>
            <tr class="bg-default f12">
                <td class="text-center">Nome</td>
                <td class="text-center">Data</td>
                <td class="text-center">Email</td>
            </tr>
        </thead>
        @foreach($dados['rows'] as $aniversariente)
            <tr>
                <td class="text-center">{{ $aniversariente['nome'] }}</td>
                <td class="text-center">{{ $aniversariente['aniversario'] }}</td>
                <td class="text-center">{{ $aniversariente['email'] }}</td>
            </tr>
        @endforeach

    </table>

    @include('layouts.rodapePdfJob')
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

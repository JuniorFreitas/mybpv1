@extends('layouts.pdf')
@section('title','RELATÓRIO CIH PERÍODO')
@section('empresa')
    @include('layouts.cabecalioEmpresa')
@endsection
@section('conteudo')
    <h5 class="text-center text-uppercase" style="margin-top: 30px">{{$empresa->nome}} <br> REGISTRO DE
        JUSTIFICATIVA DE PONTO - PERÍODO {{(new \MasterTag\DataHora($dataInicio))->dataCompleta()}}
        à {{(new \MasterTag\DataHora($dataFim))->dataCompleta()}} <br>
    </h5>

    <table width="100%" border="0" class="tabela" style="margin-top: 30px">

        <tr class="topo">
            <td class="text-center">N</td>
            <td class="text-center">Colaborador</td>
            <td class="text-center">Área</td>
            <td class="text-center">Data ocorrência</td>
            <td class="text-center">Ocorrência</td>
            <td class="text-center">Responsável Lançamento</td>
            <td class="text-center">Ação</td>
            <td class="text-center">Status</td>
            <td class="text-center">Responsável Aprovação</td>
        </tr>
        <?php $cont = 1; ?>
        @foreach($rows as $cih)
            <tr class="linha">
                <td class="text-center">{{ $cont }}</td>
                <td class="text-center">{{ $cih['colaborador'] }}</td>
                <td class="text-center">{{$cih['area'] }}</td>
                <td class="text-center">{{$cih['data_aprovacao']}}</td>
                <td class="text-center" style="text-transform: uppercase">{{ $cih['tag'] }}</td>
                <td class="text-center" style="text-transform: uppercase">{{ $cih['responsavel_lancamento'] }}</td>
                <td class="text-center" style="text-transform: uppercase">{{ $cih['acao'] }}</td>
                <td class="text-center" style="text-transform: uppercase">
                    {{ $cih['status'] }}<br>
                    {{ $cih['data_aprovacao'] }}
                </td>
                <td class="text-center" style="text-transform: uppercase">{{ $cih['responsavel_aprovacao'] }}</td>
            </tr>
            <?php $cont++; ?>
        @endforeach

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

@extends('layouts.pdf')
@section('title','RELATÓRIO CIH PERÍODO')
@section('empresa')
    @include('layouts.cabecalioEmpresaJob')
@endsection
@section('conteudo')
    <h5 class="text-center text-uppercase" style="margin-top: 30px">
        {{$usuario['razao_social']}} <br> REGISTRO DE JUSTIFICATIVA DE PONTO
        @if($dados['filtro_periodo'])
            - PERÍODO {{(new \MasterTag\DataHora($dados['data_inicio']))->dataCompleta()}}
            à {{(new \MasterTag\DataHora($dados['data_fim']))->dataCompleta()}} <br>
        @endif
    </h5>

    <table width="100%" border="0" class="dados" style="margin-top: 30px">
        <thead>
            <tr class="bg-default f12">
                <th class="text-center">N</th>
                <th class="text-center">Colaborador</th>
                <th class="text-center">Cargo</th>
                <th class="text-center">{{ $dados['modelo_cih_config'] == "area" ? "Área" : "Centro de Custo" }}</th>
                <th class="text-center">Data ocorrência</th>
                <th class="text-center">Ocorrência</th>
                <th class="text-center">Responsável Lançamento</th>
                <th class="text-center">Ação</th>
                <th class="text-center">Status</th>
                <th class="text-center">Responsável Aprovação</th>
            </tr>
        </thead>
        <?php $cont = 1; ?>
        @foreach($dados['rows'] as $cih)
            <tr>
                <td class="text-center">{{ $cont }}</td>
                <td class="text-center">{{ $cih['colaborador'] }}</td>
                <td class="text-center">{{ $cih['cargo'] }}</td>
                <td class="text-center">{{ $dados['modelo_cih_config'] === "area" ? $cih['area'] : $cih['centro_de_custo'] }}</td>
                <td class="text-center">{{$cih['data_ocorrencia']}}</td>
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

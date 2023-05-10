@extends('layouts.pdf')
@section('title','Relatório de Centros de Custo')
@section('empresa')
    @include('layouts.cabecalioEmpresaJob', ['usuario' => $usuario])
@endsection
@section('conteudo')
    <div class="center">
        <h3 style="text-transform: uppercase">Listagem Sintética de Funcionários</h3>
    </div>
    <br>
    @php
        $total = 0;
    @endphp
    @foreach($dados as $centro_de_custo)
        @if(count($centro_de_custo['admissao']) > 0)
            <div>
                <h4>{{ $centro_de_custo['label'] }}</h4>
                <table class="dados">
                    <thead>
                    <tr class="bg-default f12">
                        <th>Código</th>
                        <th>Nome</th>
                        <th>Cargo</th>
                        <th>Tipo Admissão</th>
                        <th>Data da Admissão</th>
                    </tr>
                    </thead>
                    <tbody>
                        @foreach($centro_de_custo['admissao'] as $item)
                            <tr>
                                <td class="text-center">
                                    {{ $item['feedback']['curriculo']['id'] }}
                                </td>
                                <td>
                                    {{ $item['feedback']['curriculo']['nome'] }}
                                </td>
                                <td>
                                    {{ $item['cargo'] }}
                                </td>
                                <td class="text-center">
                                    {{ $item ? $item['tipo_admissao'] : '' }}
                                </td>
                                <td class="text-center">
                                    {{ $item ? $item['data_admissao'] : '' }}
                                </td>
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="5" style="text-align: right; ">
                                <strong>Total de Funcionários: </strong>{{ count($centro_de_custo['admissao']) }}
                                @php
                                    $total = $total + count($centro_de_custo['admissao']);
                                @endphp
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif
    @endforeach
    <div>
        <table class="dados">
            <tr>
                <td class="text-center"><strong>TOTAL DE FUNCIONÁRIOS: {{ $total }}</strong></td>
            </tr>
        </table>
    </div>
    @include('layouts.rodapePdfJob', ['usuario' => $usuario])
@endsection
@push('style')
    <style type="text/css">
        table.dados, table.dados th, table.dados td {
            border: 1px solid black;
            border-collapse: collapse;
            font-size: 8.5pt;
            padding: 5px;
        }

        h4{
            margin-top: 25px;
        }

        .dados{
            width: 100%;
        }

        .text-center{
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
            position: absolute;
            bottom: 0px;
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
@endpush

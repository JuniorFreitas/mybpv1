@extends('layouts.mail.layout')
@section('titulo', 'Resposta de Convocação Intermitente')
@push('css')
    <style>
        .resposta-sim {
            font-size: 15px;
            display: inline-block;
            background: #1c7430;
            color: #fff !important;
            padding: 6px;
            margin-top: 4px;
            border-radius: 15px;
            text-align: center;
            width: 80%;
            height: 25px;
            transition: .3s;
        }
        .resposta-nao {
            font-size: 15px;
            display: inline-block;
            background: #CC0000;
            color: #fff !important;
            padding: 6px;
            margin-top: 4px;
            border-radius: 15px;
            text-align: center;
            width: 80%;
            height: 25px;
            transition: .3s;
        }
    </style>
@endpush
@section('conteudo')

    <table border="0" align="center" cellpadding="0" width="97%" style="width: 100%;padding: 25px;">
        <tr>
            <td>
{{--                <img src="https://sgibpse.com.br/imagens/bepinhas/branca_2.png" alt="Bepinha">--}}
            </td>
            <td style="text-align: justify">
                Prezado(a), <strong>{{ $dados['gestor'] }}</strong><br><br>
                O(A) colaborador(a) <strong>{{ $dados['colaborador'] }}</strong> foi convocado(a) ao trabalho<br>
                no período de <strong>{{ $dados['periodo'] }}</strong> no <strong>{{ $dados['centro_de_custo'] }} / {{ $dados['area'] }}.</strong><br><br>
                Viemos através dessa mensagem informá-lo(a) da resposta do(a) colaborador(a).<br><br>

                @if($dados['resposta'] == 'Sim')
                    <div class="resposta-sim">
                        O(A) colaborador(a) ACEITOU a convocação
                    </div>
                @else
                    <div class="resposta-nao">
                        O(A) colaborador(a) RECUSOU a convocação
                    </div>
                @endif
                <br><br>
            </td>
        </tr>
    </table>
@endsection

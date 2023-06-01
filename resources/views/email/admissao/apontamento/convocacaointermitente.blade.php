@extends('layouts.mail.layout')
@section('titulo', 'Convocação Intermitente')
@push('css')
    <style>
        .botao a {
            font-size: 15px;
            display: inline-block;
            background: #0F4C60;
            color: #fff !important;
            padding: 10px 0px 6px;
            margin-top: 4px;
            border-radius: 15px;
            text-align: center;
            width: 100%;
            height: 25px;
            transition: .3s;
        }

        .botao a:hover {
            background: #031E2D;
            color: #fff !important;
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
                Prezado(a), <strong>{{ $dados['colaborador'] }}</strong><br>
                Conforme seu modelo de contrato INTERMITENTE prevê a convocação ao trabalho,<br>
                viemos através dessa mensagem informá-lo(a) que o(a) Sr(a). está convocado(a)<br>
                para trabalho no período de <strong>{{ $dados['periodo'] }}</strong> no <strong>{{ $dados['centro_de_custo'] }} / {{ $dados['area'] }}.</strong><br><br>
                Para isso, gentileza confirmar aceite de convocação, conforme links abaixo.<br><br>
                <div class="botao">
                    <a href="{{ $dados['resposta_sim'] }}">Aceitar</a>
                </div>
                <div class="botao">
                    <a href="{{ $dados['resposta_nao'] }}">Recusar</a>
                </div><br>
                Informamos que você tem até <strong>{{ $dados['prazo_resposta_expiracao'] }}</strong> para sinalizar a sua resposta.<br><br>
                Um forte abraço da equipe <strong>{{ $dados['empresa'] }}</strong>
                <br><br>
            </td>
        </tr>
    </table>
@endsection

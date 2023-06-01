@extends('layouts.mail.layout')
@section('titulo', 'Envio de Documentos')
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

    @php
        $empresa = \App\Models\Cliente::withoutGlobalScopes()->find($dados['empresa_id']);
    @endphp

    <table border="0" align="center" cellpadding="0" width="97%" style="width: 100%;padding: 25px;">
        <tr>
            <td style="text-align: justify">

                Prezado(a) sr(a) <strong>{{$dados['nome']}}</strong>, Tudo bem?<br><br>

                Para continuidade no processo, segue o link abaixo para que seja anexada a <strong>CARTA OFERTA
                    ASSINADA</strong>.
                <br><br>

                <div class="botao">
                    <a href="{{ $dados['url_documento'] }}">CLIQUE AQUI</a>
                    <br><span style="font-size: 11px; color: #696969">Caso não consiga abrir copie e cole esse endereço no navegador: {{$dados['url_documento']}}</span>
                    <br><br>
                </div>

                Atenciosamente,<br><br>

                Equipe {{$empresa->razao_social}}<br><br>

            </td>
        </tr>
    </table>
@endsection

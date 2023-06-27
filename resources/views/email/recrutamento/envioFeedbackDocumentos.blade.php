@extends('layouts.mail.layout')
@section('titulo', 'Envio de Feedback Documentos')
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

                Prezado(a) sr(a) <strong>{{$dados['nome']}}</strong>, tudo bem?<br><br>

                <p style="white-space: pre-wrap !important;">{{ $dados['observacao'] }}</p>
                <br><br>

                Atenciosamente,<br><br>
                Equipe {{$empresa->nome_fantasia}}<br><br>

            </td>
        </tr>
    </table>
@endsection

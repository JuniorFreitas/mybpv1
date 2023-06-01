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

                Prezado(a) sr(a) <strong>{{$dados['nome']}}</strong>, tudo bem?<br><br>

                Parabéns por chegado até esta etapa! Você foi aprovado(a) na etapa de entrevista e seleção e agora vamos
                para a etapa de documentos para admissão.<br><br>

                @if(isset($dados['url_checklist']))
                    <div class="botao">
                        <a href="{{ $dados['url_checklist'] }}">DOWNLOAD DO CHECKLIST</a><br><br>
                    </div>
                @endif

                Para continuidade no processo, segue o link abaixo para que seja anexado os documentos conforme
                descrição.<br><br>

                <div class="botao">
                    <a href="{{ $dados['url_documento'] }}">CLIQUE AQUI PARA ANEXAR.</a><br><br>
                </div>

                Destaca-se que é muito importante que todos os documentos sejam anexados corretamente e sem omissões
                para que não haja atraso na etapa de documentação, necessária para a continuidade de sua
                admissão.<br><br>

                Atenciosamente,<br><br>

                Equipe {{$empresa->razao_social}}<br><br>

            </td>
        </tr>
    </table>
@endsection

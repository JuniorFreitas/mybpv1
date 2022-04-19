@extends('layouts.mail.layout')
@section('titulo', 'Você foi aprovado!')
@push('css')
    <style>
        .botao a {
            font-size: 15px;
            display: inline-block;
            background: #0F4C60;
            color: #fff !important;
            padding: 6px;
            margin-top: 4px;
            border-radius: 15px;
            text-align: center;
            width: 125px;
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

    <table border="0" cellpadding="0" width="97%" style="width: 97%;">
        <tr>
            <td style="text-align: justify">

                Prezado(a) sr(a) <strong>{{$dados['Curriculo']['nome']}}</strong>, Tudo bem?<br><br>

                Parabéns por chegado até esta etapa! Você foi aprovado na etapa de entrevista e seleção e agora vamos
                para a etapa de documentos para admissão.<br><br>

                Para continuidade no processo, segue o link abaixo para que seja anexado os documentos conforme
                descrição.<br><br>

                <div class="botao">
                    <a href="{{env('APP_URL')}}/documentos">Clique Aqui.</a><br><br>
                </div>

                Destaca-se que é muito importante que todos os documentos sejam anexados corretamente e sem omissões
                para que não haja atraso na etapa de documentação, necessária para a continuidade de sua
                admissão.<br><br>

                Atenciosamente,<br><br>

                Equipe {{auth()->user()->Empresa->Cliente->razao_social}}<br><br>

            </td>
        </tr>
    </table>
@endsection

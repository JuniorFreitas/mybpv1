@extends('layouts.mail.layout')
@section('titulo', 'Ocorrência em andamento')
@section('conteudo')
    <table border="0" cellpadding="0" width="97%" style="width: 97%;">
        <tr>
            <td style="text-align: justify; padding: 30px;">
                Olá, <strong>{{ $dados['nome_para'] }}</strong>!<br><br>
                <strong>{{ $dados['atualizado_por'] }}</strong>, adicionou uma nova mensagem na ocorrência: <br>
                ID: <strong>{{$dados['ocorrencia_id']}}</strong>. <br>
                Assunto: <strong>{{ $dados['assunto_ocorrencia'] }}</strong>
                <br><br>
                Para visualizar acesse o sistema <a href="{{ route('g.ocorrencia.ocorrencia.index') }}">clique aqui</a>
                .

                <br><br>
            </td>
        </tr>
    </table>
@endsection

@extends('layouts.mail.layout')
@section('titulo', 'Ocorrência Finalizada')
@section('conteudo')
    <table border="0" cellpadding="0" width="97%" style="width: 97%;">
        <tr>
            <td style="text-align: justify; padding: 30px;">
                Olá, <strong>{{ $dados['nome_para'] }}</strong>!<br><br>
                A ocorrência ID: <strong>{{$dados['ocorrencia_id']}}</strong>,  Assunto: <strong>{{ $dados['assunto_ocorrencia'] }}</strong><br/>
                Foi finalizada por: <strong>{{$dados['finalizado_por']}}</strong>.
                <br><br>
                Para visualizar acesse o sistema <a href="{{ route('g.ocorrencia.ocorrencia.index') }}">clique aqui</a> .

                <br><br>
            </td>
        </tr>
    </table>
@endsection

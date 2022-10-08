@extends('layouts.mail.layout')
@section('titulo', 'Ocorrência Criada')
@section('conteudo')
    <table border="0" align="center" cellpadding="0" width="97%" style="width: 100%;padding: 25px;">
        <tr>
            <td style="text-align: justify; padding: 30px;">
                Olá, <strong>{{ $dados['nome_para'] }}</strong>!<br><br>
                <strong>{{ $dados['nome_de'] }}</strong>, criou e marcou você na ocorrência. <br>
                ID: <strong>{{$dados['ocorrencia_id']}}</strong>. <br>
                Assunto: <strong>{{ $dados['assunto_ocorrencia'] }}</strong>
                <br><br>
                Para visualizar, acesse a plataforma <a href="{{ route('g.ocorrencia.ocorrencia.index') }}">clique aqui</a> .

                <br><br>
            </td>
        </tr>
    </table>
@endsection

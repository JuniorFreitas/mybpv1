@extends('layouts.mail.layout')
@section('titulo', 'Requisição Andamento')
@section('conteudo')
    <table border="0" align="center" cellpadding="0" width="97%" style="width: 100%;padding: 25px;">
        <tr>
            <td style="text-align: justify">
                Olá, <strong>{{ $dados['nome_para'] }}</strong>!<br><br>
                <strong>{{ $dados['nome_de'] }}</strong>, mudou o status da Requisição de vaga. <br>
                ID: <strong>{{$dados['requisicao_id']}}</strong>. <br>
                Cargo: <strong>{{$dados['cargo']}}</strong>. <br>
                <br><br>
                Para visualizar acesse o sistema <a href="{{ route('g.requisicao_vagas.requisicao-vaga.index') }}">clique
                    aqui</a> .

            </td>
        </tr>
    </table>
@endsection

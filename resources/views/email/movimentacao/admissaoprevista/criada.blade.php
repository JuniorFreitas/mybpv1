@extends('layouts.mail.layout')
@section('titulo', 'Demissão Prevista Criada')
@section('conteudo')
    <table border="0" align="center" cellpadding="0" width="97%" style="width: 100%;padding: 25px;">
        <tr>
            <td style="text-align: justify">
                Olá, <strong>{{ $dados['nome_para'] }}</strong>!<br><br>
                <strong>{{ $dados['nome_de'] }}</strong>, criou e marcou você em uma admissão prevista. <br>
                ID: <strong>{{$dados['admissao_id']}}</strong>. <br>
                Cargo: <strong>{{$dados['cargo']}}</strong>. <br>
                <br><br>
                Para visualizar acesse o sistema <a href="{{ route('g.movimentacao.index') }}">clique aqui</a> .

            </td>
        </tr>
    </table>
@endsection

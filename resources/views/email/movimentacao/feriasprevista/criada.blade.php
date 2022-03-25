@extends('layouts.mail.layout')
@section('titulo', 'Demissão Prevista Criada')
@section('conteudo')
    <table border="0" cellpadding="0" width="97%" style="width: 97%;">
        <tr>
            <td style="text-align: justify">
                Olá, <strong>{{ $dados['nome_para'] }}</strong>!<br><br>
                <strong>{{ $dados['nome_de'] }}</strong>, criou e marcou você em uma férias prevista. <br>
                ID: <strong>{{$dados['ferias_id']}}</strong>. <br>
                Colaborador: <strong>{{$dados['colaborador']}}</strong>. <br>
                <br><br>
                Para visualizar acesse o sistema <a href="{{ route('g.movimentacao.index') }}">clique aqui</a> .

            </td>
        </tr>
    </table>
@endsection

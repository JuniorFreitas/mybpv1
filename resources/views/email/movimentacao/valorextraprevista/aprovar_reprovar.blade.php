@extends('layouts.mail.layout')
@section('titulo', 'Ocorrência Criada')
@section('conteudo')
    <table border="0" cellpadding="0" width="97%" style="width: 97%;">
        <tr>
            <td style="text-align: justify">
                Olá, <strong>{{ $dados['nome_para'] }}</strong>!<br><br>
                <strong>{{ $dados['nome_de'] }}</strong>, mudou o status da Liderança de Pessoal e Valor Extra. <br>
                ID: <strong>{{$dados['id']}}</strong>. <br>
                Colaborador: <strong>{{$dados['colaborador']}}</strong>. <br>
                <br><br>
                Para visualizar acesse o sistema <a href="{{ route('g.movimentacao.index') }}">clique aqui</a> .

            </td>
        </tr>
    </table>
@endsection

@extends('layouts.mail.layout')
@section('titulo', 'Liderança de Pessoal e Valor Extra Prevista')
@section('conteudo')
    <table border="0" align="center" cellpadding="0" width="97%" style="width: 100%;padding: 25px;">
        <tr>

            <td style="text-align: justify">
                Olá, <strong>{{ $dados['nome_para'] }}</strong>!<br><br>
                <strong>{{ $dados['nome_de'] }}</strong>, mudou o status da Liderança de Pessoal e Valor Extra prevista. <br>
                ID: <strong>{{$dados['id']}}</strong>. <br>
                Colaborador: <strong>{{$dados['colaborador']}}</strong>. <br>
                <br><br>
                Para visualizar acesse o sistema <a href="{{ route('g.movimentacao.index') }}">clique aqui</a> .

            </td>
        </tr>
    </table>
@endsection

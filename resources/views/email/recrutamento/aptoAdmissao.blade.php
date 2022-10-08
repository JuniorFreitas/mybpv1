@extends('layouts.mail.layout')
@section('titulo', 'E-mail de desclassificacao')
@section('conteudo')

    <table border="0" align="center" cellpadding="0" width="97%" style="width: 100%;padding: 25px;">
        <tr>

            <td style="text-align: justify">
                Olá, <strong>{{ $dados['nome'] }}</strong> Parabéns!<br><br>
                {{ $dados['mensagem'] }}
               <br><br>


                <br><br>
            </td>
        </tr>
    </table>
@endsection

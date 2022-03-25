@extends('layouts.mail.layout')
@section('titulo', 'E-mail de desclassificacao')
@section('conteudo')

    <table border="0" cellpadding="0" width="97%" style="width: 97%;">
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

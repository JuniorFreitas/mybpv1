@extends('layouts.mail.layout')
@section('titulo', 'E-mail de desclassificacao')
@section('conteudo')

    <table border="0" cellpadding="0" width="97%" style="width: 97%;">
        <tr>
            <td>
                <img src="https://sgibpse.com.br/imagens/bepinhas/branca_2.png" alt="Bepinha">
            </td>
            <td style="text-align: justify">
                Olá, <strong>{{ $dados['nome'] }}</strong> Parabéns!<br><br>
                {{ $dados['mensagem'] }}
               <br><br>

                Abraços<br><br>
                BPSE-Business Partners Serviços Empresariais

                <br><br>
            </td>
        </tr>
    </table>
@endsection

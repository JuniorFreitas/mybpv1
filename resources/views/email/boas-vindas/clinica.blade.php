@extends('layouts.mail.layout')
@section('titulo', 'Bem vindo(a) ao MyBP')
@section('conteudo')
    <table border="0" cellpadding="0" width="100%" style="width: 100%;">
        <tr>
            <td style="text-align: justify; padding: 30px;">
                Olá, <strong>{{ $dados['nome'] }}</strong><br>
                Estamos enviando o login e a senha para acesso na plataforma MyBP.<br><br>
                Onde poderá anexar os exames dos candidatos.
                <br><br>
                LOGIN: {{$dados['email']}} <br>
                SENHA: {{$dados['senha']}}
                <br><br>
                Clique abaixo para acessar a plataforma:
                <br><br>
                <a href="https://sistema.mybp.com.br" class="link" target="_blank">ACESSAR A PLATAFORMA</a>
                <br><br>
                Caso não consiga abrir copie e cole esse endereço no navegador:
                <br>https://sistema.mybp.com.br
                <br><br>
            </td>
        </tr>
    </table>
@endsection

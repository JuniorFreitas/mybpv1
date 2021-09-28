@extends('layouts.mail.layout')
@section('titulo', $dados['assunto'])
@section('conteudo')

    <table border="0" cellpadding="0" width="97%" style="width: 97%;">
        <tr>
            <td>
                <img src="https://sgi.bpse.com.br/imagens/bepinhas/branca_2.png" alt="Bepinha">
            </td>
            <td style="text-align: justify">

                Olá, <strong>{{ $dados['clinica'] }}</strong>.<br><br>
                Estamos encaminhando {{ $dados['colaborador'] }}, {{ $dados['idade'] }} anos. <br>
                Para realizar exame de ordem {{ $dados['tipoExame'] }}. <br><br>
                <a href='{{$dados['link']}}' class='link'
                   style='padding: 10px; background: #072534; color: white; margin-right: 5px'>CLIQUE AQUI</a> para
                acessar a ficha, se não abrir copie e cole o link abaixo em seu navegador. <br><br>
                {{$dados['link']}}
                <br><br>


{{--                Abraços<br><br>--}}
{{--                BPSE-Business Partners Serviços Empresariais--}}

                <br><br>
            </td>
        </tr>
    </table>
@endsection

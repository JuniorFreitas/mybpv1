@extends('layouts.mail.layout')
@section('titulo', $dados['assunto'])
@section('conteudo')

    <table border="0" cellpadding="0" width="97%" style="width: 97%;">
        <tr>

            <td style="text-align: justify">

{{--                {{dd($dados['clinica'])}}--}}
                Olá, <strong>{{ $dados['colaborador'] }}</strong>.<br><br>
                Estamos encaminhando para realizar o exame de ordem {{ $dados['tipoExame'] }}.<br><br>
                Local do Exame: <strong>{{ $dados['clinica']['nome'] }}</strong> <br>
                Endereço: <strong>{{ $dados['clinica']['dados']['endereco']['endereco_completo'] }}</strong><br>
                Contato: <strong>{{ $dados['clinica']['dados']['telefone'] }}</strong>
                <br><br>
                <a href='{{$dados['link']}}' class='link' style='padding: 10px; background: #072534; color: white; margin-right: 5px'>CLIQUE AQUI</a> para acessar a ficha, se não abrir copie e cole o endereço abaixo em seu navegador.<br><br>
                {{$dados['link']}}
                <br><br>


{{--                Respeitosam<br><br>--}}
{{--                BPSE-Business Partners Serviços Empresariais--}}

                <br><br>
            </td>
        </tr>
    </table>
@endsection

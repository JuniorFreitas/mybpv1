@extends('layouts.mail.layout')
@section('titulo', 'Recuperação de senha')
@section('conteudo')
    <table border="0" align="center" cellpadding="0" width="97%" style="width: 100%;padding: 25px;">
        <tr>
            <td style="text-align: justify">
                Olá, <strong>{{ $dados['nome'] }}</strong>! Tudo bem?<br>
                Recebemos uma solicitação para a recuperação de senha. <br>
                Sua senha só será alterada se você trocá-la até {{ $dados['expiracao'] }}.<br><br>
                <h2 style="border: 3px dashed; padding: 29px; text-align: center;"
                >CÓDIGO DE SEGURANÇA: <strong>{{ $dados['token'] }}</strong></h2>
                <br>

                <a href="{{ route('recuperaSenhanew',$dados['token']) }}" class="link" target="_blank">
                    REDEFINIR MINHA SENHA
                </a>
                <br><br>
                Se não foi você quem fez essa solicitação, por favor desconsidere este e-mail.
                <br><br>
            </td>
        </tr>
    </table>
@endsection

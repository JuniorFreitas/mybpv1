@extends('layouts.mail.layout')
@section('titulo', 'Recuperação de senha')
@section('conteudo')
    <table border="0" cellpadding="0" width="97%" style="width: 97%;">
        <tr>
            <td style="text-align: justify">
                Olá, <strong>{{ $dados['nome'] }}</strong>! Tudo bem?<br>
                Recebemos uma solicitação para a recuperação de senha. <br>
                Sua senha só será alterada se você trocá-la até {{ $dados['expiracao'] }}.<br><br>
                <a href="{{ route('recuperaSenha',$dados['token']) }}" class="link" target="_blank">
                    TROCAR MINHA SENHA
                </a>
                <br><br>
                Se não foi você quem fez essa solicitação,<br>
                por favor desconsidere este e-mail.
                <br><br>
            </td>
        </tr>
    </table>
@endsection

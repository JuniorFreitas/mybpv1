@extends('layouts.mail.layout')
@section('titulo', 'Recuperação de senha')
@section('conteudo')
    <table border="0" align="center" cellpadding="0" width="97%" style="width: 100%;padding: 25px;">
        <tr>
            <td style="text-align: justify">
                Olá, <strong>{{ $dados['nome'] }}</strong>! Tudo bem?<br>
                Recebemos uma solicitação para a recuperação de senha. <br>
                Sua senha só será alterada se você trocá-la até {{ $dados['expiracao'] }}.<br><br>
                <p style="text-align: center; margin: 24px 0;">
                    Clique no botão abaixo para redefinir sua senha (o link é válido até {{ $dados['expiracao'] }}):
                </p>
                <p style="text-align: center; margin: 24px 0;">
                    <a href="{{ route('recuperaSenhanew', $dados['token']) }}" class="link" target="_blank">
                        REDEFINIR MINHA SENHA
                    </a>
                </p>
                <p style="font-size: 12px; color: #666; word-break: break-all; text-align: center;">
                    Se o botão não funcionar, copie e cole este link no navegador:<br>
                    {{ route('recuperaSenhanew', $dados['token']) }}
                </p>
                <br><br>
                Se não foi você quem fez essa solicitação, por favor desconsidere este e-mail.
                <br><br>
            </td>
        </tr>
    </table>
@endsection

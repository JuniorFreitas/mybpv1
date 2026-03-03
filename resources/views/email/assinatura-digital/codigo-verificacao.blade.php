@extends('layouts.mail.layout')
@section('titulo', 'Código de verificação')
@section('conteudo')
    <table border="0" align="center" cellpadding="0" width="97%" style="width: 100%;padding: 25px;">
        <tr>
            <td style="text-align: justify">
                Prezado(a) <strong>{{ $nome }}</strong>,<br><br>

                Recebemos uma solicitação para acessar um documento de assinatura digital.<br><br>

                Seu código de verificação é:<br><br>

                <div style="font-size: 24px; font-weight: 700; letter-spacing: 3px; text-align: center; padding: 14px; border: 1px solid #d9d9d9; border-radius: 8px;">
                    {{ $codigo }}
                </div>
                <br>

                Este código expira em {{ $minutosExpiracao }} minutos.<br><br>

                Se você não solicitou esse acesso, desconsidere este e-mail.<br><br>

                @if($nomeEmpresa)
                    Atenciosamente,<br><br>
                    Equipe {{ $nomeEmpresa }}<br><br>
                @endif
            </td>
        </tr>
    </table>
@endsection

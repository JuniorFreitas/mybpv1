@extends('layouts.mail.layout')
@section('titulo', 'Código de acesso')
@section('conteudo')
    @php
        $empresa = \App\Models\Cliente::withoutGlobalScopes()->find($dados['empresa_id']);
    @endphp
    <table border="0" align="center" cellpadding="0" width="97%" style="width: 100%;padding: 25px;">
        <tr>
            <td style="text-align: justify">
                Prezado(a) <strong>{{ $dados['nome'] }}</strong>,<br><br>
                Seu código de acesso (<strong>{{ $dados['recurso'] ?? 'Carta Oferta' }}</strong>) é:
                <br><br>
                <div style="font-size: 28px; letter-spacing: 4px; font-weight: bold; text-align: center;">
                    {{ $dados['codigo'] }}
                </div>
                <br>
                Ele é válido por <strong>{{ (int) ($dados['minutos'] ?? 60) }} minutos</strong> e só pode ser usado uma vez.
                <br><br>
                Se você não solicitou este código, ignore este e-mail.
                <br><br>
                Atenciosamente,<br>
                Equipe {{ $empresa->razao_social ?? 'RH' }}
            </td>
        </tr>
    </table>
@endsection

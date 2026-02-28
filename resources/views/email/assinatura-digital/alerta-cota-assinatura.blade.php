@extends('layouts.mail.layout')
@section('titulo', 'Alerta de cota de assinatura digital')
@section('conteudo')
    <table border="0" align="center" cellpadding="0" width="97%" style="width: 100%;padding: 25px;">
        <tr>
            <td style="text-align: justify">
                Olá,<br><br>
                A empresa <strong>{{ $nomeEmpresa }}</strong> atingiu <strong>{{ $percentual }}%</strong> da cota mensal de assinatura digital.<br><br>

                Competência: <strong>{{ $resumo['competencia'] ?? '—' }}</strong><br>
                Limite mensal: <strong>{{ $resumo['limite_mensal'] ?? '—' }}</strong><br>
                Usadas: <strong>{{ $resumo['usadas'] ?? 0 }}</strong><br>
                Restantes: <strong>{{ $resumo['restantes'] ?? 0 }}</strong><br><br>

                Acompanhe o consumo no gerenciador de assinaturas digitais.<br><br>
            </td>
        </tr>
    </table>
@endsection


@extends('layouts.mail.layout')
@section('titulo', $subject)
@section('conteudo')
    @php $t = $dados['totais'] ?? []; @endphp

    <table border="0" align="center" cellpadding="0" width="97%" style="width: 100%;padding: 25px;">
        <tr>
            <td style="text-align: justify">

                Olá, <strong>{{ $usuario->nome }}</strong>.<br><br>
                Situação dos ASOs dos colaboradores ativos em {{ $dados['data_br'] ?? '' }}:<br><br>

                <table border="1" cellpadding="6" cellspacing="0" width="100%" style="width: 100%; border-collapse: collapse;">
                    <tr><td>Vencidos</td><td style="text-align: center"><strong>{{ $t['vencido'] ?? 0 }}</strong></td></tr>
                    <tr><td>Vencendo nos próximos {{ $dados['janela'] ?? '' }} dias</td><td style="text-align: center"><strong>{{ $t['vencendo'] ?? 0 }}</strong></td></tr>
                    <tr><td>Sem ASO registrado</td><td style="text-align: center"><strong>{{ $t['sem_aso'] ?? 0 }}</strong></td></tr>
                    <tr><td>Em dia</td><td style="text-align: center"><strong>{{ $t['em_dia'] ?? 0 }}</strong></td></tr>
                    <tr><td>Total de colaboradores ativos</td><td style="text-align: center">{{ $t['total'] ?? 0 }}</td></tr>
                </table>

                <br>
                <a href="{{ $dados['link'] ?? '#' }}" style="font-weight: bold;">Abrir o Painel de Saúde Ocupacional</a>
                <br><br>
                <small style="color: #666;">Você recebe este e-mail porque marcou "Vencimento ASO" nas suas preferências de notificação.</small>
            </td>
        </tr>
    </table>
@endsection

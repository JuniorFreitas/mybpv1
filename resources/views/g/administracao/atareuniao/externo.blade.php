<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $atareuniao->codigo ?? 'Ata' }} - {{ $atareuniao->titulo ?? 'Ata de Reunião' }}</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; background: #f3f6f8; color: #1f2933; margin: 0; }
        main { max-width: 960px; margin: 32px auto; background: #fff; border-radius: 12px; padding: 28px; box-shadow: 0 10px 30px rgba(15, 23, 42, .08); }
        h1 { margin: 0 0 8px; font-size: 26px; }
        h2 { margin-top: 28px; font-size: 18px; border-bottom: 1px solid #e5e7eb; padding-bottom: 8px; }
        .meta { color: #52606d; margin-bottom: 18px; }
        .badge { display: inline-block; background: #e0f2fe; color: #075985; border-radius: 999px; padding: 4px 10px; font-size: 12px; font-weight: bold; }
        .alerta { background: #fff7ed; border: 1px solid #fed7aa; color: #9a3412; padding: 12px 14px; border-radius: 8px; margin: 18px 0; }
        table { border-collapse: collapse; width: 100%; margin-top: 10px; }
        th, td { border-bottom: 1px solid #e5e7eb; padding: 9px; text-align: left; vertical-align: top; }
        th { background: #f8fafc; font-size: 13px; text-transform: uppercase; color: #52606d; }
        .rodape { margin-top: 32px; color: #7b8794; font-size: 13px; }
    </style>
</head>
<body>
<main>
    <span class="badge">Acesso externo valido ate {{ optional($compartilhamento->expira_em)->format('d/m/Y H:i') }}</span>
    <h1>{{ $atareuniao->codigo ?? 'Ata' }} - {{ $atareuniao->titulo ?? 'Ata de Reunião' }}</h1>
    <div class="meta">
        Local: {{ $atareuniao->local ?? 'Nao informado' }}<br>
        Inicio: {{ $atareuniao->data_inicio ?? 'Nao informado' }}<br>
        Fim: {{ $atareuniao->data_fim ?? 'Nao informado' }}
    </div>

    @if(in_array($atareuniao->classificacao_confidencialidade, ['confidencial', 'restrita'], true))
        <div class="alerta">
            Esta ata possui classificacao {{ $atareuniao->classificacao_confidencialidade }}.
            Nao compartilhe este link. O acesso e temporario e auditado.
        </div>
    @endif

    <h2>Objetivo</h2>
    <p>{{ $atareuniao->objetivo ?? 'Nao informado' }}</p>

    <h2>Pauta</h2>
    <table>
        <thead><tr><th>Assunto</th></tr></thead>
        <tbody>
        @forelse($atareuniao->Assuntos as $assunto)
            <tr><td>{{ $assunto->assunto }}</td></tr>
        @empty
            <tr><td>Nenhuma pauta registrada.</td></tr>
        @endforelse
        </tbody>
    </table>

    <h2>Decisoes e comentarios</h2>
    <table>
        <thead><tr><th>Tipo</th><th>Descricao</th></tr></thead>
        <tbody>
        @forelse($atareuniao->Tipos as $tipo)
            <tr>
                <td>{{ $tipo->tipo }}</td>
                <td>{{ $tipo->observacao }}</td>
            </tr>
        @empty
            <tr><td colspan="2">Nenhuma decisao ou comentario registrado.</td></tr>
        @endforelse
        </tbody>
    </table>

    <h2>Pendencias</h2>
    <table>
        <thead><tr><th>Descricao</th><th>Prazo</th><th>Status</th></tr></thead>
        <tbody>
        @forelse($atareuniao->Acoes as $acao)
            <tr>
                <td>{{ $acao->descricao ?: $acao->acao }}</td>
                <td>{{ $acao->prazo ?: 'Nao informado' }}</td>
                <td>{{ $acao->status }}</td>
            </tr>
        @empty
            <tr><td colspan="3">Nenhuma pendencia registrada.</td></tr>
        @endforelse
        </tbody>
    </table>

    <h2>Participantes</h2>
    <table>
        <thead><tr><th>Nome</th><th>Funcao</th></tr></thead>
        <tbody>
        @forelse($atareuniao->Participantes as $participante)
            <tr>
                <td>{{ $participante->nome }}</td>
                <td>{{ $participante->funcao }}</td>
            </tr>
        @empty
            <tr><td colspan="2">Nenhum participante registrado.</td></tr>
        @endforelse
        </tbody>
    </table>

    <div class="rodape">
        Link assinado de uso temporario. O acesso externo expira automaticamente em 24 horas ou pode ser revogado antes desse prazo.
    </div>
</main>
</body>
</html>

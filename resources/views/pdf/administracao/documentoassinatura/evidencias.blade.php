<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Evidências de Assinatura</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2d35; }
        h2 { margin: 0 0 8px; color: #174257; }
        h3 { margin: 16px 0 6px; color: #174257; }
        .meta { margin-bottom: 10px; }
        .muted { color: #5b6b74; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #d9d9d9; padding: 6px 8px; font-size: 10px; vertical-align: top; }
        th { background: #f3f6f8; text-align: left; }
        .section { margin-top: 12px; }
        .chip { display: inline-block; padding: 2px 6px; border: 1px solid #d9d9d9; border-radius: 999px; margin-right: 6px; }
        .break { word-break: break-all; }
    </style>
</head>
<body>
    <h2>Evidências de Assinatura Digital</h2>
    <div class="meta">
        <div><strong>Gerado em (UTC):</strong> {{ $evidencias['gerado_em_utc'] ?? '' }}</div>
        <div><strong>Empresa ID:</strong> {{ $evidencias['empresa_id'] ?? '' }}</div>
    </div>

    <h3>Documento</h3>
    <table>
        <tbody>
            <tr><th>ID</th><td>{{ $evidencias['documento']['id'] ?? '' }}</td></tr>
            <tr><th>Token</th><td class="break">{{ $evidencias['documento']['token'] ?? '' }}</td></tr>
            <tr><th>Tipo</th><td>{{ $evidencias['documento']['tipo_documento'] ?? '' }}</td></tr>
            <tr><th>Status</th><td>{{ $evidencias['documento']['status'] ?? '' }}</td></tr>
            <tr><th>Data expiração</th><td>{{ $evidencias['documento']['data_expiracao'] ?? '' }}</td></tr>
            <tr><th>Hash do PDF (SHA-256)</th><td class="break">{{ $evidencias['documento']['hash_sha256'] ?? '' }}</td></tr>
            <tr><th>Arquivo ID</th><td>{{ $evidencias['documento']['arquivo_id'] ?? '' }}</td></tr>
            <tr><th>Arquivo assinado ID</th><td>{{ $evidencias['documento']['arquivo_assinado_id'] ?? '' }}</td></tr>
            <tr><th>Solicitante ID</th><td>{{ $evidencias['documento']['solicitante_id'] ?? '' }}</td></tr>
            <tr><th>Último consentimento (UTC)</th><td>{{ $evidencias['documento']['consentimento_ultimo_em'] ?? '' }}</td></tr>
            <tr><th>Último signatário ID</th><td>{{ $evidencias['documento']['consentimento_ultimo_signatario_id'] ?? '' }}</td></tr>
        </tbody>
    </table>

    <h3>Signatários</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Email</th>
                <th>CPF</th>
                <th>Status</th>
                <th>IP</th>
                <th>Data assinatura (UTC)</th>
                <th>Consentimento (UTC)</th>
                <th>Hash evidência</th>
            </tr>
        </thead>
        <tbody>
            @forelse(($evidencias['signatarios'] ?? []) as $s)
                <tr>
                    <td>{{ $s['id'] ?? '' }}</td>
                    <td>{{ $s['nome'] ?? '' }}</td>
                    <td>{{ $s['email'] ?? '' }}</td>
                    <td>{{ $s['cpf'] ?? '' }}</td>
                    <td>{{ $s['status'] ?? '' }}</td>
                    <td>{{ $s['ip'] ?? '' }}</td>
                    <td>{{ $s['data_assinatura_utc'] ?? '' }}</td>
                    <td>{{ $s['consentimento_em'] ?? '' }}</td>
                    <td class="break">{{ $s['hash_evidencia'] ?? '' }}</td>
                </tr>
            @empty
                <tr><td colspan="9">Sem registros</td></tr>
            @endforelse
        </tbody>
    </table>

    <h3>Eventos</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Evento</th>
                <th>Data (UTC)</th>
                <th>Payload</th>
            </tr>
        </thead>
        <tbody>
            @forelse(($evidencias['eventos'] ?? []) as $e)
                <tr>
                    <td>{{ $e['id'] ?? '' }}</td>
                    <td>{{ $e['evento'] ?? '' }}</td>
                    <td>{{ $e['created_at_utc'] ?? '' }}</td>
                    <td class="break">{{ json_encode($e['payload'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</td>
                </tr>
            @empty
                <tr><td colspan="4">Sem registros</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="section muted">
        Documento gerado eletronicamente para auditoria interna.
    </div>
</body>
</html>

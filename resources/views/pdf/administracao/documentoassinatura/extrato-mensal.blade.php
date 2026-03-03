<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Extrato Mensal de Assinaturas</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        h2 { margin: 0 0 12px; color: #174257; }
        .meta { margin-bottom: 14px; }
        .meta strong { color: #174257; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #d9d9d9; padding: 6px 8px; font-size: 11px; }
        th { background: #f3f6f8; text-align: left; }
        .totais { margin-top: 12px; }
    </style>
</head>
<body>
    <h2>Extrato Mensal de Assinaturas Digitais</h2>
    <div class="meta">
        <div><strong>Competência:</strong> {{ $dados['resumo']['competencia'] ?? '—' }}</div>
        <div><strong>Gerado em:</strong> {{ $dados['gerado_em'] ?? '' }}</div>
    </div>

    <div class="totais">
        <strong>Limite mensal:</strong> {{ $dados['resumo']['limite_mensal'] === null ? 'Sem limite' : $dados['resumo']['limite_mensal'] }}<br>
        <strong>Usadas:</strong> {{ $dados['resumo']['usadas'] ?? 0 }}<br>
        <strong>Restantes:</strong> {{ $dados['resumo']['restantes'] === null ? 'Ilimitado' : $dados['resumo']['restantes'] }}<br>
        <strong>Percentual de uso:</strong> {{ $dados['resumo']['percentual_uso'] === null ? '—' : ($dados['resumo']['percentual_uso'] . '%') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Tipo de documento</th>
                <th>Quantidade</th>
            </tr>
        </thead>
        <tbody>
            @forelse(($dados['resumo']['extrato_por_tipo'] ?? []) as $item)
                <tr>
                    <td>{{ $item['label'] ?? ($item['tipo_documento'] ?? '—') }}</td>
                    <td>{{ $item['total'] ?? 0 }}</td>
                </tr>
            @empty
                <tr>
                    <td>Sem registros</td>
                    <td>0</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>


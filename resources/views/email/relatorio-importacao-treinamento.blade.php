<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Importação</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }

        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 3px solid #4CAF50;
            margin-bottom: 30px;
        }

        .header.error {
            border-bottom-color: #f44336;
        }

        h1 {
            color: #4CAF50;
            margin: 0;
            font-size: 24px;
        }

        h1.error {
            color: #f44336;
        }

        .info-box {
            background-color: #f9f9f9;
            border-left: 4px solid #4CAF50;
            padding: 15px;
            margin: 20px 0;
        }

        .info-box.error {
            border-left-color: #f44336;
            background-color: #ffebee;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .label {
            font-weight: bold;
            color: #555;
        }

        .value {
            color: #222;
        }

        .stats {
            display: flex;
            justify-content: space-around;
            margin: 30px 0;
            flex-wrap: wrap;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            min-width: 150px;
            margin: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .stat-card.success {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
        }

        .stat-card.error {
            background: linear-gradient(135deg, #f44336 0%, #da190b 100%);
        }

        .stat-number {
            font-size: 36px;
            font-weight: bold;
            margin: 10px 0;
        }

        .stat-label {
            font-size: 14px;
            opacity: 0.9;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            color: #777;
            font-size: 12px;
        }

        .alert {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }

        .alert.error {
            background-color: #f8d7da;
            border-color: #f44336;
            color: #721c24;
        }

        .attachment-info {
            background-color: #e3f2fd;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }

        .attachment-info strong {
            color: #1976D2;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header {{ $com_erro ? 'error' : '' }}">
        <h1 class="{{ $com_erro ? 'error' : '' }}">
            {{ $com_erro ? '⚠️ Relatório de Importação - COM ERROS' : '✅ Relatório de Importação - Concluído' }}
        </h1>
        <p style="margin: 10px 0 0 0; color: #666;">Sistema de Importação de Treinamentos</p>
    </div>

    @if($com_erro)
        <div class="alert error">
            <strong>⚠️ Atenção:</strong> A importação foi concluída com erros. Por favor, verifique o relatório em anexo
            para mais detalhes.
        </div>
    @endif

    <div class="info-box {{ $com_erro ? 'error' : '' }}">
        <div class="info-row">
            <span class="label">📅 Data do Processamento:</span>
            <span class="value">{{ $data_processamento }}</span>
        </div>
        <div class="info-row">
            <span class="label">🏢 Empresa ID:</span>
            <span class="value">{{ $empresa_id }}</span>
        </div>
        <div class="info-row">
            <span class="label">📄 Arquivo Importado:</span>
            <span class="value">{{ $arquivo }}.json</span>
        </div>
    </div>

    <h2 style="color: #333; margin-top: 30px;">📊 Estatísticas da Importação</h2>

    <div class="stats">
        <div class="stat-card">
            <div class="stat-label">TOTAL DE REGISTROS</div>
            <div class="stat-number">{{ $total_registros }}</div>
        </div>

        <div class="stat-card success">
            <div class="stat-label">✓ SUCESSOS</div>
            <div class="stat-number">{{ $sucessos }}</div>
            <div class="stat-label">{{ $total_registros > 0 ? round(($sucessos / $total_registros) * 100, 1) : 0 }}%
            </div>
        </div>

        <div class="stat-card error">
            <div class="stat-label">✗ FALHAS</div>
            <div class="stat-number">{{ $falhas }}</div>
            <div class="stat-label">{{ $total_registros > 0 ? round(($falhas / $total_registros) * 100, 1) : 0 }}%</div>
        </div>
    </div>

    <div class="attachment-info">
        <strong>📎 Arquivo Anexado:</strong><br>
        O relatório completo em formato CSV está anexado a este e-mail.<br>
        <small>O arquivo contém detalhes de todos os registros processados, incluindo nome, CPF, treinamento, datas e
            status.</small>
    </div>

    @if($falhas > 0)
        <div class="alert">
            <strong>💡 Dica:</strong> Abra o arquivo CSV anexado e filtre pela coluna "Status" para visualizar apenas os
            registros com falha e suas respectivas mensagens de erro.
        </div>
    @endif

    <div class="footer">
        <p>Este é um e-mail automático do sistema de importação de treinamentos.</p>
        <p>Por favor, não responda este e-mail.</p>
        <p style="margin-top: 10px;">
            <strong>Gerado em:</strong> {{ $data_processamento }}<br>
            <strong>Caminho do arquivo:</strong> {{ basename($caminho_csv) }}
        </p>
    </div>
</div>
</body>
</html>

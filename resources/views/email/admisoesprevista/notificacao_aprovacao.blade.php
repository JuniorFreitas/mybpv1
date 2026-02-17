<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificação de Admissão Prevista</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background-color: #174257;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }

        .content {
            background-color: #f9f9f9;
            padding: 20px;
            border: 1px solid #ddd;
            border-top: none;
        }

        .info-box {
            background-color: white;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #174257;
        }

        .info-row {
            margin: 10px 0;
        }

        .label {
            font-weight: bold;
            color: #174257;
        }

        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 12px;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #174257;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>{{ config('app.name') }}</h2>
        <p>Notificação de Admissão Prevista</p>
    </div>

    <div class="content">
        <p>Olá,</p>

        @php
        $mensagem = match($tipo) {
        'criacao' => 'Uma nova <strong>Admissão Prevista</strong> foi criada no sistema.',
        'pendente_aprovacao_extra' => 'Uma <strong>Admissão Prevista</strong> está aguardando sua <strong>Aprovação Extra</strong>.',
        'pendente_aprovacao_rh' => 'Uma <strong>Admissão Prevista</strong> está aguardando <strong>Aprovação do RH</strong>.',
        'reprovado_gestor' => 'Uma <strong>Admissão Prevista</strong> foi <strong style="color: #d9534f;">REPROVADA</strong> pelo Gestor.',
        'reprovado_aprovacao_extra' => 'Uma <strong>Admissão Prevista</strong> foi <strong style="color: #d9534f;">REPROVADA</strong> pela Aprovação Extra.',
        'reprovado_rh' => 'Uma <strong>Admissão Prevista</strong> foi <strong style="color: #d9534f;">REPROVADA</strong> pelo RH.',
        'cancelado' => 'Uma <strong>Admissão Prevista</strong> foi <strong style="color: #d9534f;">CANCELADA</strong>.',
        'aprovado_final' => 'Uma <strong>Admissão Prevista</strong> foi <strong style="color: #5cb85c;">APROVADA</strong> com sucesso!',
        // Mantém compatibilidade
        'aprovacao_extra' => 'Uma <strong>Admissão Prevista</strong> está aguardando sua <strong>Aprovação Extra</strong>.',
        'aprovacao_rh' => 'Uma <strong>Admissão Prevista</strong> está aguardando <strong>Aprovação do RH</strong>.',
        'aprovacao' => 'Uma <strong>Admissão Prevista</strong> foi <strong>aprovada</strong>.',
        default => 'Você recebeu uma notificação sobre uma <strong>Admissão Prevista</strong>.',
        };
        @endphp

        <p>{!! $mensagem !!}</p>

        <div class="info-box">
            <div class="info-row">
                <span class="label">Código:</span> #{{ $admissao->id }}
            </div>
            <div class="info-row">
                <span class="label">Cargo:</span> {{ $admissao->Cargo ? $admissao->Cargo->nome : '' }}
            </div>
            <div class="info-row">
                <span class="label">Data de Admissão:</span> {{ $admissao->data_admissao }}
            </div>
            <div class="info-row">
                <span class="label">Tipo de Contrato:</span> {{ $admissao->tipo_contrato }}
            </div>
            <div class="info-row">
                <span class="label">Centro de Custo:</span> {{ $admissao->CentroCusto ? $admissao->CentroCusto->label : '' }}
            </div>
            @if($admissao->salario_format)
            <div class="info-row">
                <span class="label">Salário:</span> R$ {{ $admissao->salario_format }}
            </div>
            @endif

            @if($admissao->status_aprovacao === 'reprovado' && $admissao->obs_reprovacao)
            <div class="info-row" style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;">
                <span class="label">Motivo da Reprovação (Gestor):</span><br>
                {{ $admissao->obs_reprovacao }}
            </div>
            @endif

            @if($admissao->status_aprovacao_extra === 'reprovado' && $admissao->obs_aprovacao_extra)
            <div class="info-row" style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;">
                <span class="label">Motivo da Reprovação (Aprovação Extra):</span><br>
                {{ $admissao->obs_aprovacao_extra }}
            </div>
            @endif

            @if($admissao->status_aprovacao_rh === 'reprovado' && $admissao->obs_rh)
            <div class="info-row" style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;">
                <span class="label">Motivo da Reprovação (RH):</span><br>
                {{ $admissao->obs_rh }}
            </div>
            @endif

            @if(in_array($tipo, ['aprovado_final', 'aprovacao']) && $admissao->obs_aprovacao_extra)
            <div class="info-row" style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;">
                <span class="label">Observação:</span><br>
                {{ $admissao->obs_aprovacao_extra }}
            </div>
            @endif
        </div>

        @if(!in_array($tipo, ['reprovado_gestor', 'reprovado_aprovacao_extra', 'reprovado_rh', 'cancelado', 'aprovado_final']))
        <p style="text-align: center;">
            <a href="{{ route('g.movimentacao.solicitacao_admissoes.index') }}" class="btn">
                Visualizar no Sistema
            </a>
        </p>
        @endif

        <p>Esta é uma notificação automática. Por favor, não responda a este e-mail.</p>
    </div>

    <div class="footer">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Todos os direitos reservados.</p>
    </div>
</body>

</html>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>{{ $dados['subject'] ?? 'Pendencia proxima do vencimento' }}</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; color: #1f2933; line-height: 1.5;">
    <p>Ola, {{ $dados['nome_responsavel'] ?? $dados['nome'] ?? 'responsavel' }}.</p>

    <p>{{ $dados['mensagem_contexto'] ?? 'A pendencia abaixo esta proxima do vencimento:' }}</p>

    <table cellpadding="6" cellspacing="0" border="0" style="border-collapse: collapse; width: 100%; max-width: 720px;">
        <tr>
            <td style="font-weight: bold; width: 160px;">Ata</td>
            <td>{{ $dados['ata'] ?? 'Nao informado' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Pendencia</td>
            <td>{{ $dados['pendencia'] ?? 'Nao informado' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Prazo</td>
            <td>{{ $dados['prazo'] ?? 'Nao informado' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Status atual</td>
            <td>{{ $dados['status'] ?? 'Nao informado' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Prioridade</td>
            <td>{{ $dados['prioridade'] ?? 'Nao informado' }}</td>
        </tr>
    </table>

    <p>
        Acesse o sistema para atualizar o andamento, anexar evidencias ou solicitar ajuste de prazo:
        <br>
        <a href="{{ $dados['link'] ?? '#' }}">{{ $dados['link'] ?? 'Link nao disponivel' }}</a>
    </p>

    <p>Esta mensagem foi enviada automaticamente pelo Sistema de Gestao de Atas e Pendencias.</p>

    <p>Atenciosamente,<br>Sistema de Gestao de Atas e Pendencias</p>
</body>
</html>

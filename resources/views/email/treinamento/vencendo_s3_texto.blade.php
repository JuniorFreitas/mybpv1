========================================
MYBP SISTEMA - RELATÓRIO DE TREINAMENTOS
========================================

EMPRESA: {{ $dados['empresa'] }}
DATA DE GERAÇÃO: {{ $dados['data_geracao'] }}

========================================
RESUMO EXECUTIVO
========================================

Treinamentos Vencidos: {{ $dados['total_vencidos'] }}
Próximos a Vencer (30 dias): {{ $dados['total_proximos'] }}
Em Atenção (60 dias): {{ $dados['total_atencao'] }}

@if($dados['total_vencidos'] > 0)
    ========================================
    🚨 TREINAMENTOS VENCIDOS
    ========================================

    @foreach($dados['categorias']['VENCIDO'] as $funcionario)
        {{ $funcionario['funcionario']['nome'] }} - {{ $funcionario['funcionario']['cargo'] }} - Segmento: {{ $funcionario['funcionario']['segmento'] ?? 'N/A' }}
        @foreach($funcionario['treinamentos'] as $treinamento)
            • {{ $treinamento['vencimento_nome'] }} (Segmento: {{ $treinamento['segmento'] ?? ($funcionario['funcionario']['segmento'] ?? 'N/A') }}): {{ date('d/m/Y', strtotime($treinamento['data_vencimento'])) }} ({{ $treinamento['status_texto'] }})
        @endforeach

    @endforeach
@endif

@if($dados['total_proximos'] > 0)
    ========================================
    ⚠️ PRÓXIMOS A VENCER (30 DIAS)
    ========================================

    @foreach($dados['categorias']['PROXIMO'] as $funcionario)
        {{ $funcionario['funcionario']['nome'] }} - {{ $funcionario['funcionario']['cargo'] }} - Segmento: {{ $funcionario['funcionario']['segmento'] ?? 'N/A' }}
        @foreach($funcionario['treinamentos'] as $treinamento)
            • {{ $treinamento['vencimento_nome'] }} (Segmento: {{ $treinamento['segmento'] ?? ($funcionario['funcionario']['segmento'] ?? 'N/A') }}): {{ date('d/m/Y', strtotime($treinamento['data_vencimento'])) }} ({{ $treinamento['status_texto'] }})
        @endforeach

    @endforeach
@endif

@if($dados['total_atencao'] > 0)
    ========================================
    ℹ️ EM ATENÇÃO (60 DIAS)
    ========================================

    @foreach($dados['categorias']['ATENCAO'] as $funcionario)
        {{ $funcionario['funcionario']['nome'] }} - {{ $funcionario['funcionario']['cargo'] }} - Segmento: {{ $funcionario['funcionario']['segmento'] ?? 'N/A' }}
        @foreach($funcionario['treinamentos'] as $treinamento)
            • {{ $treinamento['vencimento_nome'] }} (Segmento: {{ $treinamento['segmento'] ?? ($funcionario['funcionario']['segmento'] ?? 'N/A') }}): {{ date('d/m/Y', strtotime($treinamento['data_vencimento'])) }} ({{ $treinamento['status_texto'] }})
        @endforeach

    @endforeach
@endif

========================================
RELATÓRIO DETALHADO
========================================

{{ $dados['arquivo_s3']['instrucoes'] }}

Para acessar o arquivo CSV completo, acesse o link fornecido
na versão HTML deste e-mail.

{{ $dados['arquivo_s3']['validade'] }}

========================================
INFORMAÇÕES DO SISTEMA
========================================

Este é um relatório automático gerado pelo Sistema MyBP.
Para dúvidas ou suporte técnico, entre em contato com nossa equipe.

Sistema MyBP - Gestão de Pessoas e Treinamentos
© {{ date('Y') }} - Todos os direitos reservados

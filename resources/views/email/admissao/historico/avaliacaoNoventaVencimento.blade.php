<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avaliações de 90 Dias</title>
</head>
<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: #f7f7f7; color: #333; line-height: 1.6;">
    <div style="width: 100%; max-width: 800px; margin: 0 auto; padding: 20px; background-color: #fff; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
        
        <!-- Cabeçalho -->
        <div style="background-color: #003755; color: white; padding: 15px; text-align: center; margin-bottom: 20px; border-radius: 5px;">
            <h1 style="margin: 0 0 10px 0;">Avaliações de 90 Dias</h1>
            <p style="margin: 5px 0;">Relatório de Vencimentos</p>
            <p style="margin: 5px 0;">Gerado em: {{ date('d/m/Y H:i') }}</p>
        </div>

    <!-- Saudação (substituída mais abaixo para usar contadores) -->

        @php
            $totalVencidos = collect($dados['vencimentos'])->where('status', 'VENCIDO')->count();
            $totalVenceHoje = collect($dados['vencimentos'])->where('status', 'VENCE HOJE')->count();
            $totalAVencer = collect($dados['vencimentos'])->where('status', 'A VENCER')->count();
            $totalGeral = count($dados['vencimentos']);
            $totalSemAvaliacao = collect($dados['vencimentos'])->where('qnt_avaliacoes', 0)->count();
            $totalUmaAvaliacao = collect($dados['vencimentos'])->where('qnt_avaliacoes', 1)->count();
        @endphp

        <!-- Saudação aprimorada -->
        <p style="margin: 0 0 8px;">Olá, <strong>{{ $dados['usuario']->nome }}</strong>,</p>
        <p style="margin: 0 0 20px; color: #555;">
            Esperamos que esteja bem. Identificamos <strong>{{ $totalGeral }}</strong> avaliação(ões) em atenção: 
            <strong>{{ $totalVencidos }}</strong> vencida(s), 
            <strong>{{ $totalVenceHoje }}</strong> que vence(m) hoje e 
            <strong>{{ $totalAVencer }}</strong> a vencer nos próximos 30 dias. Para não perder prazos, revise o resumo abaixo e programe as ações necessárias.
        </p>

        <!-- Resumo -->
        <div style="background-color: #f8f9fa; padding: 15px; margin-bottom: 20px; border-radius: 5px; border-left: 5px solid #003755;">
            <div style="font-size: 18px; font-weight: bold; margin-bottom: 10px;">Resumo</div>
            
            <table cellpadding="0" cellspacing="0" border="0" width="100%">
                <tr>
                    <td width="25%" align="center" style="padding: 10px;">
                        <div style="background-color: #dc3545; color: white; padding: 10px; border-radius: 5px; text-align: center;">
                            <div style="font-size: 24px; font-weight: bold;">{{ $totalVencidos }}</div>
                            <div>Vencidas</div>
                        </div>
                    </td>
                    <td width="25%" align="center" style="padding: 10px;">
                        <div style="background-color: #FFC324; color: black; padding: 10px; border-radius: 5px; text-align: center;">
                            <div style="font-size: 24px; font-weight: bold;">{{ $totalVenceHoje }}</div>
                            <div>Vence Hoje</div>
                        </div>
                    </td>
                    <td width="25%" align="center" style="padding: 10px;">
                        <div style="background-color: #17a2b8; color: white; padding: 10px; border-radius: 5px; text-align: center;">
                            <div style="font-size: 24px; font-weight: bold;">{{ $totalAVencer }}</div>
                            <div>A vencer (30 dias)</div>
                        </div>
                    </td>
                    <td width="25%" align="center" style="padding: 10px;">
                        <div style="background-color: #17a2b8; color: white; padding: 10px; border-radius: 5px; text-align: center;">
                            <div style="font-size: 24px; font-weight: bold;">{{ $totalGeral }}</div>
                            <div>Total</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Status de Avaliações Realizadas -->
        <div style="background-color: #f0f2f5; padding: 12px 15px; margin: -10px 0 20px; border-radius: 5px; border-left: 4px solid #6c757d;">
            <div style="font-weight: 600; margin-bottom: 6px; color: #495057;">Progresso das Avaliações</div>
            <div style="display: flex; gap: 16px; flex-wrap: wrap;">
                <span style="background-color: #e9ecef; color: #495057; padding: 6px 10px; border-radius: 20px; font-size: 13px;">
                    Nenhuma avaliação: <strong>{{ $totalSemAvaliacao }}</strong>
                </span>
                <span style="background-color: #e9ecef; color: #495057; padding: 6px 10px; border-radius: 20px; font-size: 13px;">
                    1ª avaliação realizada: <strong>{{ $totalUmaAvaliacao }}</strong>
                </span>
            </div>
        </div>

        <!-- Orientações e Atenção aos Prazos -->
        <div style="background-color: #fff3cd; color: #856404; padding: 16px; margin: 0 0 20px; border-radius: 5px; border-left: 5px solid #ffe69c;">
            <div style="font-weight: 700; margin-bottom: 8px;">Atenção aos prazos</div>
            <p style="margin: 6px 0 10px;">
                As avaliações de 90 dias possuem <strong>dois marcos</strong> (1ª e 2ª avaliação). Para não perder os prazos:
            </p>
            <ul style="margin: 0 0 0 18px; padding: 0;">
                <li style="margin: 6px 0;">Priorize colaboradores com status <strong>VENCIDO</strong> e <strong>VENCE HOJE</strong>.</li>
                <li style="margin: 6px 0;">Para itens <strong>A VENCER</strong>, programe a avaliação com antecedência (idealmente <strong>≥ 7 dias</strong> antes do prazo).</li>
                <li style="margin: 6px 0;">Se <strong>nenhuma avaliação</strong> foi feita, realize a <strong>1ª avaliação</strong>. Se a <strong>1ª já foi feita</strong>, conclua a <strong>2ª avaliação</strong> até a data limite.</li>
                <li style="margin: 6px 0;"><strong>Novidade:</strong> No relatório Excel, utilize a coluna <strong>"Link Avaliação"</strong> para acessar diretamente o formulário online de cada colaborador.</li>
                <li style="margin: 6px 0;">Use o botão abaixo para baixar o relatório detalhado e acompanhar os prazos.</li>
            </ul>
        </div>

        <!-- Lista resumida (até 15 itens) com cargo e função -->
        @php
            $listaResumida = collect($dados['vencimentos'])->take(15);
        @endphp
        @if($listaResumida->count() > 0)
        <div style="background-color: #ffffff; border: 1px solid #eee; border-radius: 6px; margin: 0 0 20px;">
            <div style="padding: 12px 16px; border-bottom: 1px solid #eee; font-weight: 600; color: #003755;">Lista resumida</div>
            <div style="overflow-x: auto;">
                <table cellpadding="6" cellspacing="0" border="0" width="100%" style="border-collapse: collapse; min-width: 600px;">
                    <thead>
                        <tr style="background-color: #f2f4f7; color: #333;">
                            <th align="left" style="padding: 8px 10px;">Colaborador</th>
                            <th align="left" style="padding: 8px 10px;">Cargo</th>
                            <th align="left" style="padding: 8px 10px;">Função</th>
                            <th align="left" style="padding: 8px 10px;">Centro de Custo</th>
                            <th align="left" style="padding: 8px 10px;">Vencimento</th>
                            <th align="left" style="padding: 8px 10px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($listaResumida as $item)
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 8px 10px;">{{ $item['colaborador'] }}</td>
                                <td style="padding: 8px 10px;">{{ $item['cargo'] ?? '-' }}</td>
                                <td style="padding: 8px 10px;">{{ $item['funcao'] ?? '-' }}</td>
                                <td style="padding: 8px 10px;">{{ $item['centro_custo'] ?? '-' }}</td>
                                <td style="padding: 8px 10px;">{{ $item['prazo_vencido'] }}</td>
                                <td style="padding: 8px 10px;">{{ $item['status'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if(count($dados['vencimentos']) > $listaResumida->count())
                <div style="padding: 10px 16px; color: #666; font-size: 12px;">Exibindo os primeiros {{ $listaResumida->count() }} de {{ count($dados['vencimentos']) }} itens. Consulte o Excel para a lista completa.</div>
            @endif
        </div>
        @endif

        <!-- Relatório Completo -->
        @if(isset($dados['arquivo_s3']))
        <div style="background-color: #e7f3ff; padding: 20px; margin: 20px 0; border-radius: 5px; border-left: 5px solid #003755; text-align: center;">
            <div style="font-size: 18px; font-weight: bold; margin-bottom: 10px; color: #003755;">
                📊 Relatório Detalhado Disponível
            </div>
            <p style="margin: 10px 0; color: #555;">
                Acesse o relatório completo em Excel com todos os detalhes das avaliações, incluindo cargo, função, dias em atraso, observações e <strong>links diretos para realizar cada avaliação online</strong>.
            </p>
            <a href="{{ $dados['arquivo_s3']['url'] }}" 
               style="display: inline-block; background-color: #003755; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 10px 0; font-weight: bold;">
                📥 Baixar Relatório Excel
            </a>
            <p style="margin: 10px 0; font-size: 12px; color: #888;">
                Tamanho: {{ number_format($dados['arquivo_s3']['tamanho'] / 1024, 2) }} KB | Válido por 7 dias
            </p>
        </div>
        @endif

        <!-- Rodapé -->
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #888; font-size: 12px;">
            <div style="margin-bottom: 15px;">
                <img src="https://sistema.mybp.com.br/images/bpin_mybp_color.svg" alt="Logo MyBP" style="height: 35px; width: auto;">
            </div>
            <p>Este é um relatório automático gerado pelo Sistema MyBP.</p>
            <p style="margin: 5px 0;"><strong>MyBP - Business Partners Serviços Empresariais</strong></p>
            <p style="margin: 5px 0;">© {{ date('Y') }} - Todos os direitos reservados</p>
        </div>

    </div>
</body>
</html>

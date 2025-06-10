<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Treinamentos</title>
</head>
<body
    style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: #f7f7f7; color: #333; line-height: 1.6;">
<div
    style="width: 100%; max-width: 800px; margin: 0 auto; padding: 20px; background-color: #fff; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
    <!-- Cabeçalho -->
    <div
        style="background-color: #2c3e50; color: white; padding: 15px; text-align: center; margin-bottom: 20px; border-radius: 5px;">
        <h1 style="margin: 0 0 10px 0;">Relatório de Treinamentos</h1>
        <p style="margin: 5px 0;">{{ $dados['empresa'] }}</p>
        <p style="margin: 5px 0;">Gerado em: {{ $dados['data_geracao'] }}</p>
    </div>

    <!-- Resumo -->
    <div
        style="background-color: #f8f9fa; padding: 15px; margin-bottom: 20px; border-radius: 5px; border-left: 5px solid #2c3e50;">
        <div style="font-size: 18px; font-weight: bold; margin-bottom: 10px;">Resumo</div>

        <table cellpadding="0" cellspacing="0" border="0" width="100%">
            <tr>
                <td width="33%" align="center" style="padding: 10px;">
                    <div
                        style="background-color: #dc3545; color: white; padding: 10px; border-radius: 5px; text-align: center;">
                        <div style="font-size: 24px; font-weight: bold;">{{ $dados['total_vencidos'] }}</div>
                        <div>Vencidos</div>
                    </div>
                </td>
                <td width="33%" align="center" style="padding: 10px;">
                    <div
                        style="background-color: #FFC324; color: black; padding: 10px; border-radius: 5px; text-align: center;">
                        <div style="font-size: 24px; font-weight: bold;">{{ $dados['total_proximos'] }}</div>
                        <div>Próximos (30 dias)</div>
                    </div>
                </td>
                <td width="33%" align="center" style="padding: 10px;">
                    <div
                        style="background-color: #17a2b8; color: white; padding: 10px; border-radius: 5px; text-align: center;">
                        <div style="font-size: 24px; font-weight: bold;">{{ $dados['total_atencao'] }}</div>
                        <div>Atenção (60 dias)</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Seção de Funcionários -->
    <h2 style="color: #2c3e50; border-bottom: 1px solid #eee; padding-bottom: 10px;">Funcionários com Treinamentos em
        Alerta</h2>

    @php
        // Combinar todos os funcionários de todas as categorias
        $todosFuncionarios = [];
        foreach(['VENCIDO', 'PROXIMO', 'ATENCAO'] as $categoria) {
            if(isset($dados['categorias'][$categoria])) {
                foreach($dados['categorias'][$categoria] as $feedbackId => $funcionario) {
                    if(!isset($todosFuncionarios[$feedbackId])) {
                        $todosFuncionarios[$feedbackId] = [
                            'funcionario' => $funcionario['funcionario'],
                            'treinamentos' => []
                        ];
                    }

                    // Adicionar treinamentos
                    foreach($funcionario['treinamentos'] as $treinamento) {
                        // Evitar duplicados
                        $todosFuncionarios[$feedbackId]['treinamentos'][$treinamento['id']] = $treinamento;
                    }
                }
            }
        }
    @endphp

    @foreach($todosFuncionarios as $feedbackId => $funcionario)
        <!-- Cartão de Funcionário -->
        <div
            style="margin: 30px 0; border: 1px solid #ddd; border-radius: 5px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <!-- Cabeçalho do Funcionário -->
            <div style="padding: 15px; background-color: #f8f9fa; border-bottom: 1px solid #ddd; position: relative;">
                <div
                    style="font-size: 1.1rem; font-weight: bold; margin-bottom: 5px;">{{ $funcionario['funcionario']['nome'] }}</div>
                <div style="color: #555;">CPF: {{ $funcionario['funcionario']['cpf'] }}</div>
            </div>

            <!-- Informações do Funcionário -->
            <div
                style="padding: 15px; background-color: #f8f9fa; border-bottom: 1px solid #ddd; display: flex; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 250px; margin-bottom: 10px;">
                    <strong>Cargo:</strong> {{ $funcionario['funcionario']['cargo'] }}
                </div>
                <div style="flex: 1; min-width: 250px; margin-bottom: 10px;">
                    <strong>Centro de Custo:</strong>
                    @if(isset($funcionario['funcionario']['centro_custo']))
                        {{ $funcionario['funcionario']['centro_custo'] }}
                    @else
                        N/A
                    @endif
                </div>
            </div>

            <!-- Tabela de Treinamentos -->
            <table cellpadding="0" cellspacing="0" border="0" width="100%" style="border-collapse: collapse;">
                <thead>
                <tr>
                    <th style="background-color: #e9ecef; text-align: left; padding: 12px; border: 1px solid #ddd;">
                        Treinamento
                    </th>
                    <th style="background-color: #e9ecef; text-align: left; padding: 12px; border: 1px solid #ddd;">Data
                        Treinamento
                    </th>
                    <th style="background-color: #e9ecef; text-align: left; padding: 12px; border: 1px solid #ddd;">Data
                        Vencimento
                    </th>
                    <th style="background-color: #e9ecef; text-align: left; padding: 12px; border: 1px solid #ddd;">
                        Status
                    </th>
                    <th style="background-color: #e9ecef; text-align: center; padding: 12px; border: 1px solid #ddd;">
                        Exibi na Carteira
                    </th>
                </tr>
                </thead>
                <tbody>
                @foreach($funcionario['treinamentos'] as $treinamento)
                    <tr style="background-color: {{ $loop->even ? '#f9f9f9' : '#ffffff' }};">
                        <td style="padding: 12px; border: 1px solid #ddd; vertical-align: middle;">{{ $treinamento['vencimento_nome'] }}</td>
                        <td style="padding: 12px; border: 1px solid #ddd; vertical-align: middle;">{{ date('d/m/Y', strtotime($treinamento['data_treinamento'])) }}</td>
                        <td style="padding: 12px; border: 1px solid #ddd; vertical-align: middle;">{{ date('d/m/Y', strtotime($treinamento['data_vencimento'])) }}</td>
                        <td style="padding: 12px; border: 1px solid #ddd; vertical-align: middle;">
                            @if($treinamento['dias_vencer'] < 0)
                                <span
                                    style="padding: 5px 10px; border-radius: 15px; font-size: 12px; font-weight: bold; display: inline-block; text-align: center; min-width: 120px; background-color: #dc3545; color: white;">Vencido a {{ abs($treinamento['dias_vencer']) }} dia(s)</span>
                            @elseif($treinamento['dias_vencer'] <= 30)
                                <span
                                    style="padding: 5px 10px; border-radius: 15px; font-size: 12px; font-weight: bold; display: inline-block; text-align: center; min-width: 120px; background-color: #FFC324; color: black;">Vence em {{ $treinamento['dias_vencer'] }} dia(s)</span>
                            @elseif($treinamento['dias_vencer'] <= 60)
                                <span
                                    style="padding: 5px 10px; border-radius: 15px; font-size: 12px; font-weight: bold; display: inline-block; text-align: center; min-width: 120px; background-color: #17a2b8; color: white;">Vence em {{ $treinamento['dias_vencer'] }} dia(s)</span>
                            @else
                                <span
                                    style="padding: 5px 10px; border-radius: 15px; font-size: 12px; font-weight: bold; display: inline-block; text-align: center; min-width: 120px; background-color: #28a745; color: white;">Em dia</span>
                            @endif
                        </td>
                        <td style="padding: 12px; border: 1px solid #ddd; vertical-align: middle; text-align: center; font-weight: bold;">
                            Sim
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endforeach

    <!-- Detalhes por Categoria (opcional - pode ser removido se preferir apenas a visão por funcionário) -->
    @if($dados['total_vencidos'] > 0)
        <div
            style="font-size: 18px; font-weight: bold; margin: 20px 0 10px 0; padding: 10px; border-radius: 5px; background-color: #dc3545; color: white;">
            Treinamentos Vencidos <span
                style="padding: 2px 8px; border-radius: 10px; margin-left: 5px; font-size: 14px; font-weight: normal; background-color: white; color: #dc3545;">{{ $dados['total_vencidos'] }}</span>
        </div>
        <p style="color: #555;">Os treinamentos vencidos necessitam de atenção imediata.</p>
    @endif

    @if($dados['total_proximos'] > 0)
        <div
            style="font-size: 18px; font-weight: bold; margin: 20px 0 10px 0; padding: 10px; border-radius: 5px; background-color: #FFC324; color: black;">
            Treinamentos Próximos a Vencer (30 dias) <span
                style="padding: 2px 8px; border-radius: 10px; margin-left: 5px; font-size: 14px; font-weight: normal; background-color: white; color: #FFC324;">{{ $dados['total_proximos'] }}</span>
        </div>
        <p style="color: #555;">Os treinamentos próximos a vencer devem ser programados em breve.</p>
    @endif

    @if($dados['total_atencao'] > 0)
        <div
            style="font-size: 18px; font-weight: bold; margin: 20px 0 10px 0; padding: 10px; border-radius: 5px; background-color: #17a2b8; color: white;">
            Treinamentos em Atenção (60 dias) <span
                style="padding: 2px 8px; border-radius: 10px; margin-left: 5px; font-size: 14px; font-weight: normal; background-color: white; color: #17a2b8;">{{ $dados['total_atencao'] }}</span>
        </div>
        <p style="color: #555;">Os treinamentos em atenção estão chegando perto do prazo de renovação.</p>
    @endif

    <!-- Rodapé -->
    <div
        style="margin-top: 30px; text-align: center; font-size: 12px; color: #777; padding-top: 15px; border-top: 1px solid #eee;">
        <p>Este é um e-mail automático. Por favor, não responda.</p>
        <p>© {{ date('Y') }} MyBP - Todos os direitos reservados</p>
    </div>
</div>
</body>
</html>

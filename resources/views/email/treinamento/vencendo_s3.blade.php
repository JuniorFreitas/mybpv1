<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Relatório de Treinamentos - {{ $dados['empresa'] }}</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
</head>
<body
    style="margin: 0; padding: 0; font-family: Arial, sans-serif; line-height: 1.6; color: #333333; background-color: #f4f4f4;">


<table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; background-color: #f4f4f4;">
    <tr>
        <td style="padding: 20px 0;">
            <!-- Wrapper -->
            <table role="presentation" cellspacing="0" cellpadding="0" border="0"
                   style="margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">

                <!-- Header -->
                <tr>
                    <td style="padding: 30px 20px; text-align: center;">
                        @if(!empty($dados['empresa_apelido']))
                            <img src="{{env('AWS_URL')}}/public/email_{{$dados['empresa_apelido']}}.jpg"
                                 style="width: 100%" alt=""> <br>
                        @else
                            <img src="{{env('AWS_URL')}}/public/email_bpse.jpg" style="width: 100%" alt=""> <br>
                        @endif
                        <h1 style="margin: 20px 0 10px 0; font-size: 24px; font-weight: bold;">📊 Relatório de
                            Treinamentos</h1>
                        <h2 style="margin: 0 0 15px 0; font-size: 20px; font-weight: normal;">{{ $dados['empresa'] }}</h2>
                        <p style="margin: 0; font-size: 14px; opacity: 0.9;">Gerado
                            em: {{ date('d/m/Y H:i', strtotime($dados['data_geracao'])) }}</p>
                    </td>
                </tr>

                <!-- Alerta de arquivo disponível -->
                <tr>
                    <td style="padding: 20px;">
                        <table role="presentation" cellspacing="0" cellpadding="0" border="0"
                               style="width: 100%; background-color: #cce7ff; border: 1px solid #99d6ff; border-radius: 4px;">
                            <tr>
                                <td style="padding: 15px; color: #0c5460;">
                                    <strong>📁 Arquivo Disponível para Download</strong><br>
                                    O arquivo CSV foi disponibilizado via link de download seguro.
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Seção de download -->
                <tr>
                    <td style="padding: 0 20px 20px 20px;">
                        <table role="presentation" cellspacing="0" cellpadding="0" border="0"
                               style="width: 100%; background-color: #e9ecef; border: 2px solid #007bff; border-radius: 8px;">
                            <tr>
                                <td style="padding: 20px; text-align: center;">
                                    <h3 style="margin: 0 0 15px 0; color: #333333; font-size: 18px;">📥 Download do
                                        Relatório Completo</h3>
                                    <p style="margin: 0 0 20px 0; color: #666666;">Clique no botão abaixo para fazer o
                                        download do arquivo CSV com todos os dados detalhados:</p>

                                    <!-- Botão de download -->
                                    <table role="presentation" cellspacing="0" cellpadding="0" border="0"
                                           style="margin: 0 auto;">
                                        <tr>
                                            <td style="background-color: #007bff; border-radius: 5px;">
                                                <a href="{{ $dados['arquivo_s3']['url'] }}"
                                                   style="display: inline-block; padding: 12px 30px; color: #ffffff; text-decoration: none; font-weight: bold; font-size: 16px;">
                                                    📄 Baixar {{ $dados['arquivo_s3']['nome_arquivo'] }}
                                                </a>
                                            </td>
                                        </tr>
                                    </table>

                                    <p style="margin: 15px 0 0 0; font-size: 15px; color: #6c757d;">
                                        <strong>⚠️ Importante:</strong> Este link é válido por 7 dias a partir da data
                                        de geração.<br>
                                        O arquivo contém informações sensíveis, não compartilhe este link.
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Resumo Executivo -->
                <tr>
                    <td style="padding: 0 20px 20px 20px;">
                        <h3 style="margin: 0 0 15px 0; color: #333333; font-size: 18px;">📈 Resumo Executivo</h3>

                        <table role="presentation" cellspacing="0" cellpadding="0" border="0"
                               style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <th style="border: 1px solid #dee2e6; padding: 12px; background-color: #e9ecef; text-align: left; font-weight: bold; color: #333333;">
                                    Categoria
                                </th>
                                <th style="border: 1px solid #dee2e6; padding: 12px; background-color: #e9ecef; text-align: left; font-weight: bold; color: #333333;">
                                    Quantidade de Funcionários
                                </th>
                            </tr>
                            <tr>
                                <td style="border: 1px solid #dee2e6; padding: 12px; background-color: #ffffff;">🔴
                                    Treinamentos Vencidos
                                </td>
                                <td style="border: 1px solid #dee2e6; padding: 12px; background-color: #ffffff;">
                                    <strong>{{ $dados['total_vencidos'] }}</strong></td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid #dee2e6; padding: 12px; background-color: #f8f9fa;">🟡
                                    Próximos a Vencer (30 dias)
                                </td>
                                <td style="border: 1px solid #dee2e6; padding: 12px; background-color: #f8f9fa;">
                                    <strong>{{ $dados['total_proximos'] }}</strong></td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid #dee2e6; padding: 12px; background-color: #ffffff;">🟠 Em
                                    Atenção (60 dias)
                                </td>
                                <td style="border: 1px solid #dee2e6; padding: 12px; background-color: #ffffff;">
                                    <strong>{{ $dados['total_atencao'] }}</strong></td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Alertas condicionais -->
                @if($dados['total_vencidos'] > 0)
                    <tr>
                        <td style="padding: 0 20px 15px 20px;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0"
                                   style="width: 100%; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px;">
                                <tr>
                                    <td style="padding: 15px; color: #721c24;">
                                        <strong>🚨 AÇÃO URGENTE NECESSÁRIA!</strong><br>
                                        Existem <strong>{{ $dados['total_vencidos'] }}</strong> funcionário(s) com
                                        treinamentos vencidos.
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                @endif

                @if($dados['total_proximos'] > 0)
                    <tr>
                        <td style="padding: 0 20px 20px 20px;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0"
                                   style="width: 100%; background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px;">
                                <tr>
                                    <td style="padding: 15px; color: #856404;">
                                        <strong>⚠️ ATENÇÃO!</strong><br>
                                        Existem <strong>{{ $dados['total_proximos'] }}</strong> funcionário(s) com
                                        treinamentos vencendo nos próximos 30 dias.
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                @endif

                <!-- Próximos passos -->
                <tr>
                    <td style="padding: 0 20px 20px 20px;">
                        <table role="presentation" cellspacing="0" cellpadding="0" border="0"
                               style="width: 100%; background-color: #e3f2fd; border-radius: 5px;">
                            <tr>
                                <td style="padding: 20px;">
                                    <h4 style="margin: 0 0 15px 0; color: #333333; font-size: 16px;">📋 Próximos Passos
                                        Recomendados:</h4>
                                    <table role="presentation" cellspacing="0" cellpadding="0" border="0"
                                           style="width: 100%;">
                                        <tr>
                                            <td style="color: #333333; line-height: 1.6;">
                                                • Baixe o arquivo CSV para análise detalhada<br>
                                                • Priorize o agendamento dos treinamentos vencidos<br>
                                                • Entre em contato com os funcionários para agendar os treinamentos
                                                próximos ao vencimento<br>
                                                • Acompanhe regularmente o status dos treinamentos em atenção
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="background-color: #6c757d; color: #ffffff; padding: 20px; text-align: center;">
                        @if(!empty($dados['empresa']))
                            <div style="font-size: 13.5px; line-height: 20px; font-family: 'Arial'; color: #555555">
                                <div
                                    style="background: #f3f3f3; padding: 13px;line-height: 37px;border-radius: 0px;text-align: center">
                                    <div style="text-align: center">
                                        <strong style="text-transform: uppercase; color: #072433">
                                            {{$dados['empresa']}}
                                            <br>
                                        </strong>
                                    </div>
                                    {{$dados['empresa_endereco_completo']}}<br>
                                </div>

                            </div>
                        @endif
                        <p style="margin: 0 0 5px 0; font-size: 14px;">Este é um e-mail automático do sistema MyBP.</p>
                        <p style="margin: 0 0 5px 0; font-size: 14px;">Para dúvidas ou suporte, entre em contato com a
                            equipe responsável.</p>
                        <p style="margin: 0; font-size: 14px;"><strong>⚠️ Não responda este e-mail.</strong></p>

                        <img src="{{env('AWS_URL')}}/logo_mybp.png" alt="" style="height: 40px">
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>
</body>
</html>

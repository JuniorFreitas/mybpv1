@extends('layouts.mail.layout')
@section('titulo', $dados['assunto'] ?? 'Notificação MyBP')
@section('conteudo')
@php
$tipo = $dados['tipo'];
$ferias = $dados['ferias'];
$colaborador = $dados['colaborador'];
$centro_custo = $dados['centro_custo'];
$periodo = $dados['periodo'] ?? '';
$data_saida = $dados['data_saida'];
$data_retorno = $dados['data_retorno'];
$ultima_data = $dados['ultima_data'];
$qnt_dias = $dados['qnt_dias'];
$dias_saldo = $dados['dias_saldo'];
$data_admissao = $dados['data_admissao'] ?? null;
$solicitante = $dados['solicitante'];
$gestor = $dados['gestor_aprovador'] ?? '';
$gestor_selecionado = $dados['gestor_selecionado'] ?? '';
$aprovacao_extra = $dados['aprovacao_extra'];
$rh = $dados['rh'];
$nome_aprovacao_extra = $dados['nome_aprovacao_extra'];
$url = $dados['url'];
$has_aprovacao_extra = $dados['has_aprovacao_extra'] ?? false;

$titulos = [
'criacao' => 'Nova solicitação de férias — sua aprovação é necessária',
'pendente_aprovacao_extra' => "Férias — aguardando aprovação de {$nome_aprovacao_extra}",
'pendente_aprovacao_rh' => 'Férias — aguardando aprovação do RH',
'reprovado_gestor' => 'Solicitação de férias reprovada pelo gestor',
'reprovado_aprovacao_extra' => "Solicitação de férias reprovada por {$nome_aprovacao_extra}",
'reprovado_rh' => 'Solicitação de férias reprovada pelo RH',
'cancelado' => 'Solicitação de férias cancelada',
'aprovado_final' => 'Férias aprovadas em todas as etapas',
];

$mensagens = [
'criacao' => 'Uma nova solicitação de férias foi registrada e está aguardando sua análise. Acesse o sistema para aprovar ou reprovar.',
'pendente_aprovacao_extra' => "O gestor já aprovou. A solicitação agora aguarda a análise de {$nome_aprovacao_extra}. Você será notificado quando houver conclusão.",
'pendente_aprovacao_rh' => 'O gestor e a aprovação anterior já validaram a solicitação. Agora ela aguarda a análise do RH para ser concluída.',
'reprovado_gestor' => 'A solicitação de férias foi reprovada pelo gestor e o processo foi encerrado.',
'reprovado_aprovacao_extra' => "A solicitação de férias foi reprovada por {$nome_aprovacao_extra} e o processo foi encerrado.",
'reprovado_rh' => 'A solicitação de férias foi reprovada pelo RH e o processo foi encerrado.',
'cancelado' => 'A solicitação de férias foi cancelada e o processo foi encerrado.',
'aprovado_final' => 'A solicitação de férias foi aprovada por todos os responsáveis e está concluída. Os próximos passos já podem ser realizados.',
];

$titulo = $titulos[$tipo] ?? 'Notificação';
$mensagem = $mensagens[$tipo] ?? '';
@endphp

<table border="0" cellpadding="0" width="100%" style="width: 100%;">
    <tr>
        <td style="text-align: justify; padding: 30px;">
            <h2 style="color: #072433; margin-top: 0;">{{ $titulo }}</h2>
            <p>{{ $mensagem }}</p>

            <div style="background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;">
                <table width="100%" cellpadding="5">
                    <tr>
                        <td align="center" style="width: {{ $has_aprovacao_extra ? '25%' : '30%' }};">
                            <div style="text-align: center;">
                                <span style="font-size: 30px;">{{ $ferias->created_at ? '✓' : '○' }}</span><br>
                                <strong style="font-size: 12px;">Solicitante</strong><br>
                                <span style="font-size: 11px; color: #6c757d;">{{ $solicitante }}</span>
                            </div>
                        </td>
                        <td align="center" style="width: 5%;">→</td>
                        <td align="center" style="width: {{ $has_aprovacao_extra ? '25%' : '30%' }};">
                            <div style="text-align: center;">
                                @if($ferias->status_aprovacao_gestor === 'aprovado')
                                <span style="font-size: 30px; color: #28a745;">✓</span>
                                @elseif($ferias->status_aprovacao_gestor === 'reprovado')
                                <span style="font-size: 30px; color: #dc3545;">✗</span>
                                @else
                                <span style="font-size: 30px; color: #ffc107;">⏳</span>
                                @endif
                                <br>
                                <strong style="font-size: 12px;">Gestor</strong><br>
                                <span style="font-size: 11px; color: #6c757d;">{{ $gestor ?: 'Pendente' }}</span>
                            </div>
                        </td>
                        @if($has_aprovacao_extra)
                        <td align="center" style="width: 5%;">→</td>
                        <td align="center" style="width: 25%;">
                            <div style="text-align: center;">
                                @if($ferias->status_aprovacao_gestor === 'reprovado')
                                <span style="font-size: 30px; color: #6c757d;">⊗</span>
                                @elseif($ferias->status_aprovacao_extra === 'aprovado')
                                <span style="font-size: 30px; color: #28a745;">✓</span>
                                @elseif($ferias->status_aprovacao_extra === 'reprovado')
                                <span style="font-size: 30px; color: #dc3545;">✗</span>
                                @elseif($ferias->status_aprovacao_gestor === 'aprovado')
                                <span style="font-size: 30px; color: #ffc107;">⏳</span>
                                @else
                                <span style="font-size: 30px; color: #adb5bd;">○</span>
                                @endif
                                <br>
                                <strong style="font-size: 12px;">{{ $nome_aprovacao_extra }}</strong><br>
                                <span style="font-size: 11px; color: #6c757d;">
                                    @if($ferias->status_aprovacao_gestor === 'reprovado')
                                    Cancelada
                                    @else
                                    {{ $aprovacao_extra ?: 'Pendente' }}
                                    @endif
                                </span>
                            </div>
                        </td>
                        @endif
                        <td align="center" style="width: 5%;">→</td>
                        <td align="center" style="width: {{ $has_aprovacao_extra ? '25%' : '30%' }};">
                            <div style="text-align: center;">
                                @if($ferias->status_aprovacao_gestor === 'reprovado' || ($ferias->status_aprovacao_extra === 'reprovado'))
                                <span style="font-size: 30px; color: #6c757d;">⊗</span>
                                @elseif($ferias->status_aprovacao_rh === 'aprovado')
                                <span style="font-size: 30px; color: #28a745;">✓</span>
                                @elseif($ferias->status_aprovacao_rh === 'reprovado')
                                <span style="font-size: 30px; color: #dc3545;">✗</span>
                                @elseif(($ferias->aprovacao_extra_id && $ferias->status_aprovacao_extra === 'aprovado') || (!$ferias->aprovacao_extra_id && $ferias->status_aprovacao_gestor === 'aprovado'))
                                <span style="font-size: 30px; color: #ffc107;">⏳</span>
                                @else
                                <span style="font-size: 30px; color: #adb5bd;">○</span>
                                @endif
                                <br>
                                <strong style="font-size: 12px;">RH</strong><br>
                                <span style="font-size: 11px; color: #6c757d;">
                                    @if($ferias->status_aprovacao_gestor === 'reprovado' || ($ferias->status_aprovacao_extra === 'reprovado'))
                                    Cancelada
                                    @else
                                    {{ $rh ?: 'Pendente' }}
                                    @endif
                                </span>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            <div style="background: #ffffff; border: 1px solid #dee2e6; border-radius: 5px; padding: 20px; margin: 20px 0;">
                <h3 style="color: #072433; margin-top: 0; border-bottom: 2px solid #072433; padding-bottom: 10px;">
                    Informações da Solicitação
                </h3>

                <table width="100%" cellpadding="8" style="font-size: 13px;">
                    <tr>
                        <td width="40%" style="color: #555;"><strong>CÓD:</strong></td>
                        <td>#{{ $ferias->id }}</td>
                    </tr>
                    <tr style="background: #f8f9fa;">
                        <td style="color: #555;"><strong>Colaborador:</strong></td>
                        <td>{{ $colaborador }}</td>
                    </tr>
                    <tr>
                        <td style="color: #555;"><strong>Centro de Custo:</strong></td>
                        <td>{{ $centro_custo }}</td>
                    </tr>
                    @if($periodo)
                    <tr style="background: #f8f9fa;">
                        <td style="color: #555;"><strong>Período Aquisitivo:</strong></td>
                        <td>{{ $periodo }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td style="color: #555;"><strong>Data de Saída:</strong></td>
                        <td>{{ $data_saida ? \Carbon\Carbon::parse($data_saida)->format('d/m/Y') : '' }}</td>
                    </tr>
                    <tr style="background: #f8f9fa;">
                        <td style="color: #555;"><strong>Data de Retorno:</strong></td>
                        <td>{{ $data_retorno ? \Carbon\Carbon::parse($data_retorno)->format('d/m/Y') : '' }}</td>
                    </tr>
                    <tr>
                        <td style="color: #555;"><strong>Última Data:</strong></td>
                        <td>{{ $ultima_data ? \Carbon\Carbon::parse($ultima_data)->format('d/m/Y') : '' }}</td>
                    </tr>
                    <tr style="background: #f8f9fa;">
                        <td style="color: #555;"><strong>Quantidade de Dias:</strong></td>
                        <td>{{ $qnt_dias }}</td>
                    </tr>
                    <tr>
                        <td style="color: #555;"><strong>Dias de Saldo:</strong></td>
                        <td>{{ $dias_saldo }}</td>
                    </tr>
                    @if($data_admissao)
                    <tr style="background: #f8f9fa;">
                        <td style="color: #555;"><strong>Data de Admissão:</strong></td>
                        <td>{{ \Carbon\Carbon::parse($data_admissao)->format('d/m/Y') }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td style="color: #555;"><strong>Solicitante:</strong></td>
                        <td>{{ $solicitante }}</td>
                    </tr>
                    <tr style="background: #f8f9fa;">
                        <td style="color: #555;"><strong>Gestor Selecionado:</strong></td>
                        <td>{{ $gestor_selecionado }}</td>
                    </tr>
                    <tr>
                        <td style="color: #555;"><strong>Data de Criação:</strong></td>
                        <td>{{ $ferias->created_at ? \Carbon\Carbon::parse($ferias->created_at)->format('d/m/Y') : '' }}</td>
                    </tr>
                    <tr style="background: #f8f9fa;">
                        <td style="color: #555;"><strong>Data Atualização Gestor:</strong></td>
                        <td>{{ $ferias->data_aprovacao_gestor ? \Carbon\Carbon::parse($ferias->data_aprovacao_gestor)->format('d/m/Y') : '' }}</td>
                    </tr>
                    @if($has_aprovacao_extra)
                    <tr>
                        <td style="color: #555;"><strong>Data Atualização {{ $nome_aprovacao_extra }}:</strong></td>
                        <td>{{ $ferias->data_aprovacao_extra ? \Carbon\Carbon::parse($ferias->data_aprovacao_extra)->format('d/m/Y') : '' }}</td>
                    </tr>
                    @endif
                    <tr style="background: #f8f9fa;">
                        <td style="color: #555;"><strong>Data Atualização RH:</strong></td>
                        <td>{{ $ferias->data_aprovacao_rh ? \Carbon\Carbon::parse($ferias->data_aprovacao_rh)->format('d/m/Y') : '' }}</td>
                    </tr>
                </table>
            </div>

            @if($ferias->obs_solicitante)
            <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <strong style="color: #856404;">Observações:</strong>
                <!-- <p style="margin: 10px 0 0 0; color: #856404;">{{ $ferias->obs_solicitante }}</p> -->
            </div>
            @endif

            @if($ferias->obs_gestor && $ferias->status_aprovacao_gestor === 'reprovado')
            <div style="background: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <strong style="color: #721c24;">Motivo da Reprovação (Gestor):</strong>
                <!-- <p style="margin: 10px 0 0 0; color: #721c24;">{{ $ferias->obs_gestor }}</p> -->
            </div>
            @endif

            @if($ferias->obs_aprovacao_extra && $ferias->status_aprovacao_extra === 'reprovado')
            <div style="background: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <strong style="color: #721c24;">Motivo da Reprovação ({{ $nome_aprovacao_extra }}):</strong>
                <!-- <p style="margin: 10px 0 0 0; color: #721c24;">{{ $ferias->obs_aprovacao_extra }}</p> -->
            </div>
            @endif

            @if($ferias->obs_rh && $ferias->status_aprovacao_rh === 'reprovado')
            <div style="background: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <strong style="color: #721c24;">Motivo da Reprovação (RH):</strong>
                <!-- <p style="margin: 10px 0 0 0; color: #721c24;">{{ $ferias->obs_rh }}</p> -->
            </div>
            @endif

            <br>
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $url }}" class="link" target="_blank">ACESSAR SISTEMA</a>
            </div>
            <br>
        </td>
    </tr>
</table>
@endsection

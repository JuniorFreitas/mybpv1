@extends('layouts.mail.layout')
@section('titulo', $dados['assunto'] ?? 'Notificação MyBP')
@section('conteudo')
@php
$tipo = $dados['tipo'];
$intermitente = $dados['intermitente'];
$colaborador = $dados['colaborador'];
$cargo_anterior = $dados['cargo_anterior'];
$novo_cargo = $dados['novo_cargo'];
$centro_custo = $dados['centro_custo'];
$solicitante = $dados['solicitante'];
$gestor = $dados['gestor'];
$aprovacao_extra = $dados['aprovacao_extra'];
$rh = $dados['rh'];
$nome_aprovacao_extra = $dados['nome_aprovacao_extra'];
$url = $dados['url'];
$has_aprovacao_extra = $dados['has_aprovacao_extra'] ?? false;

// Títulos e mensagens
$titulos = [
'criacao' => 'Nova Solicitação de Mudança Intermitente para Fixo',
'pendente_aprovacao_extra' => "Solicitação de Mudança Intermitente para Fixo - Aguardando {$nome_aprovacao_extra}",
'pendente_aprovacao_rh' => 'Solicitação de Mudança Intermitente para Fixo - Aguardando Aprovação do RH',
'reprovado_gestor' => 'Solicitação de Mudança Intermitente para Fixo - Reprovado pelo Gestor e finalizada.',
'reprovado_aprovacao_extra' => "Solicitação de Mudança Intermitente para Fixo - Reprovado de {$nome_aprovacao_extra} e finalizada.",
'reprovado_rh' => 'Solicitação de Mudança Intermitente para Fixo - Reprovado pelo RH e finalizada.',
'cancelado' => 'Solicitação de Mudança Intermitente para Fixo - Solicitação Cancelada e finalizada.',
'aprovado_final' => 'Solicitação de Mudança Intermitente para Fixo foi aprovada e finalizada.',
];

$mensagens = [
'criacao' => 'Uma nova solicitação de mudança de intermitente para fixo foi criada e aguarda sua aprovação.',
'pendente_aprovacao_extra' => "A solicitação de mudança de intermitente para fixo foi aprovada pelo gestor e aguarda aprovação da {$nome_aprovacao_extra}.",
'pendente_aprovacao_rh' => 'A solicitação de mudança de intermitente para fixo foi aprovada e aguarda aprovação do RH.',
'reprovado_gestor' => 'A solicitação de mudança de intermitente para fixo foi reprovada pelo gestor e finalizada.',
'reprovado_aprovacao_extra' => "A solicitação de mudança de intermitente para fixo foi reprovada por {$nome_aprovacao_extra} e finalizada.",
'reprovado_rh' => 'A solicitação de mudança de intermitente para fixo foi reprovada pelo RH e finalizada.',
'cancelado' => 'A solicitação de mudança de intermitente para fixo foi cancelada e finalizada.',
'aprovado_final' => 'A solicitação de mudança de intermitente para fixo foi aprovada por todos e está pronta para ser efetivada!',
];

$titulo = $titulos[$tipo] ?? 'Notificação';
$mensagem = $mensagens[$tipo] ?? '';
@endphp

<table border="0" cellpadding="0" width="100%" style="width: 100%;">
    <tr>
        <td style="text-align: justify; padding: 30px;">
            <h2 style="color: #072433; margin-top: 0;">{{ $titulo }}</h2>
            <p>{{ $mensagem }}</p>

            <!-- Fluxo de Aprovação Visual -->
            <div style="background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;">
                <table width="100%" cellpadding="5">
                    <tr>
                        <td align="center" style="width: {{ $has_aprovacao_extra ? '25%' : '30%' }};">
                            <div style="text-align: center;">
                                <span style="font-size: 30px;">{{ $intermitente->created_at ? '✓' : '○' }}</span><br>
                                <strong style="font-size: 12px;">Solicitante</strong><br>
                                <span style="font-size: 11px; color: #6c757d;">{{ $solicitante }}</span>
                            </div>
                        </td>
                        <td align="center" style="width: 5%;">→</td>
                        <td align="center" style="width: {{ $has_aprovacao_extra ? '25%' : '30%' }};">
                            <div style="text-align: center;">
                                @if($intermitente->status_aprovacao === 'aprovado')
                                <span style="font-size: 30px; color: #28a745;">✓</span>
                                @elseif($intermitente->status_aprovacao === 'reprovado')
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
                                @if($intermitente->status_aprovacao === 'reprovado')
                                <span style="font-size: 30px; color: #6c757d;">⊗</span>
                                @elseif($intermitente->status_aprovacao_extra === 'aprovado')
                                <span style="font-size: 30px; color: #28a745;">✓</span>
                                @elseif($intermitente->status_aprovacao_extra === 'reprovado')
                                <span style="font-size: 30px; color: #dc3545;">✗</span>
                                @elseif($intermitente->status_aprovacao === 'aprovado')
                                <span style="font-size: 30px; color: #ffc107;">⏳</span>
                                @else
                                <span style="font-size: 30px; color: #adb5bd;">○</span>
                                @endif
                                <br>
                                <strong style="font-size: 12px;">{{ $nome_aprovacao_extra }}</strong><br>
                                <span style="font-size: 11px; color: #6c757d;">
                                    @if($intermitente->status_aprovacao === 'reprovado')
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
                                @if($intermitente->status_aprovacao === 'reprovado' || ($intermitente->status_aprovacao_extra === 'reprovado'))
                                <span style="font-size: 30px; color: #6c757d;">⊗</span>
                                @elseif($intermitente->status_aprovacao_rh === 'aprovado')
                                <span style="font-size: 30px; color: #28a745;">✓</span>
                                @elseif($intermitente->status_aprovacao_rh === 'reprovado')
                                <span style="font-size: 30px; color: #dc3545;">✗</span>
                                @elseif(($intermitente->aprovacao_extra_id && $intermitente->status_aprovacao_extra === 'aprovado') || (!$intermitente->aprovacao_extra_id && $intermitente->status_aprovacao === 'aprovado'))
                                <span style="font-size: 30px; color: #ffc107;">⏳</span>
                                @else
                                <span style="font-size: 30px; color: #adb5bd;">○</span>
                                @endif
                                <br>
                                <strong style="font-size: 12px;">RH</strong><br>
                                <span style="font-size: 11px; color: #6c757d;">
                                    @if($intermitente->status_aprovacao === 'reprovado' || ($intermitente->status_aprovacao_extra === 'reprovado'))
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

            <!-- Informações da Solicitação -->
            <div style="background: #ffffff; border: 1px solid #dee2e6; border-radius: 5px; padding: 20px; margin: 20px 0;">
                <h3 style="color: #072433; margin-top: 0; border-bottom: 2px solid #072433; padding-bottom: 10px;">
                    Informações da Solicitação
                </h3>

                <table width="100%" cellpadding="8" style="font-size: 13px;">
                    <tr>
                        <td width="40%" style="color: #555;"><strong>ID:</strong></td>
                        <td>#{{ $intermitente->id }}</td>
                    </tr>
                    <tr style="background: #f8f9fa;">
                        <td style="color: #555;"><strong>Colaborador:</strong></td>
                        <td>{{ $colaborador }}</td>
                    </tr>
                    <tr>
                        <td style="color: #555;"><strong>Cargo Anterior:</strong></td>
                        <td>{{ $cargo_anterior }}</td>
                    </tr>
                    <tr style="background: #f8f9fa;">
                        <td style="color: #555;"><strong>Novo Cargo:</strong></td>
                        <td>{{ $novo_cargo }}</td>
                    </tr>
                    <!-- @if($intermitente->salario_anterior)
                    <tr>
                        <td style="color: #555;"><strong>Salário Anterior:</strong></td>
                        <td>{{ $intermitente->salario_anterior_format }}</td>
                    </tr>
                    @endif
                    @if($intermitente->novo_salario)
                    <tr style="background: #f8f9fa;">
                        <td style="color: #555;"><strong>Novo Salário:</strong></td>
                        <td>{{ $intermitente->novo_salario_format }}</td>
                    </tr>
                    @endif -->
                    <tr>
                        <td style="color: #555;"><strong>Centro de Custo:</strong></td>
                        <td>{{ $centro_custo }}</td>
                    </tr>
                    @if($intermitente->data_prevista)
                    <tr style="background: #f8f9fa;">
                        <td style="color: #555;"><strong>Data Prevista:</strong></td>
                        <td>{{ \Carbon\Carbon::parse($intermitente->data_prevista)->format('d/m/Y') }}</td>
                    </tr>
                    @endif
                </table>
            </div>

            <!-- Observações -->
            @if($intermitente->obs)
            <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <strong style="color: #856404;">Observações:</strong>
                <!-- <p style="margin: 10px 0 0 0; color: #856404;">{{ $intermitente->obs }}</p> -->
            </div>
            @endif

            @if($intermitente->obs_aprovacao && $intermitente->status_aprovacao === 'reprovado')
            <div style="background: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <strong style="color: #721c24;">Motivo da Reprovação (Gestor):</strong>
                <!-- <p style="margin: 10px 0 0 0; color: #721c24;">{{ $intermitente->obs_aprovacao }}</p> -->
            </div>
            @endif

            @if($intermitente->obs_aprovacao_extra && $intermitente->status_aprovacao_extra === 'reprovado')
            <div style="background: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <strong style="color: #721c24;">Motivo da Reprovação ({{ $nome_aprovacao_extra }}):</strong>
                <!-- <p style="margin: 10px 0 0 0; color: #721c24;">{{ $intermitente->obs_aprovacao_extra }}</p> -->
            </div>
            @endif

            @if($intermitente->obs_aprovacao_rh && $intermitente->status_aprovacao_rh === 'reprovado')
            <div style="background: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <strong style="color: #721c24;">Motivo da Reprovação (RH):</strong>
                <!-- <p style="margin: 10px 0 0 0; color: #721c24;">{{ $intermitente->obs_aprovacao_rh }}</p> -->
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

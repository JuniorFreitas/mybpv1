@extends('layouts.mail.layout')
@section('titulo', $dados['assunto'] ?? 'Notificação MyBP')
@section('conteudo')
@php
$tipo = $dados['tipo'];
$admissao = $dados['admissao'];
$nome_pessoa = $dados['nome_pessoa'] ?? '';
$cargo = $dados['cargo'];
$centro_custo = $dados['centro_custo'];
$tipo_contrato = $dados['tipo_contrato'];
$data_admissao = $dados['data_admissao'];
$salario = $dados['salario'];
$solicitante = $dados['solicitante'];
$gestor = $dados['gestor_aprovador'] ?? '';
$gestor_selecionado = $dados['gestor_selecionado'] ?? '';
$aprovacao_extra = $dados['aprovacao_extra'];
$rh = $dados['rh'];
$nome_aprovacao_extra = $dados['nome_aprovacao_extra'];
$url = $dados['url'];
$has_aprovacao_extra = $dados['has_aprovacao_extra'] ?? false;

$titulos = [
'criacao' => 'Nova solicitação de admissão — sua aprovação é necessária',
'pendente_aprovacao_extra' => "Admissão — aguardando aprovação de {$nome_aprovacao_extra}",
'pendente_aprovacao_rh' => 'Admissão — aguardando aprovação do RH',
'reprovado_gestor' => 'Solicitação de admissão reprovada pelo gestor',
'reprovado_aprovacao_extra' => "Solicitação de admissão reprovada por {$nome_aprovacao_extra}",
'reprovado_rh' => 'Solicitação de admissão reprovada pelo RH',
'cancelado' => 'Solicitação de admissão cancelada',
'aprovado_final' => 'Admissão aprovada em todas as etapas',
];

$mensagens = [
'criacao' => 'Uma nova solicitação de admissão foi registrada e está aguardando sua análise. Acesse o sistema para aprovar ou reprovar.',
'pendente_aprovacao_extra' => "O gestor já aprovou. A solicitação agora aguarda a análise de {$nome_aprovacao_extra}. Você será notificado quando houver conclusão.",
'pendente_aprovacao_rh' => 'O gestor e a aprovação anterior já validaram a solicitação. Agora ela aguarda a análise do RH para ser concluída.',
'reprovado_gestor' => 'A solicitação de admissão foi reprovada pelo gestor e o processo foi encerrado.',
'reprovado_aprovacao_extra' => "A solicitação de admissão foi reprovada por {$nome_aprovacao_extra} e o processo foi encerrado.",
'reprovado_rh' => 'A solicitação de admissão foi reprovada pelo RH e o processo foi encerrado.',
'cancelado' => 'A solicitação de admissão foi cancelada e o processo foi encerrado.',
'aprovado_final' => 'A solicitação de admissão foi aprovada por todos os responsáveis e está concluída. Os próximos passos já podem ser realizados.',
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
                                <span style="font-size: 30px;">{{ $admissao->created_at ? '✓' : '○' }}</span><br>
                                <strong style="font-size: 12px;">Solicitante</strong><br>
                                <span style="font-size: 11px; color: #6c757d;">{{ $solicitante }}</span>
                            </div>
                        </td>
                        <td align="center" style="width: 5%;">→</td>
                        <td align="center" style="width: {{ $has_aprovacao_extra ? '25%' : '30%' }};">
                            <div style="text-align: center;">
                                @if($admissao->status_aprovacao === 'aprovado')
                                <span style="font-size: 30px; color: #28a745;">✓</span>
                                @elseif($admissao->status_aprovacao === 'reprovado')
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
                                @if($admissao->status_aprovacao === 'reprovado')
                                <span style="font-size: 30px; color: #6c757d;">⊗</span>
                                @elseif($admissao->status_aprovacao_extra === 'aprovado')
                                <span style="font-size: 30px; color: #28a745;">✓</span>
                                @elseif($admissao->status_aprovacao_extra === 'reprovado')
                                <span style="font-size: 30px; color: #dc3545;">✗</span>
                                @elseif($admissao->status_aprovacao === 'aprovado')
                                <span style="font-size: 30px; color: #ffc107;">⏳</span>
                                @else
                                <span style="font-size: 30px; color: #adb5bd;">○</span>
                                @endif
                                <br>
                                <strong style="font-size: 12px;">{{ $nome_aprovacao_extra }}</strong><br>
                                <span style="font-size: 11px; color: #6c757d;">
                                    @if($admissao->status_aprovacao === 'reprovado')
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
                                @if($admissao->status_aprovacao === 'reprovado' || ($admissao->status_aprovacao_extra === 'reprovado'))
                                <span style="font-size: 30px; color: #6c757d;">⊗</span>
                                @elseif($admissao->status_aprovacao_rh === 'aprovado')
                                <span style="font-size: 30px; color: #28a745;">✓</span>
                                @elseif($admissao->status_aprovacao_rh === 'reprovado')
                                <span style="font-size: 30px; color: #dc3545;">✗</span>
                                @elseif(($admissao->aprovacao_extra_id && $admissao->status_aprovacao_extra === 'aprovado') || (!$admissao->aprovacao_extra_id && $admissao->status_aprovacao === 'aprovado'))
                                <span style="font-size: 30px; color: #ffc107;">⏳</span>
                                @else
                                <span style="font-size: 30px; color: #adb5bd;">○</span>
                                @endif
                                <br>
                                <strong style="font-size: 12px;">RH</strong><br>
                                <span style="font-size: 11px; color: #6c757d;">
                                    @if($admissao->status_aprovacao === 'reprovado' || ($admissao->status_aprovacao_extra === 'reprovado'))
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
                        <td>#{{ $admissao->id }}</td>
                    </tr>
                    @if($nome_pessoa)
                    <tr style="background: #f8f9fa;">
                        <td style="color: #555;"><strong>Nome do Colaborador:</strong></td>
                        <td>{{ $nome_pessoa }}</td>
                    </tr>
                    @endif
                    <tr style="background: {{ $nome_pessoa ? '#ffffff' : '#f8f9fa' }};">
                        <td style="color: #555;"><strong>Cargo:</strong></td>
                        <td>{{ $cargo }}</td>
                    </tr>
                    <tr>
                        <td style="color: #555;"><strong>Centro de Custo:</strong></td>
                        <td>{{ $centro_custo }}</td>
                    </tr>
                    <tr style="background: #f8f9fa;">
                        <td style="color: #555;"><strong>Tipo de Contrato:</strong></td>
                        <td>{{ $tipo_contrato }}</td>
                    </tr>
                    <tr>
                        <td style="color: #555;"><strong>Data de Admissão:</strong></td>
                        <td>{{ $data_admissao ? \Carbon\Carbon::parse($data_admissao)->format('d/m/Y') : '' }}</td>
                    </tr>
                    <tr style="background: #f8f9fa;">
                        <td style="color: #555;"><strong>Salário:</strong></td>
                        <!-- <td>{{ $salario }}</td> -->
                    </tr>
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
                        <td>{{ $admissao->created_at ? \Carbon\Carbon::parse($admissao->created_at)->format('d/m/Y') : '' }}</td>
                    </tr>
                    <tr style="background: #f8f9fa;">
                        <td style="color: #555;"><strong>Data Atualização Gestor:</strong></td>
                        <td>{{ $admissao->data_aprovacao ? \Carbon\Carbon::parse($admissao->data_aprovacao)->format('d/m/Y') : '' }}</td>
                    </tr>
                    @if($has_aprovacao_extra)
                    <tr>
                        <td style="color: #555;"><strong>Data Atualização {{ $nome_aprovacao_extra }}:</strong></td>
                        <td>{{ $admissao->data_aprovacao_extra ? \Carbon\Carbon::parse($admissao->data_aprovacao_extra)->format('d/m/Y') : '' }}</td>
                    </tr>
                    @endif
                    <tr style="background: #f8f9fa;">
                        <td style="color: #555;"><strong>Data Atualização RH:</strong></td>
                        <td>{{ $admissao->data_aprovacao_rh ? \Carbon\Carbon::parse($admissao->data_aprovacao_rh)->format('d/m/Y') : '' }}</td>
                    </tr>
                </table>
            </div>

            @if($admissao->obs)
            <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <strong style="color: #856404;">Observações:</strong>
                <!-- <p style="margin: 10px 0 0 0; color: #856404;">{{ $admissao->obs }}</p> -->
            </div>
            @endif

            @if($admissao->obs_aprovacao && $admissao->status_aprovacao === 'reprovado')
            <div style="background: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <strong style="color: #721c24;">Motivo da Reprovação (Gestor):</strong>
                <!-- <p style="margin: 10px 0 0 0; color: #721c24;">{{ $admissao->obs_aprovacao }}</p> -->
            </div>
            @endif

            @if($admissao->obs_aprovacao_extra && $admissao->status_aprovacao_extra === 'reprovado')
            <div style="background: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <strong style="color: #721c24;">Motivo da Reprovação ({{ $nome_aprovacao_extra }}):</strong>
                <!-- <p style="margin: 10px 0 0 0; color: #721c24;">{{ $admissao->obs_aprovacao_extra }}</p> -->
            </div>
            @endif

            @if($admissao->obs_rh && $admissao->status_aprovacao_rh === 'reprovado')
            <div style="background: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <strong style="color: #721c24;">Motivo da Reprovação (RH):</strong>
                <!-- <p style="margin: 10px 0 0 0; color: #721c24;">{{ $admissao->obs_rh }}</p> -->
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

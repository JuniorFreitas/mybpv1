@extends('layouts.mail.layout')
@section('titulo', $dados['assunto'] ?? 'Notificação MyBP')
@section('conteudo')
@php
$tipo = $dados['tipo'];
$mudanca = $dados['mudanca_cargo'];
$colaborador = $dados['colaborador'];
$centro_custo_anterior = $dados['centro_custo_anterior'];
$centro_custo_novo = $dados['centro_custo_novo'];
$cargo_anterior = $dados['cargo_anterior'];
$cargo_novo = $dados['cargo_novo'];
$funcao_anterior = $dados['funcao_anterior'] ?? '';
$funcao_nova = $dados['funcao_nova'] ?? '';
$solicitante = $dados['solicitante'];
$gestor = $dados['gestor_aprovador'] ?? '';
$gestor_selecionado = $dados['gestor_selecionado'] ?? '';
$aprovacao_extra = $dados['aprovacao_extra'];
$rh = $dados['rh'];
$nome_aprovacao_extra = $dados['nome_aprovacao_extra'];
$url = $dados['url'];
$has_aprovacao_extra = $dados['has_aprovacao_extra'] ?? false;

/** Datas de MudancaCargo vêm como d/m/Y (acessors DataHora); Carbon::parse() não interpreta esse formato. */
$formatarDataMudancaEmail = static function ($valor): string {
    if ($valor === null || $valor === '') {
        return '';
    }
    if ($valor instanceof \DateTimeInterface) {
        return \Carbon\Carbon::instance($valor)->format('d/m/Y');
    }
    $s = trim((string) $valor);
    if ($s === '') {
        return '';
    }
    if (preg_match('/^(\d{2}\/\d{2}\/\d{4})/u', $s, $m)) {
        return $m[1];
    }
    try {
        return \Carbon\Carbon::parse($s)->format('d/m/Y');
    } catch (\Throwable $e) {
        return $s;
    }
};

$titulos = [
'criacao' => 'Nova solicitação de mudança de cargo — sua aprovação é necessária',
'pendente_aprovacao_extra' => "Mudança de cargo — aguardando aprovação de {$nome_aprovacao_extra}",
'pendente_aprovacao_rh' => 'Mudança de cargo — aguardando aprovação do RH',
'reprovado_gestor' => 'Solicitação de mudança de cargo reprovada pelo gestor',
'reprovado_aprovacao_extra' => "Solicitação de mudança de cargo reprovada por {$nome_aprovacao_extra}",
'reprovado_rh' => 'Solicitação de mudança de cargo reprovada pelo RH',
'cancelado' => 'Solicitação de mudança de cargo cancelada',
'aprovado_final' => 'Mudança de cargo aprovada em todas as etapas',
];

$mensagens = [
'criacao' => 'Uma nova solicitação de mudança de cargo foi registrada e está aguardando sua análise. Acesse o sistema para aprovar ou reprovar.',
'pendente_aprovacao_extra' => "O gestor já aprovou. A solicitação agora aguarda a análise de {$nome_aprovacao_extra}. Você será notificado quando houver conclusão.",
'pendente_aprovacao_rh' => 'O gestor e a aprovação anterior já validaram a solicitação. Agora ela aguarda a análise do RH para ser concluída.',
'reprovado_gestor' => 'A solicitação de mudança de cargo foi reprovada pelo gestor e o processo foi encerrado.',
'reprovado_aprovacao_extra' => "A solicitação de mudança de cargo foi reprovada por {$nome_aprovacao_extra} e o processo foi encerrado.",
'reprovado_rh' => 'A solicitação de mudança de cargo foi reprovada pelo RH e o processo foi encerrado.',
'cancelado' => 'A solicitação de mudança de cargo foi cancelada e o processo foi encerrado.',
'aprovado_final' => 'A solicitação de mudança de cargo foi aprovada por todos os responsáveis e está concluída. Os próximos passos já podem ser realizados.',
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
                                <span style="font-size: 30px;">{{ $mudanca->created_at ? '✓' : '○' }}</span><br>
                                <strong style="font-size: 12px;">Solicitante</strong><br>
                                <span style="font-size: 11px; color: #6c757d;">{{ $solicitante }}</span>
                            </div>
                        </td>
                        <td align="center" style="width: 5%;">→</td>
                        <td align="center" style="width: {{ $has_aprovacao_extra ? '25%' : '30%' }};">
                            <div style="text-align: center;">
                                @if($mudanca->status_aprovacao_gestor === 'aprovado')
                                <span style="font-size: 30px; color: #28a745;">✓</span>
                                @elseif($mudanca->status_aprovacao_gestor === 'reprovado')
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
                                @if($mudanca->status_aprovacao_gestor === 'reprovado')
                                <span style="font-size: 30px; color: #6c757d;">⊗</span>
                                @elseif($mudanca->status_aprovacao_extra === 'aprovado')
                                <span style="font-size: 30px; color: #28a745;">✓</span>
                                @elseif($mudanca->status_aprovacao_extra === 'reprovado')
                                <span style="font-size: 30px; color: #dc3545;">✗</span>
                                @elseif($mudanca->status_aprovacao_gestor === 'aprovado')
                                <span style="font-size: 30px; color: #ffc107;">⏳</span>
                                @else
                                <span style="font-size: 30px; color: #adb5bd;">○</span>
                                @endif
                                <br>
                                <strong style="font-size: 12px;">{{ $nome_aprovacao_extra }}</strong><br>
                                <span style="font-size: 11px; color: #6c757d;">
                                    @if($mudanca->status_aprovacao_gestor === 'reprovado')
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
                                @if($mudanca->status_aprovacao_gestor === 'reprovado' || ($mudanca->status_aprovacao_extra === 'reprovado'))
                                <span style="font-size: 30px; color: #6c757d;">⊗</span>
                                @elseif($mudanca->status_aprovacao_rh === 'aprovado')
                                <span style="font-size: 30px; color: #28a745;">✓</span>
                                @elseif($mudanca->status_aprovacao_rh === 'reprovado')
                                <span style="font-size: 30px; color: #dc3545;">✗</span>
                                @elseif(($mudanca->aprovacao_extra_id && $mudanca->status_aprovacao_extra === 'aprovado') || (!$mudanca->aprovacao_extra_id && $mudanca->status_aprovacao_gestor === 'aprovado'))
                                <span style="font-size: 30px; color: #ffc107;">⏳</span>
                                @else
                                <span style="font-size: 30px; color: #adb5bd;">○</span>
                                @endif
                                <br>
                                <strong style="font-size: 12px;">RH</strong><br>
                                <span style="font-size: 11px; color: #6c757d;">
                                    @if($mudanca->status_aprovacao_gestor === 'reprovado' || ($mudanca->status_aprovacao_extra === 'reprovado'))
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
                        <td>#{{ $mudanca->id }}</td>
                    </tr>
                    <tr style="background: #f8f9fa;">
                        <td style="color: #555;"><strong>Colaborador:</strong></td>
                        <td>{{ $colaborador }}</td>
                    </tr>
                    <tr>
                        <td style="color: #555;"><strong>Centro de Custo Anterior:</strong></td>
                        <td>{{ $centro_custo_anterior }}</td>
                    </tr>
                    <tr style="background: #f8f9fa;">
                        <td style="color: #555;"><strong>Centro de Custo Novo:</strong></td>
                        <td>{{ $centro_custo_novo }}</td>
                    </tr>
                    <tr>
                        <td style="color: #555;"><strong>Cargo Anterior:</strong></td>
                        <td>{{ $cargo_anterior }}</td>
                    </tr>
                    <tr style="background: #f8f9fa;">
                        <td style="color: #555;"><strong>Novo Cargo:</strong></td>
                        <td>{{ $cargo_novo }}</td>
                    </tr>
                    @if($funcao_anterior)
                    <tr>
                        <td style="color: #555;"><strong>Função Anterior:</strong></td>
                        <td>{{ $funcao_anterior }}</td>
                    </tr>
                    @endif
                    @if($funcao_nova)
                    <tr style="background: #f8f9fa;">
                        <td style="color: #555;"><strong>Nova Função:</strong></td>
                        <td>{{ $funcao_nova }}</td>
                    </tr>
                    @endif
                    @if($mudanca->anterior_salario)
                    <tr>
                        <td style="color: #555;"><strong>Salário Anterior:</strong></td>
                        <td>{{ $mudanca->anterior_salario }}</td>
                    </tr>
                    @endif
                    @if($mudanca->novo_salario)
                    <tr style="background: #f8f9fa;">
                        <td style="color: #555;"><strong>Novo Salário:</strong></td>
                        <td>{{ $mudanca->novo_salario }}</td>
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
                        <td style="color: #555;"><strong>Data de Solicitação:</strong></td>
                        <td>{{ $formatarDataMudancaEmail($mudanca->data_solicitacao) }}</td>
                    </tr>
                    <tr style="background: #f8f9fa;">
                        <td style="color: #555;"><strong>Data Atualização Gestor:</strong></td>
                        <td>{{ $formatarDataMudancaEmail($mudanca->data_aprovacao_gestor) }}</td>
                    </tr>
                    @if($has_aprovacao_extra)
                    <tr>
                        <td style="color: #555;"><strong>Data Atualização {{ $nome_aprovacao_extra }}:</strong></td>
                        <td>{{ $formatarDataMudancaEmail($mudanca->data_aprovacao_extra) }}</td>
                    </tr>
                    @endif
                    <tr style="background: #f8f9fa;">
                        <td style="color: #555;"><strong>Data Atualização RH:</strong></td>
                        <td>{{ $formatarDataMudancaEmail($mudanca->data_aprovacao_rh) }}</td>
                    </tr>
                </table>
            </div>

            @if($mudanca->obs_solicitante)
            <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <strong style="color: #856404;">Observações:</strong>
                <!-- <p style="margin: 10px 0 0 0; color: #856404;">{{ $mudanca->obs_solicitante }}</p> -->
            </div>
            @endif

            @if($mudanca->obs_gestor_aprovacao && $mudanca->status_aprovacao_gestor === 'reprovado')
            <div style="background: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <strong style="color: #721c24;">Motivo da Reprovação (Gestor):</strong>
                <!-- <p style="margin: 10px 0 0 0; color: #721c24;">{{ $mudanca->obs_gestor_aprovacao }}</p> -->
            </div>
            @endif

            @if($mudanca->obs_aprovacao_extra && $mudanca->status_aprovacao_extra === 'reprovado')
            <div style="background: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <strong style="color: #721c24;">Motivo da Reprovação ({{ $nome_aprovacao_extra }}):</strong>
                <!-- <p style="margin: 10px 0 0 0; color: #721c24;">{{ $mudanca->obs_aprovacao_extra }}</p> -->
            </div>
            @endif

            @if($mudanca->obs_rh && $mudanca->status_aprovacao_rh === 'reprovado')
            <div style="background: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <strong style="color: #721c24;">Motivo da Reprovação (RH):</strong>
                <!-- <p style="margin: 10px 0 0 0; color: #721c24;">{{ $mudanca->obs_rh }}</p> -->
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

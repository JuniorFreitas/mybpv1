<?php $cont = 0; ?>
@foreach($treinamentos as $treinamento)
    <div style="padding: 13px">
        <table style="border: 0.1mm solid; width:14.80cm; margin-bottom: 3px;">
            <tr>
                <td style="width: 50%; height: 8.30cm; vertical-align: top;">
                    <table>
                        <tr>
                            @php
                                $segmento_config = $treinamento['segmento_config'] ?? [];
                                $cabecalho_img = $segmento_config['cabecalho_img_base64'] ?? null;
                                if (!$cabecalho_img) {
                                    $cabecalho_img = !empty($segmento_config['cabecalho_img']) ? asset($segmento_config['cabecalho_img']) : asset('images/carteira/cabecalho_carteira_alumar.webp');
                                }
                            @endphp
                            @if($empresa['empresa_id'] !== 78862)
                                <td style="text-align: center;">
                                    <img src="{{ $cabecalho_img }}"
                                         style="width: 5.7cm; margin-bottom: -1mm;">
                                </td>
                            @else
                                <td style="text-align: center;">
                                    @if(!empty($empresa['logo']))
                                        <img src="{{ $empresa['logo'] }}" alt="Logo" title="Logo" style="width: 1.6cm">
                                    @endif
                                    <img src="{{ $cabecalho_img }}"
                                         style="width: 5cm; margin-bottom: -1mm;">
                                </td>
                            @endif
                        </tr>

                        <tr>
                            <td style="text-align: left; height: 6cm; vertical-align: top">
                                <ul style="list-style: none; top: 29px; padding-left: 0.6mm">
                                    <li style="font-size: 5.5pt; margin-top: 1.5px; margin-bottom: 0.7mm; display: flex; align-items: center;">
                                        <div
                                            style="float: left; width: 100%; margin-bottom: 0px; background: #d9d9d9; padding: 4px; border-radius: 6px">
                                            <strong>Nome:</strong> {{ mb_strtoupper($treinamento['feedback_curriculo']['curriculo']['nome']) }}
                                        </div>
                                    </li>
                                    <li style="font-size: 5.5pt; margin-top: 1.5px; margin-bottom: 0px; display: flex; align-items: center;">
                                        <div
                                            style="float: left; width: 76%; margin-bottom: 0px; background: #d9d9d9; padding: 4px; border-radius: 6px">
                                            <strong>Função:</strong> {{ mb_strtoupper($treinamento['feedback_curriculo']['admissao'] ?$treinamento['feedback_curriculo']['admissao']['cargo'] : null) }}
                                        </div>
                                        <div
                                            style="float: left; width: 23%; margin-bottom: 0px; margin-left: 1%; background: #d9d9d9; padding: 4px; border-radius: 6px">
                                            <strong>Chapa:</strong> {!! $treinamento['feedback_curriculo']['admissao']['numero_cracha'] ??"&nbsp" !!}
                                        </div>
                                    </li>
                                    {{--TODO incrementado para PILLAR em 27/06/2024 uma acao deles --}}
                                    @if($treinamento['feedback_curriculo']['empresa_id'] == 39765)
                                        <li style="font-size: 5.5pt; margin-top: 1.5px; margin-bottom: 0.7mm; display: flex; align-items: center;">
                                            <div
                                                style="float: left; width: 100%; margin-bottom: 0px; background: #d9d9d9; padding: 4px; border-radius: 6px">
                                                <strong>Faz uso de Lentes
                                                    Corretivas:</strong> {{ mb_strtoupper($treinamento['feedback_curriculo']['admissao']['usa_lentes_corretivas'] ? 'Sim' : 'Não') }}
                                            </div>
                                        </li>
                                    @endif
                                </ul>
                                <div style="
                                            background: white;height: 9px;width: 269px; padding-top: 0.7mm; padding-bottom: 0.3mm; font-size: 5.5pt;font-family: 'Arial', Verdana, sans-serif;font-weight: bold;display: flex;justify-content: space-between;">
                                    <p style="width: 185px; text-align: center;">Treinamentos</p>
                                    <p style="width: 43px; text-align: center;">Data</p>
                                    <p style="width: 42px; text-align: center;">Reciclagem</p>
                                </div>
                                <ul style="list-style: none; top: 29px; padding-left: 0.6mm">
                                    @foreach($treinamento['vencimentos'] as $key => $vencimento)
                                        @if($vencimento['exibir_na_carteira'])
                                            <li style="font-size: {!! strlen($vencimento['label_reduzida']) <= 20 ? '5pt' : '4.7pt' !!}; margin-top: 1.5px; margin-bottom: 0px; display: flex; align-items: center;">
                                                <div
                                                    style="float: left; width: 175px; margin-bottom: 0px; background: #d9d9d9; padding: 2px; border-radius: 2px"> {{ $vencimento['label_reduzida'] ? mb_strimwidth($vencimento['label_reduzida'], 0, 58) : "Não informada" }}</div>
                                                <div
                                                    style="float: left; margin-bottom: 0px; font-size: 5pt; margin-left: 3px; margin-top: 0px; width:43px; background: #d9d9d9; padding: 2px; border-radius: 2px; text-align: center"> {{ $vencimento['pivot']['data_treinamento']  }}</div>
                                                <div
                                                    style="float: left; margin-bottom: 0px; font-size: 5pt; margin-left: 3px; margin-top: 0px;  width:43px; background: #d9d9d9; padding: 2px; border-radius: 2px; text-align: center"> {{ $vencimento['pivot']['data_vencimento']  }}</div>
                                                <div style="clear: both"></div>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>

                                @if(!empty($treinamento['alerta_sem_cargo_vinculo']))
                                    <div style="margin-top: 2mm; display:none; background: #fff3cd; border: 0.2mm solid #ffeeba; color: #856404; border-radius: 2px; padding: 2px; font-size: 4.6pt;">
                                        Atenção: colaborador sem cargo definido. Não foi possível validar o vínculo dos treinamentos por cargo.
                                    </div>
                                @endif

                                @if(!empty($treinamento['vencimentos_nao_vinculados_cargo']))
                                    <div style="margin-top: 1.5mm; background: #f8d7da; border: 0.2mm solid #f5c6cb; color: #721c24; border-radius: 2px; padding: 2px;">
                                        <div style="font-size: 4.8pt; font-weight: bold; margin-bottom: 0.8mm;">
                                            Treinamentos fora do vínculo do cargo:
                                        </div>
                                        <ul style="margin: 0; padding-left: 2.6mm; font-size: 4.5pt;">
                                            @foreach($treinamento['vencimentos_nao_vinculados_cargo'] as $vencimentoAlerta)
                                                <li>
                                                    {{ $vencimentoAlerta['label_reduzida'] ?: $vencimentoAlerta['label'] }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <td style="padding-bottom: 1mm;
                                                   padding-top: 1mm;">
                                @php
                                    $assinatura_sesmt = $treinamento['assinatura_sesmt'] ?? null;
                                    $assinatura_gestor_rh = $treinamento['assinatura_gestor_rh'] ?? null;
                                @endphp
                                <div style="width: 50%; font-size: 5.5pt; text-align: right; float: left;">
                                    @if($assinatura_sesmt && !empty($assinatura_sesmt['url_thumb']))
                                        <table style="width: 100%; padding: 0 2mm;">
                                            <tr>
                                                <td style="text-align: center; vertical-align: bottom; padding-bottom: 0;">
                                                    <img
                                                        src="{{ $assinatura_sesmt['url_thumb'] }}"
                                                        alt="" style="max-width: 63%; max-height: 1cm; display: block; margin: 0 auto -0.2mm;">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="text-align: center; border-top: 0.3mm solid;">
                                                    {{ $assinatura_sesmt['tipo'] ?? 'Não informado' }}
                                                </td>
                                            </tr>
                                        </table>
                                    @elseif($assinatura_sesmt)
                                        <table style="width: 100%; padding: 0 2mm;">
                                            <tr>
                                                <td style="text-align: center; vertical-align: bottom; padding-bottom: 0;">
                                                    <span style="color: blue; text-align: center; font-family: 'Sacramento', cursive; font-size: 6pt;">{{ $assinatura_sesmt['nome'] ?? 'Não informado' }}</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="text-align: center; border-top: 0.3mm solid;">
                                                    {{ $assinatura_sesmt['tipo'] ?? 'Não informado' }}
                                                </td>
                                            </tr>
                                        </table>
                                    @endif
                                </div>

                                <div style="width: 50%; font-size: 5.5pt; text-align: left; float: left;">
                                    @if($assinatura_gestor_rh && !empty($assinatura_gestor_rh['url_thumb']))
                                        <table style="width: 100%; padding: 0 2mm;">
                                            <tr>
                                                <td style="text-align: center; vertical-align: bottom; padding-bottom: 0;">
                                                    <img
                                                        src="{{ $assinatura_gestor_rh['url_thumb'] }}"
                                                        alt="" style="max-width: 55%; max-height: 1cm; display: block; margin: 0 auto -0.2mm;">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="text-align: center; border-top: 0.3mm solid;">{{ $assinatura_gestor_rh['tipo'] ?? 'Não informado' }}</td>
                                            </tr>
                                        </table>
                                    @elseif($assinatura_gestor_rh)
                                        <table style="width: 100%; padding: 0 2mm;">
                                            <tr>
                                                <td style="text-align: center; vertical-align: bottom; padding-bottom: 0;">
                                                    <span style="color: blue; text-align: center; font-family: 'Sacramento', cursive; font-size: 6pt;">{{ $assinatura_gestor_rh['nome'] ?? 'Não informado' }}</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="text-align: center; border-top: 0.3mm solid;">{{ $assinatura_gestor_rh['tipo'] ?? 'Não informado' }}</td>
                                            </tr>
                                        </table>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="width: 50%; min-height: 8.30cm; text-align: center; border-left: 0.1mm dashed #cccccc;">
                    @php
                        $verso_img = $segmento_config['verso_img_base64'] ?? null;
                        if (!$verso_img) {
                            $verso_img = !empty($segmento_config['verso_img']) ? asset($segmento_config['verso_img']) : asset('images/carteira/verso_carteira_alumar.webp');
                        }
                    @endphp
                    <img src="{{ $verso_img }}" style="width: 6.8cm">
                </td>
            </tr>
        </table>
    </div>
@endforeach


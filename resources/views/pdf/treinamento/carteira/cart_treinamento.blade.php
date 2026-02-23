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
                                $cabecalho_img = !empty($segmento_config['cabecalho_img']) ? asset($segmento_config['cabecalho_img']) : asset('images/carteira/cabecalho_carteira_alumar.webp');
                            @endphp
                            @if($empresa['empresa_id'] !== 78862)
                                <td style="text-align: center;">
                                    <img src="{{ $cabecalho_img }}"
                                         style="width: 5.7cm; margin-bottom: -1mm;">
                                </td>
                            @else
                                <td style="text-align: center;">
                                    @if(!empty($empresa['logo']))
                                        <img src="{{$empresa['logo']}}" alt="Logo" title="Logo" style="width: 1.6cm">
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
                            </td>
                        </tr>

                        <tr>
                            <td style="padding-bottom: 1mm;
                                                   padding-top: 1mm;">
                                <div style="width: 50%; font-size: 5.5pt; text-align: right; float: left;">
                                    @if(auth()->user()->Empresa->CarteiraAssinaturaSesmt() && count(auth()->user()->Empresa->CarteiraAssinaturaSesmt()->Anexos) > 0)
                                        <img
                                            src="{{ \App\Models\Sistema::convertBase3(auth()->user()->Empresa->CarteiraAssinaturaSesmt()->Anexos[0]->urlThumb,true) }}"
                                            alt="" style="width: 63%; margin-right: 0.5cm">
                                    @else
                                        <table style="width: 100%; padding: 2mm">
                                            <tr>
                                                <td style="text-align: center">
                                                        <span
                                                            style="color: blue; text-align: center; font-family: 'Sacramento', cursive; font-size: 6pt; position: relative; top: 5px">{{ auth()->user()->Empresa->CarteiraAssinaturaSesmt()->nome ?? 'Não informado' }}</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="text-align: center; border-top: 0.3mm solid; padding-bottom: -50px !important;">
                                                    {{ auth()->user()->Empresa->CarteiraAssinaturaSesmt()->tipo ?? 'Não informado' }}
                                                </td>
                                            </tr>
                                        </table>
                                    @endif
                                </div>

                                <div style="width: 50%; font-size: 5.5pt; text-align: left; float: left;">
                                    @if(auth()->user()->Empresa->CarteiraAssinaturaGestorRh() && count(auth()->user()->Empresa->CarteiraAssinaturaGestorRh()->Anexos) > 0)
                                        <img
                                            src="{{ \App\Models\Sistema::convertBase3(auth()->user()->Empresa->CarteiraAssinaturaGestorRh()->Anexos[0]->urlThumb,true) }}"
                                            alt="" style="width: 55%; margin-left: 0.5cm">
                                    @else
                                        <table style="width: 100%; padding: 2mm">
                                            <tr>
                                                <td style="text-align: center">
                                                        <span
                                                            style="color: blue; text-align: center; font-family: 'Sacramento', cursive; font-size: 6pt; position: relative; top: 5px">{{ auth()->user()->Empresa->CarteiraAssinaturaGestorRh()->nome ?? 'Não informado' }}</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="text-align: center; border-top: 0.3mm solid;">{{ auth()->user()->Empresa->CarteiraAssinaturaGestorRh()->tipo ?? 'Não informado' }}</td>
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
                        $verso_img = !empty($segmento_config['verso_img']) ? asset($segmento_config['verso_img']) : asset('images/carteira/verso_carteira_alumar.webp');
                    @endphp
                    <img src="{{ $verso_img }}" style="width: 4.4cm">
                </td>
            </tr>
        </table>
    </div>
@endforeach


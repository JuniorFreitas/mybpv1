@extends('pdf.treinamento.carteira.layout_carteira')
@section('titulo', 'Carteiras')
@section('conteudo')
    <div class="a4">
        <div
            style="height: 6.40cm; width: 5.10cm; float:left; margin-right: 3px; margin-top: 10px; margin-bottom: 12px;">
            <table cellspacing="0" cellpadding="0" style="width: 100%; height: 0.80cm">
                <tbody>
                <tr>
                    <td colspan="3"
                        style="background: #88B5DF; height: .90cm; text-align: center; vertical-align: bottom;">

                        @if ($treinamento->FeedbackCurriculo->cliente_id == 1)
                            <img src="https://sgibpse.com.br/logo_bpse_color.png" alt="" style="height: 0.6cm">
                            <br>
                        @else
                            <img src="{{ $treinamento->FeedbackCurriculo->Cliente->Logo[0]->url }}"
                                 style="height: 0.6cm" alt="">
                        @endif
                        <br>
                        <span style="font-size: 6pt;">CARTEIRA DE TREINAMENTOS</span>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" style="height: 0.02cm; border-top:none;">
                        Empresa: {{ $treinamento->FeedbackCurriculo->Cliente->razao_social }}
                    </td>
                </tr>
                <tr>
                    <td colspan="3" style="border-top:none; ">
                        Nome: {{ mb_strtoupper($treinamento->FeedbackCurriculo->Curriculo->nome) }}</td>
                </tr>
                <tr>
                    <td colspan="2" style="height: 0.02cm;border-top:none; border-right: none;">
                        Função: {{ mb_strtoupper($treinamento->FeedbackCurriculo->Curriculo->Admissao ? $treinamento->FeedbackCurriculo->Curriculo->Admissao->cargo : null) }}
                    </td>

                    <td style="text-align: center;background: yellow; border-top:none;">ID:</td>
                </tr>
                <tr>
                    <td colspan="2" style="height: 0.02cm; border-top:none; border-right: none">
                        ADM: {{ mb_strtoupper($treinamento->FeedbackCurriculo->Curriculo->Admissao ? $treinamento->FeedbackCurriculo->Curriculo->Admissao->data_admissao : null) }}
                    </td>
                    <td width="44px"
                        style="text-align: center;background: yellow; background: yellow; border-top:none;">{{ mb_strtoupper($treinamento->FeedbackCurriculo->Curriculo->Admissao ? $treinamento->FeedbackCurriculo->Curriculo->Admissao->numero_cracha : null) }}
                    </td>
                </tr>

            </table>

            <table cellspacing="0" cellpadding="0" style="width: 100%; height: 4.50cm;">
                <tbody>
                <tr style="background: #cccccc;">
                    <td style="width: 2.20cm; height: 0.04cm; font-size: 4pt; border-top:none; border-right: none; text-align: center">
                        DESCRIÇÃO DOS <br> TREINAMENTOS
                    </td>
                    <td style="width: 1.10cm; height: 0.04cm; font-size: 4pt; border-top:none;  text-align: center">
                        DATA TREINAMENTO
                    </td>
                    <td style="width: 1.10cm; height: 0.04cm; font-size: 4pt; border-top:none; border-left: none; text-align: center">
                        DATA VENCIMENTO
                    </td>
                </tr>
                @if (count($treinamento->Vencimentos) <= 16)
                    @foreach($treinamento->Vencimentos as $vencimento)
                        <tr>
                            <td style="width: 2.20cm; font-size: 4pt; height: 0.02cm; border-top:none; border-right: none;">
                                {{ $vencimento->label }}
                            </td>
                            <td style="width: 1.10cm; font-size: 4pt; height: 0.02cm; border-top:none;  text-align: center">
                                {{ $vencimento->pivot->data_treinamento }}
                            </td>
                            <td style="width: 1.10cm; font-size: 4pt; height: 0.02cm; border-top:none; border-left: none; text-align: center">
                                {{ $vencimento->pivot->data_vencimento }}
                            </td>
                        </tr>
                    @endforeach
                    @foreach(range(1, 16 - count($treinamento->Vencimentos)) as $r)
                        <tr>
                            <td style="width: 2.20cm; font-size: 4pt; height: 0.02cm; border-top:none; border-right: none; color: white">
                                _
                            </td>
                            <td style="width: 1.10cm; font-size: 4pt; height: 0.02cm; border-top:none;  text-align: center; color: white">
                                _
                            </td>
                            <td style="width: 1.10cm; font-size: 4pt; height: 0.02cm; border-top:none; border-left: none; color: white; text-align: center">
                                _
                            </td>
                        </tr>
                    @endforeach
                @else
                    @foreach($treinamento->Vencimentos as $vencimento)
                        <tr>
                            <td style="width: 2.20cm; font-size: 4pt; height: 0.02cm; border-top:none; border-right: none;">
                                {{ $vencimento->label }}
                            </td>
                            <td style="width: 1.10cm; font-size: 4pt; height: 0.02cm; border-top:none;  text-align: center">

                            </td>
                            <td style="width: 1.10cm; font-size: 4pt; height: 0.02cm; border-top:none; border-left: none; text-align: center">
                                {{ $vencimento->pivot->data_vencimento }}
                            </td>
                        </tr>
                    @endforeach
                @endif
                <tr>
                    <td style="width: 2.20cm; font-size: 3.8pt; height: 0.02cm;  border-top:none; border-right: none;text-align: center;">
                                <span class="font-assinatura">
                                    @if ($treinamento->FeedbackCurriculo->Cliente->id == 3)
                                        José Meneses Barros
                                    @else
                                        Gilson Pinto
                                    @endif
                                </span>
                    </td>
                    <td align="center" colspan="2" style="height: 0.02cm; border-top:none; ">
                                <span class="font-assinatura">
                                     @if ($treinamento->FeedbackCurriculo->Cliente->id == 3)
                                        Josue Góis
                                    @else
                                        Ronan Sombra
                                    @endif
                                </span>
                    </td>
                </tr>
                <tr>
                    <td align="center"
                        style="width: 2.20cm; font-size: 3.8pt; height: 0.02cm; border-top:none; border-right: none; text-align: center;">
                        @if ($treinamento->FeedbackCurriculo->Cliente->id == 3)
                            SESMT
                        @else
                            SSMA
                        @endif
                    </td>
                    <td align="center" style="height: 0.02cm; border-top:none; " colspan="2">
                        GERENTE OU RH
                    </td>
                </tr>

                <tr style="background: rgb(136,181,223); border-bottom: none !important;">
                    <td align="center" colspan="3" style="height: 0.02cm; border-top: none">“
                        Você é responsável pela reciclagem dos TREINAMENTOS ”
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <div
            style="height: 6.40cm; width: 5.10cm; float:left; margin-right: 5px; margin-top: 10px; margin-bottom: 12px;">
            <table cellspacing="0" cellpadding="0" style="width: 100%; height: 5.28cm">
                <tbody>
                <tr>
                    <td colspan="3"
                        style="color: red; height: .90cm; text-align: center; vertical-align: middle;">
                            <span
                                style="font-size: 6pt;">EMERGÊNCIA LIGUE: 1199 (RAMAL) 0800 727 1199 ou 3301-1199</span>
                    </td>
                </tr>

                <tr style="height: 151px; word-break: break-word;">
                    <td
                        style="text-align: center; vertical-align: top; border-top:none; padding: 2px 5px 0px 5px;">
                        <span style="font-size: 4.5pt; line-height: 6.90pt; ">
                            <br>
                            <strong>POLITICA DE EHS</strong> <br>
                            {{$treinamento->FeedbackCurriculo->Cliente->politica_ehs}}
                        </span>
                    </td>
                </tr>


                <tr>
                    <td style="width: 2.20cm; font-size: 3.8pt; height: 0.02cm; border-top:none; text-align: center">
                        CONTATO {{ $treinamento->FeedbackCurriculo->Cliente->apelido }}
                        : {{$treinamento->FeedbackCurriculo->Cliente->tel_principal}}
                    </td>
                </tr>

                <tr>
                    <td style="width: 2.20cm; font-size: 3.8pt; height: 0.02cm; border-top:none; text-align: center; font-weight: bold;">
                        COR PROIBIDA DO MÊS
                    </td>
                </tr>

                </tbody>
            </table>


            <table cellspacing="0" cellpadding="0" style="width: 100%; ">
                <tbody>
                <tr>
                    <td style="border-top: none; height: 0.20cm; border-right: none; font-size: 3.5pt; text-align: center">
                        vermelho
                    </td>
                    <td style="border-top: none; height: 0.20cm; border-right: none; font-size: 3.5pt; text-align: center">
                        azul
                    </td>
                    <td style="border-top: none; height: 0.20cm; border-right: none; font-size: 3.5pt; text-align: center">
                        amarelo
                    </td>
                    <td style="border-top: none; height: 0.20cm; border-right: none; font-size: 3.5pt; text-align: center">
                        verde
                    </td>
                    <td style="border-top: none; height: 0.20cm; width: 1.80cm; background: #f5f5c5; font-size: 3.5pt; text-align: center; vertical-align: center"
                        rowspan="4">
                        Não utilizar <br> ferramenta que esteja <br> com a cor proíbida do <br>
                        mês
                    </td>
                </tr>
                <tr>
                    <td style="height: 0.20cm;font-size: 3.5pt; border-top: none; border-right: none; background: red; color: white; text-align: center">
                        JAN
                    </td>
                    <td style="height: 0.20cm;font-size: 3.5pt; border-top: none; border-right: none; background: blue; color: white; text-align: center">
                        FEV
                    </td>
                    <td style="height: 0.20cm;font-size: 3.5pt; border-top: none; border-right: none; background: yellow; color: black; text-align: center">
                        MAR
                    </td>
                    <td style="height: 0.20cm;font-size: 3.5pt; border-top: none; border-right: none; background: green; color: black; text-align: center">
                        ABR
                    </td>
                </tr>
                <tr>
                    <td style="height: 0.20cm;font-size: 3.5pt; border-top: none; border-right: none; background: red; color: white; text-align: center">
                        MAI
                    </td>
                    <td style="height: 0.20cm;font-size: 3.5pt; border-top: none; border-right: none; background: blue; color: white; text-align: center">
                        JUN
                    </td>
                    <td style="height: 0.20cm;font-size: 3.5pt; border-top: none; border-right: none; background: yellow; color: black; text-align: center">
                        JUL
                    </td>
                    <td style="height: 0.20cm;font-size: 3.5pt; border-top: none; border-right: none; background: green; color: black; text-align: center">
                        AGO
                    </td>
                </tr>
                <tr>
                    <td style="height: 0.20cm;font-size: 3.5pt; border-top: none; border-right: none; background: red; color: white; text-align: center">
                        SET
                    </td>
                    <td style="height: 0.20cm;font-size: 3.5pt; border-top: none; border-right: none; background: blue; color: white; text-align: center">
                        OUT
                    </td>
                    <td style="height: 0.20cm;font-size: 3.5pt; border-top: none; border-right: none; background: yellow; color: black; text-align: center">
                        NOV
                    </td>
                    <td style="height: 0.20cm;font-size: 3.5pt; border-top: none; border-right: none; background: green; color: black; text-align: center">
                        DEZ
                    </td>
                </tr>
                </tbody>
            </table>

        </div>

        <div class="clearfix"></div>
        <div class="etiqueta">
            <div class="logo">
                @if ($treinamento->FeedbackCurriculo->cliente_id == 1)
                    <img src="https://sgibpse.com.br/logo_bpse_color.png" alt="">
                    <br>
                @else
                    <img src="{{ $treinamento->FeedbackCurriculo->Cliente->Logo[0]->url }}" alt="">
                @endif
            </div>
            <div class="content">
                <div class="boxBlack">
                    <div class="circuloRed">
                        <h3 class="tituloPerigo">PERIGO</h3>
                        <h3 class="tituloDanger">DANGER</h3>
                    </div>
                </div>

                <h3 class="text-center" style="margin-top: 15px">NÃO USE, MOVA OU
                    OPERE ENQUANTO ESTA
                    ETIQUETA ESTIVER
                    COLOCADA</h3>

                <h4 class="text-center colorRed">
                    Do not use, move or operate
                    while this label is placed
                </h4>

                <h3 class="text-center" style="margin-top: 15px">
                    QUEM OPERAR O EQUIPAMENTO OU REMOVER A ETIQUETA ESTÁ SUJEITO A DEMISSÃO
                </h3>

                <h4 class="text-center colorRed">
                    Whoever operate the equipment or remove this label can be dismissed
                </h4>
            </div>
        </div>

        <div class="etiqueta">
            <div class="logo">
                @if ($treinamento->FeedbackCurriculo->cliente_id == 1)
                    <img src="https://sgibpse.com.br/logo_bpse_color.png" alt="">
                    <br>
                @else
                    <img src="{{ $treinamento->FeedbackCurriculo->Cliente->Logo[0]->url }}" alt="">
                @endif
            </div>
            <div class="content">
                <div class="boxBlack">
                    <div class="circuloRed">
                        <h3 class="tituloPerigo">PERIGO</h3>
                        <h3 class="tituloDanger">DANGER</h3>
                    </div>
                </div>

                <div style="display: flex; flex-direction: row; justify-content: space-between;">
                    <div style="width: 45%;">
                        <h6 class="text-center" style="font-weight: bold;">
                            HOMENS TRABALHANDO NÃO OPERE ESTE EQUIPAMENTO
                        </h6>
                    </div>
                    <div style="width: 45%;">
                        <h6 class="text-center colorRed">
                            MEN <br> WORKING DO NOT OPERATE THIS EQUIPMENT
                        </h6>
                    </div>
                </div>

                <div style="display: flex; flex-direction: row; justify-content: center;">
                    <div class="fotoTres">
                        <img
                            src="{{ count($treinamento->FeedbackCurriculo->Curriculo->Anexo) > 0 ? $treinamento->FeedbackCurriculo->Curriculo->Anexo[0]->url : asset('sem_foto.png')}}"
                            style="max-height: 4cm; max-width: 3cm; border: solid 0.1mm black;" alt="">
                    </div>
                </div>

                <h5 class="text-center">RAMAL DE EMERGÊNCIA: <span style="font-size: 22px;">1199</span></h5>
                <h6 class="text-center colorRed">Extension line for emergency: 1199</h6>

                <h6 style="margin-top: 5px;">Nome/<span class="colorRed">Name: <span
                            style="font-size: 6.8pt;">{{ mb_strtoupper($treinamento->FeedbackCurriculo->Curriculo->nome) }}</span></span>
                </h6>
                <h6 style="margin-top: 5px;">CHAPA/ID<span class="colorRed">Plate/ID: <span
                            style="font-size: 6.8pt;">{{ mb_strtoupper($treinamento->FeedbackCurriculo->Curriculo->Admissao ? $treinamento->FeedbackCurriculo->Curriculo->Admissao->numero_cracha : null) }}</span></span>
                </h6>
                <h6 style="margin-top: 5px;">AREA/EMPRESA/<span class="colorRed">Company: <span
                            style="font-size: 6.8pt;">{{ $treinamento->FeedbackCurriculo->Cliente->apelido }}</span></span>
                </h6>
                <h6 style="margin-top: 5px;">FONE/RAMAL/<span class="colorRed">Extension: <span
                            style="font-size: 6.8pt;">
                                    {{ $treinamento->FeedbackCurriculo->Curriculo->Admissao ? $treinamento->FeedbackCurriculo->Curriculo->Admissao->area_etiqueta_id ? \App\Models\Admissao::getNumeroSupervisor($treinamento->FeedbackCurriculo->cliente_id,$treinamento->FeedbackCurriculo->Curriculo->Admissao->area_etiqueta_id) : null : null }}
                                </span></span>
                </h6>
                <h6 style="margin-top: 5px;">DATA/<span class="colorRed">Date: <span
                            style="font-size: 6.8pt;">PERMANENTE</span></span>
                </h6>
            </div>
        </div>
    </div>
@endsection

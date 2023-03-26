@extends('pdf.treinamento.carteira.layout_carteira')
@section('titulo', 'Carteiras')
@section('conteudo')

    <div style="padding: 20px">
        <?php $cont = 0; ?>
        @foreach($treinamentos as $treinamento)
                <table style="border: 0.1mm solid; width:14.80cm; margin-bottom: 3px;">
                    <tr>
                        <td style="width: 50%; height: 8.30cm; vertical-align: top;">
                            <table>
                                <tr>
                                    <td style="text-align: center;">
                                        <img src="{{asset('images/carteira/cabecalho_carteira_alumar.webp')}}" style="width: 5.7cm; margin-bottom: -1mm;">
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align: left; height: 6cm; vertical-align: top">
                                        <ul style="list-style: none; top: 29px; padding-left: 0.6mm">
                                            <li style="font-size: 5.5pt; margin-top: 1.5px; margin-bottom: 0.7mm; display: flex; align-items: center;">
                                                <div style="float: left; width: 100%; margin-bottom: 0px; background: #d9d9d9; padding: 4px; border-radius: 6px">
                                                    <strong>Nome:</strong> {{ mb_strtoupper($treinamento->FeedbackCurriculo->Curriculo->nome) }}
                                                </div>
                                            </li>
                                            <li style="font-size: 5.5pt; margin-top: 1.5px; margin-bottom: 0px; display: flex; align-items: center;">
                                                <div style="float: left; width: 76%; margin-bottom: 0px; background: #d9d9d9; padding: 4px; border-radius: 6px">
                                                    <strong>Função:</strong> {{ mb_strtoupper($treinamento->FeedbackCurriculo->Admissao ? $treinamento->FeedbackCurriculo->Admissao->cargo : null) }}
                                                </div>
                                                <div style="float: left; width: 23%; margin-bottom: 0px; margin-left: 1%; background: #d9d9d9; padding: 4px; border-radius: 6px">
                                                    <strong>Chapa:</strong> {!!  $treinamento->FeedbackCurriculo->Admissao->numero_cracha ?:"&nbsp" !!}
                                                </div>
                                            </li>
                                        </ul>
                                        <div style="
                                        background: white;height: 9px;width: 269px; padding-top: 0.7mm; padding-bottom: 0.3mm; font-size: 5.5pt;font-family: 'Arial', Verdana, sans-serif;font-weight: bold;display: flex;justify-content: space-between;">
                                            <p style="width: 180px; text-align: center;">Treinamentos</p>
                                            <p style="width: 60px; text-align: center;">Data</p>
                                            <p style="width: 48px; text-align: center;">Reciclagem</p>
                                        </div>
                                        <ul style="list-style: none; top: 29px; padding-left: 0.6mm">
                                            @foreach($treinamento->Vencimentos as $key => $vencimento)
                                                @if($vencimento->exibir_na_carteira)
                                                    <li style="font-size: {!! strlen($vencimento->label_reduzida) <= 20 ? '5pt' : '4.7pt' !!}; margin-top: 1.5px; margin-bottom: 0px; display: flex; align-items: center;">
                                                        <div
                                                            style="float: left; width: 164px; margin-bottom: 0px; background: #d9d9d9; padding: 2px; border-radius: 2px"> {{ $vencimento->label_reduzida ? mb_strimwidth($vencimento->label_reduzida, 0, 58) : "Não informada" }}</div>
                                                        <div
                                                            style="float: left; margin-bottom: 0px; font-size: 5pt; margin-left: 3px; margin-top: 0px; width:42px; background: #d9d9d9; padding: 2px; border-radius: 2px; text-align: center"> {{ $vencimento->pivot->data_treinamento  }}</div>
                                                        <div
                                                            style="float: left; margin-bottom: 0px; font-size: 5pt; margin-left: 3px; margin-top: 0px;  width:43px; background: #d9d9d9; padding: 2px; border-radius: 2px; text-align: center"> {{ $vencimento->pivot->data_vencimento  }}</div>
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
                                                <img src="{{ \App\Models\Sistema::convertBase3(auth()->user()->Empresa->CarteiraAssinaturaSesmt()->Anexos[0]->urlThumb,true) }}" alt="" style="width: 63%; margin-right: 0.5cm">
                                            @else
                                                <table style="width: 100%; padding: 2mm">
                                                    <tr>
                                                        <td style="text-align: center">
                                                            <span style="color: blue; text-align: center; font-family: 'Sacramento', cursive; font-size: 6pt; position: relative; top: 5px">{{ auth()->user()->Empresa->CarteiraAssinaturaSesmt()->nome ?? 'Não informado' }}</span>
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
                                                <img src="{{ \App\Models\Sistema::convertBase3(auth()->user()->Empresa->CarteiraAssinaturaGestorRh()->Anexos[0]->urlThumb,true) }}" alt="" style="width: 55%; margin-left: 0.5cm">
                                            @else
                                                <table style="width: 100%; padding: 2mm">
                                                    <tr>
                                                        <td style="text-align: center">
                                                            <span style="color: blue; text-align: center; font-family: 'Sacramento', cursive; font-size: 6pt; position: relative; top: 5px">{{ auth()->user()->Empresa->CarteiraAssinaturaGestorRh()->nome ?? 'Não informado' }}</span>
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
                            <img src="{{asset('images/carteira/verso_carteira_alumar.webp')}}" style="width: 4.4cm">
                        </td>
                    </tr>
                </table>
        @endforeach
    </div>


    <div style="clear: both"></div>
    <div style="page-break-after: always"></div>

    <div class="a4" style="padding: 20px">
        <?php $cont = 0; ?>
        @foreach($treinamentos as $treinamento)
            <div style="margin-top: 20px" class="etiqueta">
                <div class="logo">
                    @if(count(auth()->user()->ClientesLogo) > 0)
                        <img
                            src="{{env('AWS_URL')}}/arquivos/disco-cliente/{{auth()->user()->ClientesLogo[0]->thumb}}"
                            alt="Logo" title="Logo">
                        <br>
                    @endif
                    <br>
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

            <div style="margin-top: 20px" class="etiqueta">
                <div class="logo">
                    @if(count(auth()->user()->ClientesLogo) > 0)
                        <img
                            src="{{env('AWS_URL')}}/arquivos/disco-cliente/{{auth()->user()->ClientesLogo[0]->thumb}}"
                            alt="Logo" title="Logo">
                        <br>
                    @endif
                    <br>
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
                                src="{{ count($treinamento->FeedbackCurriculo->Curriculo->FotoTres) > 0 ? $treinamento->FeedbackCurriculo->Curriculo->FotoTres[0]->url : asset('sem_foto.png')}}"
                                style="max-height: 4cm; max-width: 3cm; border: solid 0.1mm black;" alt="">
                        </div>
                    </div>

                    <h5 class="text-center">RAMAL DE EMERGÊNCIA: <span style="font-size: 22px;">1199</span></h5>
                    <h6 class="text-center colorRed">Extension line for emergency: 1199</h6>

                    <h6 style="margin-top: 5px;">Nome/<span class="colorRed">Name: <span
                                style="font-size: 6.8pt;">{{ mb_strtoupper($treinamento->FeedbackCurriculo->Curriculo->nome) }}</span></span>
                    </h6>
                    <h6 style="margin-top: 5px;">CHAPA/ID<span class="colorRed">Plate/ID: <span
                                style="font-size: 6.8pt;">{{ mb_strtoupper($treinamento->FeedbackCurriculo->Admissao ? $treinamento->FeedbackCurriculo->Admissao->numero_cracha : null) }}</span></span>
                    </h6>
                    <h6 style="margin-top: 5px;">AREA/EMPRESA/<span class="colorRed">Company: <span
                                style="font-size: 6.8pt;">{{ $treinamento->FeedbackCurriculo->Empresa->nome_fantasia }}</span></span>
                    </h6>
                    <h6 style="margin-top: 5px;">FONE/RAMAL/<span class="colorRed">Extension: <span
                                style="font-size: 6.8pt;">
                                        {{ $treinamento->telefone ?? "Não Informado" }}
                                    </span></span>
                    </h6>
                    <h6 style="margin-top: 5px;">DATA/<span class="colorRed">Date: <span
                                style="font-size: 6.8pt;">PERMANENTE</span></span>
                    </h6>
                </div>
            </div>
                <?php $cont++ ?>
            @if ($cont==2)
                    <?php $cont = 0; ?>
                <div style="page-break-after: always"></div>
            @endif
        @endforeach
    </div>
@endsection

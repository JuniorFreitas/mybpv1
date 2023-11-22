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
                                    <img src="{{asset('images/carteira/cabecalho_carteira_alumar.webp')}}"
                                         style="width: 5.7cm; margin-bottom: -1mm;">
                                </td>
                            </tr>
                            <tr>
                                <td style="text-align: left; height: 6cm; vertical-align: top">
                                    <ul style="list-style: none; top: 29px; padding-left: 0.6mm">
                                        <li style="font-size: 5.5pt; margin-top: 1.5px; margin-bottom: 0.7mm; display: flex; align-items: center;">
                                            <div
                                                style="float: left; width: 100%; margin-bottom: 0px; background: #d9d9d9; padding: 4px; border-radius: 6px">
                                                <strong>Nome:</strong> {{ mb_strtoupper($treinamento->FeedbackCurriculo->Curriculo->nome) }}
                                            </div>
                                        </li>
                                        <li style="font-size: 5.5pt; margin-top: 1.5px; margin-bottom: 0px; display: flex; align-items: center;">
                                            <div
                                                style="float: left; width: 76%; margin-bottom: 0px; background: #d9d9d9; padding: 4px; border-radius: 6px">
                                                <strong>Função:</strong> {{ mb_strtoupper($treinamento->FeedbackCurriculo->Admissao ? $treinamento->FeedbackCurriculo->Admissao->cargo : null) }}
                                            </div>
                                            <div
                                                style="float: left; width: 23%; margin-bottom: 0px; margin-left: 1%; background: #d9d9d9; padding: 4px; border-radius: 6px">
                                                <strong>Chapa:</strong> {!! $treinamento->FeedbackCurriculo->Admissao->numero_cracha ??"&nbsp" !!}
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
                <div class="logo"></div>
                <div class="content">
                    <div class="boxBlack">
                        <div class="circuloRed">
                            <h3 class="tituloPerigo" style="font-size: 20pt;">PERIGO</h3>
                        </div>
                    </div>

                    <h3 class="text-center colorRed"
                        style="margin-top: 15px; font-weight: bold !important; font-size: 16pt;">
                        NÃO USE, MOVA OU OPERE ENQUANTO ESTA ETIQUETA ESTIVER COLOCADA
                    </h3>

                    <h3 class="text-center" style="margin-top: 1cm; font-weight: bold !important; font-size: 16pt;">
                        QUEM OPERAR O EQUIPAMENTO OU REMOVER A ETIQUETA ESTÁ SUJEITO A DEMISSÃO
                    </h3>

                    @if(count(auth()->user()->ClientesLogo) > 0)
                        <div style="display: flex; margin: 0 auto; margin-top: 0.7cm;">
                            <img
                                src="{{env('AWS_URL')}}/arquivos/disco-cliente/{{auth()->user()->ClientesLogo[0]->thumb}}"
                                alt="Logo" title="Logo" style="width: 3cm">
                        </div>
                        <br>
                    @endif
                </div>
            </div>

            <div style="margin-top: 20px" class="etiqueta">
                <div class="logo"></div>
                <div class="content">
                    <div class="boxBlack">
                        <div class="circuloRed">
                            <h3 class="tituloPerigo" style="font-size: 20pt;">PERIGO</h3>
                        </div>
                    </div>
                    <h2 class="text-center"
                        style="margin-top: 0.3cm; color: red; text-decoration: underline; font-size: 24pt;">
                        CUIDADO!</h2>


                    <div style="display: flex; flex-direction: row; align-items: center; margin-top: 0.3cm">
                        <div style=" width: 3.9cm; font-size: 20pt; ">
                            <h6 class="text-center" style="font-weight: bold;">
                                HOMENS TRABALHANDO NÃO OPERE ESTE EQUIPAMENTO
                            </h6>
                        </div>

                        <div class="fotoTres"
                             style="object-fit: cover;
                                background-size: cover;
                                background-image: url(
                                {{ count($treinamento->FeedbackCurriculo->Curriculo->FotoTres) > 0 ? $treinamento->FeedbackCurriculo->Curriculo->FotoTres[0]->url: asset('sem_foto.png')}}
                                )">
                        </div>
                    </div>

                    <h5 style="font-size: 13pt; font-weight: bold; color: red; margin-top: 0.2cm; margin-bottom: 0.2cm;">
                        RAMAL DE EMERGÊNCIA: 1199
                    </h5>
                    <h6 style="margin-top: 5px; font-size: 10.5pt;">
                        NOME: <strong>
                            {{ mb_strtoupper($treinamento->FeedbackCurriculo->Curriculo->nome) }}
                        </strong>
                    </h6>
                    <h6 style="margin-top: 5px; font-size: 10.5pt;">
                        CHAPA/ID: <strong>
                            {{ mb_strtoupper($treinamento->FeedbackCurriculo->Admissao ? $treinamento->FeedbackCurriculo->Admissao->numero_cracha : null) }}
                        </strong>
                    </h6>
                    <h6 style="margin-top: 5px; font-size: 10.5pt;">AREA/EMPRESA:
                        <strong>
                            <span style="color: #0e6fb6 !important">
                            {{ $treinamento->FeedbackCurriculo->Empresa->nome_fantasia }}
                            </span>
                        </strong>
                    </h6>
                    <h6 style="margin-top: 5px; font-size: 10.5pt;">
                        FONE/RAMAL:
                        <strong>{{ $treinamento->telefone ?? "Não Informado" }}</strong>
                    </h6>
                    <h6 style="margin-top: 5px;font-size: 10.5pt;">DATA:
                        <strong>
                                <span style=" color: #0e6fb6 !important">
                                    PERMANENTE
                                </span>
                        </strong>
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

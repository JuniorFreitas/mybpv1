@extends('pdf.treinamento.carteira.layout_carteira')
@section('titulo', 'Carteiras')
@section('conteudo')

    <div style="padding: 20px">
        <?php $cont = 0; ?>
        @foreach($treinamentos as $treinamento)

            <div style="width: 14.80cm; height: 8.30cm; float:left; margin-top: 35px;
            background: url({{asset('images/carteira/carteira_alumar_2022_limpa.webp')}});
            background-size: 14.80cm 8.30cm;
            border: 0.1mm black solid;
            margin-right: 2px;
            ">
                <p class="nome"
                   style=" font-size: 5pt;  position: relative; top: 1.40cm; left: 1.2cm">
                    {{ mb_strtoupper($treinamento->FeedbackCurriculo->Curriculo->nome) }}
                </p>
                <p class="funcao"
                   style="font-size: 4.5pt;  position: relative; top: 2cm; left: 1.2cm">
                    {{ mb_strtoupper($treinamento->FeedbackCurriculo->Admissao ? $treinamento->FeedbackCurriculo->Admissao->cargo : null) }}
                </p>
                <p class="chapa"
                   style="font-size: 4.5pt;position: relative; top: 1.77cm; left: 3.90cm">
                    {!!  $treinamento->FeedbackCurriculo->Admissao->numero_cracha ?:"&nbsp" !!}
                </p>

                <div style=" position: relative; top: 1.2cm; left: 0.25cm; height: 255px;">
                    <ul style="list-style: none; position: relative; top: 1.0cm; left: 0.87cm;">
                        @foreach($treinamento->Vencimentos as $key => $vencimento)
                            @if($key <= 11)
                                <li style=" font-size: 4.6pt; margin-top: 1.5px; margin-bottom: 0px; display: flex; align-items: center;">
                                    <div
                                        style="float: left; width: 80px; margin-bottom: 0px; background: #d9d9d9; padding: 2px; border-radius: 2px"> {{ $vencimento->label }}</div>
                                    <div
                                        style="float: left; margin-bottom: 0px; margin-left: 3px; margin-top: 0px; width:50px; background: #d9d9d9; padding: 2px; border-radius: 2px; text-align: center"> {{ $vencimento->pivot->data_treinamento  }}</div>
                                    <div
                                        style="float: left; margin-bottom: 0px; margin-left: 3px; margin-top: 0px;  width:50px; background: #d9d9d9; padding: 2px; border-radius: 2px; text-align: center"> {{ $vencimento->pivot->data_vencimento  }}</div>
                                    <div style="clear: both"></div>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
                <div style="margin-left: 55px; margin-top: 5.4px">
                    <p style="font-family: 'Sacramento', cursive; color: blue; width: 75px; font-size: 5.5pt; text-align: center; float: left;">
                        {{ auth()->user()->Empresa->CarteiraAssinaturaSesmt() ? auth()->user()->Empresa->CarteiraAssinaturaSesmt()->nome : 'Não informado' }}
                    </p>

                    <p style="font-family: 'Sacramento', cursive; color: blue; width: 75px; margin-left: 16.8px; font-size: 5.5pt; text-align: center; float: left;">
                        @if(auth()->user()->Empresa->CarteiraAssinaturaGestorRh() && auth()->user()->Empresa->CarteiraAssinaturaGestorRh()->arquivo_id)
                            <img src="" alt="">
                        @else
                            {{ auth()->user()->Empresa->CarteiraAssinaturaGestorRh() ?  auth()->user()->Empresa->CarteiraAssinaturaGestorRh()->nome : 'Não informado' }}
                        @endif
                    </p>

                </div>
            </div>
                <?php $cont++ ?>
            @if ($cont==3)
                    <?php $cont = 0; ?>
                <div style="page-break-after: always"></div>
            @endif
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
                                        {{ $treinamento->FeedbackCurriculo->Admissao ? $treinamento->FeedbackCurriculo->Admissao->area_etiqueta_id ? \App\Models\Admissao::getNumeroSupervisor($treinamento->FeedbackCurriculo->empresa_id,$treinamento->FeedbackCurriculo->Admissao->area_etiqueta_id) : null : null }}
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

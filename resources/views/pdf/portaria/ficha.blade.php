<!doctype html>
<html lang="pt-Br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Portaria</title>
    {{--    <link href="{{ mix('css/app.css') }}" rel="stylesheet">--}}
    <style type="text/css">
        * {
            margin: 0;
            padding: 0;
            font-family: 'Arial', Verdana, sans-serif;
        }

        @page {
            margin: 0cm 0cm;
            height: 29.70cm;
        }

        body {
            width: 21cm;
            height: 29.70cm;
            margin-top: .5cm;
            margin-left: .5cm;
            margin-right: .5cm;
            margin-bottom: .5cm;
            font-family: 'Arial', sans-serif;
            font-size: 9.5pt;
        }

        .a4 {
            width: 21cm;
            /*height: 29.70cm;*/
            margin-top: .5cm;
            margin-left: .5cm;
            margin-right: .5cm;
            margin-bottom: .5cm;
            /*border: 1px solid black;*/
        }


        .principal {
            height: 13.05cm;
            width: 19.70cm;
            margin-left: 10px;
            border: 2px solid black;
            border-radius: 0.5cm;
            margin-bottom: 0.5cm;
            margin-top: 0.5cm;
        }

        .principal2 {
            height: 11.55cm;
            width: 19.35cm;
            /*border: 2px solid red;*/
            border-radius: 0.5cm;
            margin: 0.1cm;
            padding: 5px;
        }

        #printPageButton {
            padding: 5px 13px 5px 13px;
            cursor: pointer;
            margin-left: 20px;
            background-color: #184056;
            color: white;
            border: none;
            border-radius: 5px;
            transition: background-color 1s;
        }

        #printPageButton:hover {
            background-color: #045588;
        }

        @media print {
            #printPageButton {
                display: none;
            }
        }

        .b-bottom {
            border-bottom: 0.01mm solid black;
        }

        .b-right {
            border-right: 0.01mm solid black;
        }

        .b-top {
            border-top: 0.01mm solid black;
        }

        .b-left {
            border-left: 0.01mm solid black;
        }

        .title {
            font-size: 18pt;
            font-weight: bold;
        }

        .linhas {
            width: 16.085cm;
            height: 37.7px;
        }

        .txt {
            font-weight: bold;
            margin-left: 15px;
            margin-right: 25px;
            padding-top: 5px;
            width: 120px;
            float: left;
        }

        .rsptxt {
            padding-top: 5px;
        }

        .linhaComum {
            height: 42px;
        }

        .txtComum {
            float: left;
            font-weight: bold;
            margin-left: 15px;
            margin-right: 25px;
            padding-top: 5px
        }

        .rsptxtComum {
            margin-left: 15px;
            margin-right: 25px;
            padding-top: 5px;
        }

        .d-flex {
            display: -ms-flexbox !important;
            display: flex !important;
        }

        .col-2 {
            -ms-flex: 0 0 16.666667%;
            flex: 0 0 16.666667%;
            max-width: 16.666667%;
        }

        .col-10 {
            -ms-flex: 0 0 83.333333%;
            flex: 0 0 83.333333%;
            max-width: 83.333333%;
        }

        .float-left {
            float: left !important;
        }

        .float-right {
            float: right !important;
        }

        .caixaSelecao{
            width: 16px;
            height: 16px;
            border: 1px solid black;
            text-align: center;
            margin-right: 5px;
            display: flex;
            align-items: stretch;
            justify-content: space-around;
            margin-top: -2px;
            margin-left: 14px;
        }

        :root {
            --breakpoint-xs: 0;
            --breakpoint-sm: 576px;
            --breakpoint-md: 768px;
            --breakpoint-lg: 992px;
            --breakpoint-xl: 1200px;
            --font-family-sans-serif: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
            --font-family-monospace: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        html {
            font-family: sans-serif;
            line-height: 1.15;
            -webkit-text-size-adjust: 100%;
            -webkit-tap-highlight-color: rgba(0, 0, 0, 0);
        }

    </style>
</head>
<body>
<button id="printPageButton" onClick="window.print()">IMPRIMIR</button>
<?php $cont = 0; ?>
@foreach($feedbacks as $candidato)
    <div class="a4">
        <div class="principal">
            <div class="principal2">
                <div class="d-flex b-bottom pb-1">
                    <div class="col-2">
                        <img src="{{ \App\Models\Sistema::convertBase(public_path('img/alumar.png'),'public') }}"
                             style="height: 38px" alt="">
                    </div>
                    <div class="col-10">
                        <span class="title">CONTRATADA - CADASTRO DE EMPREGADOS</span>
                    </div>
                </div>

                <div class="b-bottom b-right b-left float-right" style="width: 3cm; height: 4cm">
                    <img
                        src="{{ count($candidato->Curriculo->FotoTres) > 0 ? $candidato->Curriculo->FotoTres[0]->url : asset('sem_foto.png')}}"
                        style="max-height: 4cm; max-width: 3cm;" alt="">
                </div>

                <div class="b-bottom  b-left float-left linhas">
                    <div class="txt">Empresa:</div>
                    <div class="rsptxt">
                        {{ $candidato->Empresa->razao_social }}
                    </div>
                </div>

                <div class="b-bottom  b-left float-left linhas">
                    <div class="txt">Sub. Contratada:</div>
                    <div class="rsptxt">
                        @if ($candidato->Admissao)
                            {{ $candidato->Admissao->tipo_admissao == 'TEMPORARIO' ? 'NACIONAL' : null }}
                        @endif
                    </div>
                </div>

                <div class="b-bottom  b-left float-left linhas">
                    <div class="txt">Aprovador - A&L:</div>
                    <div class="rsptxt">

                    </div>
                </div>

                <div class="b-bottom  b-left float-left linhas">
                    <div class="txt">Nome:</div>
                    <div class="rsptxt">
                        {{ $candidato->Curriculo->nome}}
                    </div>
                </div>

                <div class="b-bottom b-right b-left float-left linhas" style="width: 100%">
                    <div class="txt" style="width: 40px;">Endereço:</div>
                    <div class="rsptxt">
                        {{ $candidato->Curriculo->endereco_completo}}
                    </div>
                </div>

                <div class="b-bottom b-left b-right float-left linhaComum" style="width: 227px;">
                    <div class="txtComum" style="width: 100%; margin-bottom: 4px;">Nº Reg. Minist. Trabalho:</div>
                    <div class="rsptxtComum">
                    </div>
                </div>

                <div class="b-bottom  b-right float-left linhaComum" style="width:382px">
                    <div class="txtComum" style="width: 100%; margin-bottom: 4px;">Carteira de Identidade / Emitente:
                    </div>
                    <div class="rsptxtComum">
                        @if ($candidato->Curriculo->rg)
                            {{ $candidato->Curriculo->rg}} | {{ $candidato->Curriculo->orgao_expeditor}}
                        @endif
                    </div>
                </div>

                <div class="b-bottom b-right float-left linhaComum" style="width: 2.97cm;">
                    <div class="txtComum" style="margin-left: 0px; margin-right: 0px; padding-left: 10px;">CPF:</div>
                    <div class="rsptxtComum" style="margin-left: 0px; margin-right: 0px; padding-left: 10px;">
                        {{ $candidato->Curriculo->cpf}}
                    </div>
                </div>

                <div class="b-bottom b-left b-right float-left linhaComum" style="width: 176px;">
                    <div class="txtComum" style="width: 100%; margin-bottom: 4px;">Data de Nasc.:</div>
                    <div class="rsptxtComum">
                        {{ $candidato->Curriculo->nascimento}}
                    </div>
                </div>

                <div class="b-bottom b-right float-left linhaComum" style="width: 184px">
                    <div class="txtComum" style="width: 100%; margin-bottom: 4px;">Data de Admis.:</div>
                    <div class="rsptxtComum">
                        {{ $candidato->Admissao ? $candidato->Admissao->data_admissao : null }}
                    </div>
                </div>

                <div class="b-bottom b-right float-left linhaComum" style="width: 361px;">
                    <div class="txtComum" style="width: 100%; margin-bottom: 4px;">Função:</div>
                    <div class="rsptxtComum" style="width: 100%">
                        {{ $candidato->Admissao ? $candidato->Admissao->funcao : null }}
                    </div>
                </div>

                <div class="b-bottom b-left b-right float-left linhaComum" style="width: 240px;">
                    <div class="txtComum" style="width: 100%; margin-bottom: 4px;">Ass. Resp.Empresa:</div>
                    <div class="rsptxtComum">

                    </div>
                </div>

                <div class="b-bottom b-right float-left linhaComum" style="width: 275px">
                    <div class="txtComum" style="width: 100%; margin-bottom: 4px;">O colaborador acessará a área do porto?</div>
                    <div class="rsptxtComum" style="display: inline-flex; margin: -5px 0px 0px 65px;">
                        <div class="caixaSelecao">{{ $candidato->Admissao->acessar_area_porto == 'Sim' ? 'X' : '' }}</div>Sim
                        <div class="caixaSelecao">{{ $candidato->Admissao->acessar_area_porto == 'Não' ? 'X' : '' }}</div>Não
                    </div>
                </div>

                <div class="b-bottom b-right float-left linhaComum" style="width: 206px;">
                    <div class="txtComum" style="width: 100%; margin-bottom: 4px; margin-left: 30px;">Avaliação Psicológica</div>
                    <div class="rsptxtComum" style="display: inline-flex; margin: -5px 0px 0px 35px;">
                        <div class="caixaSelecao">{{ $candidato->Admissao->avaliacao_psicologica == 'Sim' ? 'X' : '' }}</div>Sim
                        <div class="caixaSelecao">{{ $candidato->Admissao->avaliacao_psicologica == 'Não' ? 'X' : '' }}</div>Não
                    </div>
                </div>

                <div class="b-bottom b-left float-left"
                     style="width: 50%; height: 113px; border-bottom-left-radius: 9px;">
                    <div class="txtComum">Uso Exclusivo da ALUMAR:</div>
                    <div class="rsptxtComum"></div>

                    <div class="txtComum" style="margin-top: 10px;">1. Vinculo Empregatício</div>
                    <div class="rsptxtComum" style="margin-top: 22px; margin-left: 216px;"> ____/____/________
                    </div>

                    <div class="txtComum" style="margin-top: 10px; margin-right: 20px;">2.Treinamento de Introdutório
                    </div>
                    <div class="rsptxtComum" style="margin-top: 10px; "> ____/____/________</div>

                    <div class="txtComum" style="margin-top: 10px;">3. Plano de Saúde</div>
                    <div class="rsptxtComum" style="margin-top: 12px; margin-left: 210px;"> __________________
                    </div>
                </div>

                <div class="b-bottom b-right float-left"
                     style="width: 50%; height: 113px; border-bottom-right-radius: 9px;">
                    <div class="txtComum" style="color: white"></div>
                    <div class="rsptxtComum"></div>

                    <div class="txtComum" style="margin-top: 22px; margin-right: 0px">4. ASO <span
                            style="color: white"></span></div>
                    <div class="rsptxtComum b-bottom" style="margin-top: 33px; margin-left: 61px; width: 299px;"></div>

                    <div class="txtComum" style="margin-top: 15px; margin-right: 0px; margin-left: -41px;">5. Consulta
                    </div>
                    <div class="rsptxtComum b-bottom"
                         style="margin-top: 25px; margin-right: 0px; margin-left: 88px; width: 272px;"></div>

                    <div class="txtComum" style="margin-top: 13px; margin-left: -69px;">6. Aprov. Seg. Patrimonial</div>
                    <div class="rsptxtComum b-bottom"
                         style="margin-top: 25px; margin-right: 0px; margin-left: 175px; width: 184px;"></div>
                </div>


            </div>
        </div>
    </div>
    <?php $cont++ ?>
    @if ($cont==2)
        <?php $cont = 0; ?>
        <div style="page-break-after: always"></div>
    @endif
@endforeach

</body>
</html>

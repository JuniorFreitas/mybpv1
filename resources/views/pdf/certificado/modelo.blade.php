<!doctype html>
<html lang="pt-Br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Certificados</title>
    <style type="text/css">
        * {
            margin: 0;
            padding: 0;
            font-family: 'Arial', Verdana, sans-serif;
        }

        .multiply {
            mix-blend-mode: multiply;
        }

        @page {
            margin: 0cm 0cm;
            width: 29.70cm;
            size: landscape;
        }

        body {
            height: 21cm;
            width: 29.70cm;
            margin-top: .5cm;
            /*margin-left: .5cm;*/
            margin-right: .4cm;
            margin-bottom: .5cm;
            font-family: 'Arial', sans-serif;
            font-size: 9.5pt;
        }

        .a4 {
            height: 21cm;
            width: 29.70cm;
            margin-top: .6cm;
            margin-left: .5cm;
            margin-right: .5cm;
            margin-bottom: .5cm;
            /*border: 1px solid black;*/
        }


        .principal {
            height: 22.05cm;
            width: 28.70cm;
            margin-left: 10px;
            border-style: double;
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
            height: 30.3px;
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
            height: 40.3px;
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
            padding-top: 5px
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

        li {
            border-bottom: 1px solid;
            padding-bottom: 2pt;
            padding-top: 2pt;
            font-size: 11.1pt;
            line-height: 14.5pt;
        }

        li:last-child {
            border-bottom: none;
        }

        td {
            border: 0.01mm solid black;
            padding: 0.2mm;
        }

    </style>
</head>
<body>
<button id="printPageButton" onClick="window.print()">IMPRIMIR</button>
@foreach($certificados as $item)
    {{--NR33--}}
    @if($item->InstrutorTrintaTres && $nr33)
        @php
            $dataNr33 = new \MasterTag\DataHora($item->Treinamento->Vencimentos()->find(7)->pivot->data_treinamento);
            $menos1dia = $dataNr33->subtrairDia(1);
            $dataExtensa1dia = new \MasterTag\DataHora($menos1dia);
        @endphp
        <div class="a4">
            <div class="principal">
                <div style="padding: 1cm 0.75cm 1cm 0.75cm">
                    @if ($item->cliente_id == 1)
                        <img src="https://sgibpse.com.br/logo_bpse_color.png" alt="" style="height: 60px">
                        <br>
                    @else
                        <img src="{{$item->Cliente->Logo ? $item->Cliente->Logo[0]->url : '' }}"
                             alt="{{$item->Cliente->razao_social}}"
                             style="height: 60px">
                    @endif


                    <h1 style="text-align: center; margin-top: 1.5%; margin-bottom: 3.5%; font-size: 28pt;">
                        CERTIFICADO</h1>

                    <p style="text-align: justify; font-size: 14pt; line-height: 28pt">
                        <span
                            style="padding-left: 2.3cm">A  <strong>BPSE - BUSINESS PARTNESS SERVIÇOS EMPRESARIAS</strong>, certifica que</span>
                        <strong>{{$item->Feedback->Curriculo->nome}}
                            (EMPRESA: {{ $item->nacional == 'true' ? 'NACIONAL' : $item->Cliente->razao_social }}, -
                            CPF: {{$item->Feedback->Curriculo->cpf}})</strong>,
                        participou e obteve o aproveitamento desejável na prova de competência do
                    </p>

                    <p style="text-align: center; font-size: 18pt; line-height: 34pt">
                        <strong style="text-transform: uppercase; text-align: center; text-decoration: underline">
                            Treinamento NR-33 para Executante e Vigia em Espaços Confinados
                        </strong>
                    </p>

                    <p style="text-align: justify; font-size: 13pt; line-height: 24pt;">
                        <span style="padding-left: 2.3cm">O treinamento,</span> com estrutura modulada, cumpre a carga horária de 16 horas,
                        realizado nos dias {{ $menos1dia }}
                        e {{$item->Treinamento->Vencimentos()->find(7)->pivot->data_treinamento}} na
                        <strong>{{ $item->EmpresaTrintaTres->nome }}</strong>, localizada
                        na {{ $item->EmpresaTrintaTres->endereco }},

                        {{--                        no exercício--}}
                        {{--                        de {{substr($item->Treinamento->Vencimentos()->find(7)->pivot->data_treinamento,6,4)}}--}}
                        {{--                        à {{substr($item->Treinamento->Vencimentos()->find(7)->pivot->data_vencimento,6,4)}},--}}
                        capacita o(a) trabalhador(a) para atuar como Vigia (Observador)/Entrante de Espaços Confinados
                        (tanques, vasos, silos, galerias e equipamentos utilizados na industria) para trabalhos de
                        inspeção, limpeza e manutenção)das atividades nos espaços confinados na localidade da Alumar.
                    </p>

                    <div style="margin-top: 1cm; font-size: 12pt; text-align: right">
                        @php
                            $dataNr33 = new \MasterTag\DataHora($item->Treinamento->Vencimentos()->find(7)->pivot->data_treinamento);
                        @endphp
                        São Luís/MA, {{$dataNr33->dia()}} de {{$dataNr33->mesExt()}} de {{ $dataNr33->ano() }}.
                    </div>


                    <div style="margin-top: 1cm; float:left;  text-align: center">
                        <img class="multiply"
                             src="{{ asset('img/assinatura') }}/{{$item->InstrutorTrintaTres->assinatura}}"
                             alt="{{$item->InstrutorTrintaTres->nome }}" style="height: 126px;">


                        <p style=" width: 386px; border-top: 1px solid; margin: 0 auto; font-size: 11pt;">
                            INSTRUTOR <br>
                            {{ $item->InstrutorTrintaTres->nome }} <br>
                            {{ $item->InstrutorTrintaTres->cargo }} <br>
                            {{ $item->InstrutorTrintaTres->registro ? 'Registro: '. $item->InstrutorTrintaTres->registro : '' }}
                        </p>
                    </div>

                    <div style="margin-top: 1cm; float:right; text-align: center">
                        <img class="multiply" src="{{ asset('img/assinatura') }}/gilson.jpg"
                             style="height: 126px;">
                        <p style=" width: 386px; border-top: 1px solid; margin: 0 auto; font-size: 11pt;">
                            RESPONSÁVEL TÉCNICO <br>
                            GILSON CÉLIO PINTO SOUSA <br>
                            Engenheiro de Segurança <br>
                            CREA-MA: 7684-D/RN 1100624309
                        </p>
                    </div>


                </div>


            </div>
        </div>
        <div style="page-break-after: always"></div>
        <div class="a4">
            <div class="principal">
                <div style="padding: 1cm 0.75cm 1cm 0.75cm; text-align: center">
                    <ul style="list-style: none; text-align: left; padding: 3pt; ">
                        <li style="border: none">33.3.5.4 A capacitação deve ter carga horária mínima de dezesseis
                            horas,
                            ser
                            realizada dentro do horário de trabalho, com conteúdo programático de:(Alteração dada pela
                            Portaria
                            MTE 1.409/2012).
                        </li>
                        <li style="border: none">a) Definições;</li>
                        <li style="border: none">b) Reconhecimento, avaliação e controle de riscos;
                        </li>
                        <li style="border: none">c) Funcionamento de equipamentos utilizados;
                        </li>
                        <li style="border: none">d) Procedimentos e utilização da Permissão de Entrada e Trabalho;
                        </li>
                        <li style="border: none">e) Noções de resgate e primeiros socorros.</li>

                    </ul>
                </div>
            </div>
        </div>
        <div style="page-break-after: always"></div>
    @endif

    @if($item->InstrutorTrintaCinco && $nr35)
        @php
            $dataNr35 = new \MasterTag\DataHora($item->Treinamento->Vencimentos()->find(6)->pivot->data_treinamento);
        @endphp
        {{--NR35--}}
        <div class="a4">
            <div class="principal">
                <div style="padding: 1cm 0.75cm 1cm 0.75cm">
                    @if ($item->cliente_id == 1)
                        <img src="https://sgibpse.com.br/logo_bpse_color.png" alt="" style="height: 60px">
                        <br>
                    @else
                        <img src="{{$item->Cliente->Logo ? $item->Cliente->Logo[0]->url : '' }}"
                             alt="{{$item->Cliente->razao_social}}"
                             style="height: 60px">
                    @endif
                    <h1 style="text-align: center; margin-top: 1.5%; margin-bottom: 5%; font-size: 28pt;">
                        CERTIFICADO</h1>

                    <p style="text-align: justify; font-size: 14pt; line-height: 34pt">
                        <span
                            style="padding-left: 2.3cm">A <strong>BPSE - BUSINESS PARTNESS SERVIÇOS EMPRESARIAS</strong>, certificamos que</span>
                        <strong>{{$item->Feedback->Curriculo->nome}}
                            (EMPRESA: {{ $item->nacional == 'true' ? 'NACIONAL' : $item->Cliente->razao_social }}, -
                            CPF: {{$item->Feedback->Curriculo->cpf}})</strong>,
                        participou e obteve o aproveitamento desejável na prova de competência do
                    </p>

                    <p style="text-align: center; font-size: 18pt; line-height: 28pt">
                        <strong style="text-transform: uppercase; text-align: center; text-decoration: underline">Treinamento
                            NR-35 para
                            Trabalho em Altura
                        </strong>
                    </p>

                    <p style="text-align: justify; font-size: 13pt; line-height: 24pt;">
                        <span style="padding-left: 2.3cm">O treinamento,</span> com estrutura modulada, cumpre a carga
                        horária
                        de 08 horas, realizado em {{ $dataNr35->dataCompleta() }} na empresa
                        <strong>{{ $item->EmpresaTrintaCinco->nome }}</strong>, localizada
                        na {{ $item->EmpresaTrintaCinco->endereco }},
                        {{--                        no exercício--}}
                        {{--                        de {{substr($item->Treinamento->Vencimentos()->find(6)->pivot->data_treinamento,6,4)}}--}}
                        {{--                        à {{substr($item->Treinamento->Vencimentos()->find(6)->pivot->data_vencimento,6,4)}},--}}
                        capacita o(a) trabalhador(a) para trabalho em altura.
                    </p>

                    <div style="margin-top: 1cm; font-size: 12pt; text-align: right">
                        São Luís/MA, {{$dataNr35->dia()}} de {{$dataNr35->mesExt()}} de {{ $dataNr35->ano() }}.
                    </div>

                    <div style="margin-top: 1cm; float:left; text-align: center">
                        <img class="multiply"
                             src="{{ asset('img/assinatura') }}/{{$item->InstrutorTrintaCinco->assinatura}}"
                             alt="{{$item->InstrutorTrintaCinco->nome }}" style="height: 100px;">
                        <p style=" width: 386px; border-top: 1px solid; margin: 0 auto; font-size: 11pt;">
                            INSTRUTOR <br>
                            {{ $item->InstrutorTrintaCinco->nome }} <br>
                            {{ $item->InstrutorTrintaCinco->cargo }} <br>
                            {{ $item->InstrutorTrintaCinco->registro ? 'Registro: '. $item->InstrutorTrintaCinco->registro : '' }}
                        </p>
                    </div>

                    <div style="margin-top: 1cm; float:right; text-align: center">
                        <img class="multiply" src="{{ asset('img/assinatura') }}/gilson.jpg"
                             style="height: 126px;">
                        <p style=" width: 386px; border-top: 1px solid; margin: 0 auto; font-size: 11pt;">
                            RESPONSÁVEL TÉCNICO <br>
                            GILSON CÉLIO PINTO SOUSA <br>
                            Engenheiro de Segurança <br>
                            CREA-MA: 7684-D/RN 1100624309
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div style="page-break-after: always"></div>
        <div class="a4">
            <div class="principal">
                <div style="padding: 1cm 0.75cm 1cm 0.75cm; text-align: center">
                    <table border="0" cellpadding="0" cellspacing="0" width="75%"
                           style="margin: 0 auto; margin-top: 2.5%;">
                        <tr style="background: rgb(178,198,229)">
                            <td colspan="3" style="font-size: 18pt; font-weight: bold;padding: 12pt 0">TREINAMENTO <br>NR35
                            </td>
                        </tr>
                        <tr style="font-size: 14pt; background: rgb(178,198,229)">
                            <td style="padding: 6pt 0; font-weight: bold; text-transform: uppercase">Módulo</td>
                            <td style="padding: 6pt 0; font-weight: bold; text-transform: uppercase">Carga <br> Horária
                            </td>
                            <td style="padding: 6pt 0; font-weight: bold; text-transform: uppercase">Conteúdo <br>
                                Programático
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size: 16pt;" width="37%">
                                Trabalho em Altura <br> Módulo Teórico
                            </td>
                            <td style="font-size: 24pt;" width="25%">
                                4h
                            </td>
                            <td width="45%">
                                <ul style="list-style: none; text-align: left; padding: 3pt; ">
                                    <li>a) Normas e regulamentos aplicáveis ao trabalho
                                        em altura;
                                    </li>
                                    <li>b) Análise de risco e condições impeditivas;</li>
                                    <li>c) Riscos potenciais inerentes ao trabalho em
                                        altura e medidas de prevenção e controle;
                                    </li>
                                    <li>d) Sistemas, equipamentos e procedimentos de proteção coletiva;
                                    </li>
                                    <li>e) Equipamentos de Proteção Individual para trabalho em altura: seleção,
                                        inspeção,
                                        conservação e limitação de uso;
                                    </li>
                                    <li>f) Acidentes típicos em trabalhos em altura;</li>
                                    <li>g) Condutas em situações de emergência,
                                        incluindo noções de técnicas de resgate e de primeiros socorros.
                                    </li>
                                </ul>
                            </td>
                        </tr>

                        <tr>
                            <td style="font-size: 16pt; padding: 8pt">
                                Trabalho em Altura <br> Módulo Prático
                            </td>
                            <td style="font-size: 24pt;">
                                4h
                            </td>
                            <td width="40%"></td>
                        </tr>

                        <tr>
                            <td style="font-size: 16pt;border-left: none; border-bottom: none"></td>
                            <td style="font-size: 24pt;  padding: 8pt; border-bottom: 2px solid">
                                8h
                            </td>
                            <td width="40%" style="border-right: none; border-bottom: none"></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    @endif
@endforeach
</body>
</html>

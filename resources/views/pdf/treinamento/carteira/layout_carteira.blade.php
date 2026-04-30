<!doctype html>
<html lang="pt-Br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('titulo')</title>

    <style type="text/css">
        {{-- Escala compartilhada: carteira (texto pequeno) + etiqueta (destaques). Tipografia em pt; caixas físicas em cm/mm. --}}
        :root {
            --font-sans: 'Noto Sans', 'DejaVu Sans', Arial, sans-serif;
            --font-script: 'Sacramento', cursive;
            --font-scale-xxs: 5pt;
            --font-scale-xs: 5.5pt;
            --font-scale-sm: 5.5pt;
            --font-scale-md: 6pt;
            --font-scale-base: 10pt;
            --font-scale-lg: 13pt;
            --font-scale-xl: 16pt;
            --font-scale-2xl: 20pt;
            --font-scale-3xl: 24pt;
        }

        @font-face {
            font-family: 'Noto Sans';
            font-style: normal;
            font-weight: 400;
            font-display: swap;
            src: url('{{ asset('fonts/carteira/noto-sans-400.woff2') }}') format('woff2');
        }

        @font-face {
            font-family: 'Noto Sans';
            font-style: normal;
            font-weight: 700;
            font-display: swap;
            src: url('{{ asset('fonts/carteira/noto-sans-700.woff2') }}') format('woff2');
        }

        @font-face {
            font-family: 'Sacramento';
            font-style: normal;
            font-weight: 400;
            font-display: swap;
            src: url('{{ asset('fonts/carteira/sacramento-400.woff2') }}') format('woff2');
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: var(--font-sans);
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Margem da folha zero (PDF Dompdf e impressão; combine com margens “Nenhuma” no diálogo do navegador se precisar) */
        @page {
            size: A4 portrait;
            margin: 2mm;
        }

        body {
            margin: 0 !important;
            padding: 0 !important;
        }

        body.carteira--pdf {
            box-sizing: border-box;
        }

        body.carteira--pdf .carteira-a4-wrap--bloqueio {
            padding: 2mm;
        }

        /* Quebra só quando bloqueio vem logo após carteira (evita div vazio = folha em branco no Dompdf) */
        .carteira-bloqueio-nova-pagina {
            clear: both;
            page-break-before: always;
            break-before: page;
        }

        h4 {
            margin: 5mm 0 0 5mm;
            font-family: var(--font-sans);
            font-weight: normal;
            font-size: 11pt;
            color: #3b3b3b;
            text-align: center;
        }

        .page-break {
            display: block;
            page-break-before: always;
        }

        #printPageButton {
            padding: 5px 13px;
            cursor: pointer;
            margin-top: 20px;
            margin-left: 20px;
            background-color: #184056;
            color: white;
            border: none;
            border-radius: 5px;
        }

        #printPageButton:hover {
            background-color: #045588;
        }

        .observacao {
            padding: 5px 13px;
            width: 600px;
            margin-top: 20px;
            margin-left: 20px;
            background-color: #ffe5d2;
            color: #ff5c15;
            border: #ff5c15;
            border-radius: 5px;
        }

        @media print {
            #printPageButton,
            .observacao {
                display: none;
            }

            body {
                margin: 0 !important;
                padding: 0 !important;
            }

            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                font-family: var(--font-sans) !important;
            }

            /* Margens simétricas (o deslocamento 8mm à esquerda cortava no PDF) */
            div.carteira-a4-wrap:not(.carteira-a4-wrap--bloqueio) {
                margin: 3mm 5mm 3mm 5mm !important;
                padding: 3mm 0 0 0 !important;
            }

            div.carteira-a4-wrap.carteira-a4-wrap--bloqueio {
                margin: 0 !important;
                padding: 0 !important;
            }

            table {
                margin-left: 0 !important;
            }

            .font-assinatura {
                font-family: var(--font-script) !important;
            }
        }

        .etiqueta-bloqueio-page-break {
            clear: both;
            height: 0;
            margin: 0;
            padding: 0;
            border: 0;
            page-break-after: always;
            break-after: page;
        }

        /* Duas duplas (frente+verso) por folha; quebra só após o bloco de 2 */
        .etiqueta-bloqueio-dupla-folha {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            box-sizing: border-box;
        }

        .etiqueta-bloqueio-dupla-folha > .etiqueta-bloqueio-par {
            margin: 4mm auto;
        }

        /*
         * PDF: altura da linha = CarteiraEtiquetaBloqueioLayout::alturaLinhaParMm() via --bloqueio-altura-linha-par no wrap.
         * Constantes PHP devem bater com @page margin vertical + padding do wrap bloqueio.
         */
        body.carteira--pdf .etiqueta-bloqueio-dupla-folha--duas-linhas > .etiqueta-bloqueio-par {
            height: var(--bloqueio-altura-linha-par, 131mm);
            max-height: var(--bloqueio-altura-linha-par, 131mm);
            min-height: 0;
            margin: 0 auto !important;
            overflow: hidden;
        }

        body.carteira--pdf .etiqueta-bloqueio-dupla-folha--duas-linhas > .etiqueta-bloqueio-par:first-child {
            margin-bottom: 3mm !important;
        }

        body.carteira--pdf .etiqueta-bloqueio-dupla-folha--duas-linhas > .etiqueta-bloqueio-par > .etiqueta-bloqueio-celula {
            height: 100%;
            min-height: 0;
            max-height: 100%;
        }

        body.carteira--pdf .etiqueta-bloqueio-dupla-folha--duas-linhas > .etiqueta-bloqueio-par > .etiqueta-bloqueio-celula > .etiqueta {
            flex: 1 1 auto !important;
            min-height: 0 !important;
            max-height: 100%;
            height: auto;
            overflow: hidden;
            padding: 4.5mm 3mm 3.5mm 3mm;
        }

        body.carteira--pdf .etiqueta-bloqueio-dupla-folha--duas-linhas .etiqueta > .content {
            min-height: 0;
            flex: 1 1 auto;
            overflow: hidden;
        }

        body.carteira--pdf .etiqueta-bloqueio-dupla-folha:not(.etiqueta-bloqueio-dupla-folha--duas-linhas) > .etiqueta-bloqueio-par {
            max-height: var(--bloqueio-altura-linha-par, 131mm);
        }

        .carteira-a4-wrap--bloqueio {
            padding: 5mm;
            margin-left: 0;
            box-sizing: border-box;
        }

        /*
         * Par frente + costa: flex + stretch = altura dinâmica igual à da coluna mais alta
         * (tabela + td não esticava o .etiqueta mais baixo; flex preenche a coluna).
         */
        .etiqueta-bloqueio-par {
            display: flex;
            flex-direction: row;
            flex-wrap: nowrap;
            align-items: stretch;
            justify-content: center;
            margin: 6mm auto;
            width: auto;
            max-width: 100%;
            clear: both;
        }

        .etiqueta-bloqueio-celula {
            flex: 0 0 7.8cm;
            width: 7.8cm;
            max-width: 7.8cm;
            min-width: 0;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
        }

        .etiqueta-bloqueio-celula + .etiqueta-bloqueio-celula {
            margin-left: 5mm;
        }

        .etiqueta-bloqueio-par > .etiqueta-bloqueio-celula > .etiqueta {
            flex: 1 1 auto;
            width: 100%;
            max-width: 7.8cm;
            min-height: 12cm;
            height: auto;
        }

        .etiqueta-stack {
            margin-top: 5mm;
        }

        /* Etiqueta: moldura em CSS — cantos superiores chanfrados (equivalente ao traçado do etiqueta.svg) */
        .etiqueta {
            position: relative;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            width: 7.8cm;
            max-width: 7.8cm;
            flex: 0 0 7.8cm;
            min-height: 12cm;
            height: auto;
            align-self: stretch;
            margin: 0;
            padding: 8mm 4mm 5mm 4mm;
            background: transparent;
            border: 0;
            overflow: hidden;
        }

        /* Camada visual da etiqueta: desenha fundo + borda chanfrada sem recortar o conteúdo. */
        .etiqueta::after {
            content: '';
            position: absolute;
            inset: 0;
            background: #fff;
            border: 0.3mm solid #000;
            z-index: 0;
            pointer-events: none;
            /* Chanfro ~5,6 mm no topo (78,4 mm no SVG) e ~3,8 mm na vertical (135 mm no SVG) */
            -webkit-clip-path: polygon(
                5.6mm 0,
                calc(100% - 5.6mm) 0,
                100% 3.8mm,
                100% 100%,
                0 100%,
                0 3.8mm
            );
            clip-path: polygon(
                5.6mm 0,
                calc(100% - 5.6mm) 0,
                100% 3.8mm,
                100% 100%,
                0 100%,
                0 3.8mm
            );
        }

        /* Furo superior (equivalente ao círculo do SVG), acima do conteúdo */
        .etiqueta::before {
            content: '';
            position: absolute;
            top: 2.5mm;
            left: 50%;
            width: 4.2mm;
            height: 4.2mm;
            margin-left: -2.1mm;
            border: 0.3mm solid #000;
            border-radius: 50%;
            background: #fff;
            z-index: 2;
            pointer-events: none;
        }

        .etiqueta > .logo {
            display: flex;
            justify-content: center;
            margin-top: 0;
            margin-bottom: 2mm;
            position: relative;
            z-index: 1;
        }

        .etiqueta > .logo img {
            height: 1.5cm;
        }

        .etiqueta > .content {
            flex: 1 1 auto;
            min-height: 0;
        }

        .content {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .boxBlack {
            display: flex;
            align-items: center;
            height: 2cm;
            width: 100%;
            background: black;
            border-radius: 0.5cm;
        }

        .circuloRed {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: red;
            width: 70%;
            height: 1.4cm;
            margin: 0 auto;
            border: 0.35mm solid white;
            border-radius: 50%;
        }

        .tituloPerigo {
            text-align: center;
            color: white;
            font-size: var(--font-scale-2xl);
            font-weight: bold;
        }

        .tituloDanger {
            text-align: center;
            color: white;
            font-size: var(--font-scale-2xl);
            font-weight: 100;
        }

        .etiqueta-texto-aviso {
            margin-top: 4mm;
            font-weight: bold !important;
            font-size: var(--font-scale-xl);
            word-wrap: normal;
            overflow-wrap: normal;
            word-break: normal;
            hyphens: none;
        }

        .etiqueta-texto-aviso.colorRed {
            color: red;
        }

        .etiqueta-texto-aviso-espacado {
            margin-top: 1cm;
            font-weight: bold !important;
            font-size: var(--font-scale-xl);
            word-wrap: normal;
            overflow-wrap: normal;
            word-break: normal;
            hyphens: none;
        }

        .etiqueta-titulo-cuidado {
            margin-top: 3mm;
            color: red;
            text-decoration: underline;
            font-size: var(--font-scale-3xl);
            word-wrap: break-word;
            overflow-wrap: anywhere;
        }

        .etiqueta-lateral-com-foto {
            display: flex;
            flex-direction: row;
            align-items: flex-start;
            margin-top: 3mm;
            min-width: 0;
        }

        .etiqueta-texto-lateral {
            flex: 1 1 0;
            min-width: 0;
            max-width: 3.9cm;
            font-size: var(--font-scale-2xl);
        }

        .etiqueta-texto-lateral h6 {
            font-weight: bold;
            word-wrap: break-word;
            overflow-wrap: anywhere;
        }

        .etiqueta-ramal {
            font-size: var(--font-scale-lg);
            font-weight: bold;
            color: red;
            margin-top: 2mm;
            margin-bottom: 2mm;
            word-wrap: break-word;
            overflow-wrap: anywhere;
        }

        .etiqueta-meta-linha {
            margin-top: 1.3mm;
            font-size: var(--font-scale-base);
            word-wrap: normal;
            overflow-wrap: normal;
            word-break: normal;
            hyphens: none;
        }

        .etiqueta-logos-row {
            display: flex;
            margin: 0 auto;
            margin-top: 0.7cm;
        }

        .text-center {
            text-align: center;
        }

        .colorRed {
            color: red;
        }

        .etiqueta-lateral-com-foto .fotoTres {
            margin-left: 2mm;
        }

        .fotoTres {
            flex-shrink: 0;
            height: 4cm !important;
            width: 3cm !important;
            background: white;
            border: 0.2mm solid #7e7e7e;
        }

        .clearfix::after {
            display: block;
            clear: both;
            content: "";
        }

        .font-assinatura {
            color: blue;
            text-align: center;
            font-family: var(--font-script);
            font-size: var(--font-scale-md);
        }

        .carteira-font-xxs {
            font-size: var(--font-scale-xxs);
        }

        .carteira-font-xs {
            font-size: var(--font-scale-xs);
        }

        .carteira-font-sm {
            font-size: var(--font-scale-sm);
        }

        .carteira-font-md {
            font-size: var(--font-scale-md);
        }
    </style>

</head>
<body @class(['carteira--pdf' => !empty($forPdf)])>
<button id="printPageButton" onClick="window.print();">IMPRIMIR</button>
<p class="observacao">OBS: Para carteiras com mais de 12 treinamentos, favor imprimí-la individualmente.</p>
@yield('conteudo')

</body>
</html>

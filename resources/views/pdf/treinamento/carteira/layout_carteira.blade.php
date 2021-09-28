<!doctype html>
<html lang="pt-Br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('titulo')</title>
    <link href="https://fonts.googleapis.com/css2?family=Sacramento&display=swap" rel="stylesheet">

    <style type="text/css">
        * {
            margin: 0;
            padding: 0;
            font-family: 'Arial', Verdana, sans-serif;
            -webkit-print-color-adjust: exact !important; /* Chrome, Safari */
            color-adjust: exact !important; /*Firefox*/
        }

        @page {
            margin: 0cm 0cm;
            height: 29.70cm;
        }


        /** Define now the real margins of every page in the PDF **/
        body {
            width: 21cm;
            height: 29.70cm;
            margin-top: .5cm;
            margin-left: .5cm;
            margin-right: .5cm;
            margin-bottom: .5cm;
            font-family: 'Arial', sans-serif;
            -webkit-print-color-adjust: exact;
        }

        .a4 {
            width: 21cm;
            height: 29.70cm;
            /*margin-top: .5cm;*/
            /*margin-left: .5cm;*/
            /*margin-right: .5cm;*/
            /*margin-bottom: .5cm;*/
        }

        tr {
            font-size: 4.7pt;
        }

        td {
            border: 0.01mm solid black;
            padding: 0.2mm;
        }

        .container {
            display: flex;
            background-color: #cdf6eb;
            margin: 10px auto 30px;
            max-width: 500px;
            font-family: sans-serif;
        }

        .nowrap {
            flex-wrap: nowrap;
        }

        .wrap {
            flex-wrap: wrap;
        }

        .wrap-reverse {
            flex-wrap: wrap-reverse;
        }

        .container div {
            background: #028082;
            margin: 8px 4px;
            width: 80px;
            height: 80px;
            font-size: 1em;
            color: #fff;
            /* as proriedades a partir daqui alinham o texto no centro */
            display: flex;
            text-align: center;
            justify-content: center;
            align-items: center;
        }

        h4 {
            margin: 20px 0 0 20px;
            font-family: sans-serif;
            font-weight: normal;
            font-size: 1em;
            color: #3b3b3b;
            text-align: center;
        }

        .page-break {
            display: block;
            page-break-before: always;
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

        .etiqueta {
            height: 135.39999mm;
            width: 78.400002mm;
            float: left;
            margin-top: 1cm;
            margin-right: 1cm;
        }

        .etiqueta::before {
            height: 135.39999mm;
            width: 78.400002mm;
            content: url({{ asset('etiqueta.svg') }});
            position: absolute;
            z-index: -1;
        }


        .etiqueta > .logo {
            display: flex;
            justify-content: center;
            margin-top: 0.90cm;
            margin-bottom: 0.10cm;
        }

        .etiqueta > .logo img {
            height: 1.5cm;
        }

        .content {
            width: 90%;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
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
            width: 90%;
            height: 1.4cm;
            margin: 0 auto;
            border: 3px solid white;
            border-radius: 0.7cm;
        }

        .tituloPerigo {
            text-align: center;
            color: white;
            font-size: 20px;
            font-weight: bold;
        }

        .tituloDanger {
            text-align: center;
            color: white;
            font-size: 20px;
            font-weight: 100;
        }

        .text-center {
            text-align: center;
        }

        .colorRed {
            color: red;
        }

        .fotoTres {
            max-height: 4cm;
            max-width: 3cm;
            background: white;
        }

        .clearfix::after {
            display: block;
            clear: both;
            content: "";
        }
    </style>

</head>
<body>
<button id="printPageButton" onClick="window.print();">IMPRIMIR</button>
@yield('conteudo')

</body>
</html>

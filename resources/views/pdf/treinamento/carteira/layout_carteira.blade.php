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
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0 !important;
            padding: 0 !important;
        }

        @media print {
            #printPageButton, .observacao {
                display: none;
            }

            body {
                margin: 0 !important;
                padding: 0 !important;
            }

            * {
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
            }

            /* Força margem específica no container principal das carteiras */
            div[style*="padding: 20px"] {
                margin: 10px 0 0 30px !important;
                padding: 20px 0 0 0 !important;
            }

            /* Força margem nas tabelas individuais */
            table {
                margin-left: 0 !important;
            }
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
            margin-top: 20px;
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
            #printPageButton, .observacao {
                display: none;
            }
        }

        .etiqueta {
            height: 12cm;
            width: 7.8cm;
            float: left;
            margin-top: 1cm;
            margin-bottom: 1.2cm;
            margin-right: 1cm;
        }

        .etiqueta::before {
            height: 12cm;
            width: 7.8cm;
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
            width: 70%;
            height: 1.4cm;
            margin: 0 auto;
            border: 4px solid white;
            border-radius: 50%;
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
            height: 4cm !important;
            width: 3cm !important;
            background: white;
            border: 1px solid #7e7e7e;
        }

        .clearfix::after {
            display: block;
            clear: both;
            content: "";
        }

        .observacao {
            padding: 5px 13px 5px 13px;
            width: 600px;
            margin-top: 20px;
            margin-left: 20px;
            background-color: #ffe5d2;
            color: #ff5c15;
            border: #ff5c15;
            border-radius: 5px;
        }
    </style>

</head>
<body>
<button id="printPageButton" onClick="window.print();">IMPRIMIR</button>
<p class="observacao">OBS: Para carteiras com mais de 12 treinamentos, favor imprimí-la individualmente.</p>
@yield('conteudo')

</body>
</html>

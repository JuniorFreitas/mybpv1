<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('titulo','PDF MYBP')</title>
    <style>
        table.dados, table.dados th, table.dados td {
            border: 1px solid black;
            border-collapse: collapse;
            font-size: 7.7pt;
            padding: 5px;
        }

        table {
            max-width: 100%;
            max-height: 100%;
        }

        table.dados2 {
            width: 100%;
            border: 1px solid black;
            border-collapse: collapse;
            font-size: 7.7pt;
            padding: 3px;
        }

        table.dados2 th, table.dados2 td {
            border-collapse: collapse;
            font-size: 7.7pt;
            padding: 3px;
        }

        table.dados3, table.dados3 th, table.dados3 td {
            border: 1px solid black;
            border-collapse: collapse;
            font-size: 7.7pt;
            padding: 4px;
        }

        @page {
            size: A4;
            margin: 10mm 2mm 5mm 2mm;
        }

        body {
            height: 27cm;
            width: 21cm;
            margin-right: .4cm;
            font-family: 'Arial', sans-serif;
            font-size: 7.7pt;
        }

        .a4 {
            height: 27cm;
            width: 20cm;
            margin-top: 0px;
            margin-left: .5cm;
            margin-right: .5cm;
        }


        h4 {
            margin-top: 25px;
        }

        .dados {
            width: 100%;
        }

        .text-center {
            text-align: center;
        }

        .espaco {
            padding: 20px 20px;
        }

        .border-bottom {
            border-bottom: 1px solid #ccc;
        }

        .center {
            text-align: center;
        }

        .coluna {
            width: 50%;
            float: left;
        }

        .resetFloat {
            clear: both;
        }

        .text-left {
            text-align: left;
        }

        .footer {
            position: relative;
            /*bottom: 0px;*/
            font-size: 8.4pt;
            /*width: 10cm;*/
        }

        .f14 {
            font-size: 14pt !important
        }

        .f11 {
            font-size: 11pt !important
        }

        .f12 {
            font-size: 12pt !important;
        }

        .f10 {
            font-size: 10pt !important;
        }

        .obs {
            font-size: 8.4pt;
            color: #444444;
            margin-bottom: 10px;
        }
        .text-justify {
            text-align: justify;
        }

    </style>

    @stack('style')
</head>
<body>
@yield('conteudo')
</body>
</html>

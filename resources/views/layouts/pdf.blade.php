<!doctype html>
<html lang="pt-Br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title')</title>
    <style type="text/css">

        * {
            margin: 0;
            padding: 0;
            font-family: 'Arial', Verdana, sans-serif;
        }

        @page {
            margin: 0cm 0cm;
            height: 22cm;
        }

        /** Define now the real margins of every page in the PDF **/
        body {
            margin-top: 3cm;
            margin-left: 2cm;
            margin-right: 2cm;
            margin-bottom: 2cm;
        }

        .conteudo {
            margin-top: 0.2cm;
        }

        .h5 {
            font-size: 9.5pt;
            font-weight: bold;
        }

        fieldset {
            height: 10px;
            margin-top: 15px;
            margin-bottom: 0px;
            border: none;
            border-top: 1px solid #333;
        }

        legend {
            background: #d6d6d6;
            margin-left: -0.29cm;
            text-transform: uppercase;
            padding-left: 3px;
            margin-top: -2px;
        }

        .titulo {
            margin-top: 5px;
            margin-bottom: 5px;
            text-decoration: underline
        }

        .h5 span {
            font-weight: normal;
            line-height: 20px
        }

        h5 span {
            font-weight: normal;
            line-height: 20px
        }

        .bg-default {
            background: #0f4c60;
            color: #FFFFFF;
            text-align: center;
        }

        .page_break {
            page-break-before: always;
        }

        .text-center {
            text-align: center;
        }

        .text-justify {
            text-align: justify;
        }

        p {
            font-size: 9pt;
        }
        .footer {
            font-size: 50px;
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: right;
            /*z-index: 1;*/
        }
    </style>
    @stack('style')
</head>
<body style="margin: 1cm">
@yield('empresa')
<div class="conteudo">
    @yield('conteudo')
</div>
</body>
</html>

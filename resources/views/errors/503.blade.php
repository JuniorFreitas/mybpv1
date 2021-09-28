<!DOCTYPE html>
<html>
<head>
    <title>Estamos em manutenção</title>

    <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">

    <style>
        html, body {
            height: 100%;
        }
        body {
            margin: 0;
            padding: 0;
            width: 100%;
            color: #013d58;
            display: table;
            font-weight: 100;
            font-family: 'Lato', sans-serif;
        }
        .container {
            text-align: center;
            display: table-cell;
            vertical-align: middle;
        }
        .content {
            text-align: center;
            display: inline-block;
        }
        .title {
            font-size: 72px;
            margin-bottom: 40px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="content">
        <img src="{{ asset('imagens/bepinhas/branca_2.png') }}" alt="">
        <div class="title">Estamos em manutenção programada,</div>
        <div class="title">retorne em alguns minutos.</div>
    </div>
</div>
</body>
</html>

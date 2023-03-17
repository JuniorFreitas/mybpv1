<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manutenção Programada</title>
    <style>
        body {
            background-color: #f2f2f2;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            padding: 20px;
            box-sizing: border-box;
        }

        .logo {
            max-width: 100%;
            height: auto;
            margin-bottom: 20px;
        }

        .title {
            font-size: 30px;
            font-weight: bold;
            margin-bottom: 20px;
            text-align: center;
        }

        .message {
            font-size: 20px;
            text-align: center;
            margin-bottom: 20px;
        }

        .timer {
            font-size: 40px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }

        @media only screen and (min-width: 768px) {
            .container {
                max-width: 600px;
                margin: 0 auto;
            }
        }

        @media only screen and (min-width: 992px) {
            .container {
                max-width: 800px;
            }
        }
    </style>
</head>
<body>
<div class="container">
{{--    <img class="logo" src="" alt="Logo da empresa">--}}
    <h1 class="title">Manutenção Programada</h1>
    <p class="message">Estamos realizando uma manutenção em nosso sistema para melhor atendê-lo.</p>
    <p class="timer">Tempo previsto: 2 horas</p>
</div>
</body>
</html>

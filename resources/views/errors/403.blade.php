<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erro 403</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 50px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            text-align: center;
        }

        .heading {
            font-size: 72px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #333;
            text-shadow: 1px 1px 2px #ccc;
        }

        .subheading {
            font-size: 24px;
            margin-bottom: 40px;
            color: #666;
        }

        .btn {
            display: inline-block;
            padding: 10px 30px;
            border-radius: 30px;
            background-color: #3366cc;
            color: #fff;
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #6699cc;
        }

        @media only screen and (max-width: 768px) {
            .heading {
                font-size: 48px;
            }

            .subheading {
                font-size: 18px;
            }

            .btn {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h1 class="heading">Erro 403</h1>
    <p class="subheading">Desculpe, você não tem permissão para acessar esta página.</p>
    <a href="/" class="btn">Voltar à página inicial</a>
</div>
</body>
</html>

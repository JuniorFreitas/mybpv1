<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avaliação Enviada - MyBP</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #003755 0%, #001a2e 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .success-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 50px;
            max-width: 600px;
            text-align: center;
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .success-icon {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, #003755 0%, #001a2e 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            animation: pulse 2s infinite;
        }

        .success-icon i {
            font-size: 50px;
            color: white;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }

        .success-title {
            color: #333;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .success-message {
            color: #666;
            font-size: 18px;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .colaborador-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 30px 0;
        }

        .colaborador-info strong {
            color: #003755;
        }

        .checkmark {
            display: inline-block;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #28a745;
            margin: 20px auto;
            position: relative;
            animation: scaleIn 0.5s ease-out 0.2s both;
        }

        .checkmark:after {
            content: '';
            position: absolute;
            left: 28px;
            top: 14px;
            width: 15px;
            height: 30px;
            border: solid white;
            border-width: 0 4px 4px 0;
            transform: rotate(45deg);
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }

        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            border-radius: 5px;
            margin-top: 30px;
            text-align: left;
        }

        .info-box i {
            color: #2196f3;
            margin-right: 10px;
        }

        /* Rodapé MyBP */
        .mybp-footer {
            text-align: center;
            margin-top: 32px;
            color: rgba(255, 255, 255, 0.9);
        }

        .mybp-footer .brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 6px;
        }

        .mybp-footer .brand img {
            height: 28px;
            width: auto;
            display: block;
        }

        .mybp-footer small {
            opacity: 0.9;
        }
    </style>
</head>
<body>
<div class="success-card">
    <div class="success-icon">
        <i class="fas fa-check"></i>
    </div>

    <h1 class="success-title">Avaliação Enviada com Sucesso!</h1>

    <p class="success-message">
        Sua avaliação foi registrada em nosso sistema.<br>
        Agradecemos pela sua colaboração!
    </p>

    <div class="colaborador-info">
        <p class="mb-2">
            <strong>Colaborador Avaliado:</strong><br>
            {{ $colaborador->nome }}
        </p>
        <p class="mb-0">
            <strong>Avaliação:</strong> {{ $numero_avaliacao }}ª de 90 dias
        </p>
    </div>

    <div class="info-box">
        <i class="fas fa-info-circle"></i>
        <strong>Importante:</strong> Este link não poderá mais ser utilizado.
        A avaliação já foi registrada e processada pelo nosso sistema.
    </div>

    <hr class="my-4">

    <p class="text-muted mb-0">
        <small>
            <i class="fas fa-shield-alt"></i>
            Seus dados foram tratados de forma segura e confidencial
        </small>
    </p>
</div>

<!-- Rodapé MyBP -->
<footer class="mybp-footer">
    <div class="brand">
        <img src="https://sistema.mybp.com.br/images/bpin_mybp_color.svg" alt="Logo MyBP">
        <strong>MyBP</strong>
    </div>
    <div>
        <small>&copy; {{ date('Y') }} MyBP. Todos os direitos reservados.</small>
    </div>
</footer>
</body>
</html>

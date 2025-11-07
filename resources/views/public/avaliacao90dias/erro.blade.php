<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Link Inválido - MyBP</title>
    
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
        
        .error-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 50px;
            max-width: 600px;
            text-align: center;
        }
        
        .error-icon {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: #dc3545;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
        }
        
        .error-icon i {
            font-size: 50px;
            color: white;
        }
        
        .error-title {
            color: #333;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .error-message {
            color: #666;
            font-size: 18px;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        
        .reasons-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 20px;
            border-radius: 5px;
            text-align: left;
            margin: 30px 0;
        }
        
        .reasons-box h6 {
            color: #856404;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .reasons-box ul {
            margin-bottom: 0;
            padding-left: 20px;
        }
        
        .reasons-box li {
            color: #856404;
            margin-bottom: 8px;
        }
        
        .contact-box {
            background: #e3f2fd;
            padding: 20px;
            border-radius: 10px;
            margin-top: 30px;
        }
        
        .contact-box i {
            color: #2196f3;
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .contact-box p {
            color: #1976d2;
            margin-bottom: 0;
        }
        
        /* Rodapé MyBP */
        .mybp-footer {
            text-align: center;
            margin-top: 32px;
            color: rgba(255,255,255,0.9);
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
        .mybp-footer small { opacity: 0.9; }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        
        <h1 class="error-title">Link Inválido ou Expirado</h1>
        
        <p class="error-message">
            {{ $mensagem ?? 'Não foi possível acessar o formulário de avaliação.' }}
        </p>
        
        <div class="reasons-box">
            <h6><i class="fas fa-info-circle"></i> Possíveis motivos:</h6>
            <ul>
                <li>O link de avaliação expirou</li>
                <li>Esta avaliação já foi realizada anteriormente</li>
                <li>O link está incorreto ou foi modificado</li>
                <li>O número máximo de avaliações já foi atingido</li>
            </ul>
        </div>
        
        <div class="contact-box">
            <i class="fas fa-headset"></i>
            <p class="mb-2">
                <strong>Precisa de ajuda?</strong>
            </p>
            <p>
                Entre em contato com o setor de Recursos Humanos<br>
                para solicitar um novo link de avaliação.
            </p>
        </div>
        
        <hr class="my-4">
        
        <p class="text-muted mb-0">
            <small>
                <i class="fas fa-shield-alt"></i> 
                MyBP - Sistema de Gestão de Pessoas
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

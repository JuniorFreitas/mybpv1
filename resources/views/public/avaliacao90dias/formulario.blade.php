<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Avaliação 90 Dias - MyBP</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <!-- Tippy.js (tooltips nas notas 1–5) -->
    <link rel="stylesheet" href="https://unpkg.com/tippy.js@6/dist/tippy.css" crossorigin="anonymous">
    
    <style>
        body {
            background: linear-gradient(135deg, #003755 0%, #001a2e 100%);
            min-height: 100vh;
            padding: 20px 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .card-container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .header-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            padding: 30px;
            margin-bottom: 25px;
        }
        
        .header-logo {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .header-logo i {
            font-size: 60px;
            color: #003755;
        }
        .header-logo img.header-logo-img {
            max-height: 60px;
            width: auto;
        }
        
        .header-title {
            text-align: center;
            color: #333;
            margin-bottom: 10px;
        }
        
        .header-subtitle {
            text-align: center;
            color: #666;
            font-size: 16px;
        }
        
        .info-colaborador {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }
        
        .info-colaborador h5 {
            color: #003755;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .info-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        
        .info-label {
            font-weight: 600;
            color: #495057;
        }
        
        .info-value {
            color: #6c757d;
        }
        
        .form-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            padding: 35px;
        }
        
        .pergunta-item {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .pergunta-item:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .pergunta-numero {
            background: #003755;
            color: white;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 10px;
        }
        
        .pergunta-texto {
            font-weight: 500;
            color: #333;
            margin-bottom: 15px;
        }
        
        .nota-options {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .nota-option {
            flex: 1;
            min-width: 72px;
        }
        
        .nota-option input[type="radio"] {
            display: none;
        }
        
        .nota-option label.nota-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px 6px;
            min-height: 76px;
            border: 2px solid #dee2e6;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.25s ease;
            font-weight: 600;
            background: #fff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        }
        .nota-btn .nota-btn-num {
            font-size: 1.4rem;
            font-weight: 800;
            line-height: 1;
        }
        .nota-btn .nota-btn-hint {
            display: block;
            font-size: 0.62rem;
            font-weight: 700;
            line-height: 1.15;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            max-width: 100%;
        }
        /* Cores alinhadas à escala (repouso) */
        .nota-option label.nota-btn-5 { border-color: rgba(26, 153, 102, 0.45); color: #0d5a3f; background: rgba(26, 153, 102, 0.07); }
        .nota-option label.nota-btn-4 { border-color: rgba(26, 158, 114, 0.45); color: #0d6b50; background: rgba(26, 158, 114, 0.07); }
        .nota-option label.nota-btn-3 { border-color: rgba(201, 162, 39, 0.55); color: #6b5a12; background: rgba(212, 175, 55, 0.12); }
        .nota-option label.nota-btn-2 { border-color: rgba(196, 92, 38, 0.5); color: #8b3d12; background: rgba(217, 119, 54, 0.1); }
        .nota-option label.nota-btn-1 { border-color: rgba(168, 50, 50, 0.5); color: #8b2020; background: rgba(196, 69, 69, 0.08); }
        .nota-option label.nota-btn:hover {
            filter: brightness(0.97);
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(0, 55, 85, 0.12);
        }
        /* Selecionado = mesmo degradê dos badges da escala */
        .nota-option input[type="radio"]:checked + label.nota-btn-5 {
            background: linear-gradient(145deg, #0d6e4a, #1a9966);
            color: #fff;
            border-color: #0d6e4a;
            transform: scale(1.06);
            box-shadow: 0 6px 16px rgba(13, 110, 74, 0.4);
        }
        .nota-option input[type="radio"]:checked + label.nota-btn-4 {
            background: linear-gradient(145deg, #0d7a5c, #1a9e72);
            color: #fff;
            border-color: #0d7a5c;
            transform: scale(1.06);
            box-shadow: 0 6px 16px rgba(13, 122, 92, 0.4);
        }
        .nota-option input[type="radio"]:checked + label.nota-btn-3 {
            background: linear-gradient(145deg, #c9a227, #d4af37);
            color: #1a1a1a;
            border-color: #b8941f;
            transform: scale(1.06);
            box-shadow: 0 6px 16px rgba(201, 162, 39, 0.45);
        }
        .nota-option input[type="radio"]:checked + label.nota-btn-2 {
            background: linear-gradient(145deg, #c45c26, #d97736);
            color: #fff;
            border-color: #c45c26;
            transform: scale(1.06);
            box-shadow: 0 6px 16px rgba(196, 92, 38, 0.4);
        }
        .nota-option input[type="radio"]:checked + label.nota-btn-1 {
            background: linear-gradient(145deg, #a83232, #c44545);
            color: #fff;
            border-color: #a83232;
            transform: scale(1.06);
            box-shadow: 0 6px 16px rgba(168, 50, 50, 0.4);
        }
        .nota-option input[type="radio"]:checked + label.nota-btn .nota-btn-hint {
            opacity: 0.95;
        }
        .nota-option input[type="radio"]:checked + label.nota-btn-3 .nota-btn-hint {
            color: #2d2510;
        }

        /* Tippy — tema alinhado ao MyBP */
        .tippy-box[data-theme~='mybp-nota'] {
            background: #fff;
            color: #1a1a1a;
            border: 1px solid rgba(0, 55, 85, 0.22);
            box-shadow: 0 10px 30px rgba(0, 55, 85, 0.18);
            font-size: 0.875rem;
            line-height: 1.45;
        }
        .tippy-box[data-theme~='mybp-nota'] .tippy-content {
            padding: 0.65rem 0.75rem;
        }
        
        .observacao-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 30px;
        }

        /* Definição sobre o colaborador — cards (prorroga / finaliza) */
        .definicao-contrato-section {
            border-radius: 12px;
            padding: 2px;
            transition: box-shadow 0.2s ease;
        }
        .definicao-contrato-section.border-danger {
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.45);
        }
        .definicao-contrato-box {
            background: linear-gradient(135deg, #f5f9fb 0%, #ffffff 100%);
            border: 1px solid rgba(0, 55, 85, 0.12);
            border-radius: 12px;
            padding: 1.25rem 1.35rem 1.35rem;
            box-shadow: 0 4px 18px rgba(0, 55, 85, 0.08);
        }
        .definicao-contrato-box.definicao-contrato-erro {
            border-color: #dc3545;
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.2);
        }
        .definicao-label-titulo {
            display: block;
            font-size: 1.05rem;
            color: #003755;
            margin-bottom: 0.35rem;
        }
        .definicao-label-desc {
            margin-bottom: 1rem !important;
        }
        .definicao-opcoes {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
        }
        .definicao-opcao {
            flex: 1;
            min-width: 200px;
        }
        .definicao-opcao .definicao-input {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
            pointer-events: none;
        }
        .definicao-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 10px;
            margin: 0;
            padding: 1.15rem 1rem;
            min-height: 148px;
            border: 2px solid #dee2e6;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.25s ease;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        .definicao-card-icon {
            font-size: 2rem;
            line-height: 1;
        }
        .definicao-card-titulo {
            font-weight: 800;
            font-size: 1rem;
            line-height: 1.25;
            color: #1a1a1a;
        }
        .definicao-card-hint {
            font-size: 0.78rem;
            line-height: 1.35;
            color: #5a6570;
            font-weight: 500;
        }
        .definicao-card--prorroga {
            border-color: rgba(26, 153, 102, 0.45);
            background: rgba(26, 153, 102, 0.06);
        }
        .definicao-card--prorroga .definicao-card-icon {
            color: #1a9966;
        }
        .definicao-card--finaliza {
            border-color: rgba(196, 69, 69, 0.45);
            background: rgba(196, 69, 69, 0.06);
        }
        .definicao-card--finaliza .definicao-card-icon {
            color: #c44545;
        }
        .definicao-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 55, 85, 0.12);
        }
        .definicao-opcao .definicao-input:focus + .definicao-card {
            outline: 2px solid rgba(0, 55, 85, 0.35);
            outline-offset: 2px;
        }
        .definicao-opcao .definicao-input:checked + .definicao-card--prorroga {
            background: linear-gradient(145deg, #0d6e4a, #1a9966);
            border-color: #0d6e4a;
            color: #fff;
            transform: scale(1.02);
            box-shadow: 0 10px 26px rgba(13, 110, 74, 0.45);
        }
        .definicao-opcao .definicao-input:checked + .definicao-card--prorroga .definicao-card-icon,
        .definicao-opcao .definicao-input:checked + .definicao-card--prorroga .definicao-card-titulo,
        .definicao-opcao .definicao-input:checked + .definicao-card--prorroga .definicao-card-hint {
            color: #fff;
        }
        .definicao-opcao .definicao-input:checked + .definicao-card--prorroga .definicao-card-hint {
            opacity: 0.95;
        }
        .definicao-opcao .definicao-input:checked + .definicao-card--finaliza {
            background: linear-gradient(145deg, #a83232, #c44545);
            border-color: #a83232;
            color: #fff;
            transform: scale(1.02);
            box-shadow: 0 10px 26px rgba(168, 50, 50, 0.45);
        }
        .definicao-opcao .definicao-input:checked + .definicao-card--finaliza .definicao-card-icon,
        .definicao-opcao .definicao-input:checked + .definicao-card--finaliza .definicao-card-titulo,
        .definicao-opcao .definicao-input:checked + .definicao-card--finaliza .definicao-card-hint {
            color: #fff;
        }
        .definicao-opcao .definicao-input:checked + .definicao-card--finaliza .definicao-card-hint {
            opacity: 0.95;
        }
        @media (max-width: 576px) {
            .definicao-opcao {
                min-width: 100%;
            }
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #003755 0%, #001a2e 100%);
            border: none;
            padding: 15px 40px;
            font-size: 18px;
            font-weight: 600;
            border-radius: 50px;
            box-shadow: 0 5px 15px rgba(0, 55, 85, 0.4);
            transition: all 0.3s ease;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 55, 85, 0.6);
        }
        
        .alert-custom {
            border-radius: 10px;
            border-left: 4px solid;
        }
        
        .expiracao-badge {
            background: #ffc107;
            color: #856404;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
            display: inline-block;
            margin-top: 10px;
        }
        
        @media (max-width: 768px) {
            .nota-options {
                flex-direction: column;
            }
            
            .nota-option {
                min-width: 100%;
            }
            
            .info-row {
                flex-direction: column;
                gap: 5px;
            }
        }

        /* Rodapé MyBP */
        .mybp-footer {
            text-align: center;
            margin: 32px auto 0;
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

        .escala-avaliacao-experiencia {
            position: relative;
            background: linear-gradient(135deg, #f0f7fb 0%, #ffffff 55%, #f5fafc 100%);
            border: 1px solid rgba(0, 55, 85, 0.18);
            border-left: 5px solid #003755;
            border-radius: 12px;
            padding: 1.25rem 1.35rem 1.35rem;
            margin-bottom: 1.75rem;
            box-shadow: 0 8px 24px rgba(0, 55, 85, 0.12), 0 2px 6px rgba(0, 0, 0, 0.04);
        }
        .escala-avaliacao-experiencia::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            border-radius: 12px 12px 0 0;
            background: linear-gradient(90deg, #003755, #1a5f7a);
        }
        .escala-avaliacao-experiencia .escala-cabecalho {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 1rem;
            padding-bottom: 0.85rem;
            border-bottom: 2px solid rgba(0, 55, 85, 0.12);
        }
        .escala-avaliacao-experiencia .escala-cabecalho i {
            font-size: 1.35rem;
            color: #003755;
            opacity: 0.95;
        }
        .escala-avaliacao-experiencia .escala-titulo {
            font-size: 1.05rem;
            font-weight: 800;
            letter-spacing: 0.03em;
            color: #003755;
            margin: 0;
            text-transform: uppercase;
        }
        .escala-avaliacao-experiencia .escala-intro {
            font-size: 0.95rem;
            line-height: 1.5;
            color: #1a1a1a;
            margin-bottom: 1rem;
            padding: 0.65rem 0.75rem;
            background: rgba(255, 255, 255, 0.85);
            border-radius: 8px;
            border: 1px dashed rgba(0, 55, 85, 0.25);
        }
        .escala-avaliacao-experiencia .escala-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 0.65rem;
            padding: 0.5rem 0.5rem 0.5rem 0.35rem;
            border-radius: 8px;
            transition: background 0.2s ease;
        }
        .escala-avaliacao-experiencia .escala-item:last-child {
            margin-bottom: 0;
        }
        .escala-avaliacao-experiencia .escala-item:hover {
            background: rgba(0, 55, 85, 0.04);
        }
        .escala-avaliacao-experiencia .nota-badge {
            flex-shrink: 0;
            min-width: 2rem;
            height: 2rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 0.95rem;
            color: #fff;
            background: linear-gradient(145deg, #003755, #0d4a66);
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 55, 85, 0.35);
        }
        .escala-avaliacao-experiencia .nota-badge.nota-5 { background: linear-gradient(145deg, #0d6e4a, #1a9966); }
        .escala-avaliacao-experiencia .nota-badge.nota-4 { background: linear-gradient(145deg, #0d7a5c, #1a9e72); }
        .escala-avaliacao-experiencia .nota-badge.nota-3 { background: linear-gradient(145deg, #c9a227, #d4af37); color: #1a1a1a; box-shadow: 0 2px 6px rgba(201, 162, 39, 0.4); }
        .escala-avaliacao-experiencia .nota-badge.nota-2 { background: linear-gradient(145deg, #c45c26, #d97736); }
        .escala-avaliacao-experiencia .nota-badge.nota-1 { background: linear-gradient(145deg, #a83232, #c44545); }
        .escala-avaliacao-experiencia .escala-texto {
            font-size: 0.9rem;
            line-height: 1.5;
            color: #2c3e50;
            padding-top: 2px;
        }
    </style>
</head>
<body>
    <div class="card-container">
        <!-- Header -->
        <div class="header-card">
            <div class="header-logo">
                @if(!empty($logo))
                    <img src="{{ $logo }}" alt="Logo da empresa" class="header-logo-img">
                @else
                    <i class="fas fa-clipboard-check"></i>
                @endif
            </div>
            <h2 class="header-title">Avaliação de Experiência</h2>
            <p class="header-subtitle">{{ $numero_avaliacao }}ª Avaliação de Experiência</p>
            
            <!-- Informações do Colaborador -->
            <div class="info-colaborador">
                <h5><i class="fas fa-user"></i> Informações do Colaborador</h5>
                <div class="info-row">
                    <span class="info-label">Nome:</span>
                    <span class="info-value">{{ $colaborador->nome }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">CPF:</span>
                    <span class="info-value">{{ $colaborador->cpf }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Cargo:</span>
                    <span class="info-value">{{ $admissao->cargo ?? 'Não informado' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Função:</span>
                    <span class="info-value">{{ $admissao->funcao ?? 'Não informado' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Centro de Custo:</span>
                    <span class="info-value">{{ $centro_custo }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Data de Admissão:</span>
                    <span class="info-value">{{ $admissao->data_admissao }}</span>
                </div>
                
                @if($expiracao)
                <div class="text-center">
                    <span class="expiracao-badge">
                        <i class="fas fa-clock"></i> Link válido até: {{ \Carbon\Carbon::parse($expiracao)->format('d/m/Y H:i') }}
                    </span>
                </div>
                @endif
            </div>
        </div>

        <!-- Formulário -->
        <div class="form-card">
            @if(session('error'))
            <div class="alert alert-danger alert-custom">
                <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
            </div>
            @endif

            @if ($errors->any())
            <div class="alert alert-danger alert-custom">
                <h6><i class="fas fa-exclamation-circle"></i> Atenção aos seguintes erros:</h6>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('avaliacao.publica.salvar', ['token' => $token]) }}" id="formAvaliacao">
                @csrf
                @php
                    $dicasNota = [
                        1 => 'Muito abaixo',
                        2 => 'Abaixo',
                        3 => 'Atingiu',
                        4 => 'Superou',
                        5 => 'Superou muito',
                    ];
                    $tippyNota = [
                        5 => 'Superou muito as expectativas: É percebido por outras áreas/pessoas como alguém com uma atuação excepcional, modelo de referência.',
                        4 => 'Superou as expectativas: Atuação melhor que o esperado com alto padrão de qualidade.',
                        3 => 'Atingiu as expectativas: Atuação adequada ao esperado (satisfatório), atende os padrões de qualidade e produtividade.',
                        2 => 'Abaixo das expectativas: Atuação abaixo do esperado (precisa de desenvolvimento).',
                        1 => 'Muito abaixo das expectativas: Atuação não aceitável, desempenho muito abaixo do que é esperado para a função.',
                    ];
                @endphp

                <div class="mb-4">
                    <h4 class="mb-3"><i class="fas fa-star"></i> Avaliação de Desempenho</h4>
                    <p class="text-muted mb-3">Por favor, avalie cada item abaixo atribuindo uma nota de 1 a 5, conforme a escala:</p>

                    <div class="escala-avaliacao-experiencia" role="region" aria-label="Escala de avaliação de 1 a 5">
                        <div class="escala-cabecalho">
                            <i class="fas fa-chart-bar" aria-hidden="true"></i>
                            <div class="escala-titulo">Escala de avaliação</div>
                        </div>
                        <p class="escala-intro"><strong>Para esta avaliação, considere as atribuições básicas abaixo, conforme as seguintes notas:</strong></p>
                        <div class="escala-item">
                            <span class="nota-badge nota-5" title="Nota 5">5</span>
                            <span class="escala-texto"><strong>Superou muito as expectativas:</strong> É percebido por outras áreas/pessoas como alguém com uma atuação excepcional, modelo de referência</span>
                        </div>
                        <div class="escala-item">
                            <span class="nota-badge nota-4" title="Nota 4">4</span>
                            <span class="escala-texto"><strong>Superou as expectativas:</strong> Atuação melhor que o esperado com alto padrão de qualidade</span>
                        </div>
                        <div class="escala-item">
                            <span class="nota-badge nota-3" title="Nota 3">3</span>
                            <span class="escala-texto"><strong>Atingiu as expectativas:</strong> Atuação adequada ao esperado (satisfatório), atende os padrões de qualidade e produtividade</span>
                        </div>
                        <div class="escala-item">
                            <span class="nota-badge nota-2" title="Nota 2">2</span>
                            <span class="escala-texto"><strong>Abaixo das expectativas:</strong> Atuação abaixo do esperado (precisa de desenvolvimento)</span>
                        </div>
                        <div class="escala-item">
                            <span class="nota-badge nota-1" title="Nota 1">1</span>
                            <span class="escala-texto"><strong>Muito abaixo das expectativas:</strong> Atuação não aceitável, desempenho muito abaixo do que é esperado para a função</span>
                        </div>
                    </div>
                </div>

                <!-- Perguntas -->
                @foreach($perguntas as $perguntaIndex => $pergunta)
                <div class="pergunta-item">
                    <div class="d-flex align-items-start mb-3">
                        <span class="pergunta-numero">{{ $pergunta->id }}</span>
                        <label class="pergunta-texto mb-0">{{ $pergunta->pergunta }}</label>
                    </div>
                    
                    <div class="nota-options">
                        @for($i = 1; $i <= 5; $i++)
                        <div class="nota-option">
                            <input
                                type="radio"
                                name="perguntas[{{ $perguntaIndex }}][nota]"
                                id="pergunta_{{ $pergunta->id }}_nota_{{ $i }}"
                                value="{{ $i }}"
                                {{ old("perguntas.{$perguntaIndex}.nota") == $i ? 'checked' : '' }}>
                            <label
                                class="nota-btn nota-btn-{{ $i }} nota-btn-tippy"
                                for="pergunta_{{ $pergunta->id }}_nota_{{ $i }}"
                                data-tippy-content="{{ $tippyNota[$i] }}"
                            >
                                <span class="nota-btn-num">{{ $i }}</span>
                                <span class="nota-btn-hint">{{ $dicasNota[$i] }}</span>
                            </label>
                        </div>
                        @endfor
                    </div>
                    
                    <small class="text-danger d-none nota-error mt-2">
                        <i class="fas fa-exclamation-circle"></i> Selecione uma nota para esta pergunta.
                    </small>

                    <input type="hidden" name="perguntas[{{ $perguntaIndex }}][id]" value="{{ $pergunta->id }}">
                    
                    @error("perguntas.{$perguntaIndex}.nota")
                    <small class="text-danger d-block mt-2">
                        <i class="fas fa-exclamation-circle"></i> {{ $message }}
                    </small>
                    @enderror
                </div>
                @endforeach

                @if(!$ehGestorCentroCusto)
                    <!-- Gestor Imediato -->
                    <div class="form-group mt-4">
                        <label for="gestor_imediato">
                            <i class="fas fa-user-tie"></i> <strong>Gestor Imediato *</strong>
                        </label>
                        <input 
                            type="text" 
                            class="form-control @error('gestor_imediato') is-invalid @enderror" 
                            id="gestor_imediato" 
                            name="gestor_imediato" 
                            value="{{ old('gestor_imediato') }}"
                            placeholder="Digite o nome do gestor imediato"
                            required>
                        @error('gestor_imediato')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @endif

                <!-- Observação -->
                <div class="observacao-section">
                    <label for="observacao">
                        <i class="fas fa-comment-dots"></i> <strong>Observações (opcional)</strong>
                    </label>
                    <textarea 
                        class="form-control @error('observacao') is-invalid @enderror" 
                        id="observacao" 
                        name="observacao" 
                        rows="4"
                        placeholder="Digite aqui observações adicionais sobre a avaliação...">{{ old('observacao') }}</textarea>
                    @error('observacao')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Definição sobre o colaborador -->
                <div class="form-group mt-4 definicao-contrato-section">
                    <div class="definicao-contrato-box @error('definicao_contrato') definicao-contrato-erro @enderror">
                        <span class="definicao-label-titulo" id="definicao-contrato-legenda">
                            <i class="fas fa-gavel" aria-hidden="true"></i>
                            <strong>Definição sobre o colaborador *</strong>
                        </span>
                        <p class="definicao-label-desc mb-0">
                            Indique se o contrato deve ser <strong>prorrogado</strong> ou <strong>encerrado</strong>, conforme a política da empresa.
                        </p>
                        <div class="definicao-opcoes mt-3" role="radiogroup" aria-labelledby="definicao-contrato-legenda">
                            <div class="definicao-opcao">
                                <input type="radio"
                                       class="definicao-input"
                                       id="definicao_prorroga"
                                       name="definicao_contrato"
                                       value="prorroga"
                                       {{ old('definicao_contrato') === 'prorroga' ? 'checked' : '' }}
                                       required>
                                <label class="definicao-card definicao-card--prorroga" for="definicao_prorroga">
                                    <span class="definicao-card-icon" aria-hidden="true"><i class="fas fa-calendar-check"></i></span>
                                    <span class="definicao-card-titulo">Prorroga o contrato</span>
                                    <span class="definicao-card-hint">Renova o vínculo e segue com o colaborador na empresa</span>
                                </label>
                            </div>
                            <div class="definicao-opcao">
                                <input type="radio"
                                       class="definicao-input"
                                       id="definicao_finaliza"
                                       name="definicao_contrato"
                                       value="finaliza"
                                       {{ old('definicao_contrato') === 'finaliza' ? 'checked' : '' }}>
                                <label class="definicao-card definicao-card--finaliza" for="definicao_finaliza">
                                    <span class="definicao-card-icon" aria-hidden="true"><i class="fas fa-times-circle"></i></span>
                                    <span class="definicao-card-titulo">Finaliza o contrato</span>
                                    <span class="definicao-card-hint">Encerra o vínculo neste contrato, sem prorrogação</span>
                                </label>
                            </div>
                        </div>
                        @error('definicao_contrato')
                        <div class="text-danger small mt-3 mb-0"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Botão Submit -->
                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary btn-submit">
                        <i class="fas fa-paper-plane"></i> Enviar Avaliação
                    </button>
                </div>

                <p class="text-center text-muted mt-3 mb-0">
                    <small>* Campos obrigatórios</small>
                </p>
            </form>
        </div>
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

    <!-- jQuery e Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    {{--
      Tippy 6 exige @popperjs/core v2 em window.Popper (applyStyles, etc.).
      O bundle do Bootstrap 4 traz Popper v1; sem o script abaixo o tippy quebra ao inicializar.
    --}}
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://unpkg.com/tippy.js@6/dist/tippy-bundle.umd.min.js"></script>

    <script>
        // Tooltips da escala (notas 1–5)
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Popper === 'undefined' || typeof tippy === 'undefined') {
                return;
            }
            tippy('.nota-btn-tippy[data-tippy-content]', {
                theme: 'mybp-nota',
                maxWidth: 340,
                placement: 'top',
                animation: 'fade',
                duration: [200, 150],
                delay: [100, 40],
                interactive: true,
                appendTo: function () {
                    return document.body;
                },
                trigger: 'mouseenter',
                hideOnClick: true,
                touch: ['hold', 450],
                zIndex: 1080
            });
        });

        // Validação antes de enviar
        document.getElementById('formAvaliacao').addEventListener('submit', function(e) {
            let allAnswered = true;
            const perguntas = document.querySelectorAll('.pergunta-item');
            let firstError = null;

            perguntas.forEach(function(pergunta) {
                const radios = pergunta.querySelectorAll('input[type="radio"]');
                const hasChecked = Array.from(radios).some(radio => radio.checked);
                const errorEl = pergunta.querySelector('.nota-error');

                if (!hasChecked) {
                    allAnswered = false;
                    pergunta.style.border = '2px solid #dc3545';
                    if (errorEl) errorEl.classList.remove('d-none');
                    if (!firstError) firstError = pergunta;
                } else {
                    pergunta.style.border = 'none';
                    if (errorEl) errorEl.classList.add('d-none');
                }
            });

            var definicaoChecked = document.querySelector('input[name="definicao_contrato"]:checked');
            if (!definicaoChecked) {
                e.preventDefault();
                var sec = document.querySelector('.definicao-contrato-section');
                if (sec) {
                    sec.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    sec.classList.add('border-danger');
                }
                return;
            }
            document.querySelector('.definicao-contrato-section') && document.querySelector('.definicao-contrato-section').classList.remove('border-danger');

            if (!allAnswered) {
                e.preventDefault();
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    const firstLabel = firstError.querySelector('.nota-option label');
                    if (firstLabel) firstLabel.focus();
                }
            }
        });
        
        // Remove borda vermelha ao selecionar nota
        document.querySelectorAll('.pergunta-item input[type="radio"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                const pergunta = this.closest('.pergunta-item');
                if (pergunta) {
                    pergunta.style.border = 'none';
                    const errorEl = pergunta.querySelector('.nota-error');
                    if (errorEl) errorEl.classList.add('d-none');
                }
            });
        });

        document.querySelectorAll('input[name="definicao_contrato"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                var sec = document.querySelector('.definicao-contrato-section');
                if (sec) sec.classList.remove('border-danger');
            });
        });
    </script>
</body>
</html>

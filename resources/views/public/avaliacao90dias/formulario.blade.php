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
            min-width: 60px;
        }
        
        .nota-option input[type="radio"] {
            display: none;
        }
        
        .nota-option label {
            display: block;
            padding: 12px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            color: #6c757d;
            background: white;
        }
        
        .nota-option input[type="radio"]:checked + label {
            background: #003755;
            color: white;
            border-color: #003755;
            transform: scale(1.05);
        }
        
        .nota-option label:hover {
            border-color: #003755;
            color: #003755;
        }
        
        .observacao-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 30px;
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
                
                <div class="mb-4">
                    <h4 class="mb-3"><i class="fas fa-star"></i> Avaliação de Desempenho</h4>
                    <p class="text-muted">Por favor, avalie cada item abaixo atribuindo uma nota de 1 a 5:</p>
                </div>

                <!-- Perguntas -->
                @foreach($perguntas as $pergunta)
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
                                name="perguntas[{{ $loop->index }}][nota]"
                                id="pergunta_{{ $pergunta->id }}_nota_{{ $i }}"
                                value="{{ $i }}"
                                {{ old("perguntas.{$loop->index}.nota") == $i ? 'checked' : '' }}>
                            <label for="pergunta_{{ $pergunta->id }}_nota_{{ $i }}">
                                {{ $i }}
                            </label>
                        </div>
                        @endfor
                    </div>
                    
                    <small class="text-danger d-none nota-error mt-2">
                        <i class="fas fa-exclamation-circle"></i> Selecione uma nota para esta pergunta.
                    </small>

                    <input type="hidden" name="perguntas[{{ $loop->index }}][id]" value="{{ $pergunta->id }}">
                    
                    @error("perguntas.{$loop->index}.nota")
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
                    <label class="d-block">
                        <i class="fas fa-gavel"></i> <strong>Definição sobre o colaborador *</strong>
                    </label>
                    <div class="d-flex flex-wrap gap-3 mt-2">
                        <div class="custom-control custom-radio">
                            <input type="radio"
                                   class="custom-control-input @error('definicao_contrato') is-invalid @enderror"
                                   id="definicao_prorroga"
                                   name="definicao_contrato"
                                   value="prorroga"
                                   {{ old('definicao_contrato') === 'prorroga' ? 'checked' : '' }}
                                   required>
                            <label class="custom-control-label" for="definicao_prorroga">Prorroga o contrato</label>
                        </div>
                        <div class="custom-control custom-radio">
                            <input type="radio"
                                   class="custom-control-input @error('definicao_contrato') is-invalid @enderror"
                                   id="definicao_finaliza"
                                   name="definicao_contrato"
                                   value="finaliza"
                                   {{ old('definicao_contrato') === 'finaliza' ? 'checked' : '' }}>
                            <label class="custom-control-label" for="definicao_finaliza">Finaliza o contrato</label>
                        </div>
                    </div>
                    @error('definicao_contrato')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
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
    
    <script>
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
        document.querySelectorAll('input[type="radio"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                const pergunta = this.closest('.pergunta-item');
                if (pergunta) {
                    pergunta.style.border = 'none';
                    const errorEl = pergunta.querySelector('.nota-error');
                    if (errorEl) errorEl.classList.add('d-none');
                }
            });
        });
    </script>
</body>
</html>

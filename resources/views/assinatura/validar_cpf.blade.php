@extends('assinatura.layout')

@section('title', 'Validar acesso')

@section('content')
    <div class="card assin-card">
        <div class="card-header">
            <h5 class="mb-0">Validação de segurança</h5>
            <p class="assin-subtitle">Etapa 1 de 2: confirmação de identidade</p>
        </div>
        <div class="card-body">
            <p class="text-muted mb-2">Antes de acessar o documento, confirme seu CPF para receber um código de segurança por e-mail.</p>
            <div class="assin-origin mb-3">
                A empresa <strong>{{ $empresaNome }}</strong> enviou o documento <strong>{{ $nomeDocumento }}</strong> para ser assinado digitalmente.
            </div>
            <div class="mb-3">
                <span class="assin-chip">{{ $signatario->nome }}</span>
                <span class="assin-chip">{{ $signatario->email }}</span>
            </div>

            <form action="{{ $validarCpfUrl }}" method="POST" class="assin-section">
                @csrf
                <div class="assin-section-title">Informe seu CPF</div>
                <div class="mb-3">
                    <label class="form-label">CPF</label>
                    <input type="text" name="cpf" class="form-control cpf-mask" placeholder="000.000.000-00" value="{{ old('cpf') }}" maxlength="14" required onblur="valida_cpf_vazio(this)">
                </div>

                <button type="submit" class="btn btn-primary">Enviar código por e-mail</button>
                <a href="{{ $codigoUrl }}" class="btn btn-link">Já recebi o código</a>
            </form>
        </div>
    </div>
@endsection

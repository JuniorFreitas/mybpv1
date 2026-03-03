@extends('assinatura.layout')

@section('title', 'Código de verificação')

@section('content')
    <div class="card assin-card">
        <div class="card-header">
            <h5 class="mb-0">Confirme o código</h5>
            <p class="assin-subtitle">Etapa 2 de 2: confirmação por e-mail</p>
        </div>
        <div class="card-body">
            <p class="text-muted mb-2">Enviamos um código de até 8 caracteres para o e-mail do signatário.</p>
            <div class="assin-origin mb-3">
                A empresa <strong>{{ $empresaNome }}</strong> enviou o documento <strong>{{ $nomeDocumento }}</strong> para ser assinado digitalmente.
            </div>
            <div class="mb-3">
                <span class="assin-chip">{{ $signatario->nome }}</span>
                <span class="assin-chip">{{ $signatario->email }}</span>
            </div>

            <form action="{{ $validarCodigoUrl }}" method="POST" class="assin-section mb-3">
                @csrf
                <div class="assin-section-title">Digite o código recebido</div>
                <div class="mb-3">
                    <label class="form-label">Código</label>
                    <input type="text" name="codigo" class="form-control text-uppercase" value="{{ old('codigo') }}" maxlength="8" required>
                </div>
                <button type="submit" class="btn btn-success">Validar e acessar documento</button>
            </form>

            <a href="{{ $voltarCpfUrl }}" class="btn btn-link p-0">Informar CPF novamente e reenviar código</a>
        </div>
    </div>
@endsection

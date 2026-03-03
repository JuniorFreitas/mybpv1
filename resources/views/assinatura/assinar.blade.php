@extends('assinatura.layout')

@section('title', 'Assinar documento')

@section('content')
<div class="card assin-card">
    <div class="card-header">
        <h5 class="mb-0">Assinatura digital</h5>
        <p class="assin-subtitle">Validade jurídica com registro de evidências</p>
    </div>
    <div class="card-body">
        <p class="text-muted mb-3">Você foi designado(a) como signatário(a) do documento abaixo.</p>
        <div class="assin-origin mb-3">
            A empresa <strong>{{ $empresaNome }}</strong> enviou o documento <strong>{{ $nomeDocumento }}</strong> para ser assinado digitalmente.
        </div>
        <div class="mb-2">
            <span class="assin-chip">{{ $signatario->nome }} &lt;{{ $signatario->email }}&gt;</span>
            <span class="assin-chip">{{ $nomeDocumento }}</span>
        </div>

        <div class="mt-3">
            <div class="assin-section-title">Visualização do documento</div>
            <div class="assin-pdf-wrap" style="min-height: 400px;">
                <iframe src="{{ $pdfUrl }}" title="Documento PDF" width="100%" height="500" class="border-0"></iframe>
            </div>
        </div>

        <form id="form-assinar" action="{{ $assinarUrl }}" method="POST" class="mt-4">
            @csrf
            <div class="assin-section">
                <div class="assin-section-title">Confirmação da assinatura</div>
                <div class="mb-3">
                    <label class="form-label">CPF (opcional, para reforço jurídico)</label>
                    <input type="text" name="cpf" class="form-control cpf-mask" placeholder="000.000.000-00" value="{{ old('cpf', $signatario->cpf) }}" maxlength="14" onblur="if(this.value){valida_cpf_vazio(this);}">
                    @error('cpf')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" name="consentimento" id="consentimento" value="1" required {{ old('consentimento') ? 'checked' : '' }}>
                    <label class="form-check-label" for="consentimento">Li e concordo com o conteúdo do documento (obrigatório para assinatura com validade jurídica).</label>
                </div>
                @error('consentimento')
                <div class="text-danger small mb-2">{{ $message }}</div>
                @enderror
                <div class="d-flex gap-2 flex-wrap">
                    <button type="submit" class="btn btn-success mr-2" id="btn-assinar">Assinar documento</button>
                    <button type="button" class="btn btn-outline-secondary" data-toggle="modal" data-target="#modalRecusar">Recusar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalRecusar" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ $recusarUrl }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Recusar documento</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Motivo (opcional)</label>
                    <textarea name="motivo" class="form-control" rows="3"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Confirmar recusa</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('form-assinar').addEventListener('submit', function() {
        document.getElementById('btn-assinar').disabled = true;
    });
</script>
@endpush
